<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for the single guest-access credential row that gates
 * unauthenticated ticket submission on index.php.
 */
class GuestAccessRepository extends Repository
{
    /**
     * Get the guest access settings (there is always exactly one row).
     *
     * @return GuestAccess
     */
    public function get(): GuestAccess
    {
        $row = $this->database->selectOne("SELECT * FROM guest_access WHERE guest_id = 1");
        return new GuestAccess($row);
    }

    /**
     * Update the guest login username.
     *
     * @param string $username
     */
    public function updateUsername(string $username): void
    {
        $this->database->update(
            "UPDATE guest_access SET guest_username = :username WHERE guest_id = 1",
            [":username" => $username]
        );
    }

    /**
     * Replace the guest password hash.
     *
     * @param string $password Plaintext password (will be hashed).
     */
    public function updatePassword(string $password): void
    {
        $this->database->update(
            "UPDATE guest_access SET guest_password = :password WHERE guest_id = 1",
            [":password" => password_hash($password, PASSWORD_DEFAULT)]
        );
    }

    /**
     * Enable or disable guest ticket submission.
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->database->update(
            "UPDATE guest_access SET guest_enabled = :enabled WHERE guest_id = 1",
            [":enabled" => $enabled ? 1 : 0]
        );
    }

    /**
     * Verify a login attempt against the currently configured guest credentials.
     * Always fails when guest access is disabled.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function verify(string $username, string $password): bool
    {
        $guest = $this->get();
        if (!$guest->guest_enabled) return false;
        if (!hash_equals($guest->guest_username, $username)) return false;
        return $guest->verifyPassword($password);
    }
}
