<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for all ticket-related queries.
 *
 * Handles creation, status changes, statistics, paginated listing
 * and the complex joined queries that populate Ticket DTOs with
 * teacher/category/room/priority/assignment data.
 */
class TicketRepository extends Repository
{
    /** Default number of tickets per page. */
    private const PER_PAGE = 50;

    /**
     * Execute a SELECT and wrap each result row in a Ticket object.
     *
     * @param string $sql
     * @param array  $params
     * @return Ticket[]
     */
    private function ticketRow(string $sql, array $params = []): array
    {
        $rows = $this->database->select($sql, $params);
        return array_map(fn($r) => new Ticket($r), $rows);
    }

    /**
     * Execute a SELECT … LIMIT 1 and return a single Ticket or null.
     *
     * @param string $sql
     * @param array  $params
     * @return ?Ticket
     */
    private function ticketRowOne(string $sql, array $params = []): ?Ticket
    {
        $row = $this->database->selectOne($sql, $params);
        return $row ? new Ticket($row) : null;
    }

    /**
     * Apply LIMIT/OFFSET pagination to a query.
     *
     * @param string   $baseSql  The main SELECT without LIMIT.
     * @param string   $countSql A COUNT(*) equivalent of baseSql.
     * @param array    $params   Bound parameters for both queries.
     * @param int      $page     Current page (1-based).
     * @param int|null $perPage  Items per page (defaults to self::PER_PAGE).
     * @return PaginatedResult
     */
    private function applyPagination(string $baseSql, string $countSql, array $params, int $page, ?int $perPage): PaginatedResult
    {
        $perPage = $perPage ?? self::PER_PAGE;
        $row = $this->database->selectOne($countSql, $params);
        $total = (int)($row["cnt"] ?? 0);
        $offset = ($page - 1) * $perPage;
        $items = $this->ticketRow($baseSql . " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset, $params);
        return new PaginatedResult($items, $total, $page, $perPage);
    }

    /**
     * Create a new ticket.
     *
     * @param int    $origin      FK to teachers.teacher_id.
     * @param int    $category    FK to categories.category_id.
     * @param int    $room        FK to rooms.room_id.
     * @param string $title       Ticket subject.
     * @param string $description Ticket description body.
     * @return false|string The new ticket ID, or false on failure.
     */
    public function addTicket($origin, $category, $room, $title, $description): false|string
    {
        return $this->database->insert(
            "INSERT INTO tickets (ticket_origin, ticket_category, ticket_room, ticket_title, ticket_description) VALUES (:origin, :category, :room, :title, :description)",
            [":origin" => $origin, ":category" => $category, ":room" => $room, ":title" => $title, ":description" => $description]
        );
    }

    /**
     * Get a single ticket by ID with all joined fields.
     *
     * @param int $ticket_id
     * @return ?Ticket
     */
    public function getTicketById($ticket_id): ?Ticket
    {
        return $this->ticketRowOne(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

    /**
     * Get paginated open tickets.
     *
     * @param int      $page
     * @param int|null $perPage
     * @return PaginatedResult
     */
    public function getOpenTickets(int $page = 1, ?int $perPage = null): PaginatedResult
    {
        $base = "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_is_open = TRUE ORDER BY ticket_creation DESC";
        return $this->applyPagination($base, "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = TRUE", [], $page, $perPage);
    }

    /**
     * Get paginated closed tickets.
     *
     * @param int      $page
     * @param int|null $perPage
     * @return PaginatedResult
     */
    public function getClosedTickets(int $page = 1, ?int $perPage = null): PaginatedResult
    {
        $base = "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_is_open = FALSE ORDER BY ticket_creation DESC";
        return $this->applyPagination($base, "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = FALSE", [], $page, $perPage);
    }

    /**
     * Get all open tickets without any assignment.
     *
     * @return Ticket[]
     */
    public function getUnassignedTickets(): array
    {
        return $this->ticketRow(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets LEFT JOIN assignments ON ticket_id = assignment_ticket INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_ticket IS NULL AND ticket_is_open = TRUE ORDER BY ticket_creation DESC"
        );
    }

    /**
     * Get all tickets (open + closed) with basic joined fields.
     *
     * @return Ticket[]
     */
    public function getAllTickets(): array
    {
        return $this->ticketRow(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id ORDER BY ticket_creation DESC"
        );
    }

    /**
     * Mark a ticket as closed.
     *
     * @param int $ticket_id
     */
    public function closeTicket($ticket_id): void
    {
        $this->database->update(
            "UPDATE tickets SET ticket_is_open = FALSE WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

    /**
     * Reopen a closed ticket.
     *
     * @param int $ticket_id
     */
    public function reopenTicket($ticket_id): void
    {
        $this->database->update(
            "UPDATE tickets SET ticket_is_open = TRUE WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

    /**
     * Partially update a ticket — only the keys present in $data are changed.
     *
     * Allowed columns: ticket_category, ticket_room, ticket_priority,
     * ticket_title, ticket_description, ticket_deadline.
     *
     * @param int   $ticket_id
     * @param array $data      Associative array of column => value.
     */
    public function updateTicket($ticket_id, $data): void
    {
        $fields = [];
        $params = [":ticket_id" => $ticket_id];

        $allowed = ["ticket_category", "ticket_room", "ticket_priority", "ticket_title", "ticket_description", "ticket_deadline"];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($fields)) return;

        $this->database->update(
            "UPDATE tickets SET " . implode(", ", $fields) . " WHERE ticket_id = :ticket_id",
            $params
        );
    }

    /**
     * Get aggregate counts (total / open / closed).
     *
     * @return array{total: int, open: int, closed: int}
     */
    public function getCountByStatus(): array
    {
        return $this->database->selectOne(
            "SELECT COUNT(*) AS total, SUM(ticket_is_open) AS open, SUM(CASE WHEN ticket_is_open = FALSE THEN 1 ELSE 0 END) AS closed FROM tickets"
        );
    }

    /**
     * Get the number of open tickets with no assignments.
     *
     * @return int
     */
    public function getCountUnassigned(): int
    {
        $row = $this->database->selectOne(
            "SELECT COUNT(*) AS cnt FROM tickets LEFT JOIN assignments ON ticket_id = assignment_ticket WHERE assignment_ticket IS NULL AND ticket_is_open = TRUE"
        );
        return (int)($row["cnt"] ?? 0);
    }

    /**
     * Get the number of open tickets past their deadline.
     *
     * @return int
     */
    public function getCountOverdue(): int
    {
        $row = $this->database->selectOne(
            "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = TRUE AND ticket_deadline IS NOT NULL AND ticket_deadline < CURDATE()"
        );
        return (int)($row["cnt"] ?? 0);
    }

    /**
     * Get assignment stats for a single user.
     *
     * @param int $user_id
     * @return array{total_assigned: int, active: int, resolved: int}
     */
    public function getUserStats($user_id): array
    {
        return $this->database->selectOne(
            "SELECT COUNT(*) AS total_assigned, SUM(CASE WHEN ticket_is_open = TRUE THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN ticket_is_open = FALSE THEN 1 ELSE 0 END) AS resolved FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id WHERE assignment_user = :user_id",
            [":user_id" => $user_id]
        );
    }

    /**
     * Get total work minutes logged by a user across all tickets.
     *
     * @param int $user_id
     * @return int
     */
    public function getUserTotalWorkMinutes($user_id): int
    {
        $row = $this->database->selectOne(
            "SELECT COALESCE(SUM(work_minutes), 0) AS total FROM works WHERE work_user = :user_id",
            [":user_id" => $user_id]
        );
        return (int)($row["total"] ?? 0);
    }

    /**
     * Get work minutes logged by a user in the current month.
     *
     * @param int $user_id
     * @return int
     *
     * @deprecated Uses MONTH(work_id) which is semantically incorrect;
     *             the works table has no date column.
     */
    public function getUserMonthlyWorkMinutes($user_id): int
    {
        $row = $this->database->selectOne(
            "SELECT COALESCE(SUM(work_minutes), 0) AS total FROM works WHERE work_user = :user_id AND MONTH(work_id) = MONTH(CURDATE()) AND YEAR(work_id) = YEAR(CURDATE())",
            [":user_id" => $user_id]
        );
        return (int)($row["total"] ?? 0);
    }

    /**
     * Get per-month assignment statistics for a user over the last N months.
     *
     * Returns an array with keys: year_month, label, assigned, resolved, still_open.
     *
     * @param int $user_id
     * @param int $months Number of months to look back (default 6).
     * @return array[]
     */
    public function getUserMonthlyStats($user_id, int $months = 6): array
    {
        $months = (int)$months;
        $sql = "SELECT CONCAT(YEAR(a.assignment_creation), '-', LPAD(MONTH(a.assignment_creation), 2, '0')) AS ym, COUNT(*) AS assigned, SUM(CASE WHEN t.ticket_is_open = 1 THEN 1 ELSE 0 END) AS still_open FROM assignments a INNER JOIN tickets t ON a.assignment_ticket = t.ticket_id WHERE a.assignment_user = :user_id AND a.assignment_creation >= DATE_SUB(CURDATE(), INTERVAL $months MONTH) GROUP BY ym ORDER BY ym ASC";
        $rows = $this->database->select($sql, [":user_id" => $user_id]);

        $monthNames = ["01" => "Leden", "02" => "Únor", "03" => "Březen", "04" => "Duben",
                       "05" => "Květen", "06" => "Červen", "07" => "Červenec", "08" => "Srpen",
                       "09" => "Září", "10" => "Říjen", "11" => "Listopad", "12" => "Prosinec"];

        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = date("Y-m", strtotime("-$i months"));
            $found = null;
            foreach ($rows as $r) {
                if ($r["ym"] === $ym) {
                    $found = $r;
                    break;
                }
            }
            $parts = explode("-", $ym);
            $label = $monthNames[$parts[1]] ?? $parts[1] . " " . $parts[0];
            $assigned = $found ? (int)$found["assigned"] : 0;
            $stillOpen = $found ? (int)$found["still_open"] : 0;
            $result[] = [
                "year_month" => $ym,
                "label" => $label . " " . $parts[0],
                "assigned" => $assigned,
                "resolved" => $assigned - $stillOpen,
                "still_open" => $stillOpen,
            ];
        }

        return $result;
    }
}
