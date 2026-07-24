<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for ticket-to-user assignment operations.
 */
class AssignmentRepository extends Repository
{
    /**
     * Get all assignments (with teacher names) for a given ticket.
     *
     * @param int $assignment_ticket
     * @return Assignment[]
     */
    public function getAssignedUsersToTicket($assignment_ticket): array
    {
        $rows = $this->database->select(
            "SELECT assignments.*, teachers.teacher_name FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = :assignment_ticket",
            [":assignment_ticket" => $assignment_ticket]
        );
        return array_map(fn($r) => new Assignment($r), $rows);
    }

    /**
     * Get all (open + closed) tickets assigned to a user, as raw arrays.
     *
     * @param int  $assignment_user
     * @param string $extra_params Extra SQL to append (e.g. "AND ticket_is_open = TRUE")
     * @return array[]
     */
    public function getAssignedTicketsToUser($assignment_user, $extra_params = ""): array
    {
        return $this->database->select(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_user = :assignment_user " . $extra_params,
            [":assignment_user" => $assignment_user]
        );
    }

    /**
     * Get open tickets assigned to a user, returned as Ticket objects.
     *
     * @param int    $assignment_user
     * @param string $extra_params    Extra SQL to append
     * @return Ticket[]
     */
    public function getActiveAssignedTicketsToUser($assignment_user, $extra_params = ""): array
    {
        $assigneeIds = $this->database->groupConcat("assignment_user", ",");
        $assigneeNames = $this->database->groupConcat("teacher_name", ", ");
        $rows = $this->database->select(
            "SELECT tickets.*, teachers.teacher_name, categories.category_name, rooms.room_name, priorities.priority_name, priorities.priority_weight, priorities.priority_color, (SELECT $assigneeIds FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_ids, (SELECT $assigneeNames FROM assignments INNER JOIN teachers ON assignment_user = teacher_id WHERE assignment_ticket = ticket_id) AS assignee_names, (SELECT COUNT(*) FROM assignments WHERE assignment_ticket = ticket_id) AS assignee_count FROM assignments INNER JOIN tickets ON assignment_ticket = ticket_id INNER JOIN teachers ON ticket_origin = teacher_id LEFT JOIN categories ON ticket_category = category_id LEFT JOIN rooms ON ticket_room = room_id LEFT JOIN priorities ON ticket_priority = priority_id WHERE assignment_user = :assignment_user AND ticket_is_open = 1 " . $extra_params,
            [":assignment_user" => $assignment_user]
        );
        return array_map(fn($r) => new Ticket($r), $rows);
    }

    /**
     * Assign a user to a ticket (skips silently if already assigned).
     *
     * @param int $assignment_ticket
     * @param int $assignment_user
     * @return false|string The last insert ID, or false on failure.
     */
    public function addAssignment($assignment_ticket, $assignment_user): false|string
    {
        return $this->database->insertIgnore(
            "INSERT IGNORE INTO assignments (assignment_ticket, assignment_user) VALUES (:assignment_ticket, :assignment_user)",
            [":assignment_ticket" => $assignment_ticket, ":assignment_user" => $assignment_user]
        );
    }

    /**
     * Remove a user's assignment from a ticket.
     *
     * @param int $assignment_ticket
     * @param int $assignment_user
     */
    public function deleteAssignment($assignment_ticket, $assignment_user): void
    {
        $this->database->deleteIgnore(
            "DELETE IGNORE FROM assignments WHERE assignment_ticket = :assignment_ticket AND assignment_user = :assignment_user",
            [":assignment_ticket" => $assignment_ticket, ":assignment_user" => $assignment_user]
        );
    }
}
