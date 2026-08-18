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

    private function ticketRow(string $sql, array $params = []): array
    {
        $rows = $this->database->select($sql, $params);
        return array_map(fn($r) => new Ticket($r), $rows);
    }

    private function ticketRowOne(string $sql, array $params = []): ?Ticket
    {
        $row = $this->database->selectOne($sql, $params);
        return $row ? new Ticket($row) : null;
    }

    private function applyPagination(string $baseSql, string $countSql, array $params, int $page, ?int $perPage): PaginatedResult
    {
        $perPage = $perPage ?? self::PER_PAGE;
        $row = $this->database->selectOne($countSql, $params);
        $total = (int)($row["cnt"] ?? 0);
        $offset = ($page - 1) * $perPage;
        $items = $this->ticketRow($baseSql . " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset, $params);
        return new PaginatedResult($items, $total, $page, $perPage);
    }

    private function ticketSelectWithAssignees(): string
    {
        $assigneeIds = $this->database->groupConcat("assignment_user", ",");
        $assigneeNames = $this->database->groupConcat("teacher_name", ", ");
        return "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name,
                priorities.priority_name, priorities.priority_weight, priorities.priority_color,
                (SELECT $assigneeIds FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids,
                (SELECT $assigneeNames FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names,
                (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count";
    }

    public function addTicket($origin, $category, $room, $title, $description): false|string
    {
        return $this->database->insert(
            "INSERT INTO tickets (ticket_origin, ticket_category, ticket_room, ticket_title, ticket_description) VALUES (:origin, :category, :room, :title, :description)",
            [":origin" => $origin, ":category" => $category, ":room" => $room, ":title" => $title, ":description" => $description]
        );
    }

    public function getTicketById($ticket_id): ?Ticket
    {
        $base = $this->ticketSelectWithAssignees();
        return $this->ticketRowOne(
            $base . " FROM tickets LEFT JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

    public function getOpenTickets(int $page = 1, ?int $perPage = null): PaginatedResult
    {
        $base = $this->ticketSelectWithAssignees() . " FROM tickets LEFT JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_is_open = 1 ORDER BY ticket_creation DESC";
        return $this->applyPagination($base, "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = 1", [], $page, $perPage);
    }

    public function getClosedTickets(int $page = 1, ?int $perPage = null): PaginatedResult
    {
        $base = $this->ticketSelectWithAssignees() . " FROM tickets LEFT JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_is_open = 0 ORDER BY ticket_creation DESC";
        return $this->applyPagination($base, "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = 0", [], $page, $perPage);
    }

    public function getUnassignedTickets(): array
    {
        $base = $this->ticketSelectWithAssignees();
        return $this->ticketRow(
            $base . " FROM tickets LEFT JOIN assignments ON ticket_id = assignment_ticket LEFT JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_ticket IS NULL AND ticket_is_open = 1 ORDER BY ticket_creation DESC"
        );
    }

    public function getAllTickets(): array
    {
        return $this->ticketRow(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color FROM tickets LEFT JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id ORDER BY ticket_creation DESC"
        );
    }

    public function closeTicket($ticket_id): void
    {
        $this->database->update(
            "UPDATE tickets SET ticket_is_open = 0 WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

    public function reopenTicket($ticket_id): void
    {
        $this->database->update(
            "UPDATE tickets SET ticket_is_open = 1 WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

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

    public function getCountByStatus(): array
    {
        return $this->database->selectOne(
            "SELECT COUNT(*) AS total, SUM(ticket_is_open) AS open, SUM(CASE WHEN ticket_is_open = 0 THEN 1 ELSE 0 END) AS closed FROM tickets"
        );
    }

    public function getCountUnassigned(): int
    {
        $row = $this->database->selectOne(
            "SELECT COUNT(*) AS cnt FROM tickets LEFT JOIN assignments ON ticket_id = assignment_ticket WHERE assignment_ticket IS NULL AND ticket_is_open = 1"
        );
        return (int)($row["cnt"] ?? 0);
    }

    public function getCountOverdue(): int
    {
        $row = $this->database->selectOne(
            "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = 1 AND ticket_deadline IS NOT NULL AND ticket_deadline < " . $this->database->curdate()
        );
        return (int)($row["cnt"] ?? 0);
    }

    public function getUserStats($user_id): array
    {
        return $this->database->selectOne(
            "SELECT COUNT(*) AS total_assigned, SUM(CASE WHEN ticket_is_open = 1 THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN ticket_is_open = 0 THEN 1 ELSE 0 END) AS resolved FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id WHERE assignment_user = :user_id",
            [":user_id" => $user_id]
        );
    }

    public function getUserTotalWorkMinutes($user_id): int
    {
        $row = $this->database->selectOne(
            "SELECT COALESCE(SUM(work_minutes), 0) AS total FROM works WHERE work_user = :user_id",
            [":user_id" => $user_id]
        );
        return (int)($row["total"] ?? 0);
    }

    public function getUserMonthlyWorkMinutes($user_id): int
    {
        $monthExpr = $this->database->month('work_creation');
        $yearExpr = $this->database->year('work_creation');
        $row = $this->database->selectOne(
            "SELECT COALESCE(SUM(work_minutes), 0) AS total FROM works WHERE work_user = :user_id AND $monthExpr = :month AND $yearExpr = :year",
            [":user_id" => $user_id, ":month" => (int)date("n"), ":year" => (int)date("Y")]
        );
        return (int)($row["total"] ?? 0);
    }

    /**
     * Get the count of tickets assigned to a user, broken down by category.
     *
     * @param int $user_id
     * @return array[] Each row: category_name (nullable), cnt.
     */
    public function getUserCategoryBreakdown($user_id): array
    {
        return $this->database->select(
            "SELECT categories.category_name, COUNT(*) AS cnt FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id LEFT JOIN categories ON ticket_category = category_id WHERE assignment_user = :user_id GROUP BY ticket_category ORDER BY cnt DESC",
            [":user_id" => $user_id]
        );
    }

    /**
     * Get the count of tickets assigned to a user, broken down by priority.
     *
     * @param int $user_id
     * @return array[] Each row: priority_name (nullable), priority_color, priority_weight, cnt.
     */
    public function getUserPriorityBreakdown($user_id): array
    {
        return $this->database->select(
            "SELECT priorities.priority_name, priorities.priority_color, priorities.priority_weight, COUNT(*) AS cnt FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_user = :user_id GROUP BY ticket_priority ORDER BY priorities.priority_weight DESC",
            [":user_id" => $user_id]
        );
    }

    public function getUserMonthlyStats($user_id, int $months = 6): array
    {
        $months = (int)$months;
        $monthExpr = $this->database->month('a.assignment_creation');
        $yearExpr = $this->database->year('a.assignment_creation');
        $lpadExpr = $this->database->lpad($this->database->month('a.assignment_creation'), 2, '0');
        $ymExpr = $this->database->concat($yearExpr, "'-'", $lpadExpr);
        $dateSub = $this->database->dateSub($this->database->curdate(), $months, 'MONTH');

        $sql = "SELECT $ymExpr AS ym, COUNT(*) AS assigned, SUM(CASE WHEN t.ticket_is_open = 1 THEN 1 ELSE 0 END) AS still_open FROM assignments a INNER JOIN tickets t ON a.assignment_ticket = t.ticket_id WHERE a.assignment_user = :user_id AND a.assignment_creation >= $dateSub GROUP BY ym ORDER BY ym ASC";
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
