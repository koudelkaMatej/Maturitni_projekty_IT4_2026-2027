<?php

class Configuration
{
    // Database Connection Configuration
    public string $databaseHost = "localhost";
    public string $databaseName = "spstickets";
    public string $databaseUser = "root";
    public string $databasePassword = "";

    public bool $development = true;

    public string $sessionCookie = "SPSTICKETS_SESSION";
    public bool $singleSession = false;
    public int $sessionLifetime = 604800; // 7 days
}