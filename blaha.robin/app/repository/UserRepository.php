<?php

class UserRepository extends Repository
{
    public function getAllUsers(): array
    {
        return $this->database->select(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id"
        );
    }

    public function getUserById($user_id): array
    {
        return $this->database->selectOne(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id WHERE user_id = :user_id",
            [
                ":user_id" => $user_id,
            ]
        );
    }

    public function getUserByUsername($user_username): array
    {
        return $this->database->selectOne(
            "SELECT * FROM users INNER JOIN teachers ON users.user_id = teachers.teacher_id WHERE user_username = :user_username",
            [
                ":user_username" => $user_username,
            ]
        );
    }

    public function updateUserPassword($user_id, $user_password): void
    {
        $this->database->update(
            "UPDATE users SET user_password = :user_password WHERE user_id = :user_id",
            [
                ":user_password" => $user_password,
                ":user_id" => $user_id,
            ]
        );
    }

}