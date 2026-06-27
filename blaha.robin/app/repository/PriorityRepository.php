<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for ticket priority CRUD.
 */
class PriorityRepository extends Repository
{
    /**
     * Get a single priority by ID.
     *
     * @param int $priority_id
     * @return ?Priority
     */
    public function getPriorityById($priority_id): ?Priority
    {
        $row = $this->database->selectOne(
            "SELECT * FROM priorities WHERE priority_id = :priority_id",
            [":priority_id" => $priority_id]
        );
        return $row ? new Priority($row) : null;
    }

    /**
     * Get all priorities.
     *
     * @return Priority[]
     */
    public function getAllPriorities(): array
    {
        $rows = $this->database->select("SELECT * FROM priorities");
        return array_map(fn($r) => new Priority($r), $rows);
    }

    /**
     * Create a new priority.
     *
     * @param string $priority_name
     * @param int    $priority_weight
     * @param string $priority_color
     * @return false|string The new priority ID, or false on failure.
     */
    public function addPriority($priority_name, $priority_weight, $priority_color): false|string
    {
        return $this->database->insert(
            "INSERT INTO priorities (priority_name, priority_weight, priority_color) VALUES (:priority_name, :priority_weight, :priority_color)",
            [":priority_name" => $priority_name, ":priority_weight" => $priority_weight, ":priority_color" => $priority_color]
        );
    }

    /**
     * Update a priority.
     *
     * @param int    $priority_id
     * @param string $priority_name
     * @param int    $priority_weight
     * @param string $priority_color
     */
    public function updatePriority($priority_id, $priority_name, $priority_weight, $priority_color): void
    {
        $this->database->update(
            "UPDATE priorities SET priority_name = :priority_name, priority_weight = :priority_weight, priority_color = :priority_color WHERE priority_id = :priority_id",
            [":priority_id" => $priority_id, ":priority_name" => $priority_name, ":priority_weight" => $priority_weight, ":priority_color" => $priority_color]
        );
    }

    /**
     * Delete a priority. Tickets referencing it will have ticket_priority set to NULL.
     *
     * @param int $priority_id
     */
    public function deletePriority($priority_id): void
    {
        $this->database->delete(
            "DELETE FROM priorities WHERE priority_id = :priority_id",
            [":priority_id" => $priority_id]
        );
    }
}
