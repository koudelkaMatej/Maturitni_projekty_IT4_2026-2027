<?php

class SessionRepository extends Repository
{
    public function getSessionById($session_id): array
    {
        return $this->database->selectOne(
            "SELECT * FROM sessions WHERE session_id = :session_id",
            [
                ":session_id" => $session_id,
            ]
        );
    }

    public function getSessionsByUser($session_user): array
    {
        return $this->database->select(
            "SELECT * FROM sessions WHERE session_user = :session_user",
            [
                ":session_user" => $session_user,
            ]
        );
    }

    public function refreshSession($session_id): void
    {
        $this->database->update(
            "UPDATE sessions SET session_last_use = NOW() WHERE session_id = :session_id",
            [
                ":session_id" => $session_id,
            ]
        );
    }

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

    public function deleteSession($session_id): void
    {
        $this->database->delete(
            "DELETE FROM sessions WHERE session_id = :session_id",
            [
                ":session_id" => $session_id,
            ]
        );
    }

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