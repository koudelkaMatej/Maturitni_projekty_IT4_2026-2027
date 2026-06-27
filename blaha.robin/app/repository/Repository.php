<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Base class for all repository types.
 *
 * Provides the shared Database instance to every child repository.
 */
abstract class Repository
{
    /** The PDO wrapper used for all queries. */
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
}