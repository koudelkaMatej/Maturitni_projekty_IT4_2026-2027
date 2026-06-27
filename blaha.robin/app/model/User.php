<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

class User implements JsonSerializable
{
    public readonly int $user_id;
    public readonly string $user_username;
    public readonly string $teacher_name;
    public readonly ?string $teacher_code;

    private readonly string $user_password;

    public function __construct(array $row)
    {
        $this->user_id = (int)($row["user_id"] ?? 0);
        $this->user_username = $row["user_username"] ?? "";
        $this->teacher_name = $row["teacher_name"] ?? "";
        $this->teacher_code = $row["teacher_code"] ?? null;
        $this->user_password = $row["user_password"] ?? "";
    }

    public function getPasswordHash(): string
    {
        return $this->user_password;
    }

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
