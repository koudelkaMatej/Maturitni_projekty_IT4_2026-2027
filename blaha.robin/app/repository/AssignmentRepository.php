<?php

class AssignmentRepository extends Repository
{
    public function getAssignedUsersToTicket($assignment_ticket): array
    {
        return $this->database->select(
            "SELECT * FROM assignments INNER JOIN users on assignment_user = user_id INNER JOIN teachers on user_id = teacher_id WHERE assignment_ticket = :assignment_ticket",
            [
                ":assignment_ticket" => $assignment_ticket,
            ]
        );
    }

    public function getAssignedTicketsToUser($assignment_user, $extra_params = ""): array
    {
        return $this->database->select(
            "SELECT * FROM assignments INNER JOIN tickets on assignment_ticket = ticket_id INNER JOIN teachers on ticket_origin = teacher_id INNER JOIN categories on ticket_category = category_id INNER JOIN rooms on ticket_room = room_id INNER JOIN priorities on ticket_priority = priority_id WHERE assignment_user = :assignment_user " . $extra_params,
            [
                ":assignment_user" => $assignment_user,
            ]
        );
    }

    public function getActiveAssignedTicketsToUser($assignment_user, $extra_params = ""): array
    {
        return $this->database->select(
            "SELECT * FROM assignments INNER JOIN tickets on assignment_ticket = ticket_id INNER JOIN teachers on ticket_origin = teacher_id INNER JOIN categories on ticket_category = category_id INNER JOIN rooms on ticket_room = room_id INNER JOIN priorities on ticket_priority = priority_id WHERE assignment_user = :assignment_user AND ticket_is_open = TRUE " . $extra_params,
            [
                ":assignment_user" => $assignment_user,
            ]
        );
    }

    public function addAssignment($assignment_ticket, $assignment_user): false|string
    {
        return $this->database->insert(
            "INSERT IGNORE INTO assignments (assignment_ticket, assignment_user) VALUES (:assignment_ticket, :assignment_user)",
            [
                ":assignment_ticket" => $assignment_ticket,
                ":assignment_user" => $assignment_user,
            ]
        );
    }

    public function deleteAssignment($assignment_ticket, $assignment_user): void
    {
        $this->database->delete(
            "DELETE IGNORE FROM assignments WHERE assignment_ticket = :assignment_ticket AND assignment_user = :assignment_user",
            [
                ":assignment_ticket" => $assignment_ticket,
                ":assignment_user" => $assignment_user,
            ]
        );
    }
}