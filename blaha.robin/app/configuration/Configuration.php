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
    /** MySQL/MariaDB host address. */
    public string $databaseHost = "127.0.0.1";

    /** Database name to connect to. */
    public string $databaseName = "spstickets";

    /** Database user name. */
    public string $databaseUser = "root";

    /** Database user password. */
    public string $databasePassword = "";

    /** When true, display_errors and full error reporting are enabled. */
    public bool $development = true;

    /** Name of the session cookie. */
    public string $sessionCookie = "SPSTICKETS_SESSION";

    /** Whether to invalidate previous sessions on new login. */
    public bool $singleSession = false;

    /** Session lifetime in seconds (default 7 days). */
    public int $sessionLifetime = 604800;
}