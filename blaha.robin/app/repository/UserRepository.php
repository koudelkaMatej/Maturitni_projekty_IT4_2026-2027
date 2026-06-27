<?php

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
}
