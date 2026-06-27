<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for automatic ticket-assignment rules per category.
 *
 * When a ticket is created in a certain category, any user
 * registered via an auto-assign rule is automatically assigned.
 */
class AutoAssignRepository extends Repository
{
    /**
     * Get all users (with teacher details) who auto-assign to a given category.
     *
     * @param int $autoassign_category
     * @return array[]
     */
    public function getAutoAssignUsersByCategory($autoassign_category): array
    {
        return $this->database->select(
            "SELECT * FROM autoassigns INNER JOIN users on autoassign_user = user_id INNER JOIN teachers on user_id = teacher_id WHERE autoassign_category = :autoassign_category",
            [
                ":autoassign_category" => $autoassign_category,
            ]
        );
    }

    /**
     * Get all categories that a given user auto-assigns to.
     *
     * @param int $autoassign_user
     * @return array[]
     */
    public function getAutoAssignCategoriesByUser($autoassign_user): array
    {
        return $this->database->select(
            "SELECT * FROM autoassigns INNER JOIN categories on autoassign_category = category_id WHERE autoassign_user = :autoassign_user",
            [
                ":autoassign_user" => $autoassign_user,
            ]
        );
    }

    /**
     * Add an auto-assign rule (skips silently if already exists).
     *
     * @param int $autoassign_category
     * @param int $autoassign_user
     */
    public function addAutoAssign($autoassign_category, $autoassign_user): void
    {
        $this->database->insert(
            "INSERT IGNORE INTO autoassigns (autoassign_user, autoassign_category) VALUES (:autoassign_user, :autoassign_category)",
            [
                ":autoassign_category" => $autoassign_category,
                ":autoassign_user" => $autoassign_user,
            ]
        );
    }

    /**
     * Remove an auto-assign rule.
     *
     * @param int $autoassign_category
     * @param int $autoassign_user
     */
    public function deleteAutoAssign($autoassign_category, $autoassign_user): void
    {
        $this->database->delete(
            "DELETE IGNORE FROM autoassigns WHERE autoassign_category = :autoassign_category AND autoassign_user = :autoassign_user",
            [
                ":autoassign_category" => $autoassign_category,
                ":autoassign_user" => $autoassign_user,
            ]
        );
    }
}