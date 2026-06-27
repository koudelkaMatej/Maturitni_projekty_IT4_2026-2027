<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for DB-backed session management.
 *
 * Sessions are stored in the `sessions` table with a remote
 * address and last-use timestamp for expiry checks.
 */
class SessionRepository extends Repository
{
    /**
     * Get a single session by its ID.
     *
     * @param int $session_id
     * @return array
     */
    public function getSessionById($session_id): array
    {
        return $this->database->selectOne(
            "SELECT * FROM sessions WHERE session_id = :session_id",
            [
                ":session_id" => $session_id,
            ]
        );
    }

    /**
     * Get all sessions belonging to a user.
     *
     * @param int $session_user
     * @return array[]
     */
    public function getSessionsByUser($session_user): array
    {
        return $this->database->select(
            "SELECT * FROM sessions WHERE session_user = :session_user",
            [
                ":session_user" => $session_user,
            ]
        );
    }

    /**
     * Update the last-use timestamp of a session.
     *
     * @param int $session_id
     */
    public function refreshSession($session_id): void
    {
        $this->database->update(
            "UPDATE sessions SET session_last_use = NOW() WHERE session_id = :session_id",
            [
                ":session_id" => $session_id,
            ]
        );
    }

    /**
     * Create a new session for a user.
     *
     * @param int $session_user
     * @return false|string The new session ID, or false on failure.
     */
    public function addSession($session_user): false|string
    {
        $session_address = $_SERVER['REMOTE_ADDR'];

        return $this->database->insert(
            "INSERT INTO sessions (session_user, session_address) VALUES (:session_user, :session_address)",
            [
                ":session_user" => $session_user,
                ":session_address" => $session_address,
            ]
        );
    }

    /**
     * Delete a single session by ID.
     *
     * @param int $session_id
     */
    public function deleteSession($session_id): void
    {
        $this->database->delete(
            "DELETE FROM sessions WHERE session_id = :session_id",
            [
                ":session_id" => $session_id,
            ]
        );
    }

    /**
     * Delete all sessions for a given user (used for single-session mode).
     *
     * @param int $session_user
     */
    public function deleteUserSessions($session_user): void
    {
        $this->database->delete(
            "DELETE FROM sessions WHERE session_user = :session_user",
            [
                ":session_user" => $session_user,
            ]
        );
    }
}