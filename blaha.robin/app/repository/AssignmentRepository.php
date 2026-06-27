<?php

class AssignmentRepository extends Repository
{
    public function getAssignedUsersToTicket($assignment_ticket): array
    {
        $rows = $this->database->select(
            "SELECT assignments.*, teachers.teacher_name FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = :assignment_ticket",
            [":assignment_ticket" => $assignment_ticket]
        );
        return array_map(fn($r) => new Assignment($r), $rows);
    }

    public function getAssignedTicketsToUser($assignment_user, $extra_params = ""): array
    {
        return $this->database->select(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_user = :assignment_user " . $extra_params,
            [":assignment_user" => $assignment_user]
        );
    }

    public function getActiveAssignedTicketsToUser($assignment_user, $extra_params = ""): array
    {
        $rows = $this->database->select(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT GROUP_CONCAT(assignment_user SEPARATOR ',') FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT GROUP_CONCAT(teacher_name SEPARATOR ', ') FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_user = :assignment_user AND ticket_is_open = TRUE " . $extra_params,
            [":assignment_user" => $assignment_user]
        );
        return array_map(fn($r) => new Ticket($r), $rows);
    }

    public function addAssignment($assignment_ticket, $assignment_user): false|string
    {
        return $this->database->insert(
            "INSERT IGNORE INTO assignments (assignment_ticket, assignment_user) VALUES (:assignment_ticket, :assignment_user)",
            [":assignment_ticket" => $assignment_ticket, ":assignment_user" => $assignment_user]
        );
    }

    public function deleteAssignment($assignment_ticket, $assignment_user): void
    {
        $this->database->delete(
            "DELETE IGNORE FROM assignments WHERE assignment_ticket = :assignment_ticket AND assignment_user = :assignment_user",
            [":assignment_ticket" => $assignment_ticket, ":assignment_user" => $assignment_user]
        );
    }
}
