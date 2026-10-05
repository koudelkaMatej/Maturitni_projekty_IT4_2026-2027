<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for user (technician) account CRUD.
 *
 * Users extend teacher records in a 1:1 relationship — every
 * query JOINS the teachers table so all User objects carry
 * teacher_name.
 */
class UserRepository extends Repository
{
    /**
     * Get all users with their teacher details.
     *
     * @return User[]
     */
    public function getAllUsers(): array
    {
        $rows = $this->database->select(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id"
        );
        return array_map(fn($r) => new User($r), $rows);
    }

    /**
     * Get a single user (with teacher details) by user ID.
     *
     * @param int $user_id
     * @return ?User
     */
    public function getUserById($user_id): ?User
    {
        $row = $this->database->selectOne(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );
        return $row ? new User($row) : null;
    }

    /**
     * Find a user by their login username.
     *
     * @param string $user_username
     * @return ?User
     */
    public function getUserByUsername($user_username): ?User
    {
        $row = $this->database->selectOne(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id WHERE user_username = :user_username",
            [":user_username" => $user_username]
        );
        return $row ? new User($row) : null;
    }

    /**
     * Replace the password hash for a user.
     *
     * @param int    $user_id
     * @param string $user_password Already-hashed password string.
     */
    public function updateUserPassword($user_id, $user_password): void
    {
        $this->database->update(
            "UPDATE users SET user_password = :user_password WHERE user_id = :user_id",
            [":user_password" => $user_password, ":user_id" => $user_id]
        );
    }

    /**
     * Create a new user account for an existing teacher.
     *
     * @param int    $user_id       FK to teachers.teacher_id.
     * @param string $user_username Unique login name.
     * @param string $user_password Plaintext password (will be hashed).
     * @return false|string The new user ID, or false on failure.
     */
    public function addUser(int $user_id, string $user_username, string $user_password, bool $admin = false): false|string
    {
        return $this->database->insert(
            "INSERT INTO users (user_id, user_username, user_password, user_admin) VALUES (:user_id, :user_username, :user_password, :user_admin)",
            [":user_id" => $user_id, ":user_username" => $user_username, ":user_password" => password_hash($user_password, PASSWORD_DEFAULT), ":user_admin" => $admin ? 1 : 0]
        );
    }

    /**
     * Change a user's login username.
     *
     * @param int    $user_id
     * @param string $user_username
     */
    public function updateUserUsername(int $user_id, string $user_username): void
    {
        $this->database->update(
            "UPDATE users SET user_username = :user_username WHERE user_id = :user_id",
            [":user_id" => $user_id, ":user_username" => $user_username]
        );
    }

    public function updateUserAdmin(int $user_id, bool $admin): void
    {
        $this->database->update(
            "UPDATE users SET user_admin = :user_admin WHERE user_id = :user_id",
            [":user_id" => $user_id, ":user_admin" => $admin ? 1 : 0]
        );
    }

    /**
     * Delete a user account (cascades to sessions and assignments).
     *
     * Explicitly deletes this user's own assignment rows first — scoped
     * strictly to this user_id — so ticket assignments stay correct even
     * on a database where the FK's ON DELETE CASCADE was never applied
     * (the auto-migrator only adds constraints to newly-created tables).
     *
     * @param int $user_id
     */
    public function deleteUser(int $user_id): void
    {
        $this->database->delete(
            "DELETE FROM assignments WHERE assignment_user = :user_id",
            [":user_id" => $user_id]
        );
        $this->database->delete(
            "DELETE FROM users WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );
    }
}
