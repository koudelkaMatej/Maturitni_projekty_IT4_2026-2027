<?php

class TicketRepository extends Repository
{
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

    public function addTicket($origin, $category, $room, $title, $description): false|string
    {
        return $this->database->insert(
            "INSERT INTO tickets (ticket_origin, ticket_category, ticket_room, ticket_title, ticket_description) VALUES (:origin, :category, :room, :title, :description)",
            [":origin" => $origin, ":category" => $category, ":room" => $room, ":title" => $title, ":description" => $description]
        );
    }

    public function getTicketById($ticket_id): ?Ticket
    {
        return $this->ticketRowOne(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

    public function getOpenTickets(int $page = 1, ?int $perPage = null): PaginatedResult
    {
        $base = "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_is_open = TRUE ORDER BY ticket_creation DESC";
        return $this->applyPagination($base, "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = TRUE", [], $page, $perPage);
    }

    public function getClosedTickets(int $page = 1, ?int $perPage = null): PaginatedResult
    {
        $base = "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE ticket_is_open = FALSE ORDER BY ticket_creation DESC";
        return $this->applyPagination($base, "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = FALSE", [], $page, $perPage);
    }

    public function getUnassignedTickets(): array
    {
        return $this->ticketRow(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM tickets LEFT JOIN assignments ON ticket_id = assignment_ticket INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_ticket IS NULL AND ticket_is_open = TRUE ORDER BY ticket_creation DESC"
        );
    }

    public function getAllTickets(): array
    {
        return $this->ticketRow(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color FROM tickets INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id ORDER BY ticket_creation DESC"
        );
    }

    public function closeTicket($ticket_id): void
    {
        $this->database->update(
            "UPDATE tickets SET ticket_is_open = FALSE WHERE ticket_id = :ticket_id",
            [":ticket_id" => $ticket_id]
        );
    }

    public function reopenTicket($ticket_id): void
    {
        $this->database->update(
            "UPDATE tickets SET ticket_is_open = TRUE WHERE ticket_id = :ticket_id",
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
            "SELECT COUNT(*) AS total, SUM(ticket_is_open) AS open, SUM(CASE WHEN ticket_is_open = FALSE THEN 1 ELSE 0 END) AS closed FROM tickets"
        );
    }

    public function getCountUnassigned(): int
    {
        $row = $this->database->selectOne(
            "SELECT COUNT(*) AS cnt FROM tickets LEFT JOIN assignments ON ticket_id = assignment_ticket WHERE assignment_ticket IS NULL AND ticket_is_open = TRUE"
        );
        return (int)($row["cnt"] ?? 0);
    }

    public function getCountOverdue(): int
    {
        $row = $this->database->selectOne(
            "SELECT COUNT(*) AS cnt FROM tickets WHERE ticket_is_open = TRUE AND ticket_deadline IS NOT NULL AND ticket_deadline < CURDATE()"
        );
        return (int)($row["cnt"] ?? 0);
    }

    public function getUserStats($user_id): array
    {
        return $this->database->selectOne(
            "SELECT COUNT(*) AS total_assigned, SUM(CASE WHEN ticket_is_open = TRUE THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN ticket_is_open = FALSE THEN 1 ELSE 0 END) AS resolved FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id WHERE assignment_user = :user_id",
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
        $row = $this->database->selectOne(
            "SELECT COALESCE(SUM(work_minutes), 0) AS total FROM works WHERE work_user = :user_id AND MONTH(work_id) = MONTH(CURDATE()) AND YEAR(work_id) = YEAR(CURDATE())",
            [":user_id" => $user_id]
        );
        return (int)($row["total"] ?? 0);
    }

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
