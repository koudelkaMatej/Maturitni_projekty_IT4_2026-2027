<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a user (technician) account.
 *
 * Extends the teacher record with login credentials. The password
 * hash is kept private and is never included in JSON serialization.
 */
class User implements JsonSerializable
{
    /** FK to teachers.teacher_id (1:1). */
    public readonly int $user_id;

    /** Unique login username. */
    public readonly string $user_username;

    /** Joined full name of the teacher. */
    public readonly string $teacher_name;

    /** Joined optional teacher code. */
    public readonly ?string $teacher_code;

    /** Password hash (bcrypt) — excluded from jsonSerialize(). */
    private readonly string $user_password;

    public function __construct(array $row)
    {
        $this->user_id = (int)($row["user_id"] ?? 0);
        $this->user_username = $row["user_username"] ?? "";
        $this->teacher_name = $row["teacher_name"] ?? "";
        $this->teacher_code = $row["teacher_code"] ?? null;
        $this->user_password = $row["user_password"] ?? "";
    }

    /** @return string The raw bcrypt password hash. */
    public function getPasswordHash(): string
    {
        return $this->user_password;
    }

    /** @return bool Whether the given plaintext password matches the stored hash. */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->user_password);
    }

    public function jsonSerialize(): array
    {
        return [
            "user_id" => $this->user_id,
            "user_username" => $this->user_username,
            "teacher_name" => $this->teacher_name,
            "teacher_code" => $this->teacher_code,
        ];
    }
}
