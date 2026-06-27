<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

class UserRepository extends Repository
{
    public function getAllUsers(): array
    {
        $rows = $this->database->select(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id"
        );
        return array_map(fn($r) => new User($r), $rows);
    }

    public function getUserById($user_id): ?User
    {
        $row = $this->database->selectOne(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );
        return $row ? new User($row) : null;
    }

    public function getUserByUsername($user_username): ?User
    {
        $row = $this->database->selectOne(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id WHERE user_username = :user_username",
            [":user_username" => $user_username]
        );
        return $row ? new User($row) : null;
    }

    public function updateUserPassword($user_id, $user_password): void
    {
        $this->database->update(
            "UPDATE users SET user_password = :user_password WHERE user_id = :user_id",
            [":user_password" => $user_password, ":user_id" => $user_id]
        );
    }

    public function addUser(int $user_id, string $user_username, string $user_password): false|string
    {
        return $this->database->insert(
            "INSERT INTO users (user_id, user_username, user_password) VALUES (:user_id, :user_username, :user_password)",
            [":user_id" => $user_id, ":user_username" => $user_username, ":user_password" => password_hash($user_password, PASSWORD_DEFAULT)]
        );
    }

    public function updateUserUsername(int $user_id, string $user_username): void
    {
        $this->database->update(
            "UPDATE users SET user_username = :user_username WHERE user_id = :user_id",
            [":user_id" => $user_id, ":user_username" => $user_username]
        );
    }

    public function deleteUser(int $user_id): void
    {
        $this->database->delete(
            "DELETE FROM users WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );
    }
}
