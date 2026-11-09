<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Application-wide configuration settings.
 *
 * Holds database credentials, session parameters, and the
 * development flag for verbose error output.
 */
class Configuration
{
    /**
     * The complete PDO DSN connection string.
     * Examples:
     * - MySQL: "mysql:host=127.0.0.1;dbname=spstickets;charset=utf8mb4"
     * - SQLite File: "sqlite:/path/to/database.sqlite"
     * - SQLite Memory: "sqlite::memory:"
     */
    public string $databaseDsn = "sqlite:" . __DIR__ . "/database.sqlite";

    /** Database username (null for SQLite). */
    public ?string $databaseUser = null;

    /** Database user password (null for SQLite). */
    public ?string $databasePassword = null;

    /** When true, display_errors and full error reporting are enabled. */
    public bool $development = false;

    /** Name of the session cookie. */
    public string $sessionCookie = "SPSTICKETS_SESSION";

    /** Whether to invalidate previous sessions on new login. */
    public bool $singleSession = false;

    /** Session lifetime in seconds (default 7 days). */
    public int $sessionLifetime = 604800;
}