<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

abstract class Repository
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
}