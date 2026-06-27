<?php

class WorkRepository extends Repository
{
    public function getWorkById($work_id): ?Work
    {
        $row = $this->database->selectOne(
            "SELECT * FROM works WHERE work_id = :work_id",
            [":work_id" => $work_id]
        );
        return $row ? new Work($row) : null;
    }

    public function getWorksByTicket($work_ticket): array
    {
        $rows = $this->database->select(
            "SELECT * FROM works WHERE work_ticket = :work_ticket",
            [":work_ticket" => $work_ticket]
        );
        return array_map(fn($r) => new Work($r), $rows);
    }

    public function getWorksByTicketWithUsers($work_ticket): array
    {
        $rows = $this->database->select(
            "SELECT works.*, teachers.teacher_name FROM works LEFT JOIN users ON work_user = user_id LEFT JOIN teachers ON user_id = teacher_id WHERE work_ticket = :work_ticket ORDER BY work_id DESC",
            [":work_ticket" => $work_ticket]
        );
        return array_map(fn($r) => new Work($r), $rows);
    }

    public function getWorksByUser($work_user): array
    {
        $rows = $this->database->select(
            "SELECT * FROM works WHERE work_user = :work_user",
            [":work_user" => $work_user]
        );
        return array_map(fn($r) => new Work($r), $rows);
    }

    public function addWork($work_ticket, $work_user, $work_minutes, $work_description): false|string
    {
        return $this->database->insert(
            "INSERT INTO works (work_ticket, work_user, work_minutes, work_description) VALUES (:work_ticket, :work_user, :work_minutes, :work_description)",
            [":work_ticket" => $work_ticket, ":work_user" => $work_user, ":work_minutes" => $work_minutes, ":work_description" => $work_description]
        );
    }

    public function updateWork($work_id, $work_minutes, $work_description): void
    {
        $this->database->update(
            "UPDATE works SET work_minutes = :work_minutes, work_description = :work_description WHERE work_id = :work_id",
            [":work_id" => $work_id, ":work_minutes" => $work_minutes, ":work_description" => $work_description]
        );
    }

    public function deleteWork($work_id): void
    {
        $this->database->delete(
            "DELETE FROM works WHERE work_id = :work_id",
            [":work_id" => $work_id]
        );
    }
}
