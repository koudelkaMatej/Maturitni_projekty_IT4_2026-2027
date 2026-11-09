<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing the single guest-access credential
 * used to gate the unauthenticated ticket submission form on index.php.
 */
class GuestAccess implements JsonSerializable
{
    /** Login username shown/entered on the public form. */
    public readonly string $guest_username;

    /** Whether guest ticket submission is currently allowed. */
    public readonly bool $guest_enabled;

    /** Password hash (bcrypt) — excluded from jsonSerialize(). */
    private readonly string $guest_password;

    public function __construct(array $row)
    {
        $this->guest_username = $row["guest_username"] ?? "";
        $this->guest_enabled = (int)($row["guest_enabled"] ?? 0) === 1;
        $this->guest_password = $row["guest_password"] ?? "";
    }

    /** @return bool Whether the given plaintext password matches the stored hash. */
    public function verifyPassword(string $password): bool
    {
        if ($this->guest_password === "") return false;
        return password_verify($password, $this->guest_password);
    }

    /** @return bool Whether credentials have ever been configured. */
    public function isConfigured(): bool
    {
        return $this->guest_username !== "" && $this->guest_password !== "";
    }

    public function jsonSerialize(): array
    {
        return [
            "guest_username" => $this->guest_username,
            "guest_enabled" => $this->guest_enabled,
        ];
    }
}
