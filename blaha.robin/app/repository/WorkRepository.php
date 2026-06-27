<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for work log entries on tickets.
 */
class WorkRepository extends Repository
{
    /**
     * Get a single work entry by ID.
     *
     * @param int $work_id
     * @return ?Work
     */
    public function getWorkById($work_id): ?Work
    {
        $row = $this->database->selectOne(
            "SELECT * FROM works WHERE work_id = :work_id",
            [":work_id" => $work_id]
        );
        return $row ? new Work($row) : null;
    }

    /**
     * Get all work entries for a ticket (without teacher names).
     *
     * @param int $work_ticket
     * @return Work[]
     */
    public function getWorksByTicket($work_ticket): array
    {
        $rows = $this->database->select(
            "SELECT * FROM works WHERE work_ticket = :work_ticket",
            [":work_ticket" => $work_ticket]
        );
        return array_map(fn($r) => new Work($r), $rows);
    }

    /**
     * Get all work entries for a ticket, joined with teacher names.
     *
     * @param int $work_ticket
     * @return Work[]
     */
    public function getWorksByTicketWithUsers($work_ticket): array
    {
        $rows = $this->database->select(
            "SELECT works.*, teachers.teacher_name FROM works LEFT JOIN users ON work_user = user_id LEFT JOIN teachers ON user_id = teacher_id WHERE work_ticket = :work_ticket ORDER BY work_id DESC",
            [":work_ticket" => $work_ticket]
        );
        return array_map(fn($r) => new Work($r), $rows);
    }

    /**
     * Get all work entries logged by a specific user.
     *
     * @param int $work_user
     * @return Work[]
     */
    public function getWorksByUser($work_user): array
    {
        $rows = $this->database->select(
            "SELECT * FROM works WHERE work_user = :work_user",
            [":work_user" => $work_user]
        );
        return array_map(fn($r) => new Work($r), $rows);
    }

    /**
     * Log a new work entry on a ticket.
     *
     * @param int    $work_ticket      FK to tickets.ticket_id.
     * @param int    $work_user        FK to users.user_id.
     * @param int    $work_minutes     Time spent.
     * @param string $work_description Free-text description.
     * @return false|string The new work ID, or false on failure.
     */
    public function addWork($work_ticket, $work_user, $work_minutes, $work_description): false|string
    {
        return $this->database->insert(
            "INSERT INTO works (work_ticket, work_user, work_minutes, work_description) VALUES (:work_ticket, :work_user, :work_minutes, :work_description)",
            [":work_ticket" => $work_ticket, ":work_user" => $work_user, ":work_minutes" => $work_minutes, ":work_description" => $work_description]
        );
    }

    /**
     * Update a work entry's minutes and description.
     *
     * @param int    $work_id
     * @param int    $work_minutes
     * @param string $work_description
     */
    public function updateWork($work_id, $work_minutes, $work_description): void
    {
        $this->database->update(
            "UPDATE works SET work_minutes = :work_minutes, work_description = :work_description WHERE work_id = :work_id",
            [":work_id" => $work_id, ":work_minutes" => $work_minutes, ":work_description" => $work_description]
        );
    }

    /**
     * Delete a work entry.
     *
     * @param int $work_id
     */
    public function deleteWork($work_id): void
    {
        $this->database->delete(
            "DELETE FROM works WHERE work_id = :work_id",
            [":work_id" => $work_id]
        );
    }
}
