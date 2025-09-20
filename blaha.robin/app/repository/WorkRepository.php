<?php

class WorkRepository extends Repository
{
    public function getWorkById($work_id): array
    {
        return $this->database->selectOne(
            "SELECT * FROM works WHERE work_id = :work_id",
            [
                ":work_id" => $work_id,
            ]
        );
    }

    public function getWorksByTicket($work_ticket): array
    {
        return $this->database->select(
            "SELECT * FROM works WHERE work_ticket = :work_ticket",
            [
                ":work_ticket" => $work_ticket,
            ]
        );
    }

    public function getWorksByUser($work_user): array
    {
        return $this->database->select(
            "SELECT * FROM works WHERE work_user = :work_user",
            [
                ":work_user" => $work_user,
            ]
        );
    }

    public function addWork($work_ticket, $work_user, $work_minutes, $work_description): false|string
    {
        return $this->database->insert(
            "INSERT INTO works (work_ticket, work_user, work_minutes, work_description) VALUES (:work_ticket, :work_user, :work_minutes, :work_description)",
            [
                ":work_ticket" => $work_ticket,
                ":work_user" => $work_user,
                ":work_minutes" => $work_minutes,
                ":work_description" => $work_description,
            ]
        );
    }

    public function updateWork($work_id, $work_minutes, $work_description): void
    {
        $this->database->update(
            "UPDATE works SET work_minutes = :work_minutes, work_description = :work_description WHERE work_id = :work_id",
            [
                ":work_id" => $work_id,
                ":work_minutes" => $work_minutes,
                ":work_description" => $work_description,
            ]
        );
    }

    public function deleteWork($work_id): void
    {
        $this->database->delete(
            "DELETE FROM works WHERE work_id = :work_id",
            [
                ":work_id" => $work_id,
            ]
        );
    }
}