<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

class Configuration
{
    // Database Connection Configuration
    public string $databaseHost = "127.0.0.1";
    public string $databaseName = "spstickets";
    public string $databaseUser = "root";
    public string $databasePassword = "";

    public bool $development = true;

    public string $sessionCookie = "SPSTICKETS_SESSION";
    public bool $singleSession = false;
    public int $sessionLifetime = 604800; // 7 days
}