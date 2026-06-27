<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Thin PDO wrapper providing convenience methods for common query patterns.
 *
 * All queries use prepared statements with named parameters for safety.
 * The connection uses utf8mb4 charset and throws PDOException on errors.
 */
class Database
{
    /** The configuration object holding DB credentials. */
    protected Configuration $configuration;

    /** The underlying PDO connection. */
    protected PDO $connection;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->connection = new PDO(
            "mysql:host=" . $configuration->databaseHost . ";dbname=" . $configuration->databaseName . ";charset=utf8",
            $configuration->databaseUser,
            $configuration->databasePassword,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 1]
        );
    }

    /**
     * Execute a SELECT and return all result rows as an array of associative arrays.
     *
     * @param string $query
     * @param array  $parameters Named parameters (e.g. [":id" => 1]).
     * @return array[]
     */
    public function select(string $query, array $parameters = []): array
    {
        $statement = $this->execute($query, $parameters);
        return $statement->fetchAll();
    }

    /**
     * Execute a SELECT and return the first row, or an empty array if none found.
     *
     * @param string $query
     * @param array  $parameters
     * @return array
     */
    public function selectOne(string $query, array $parameters = []): array
    {
        $statement = $this->execute($query, $parameters);
        return $statement->fetch() ?: [];
    }

    /**
     * Execute an INSERT and return the last inserted auto-increment ID.
     *
     * @param string $query
     * @param array  $parameters
     * @return false|string
     */
    public function insert(string $query, array $parameters = []): false|string
    {
        $statement = $this->execute($query, $parameters);
        return $this->connection->lastInsertId();
    }

    /**
     * Execute an UPDATE query (returns no result).
     *
     * @param string $query
     * @param array  $parameters
     */
    public function update(string $query, array $parameters = []): void
    {
        $this->execute($query, $parameters);
    }

    /**
     * Execute a DELETE query (returns no result).
     *
     * @param string $query
     * @param array  $parameters
     */
    public function delete(string $query, array $parameters = []): void
    {
        $this->execute($query, $parameters);
    }

    /**
     * Prepare and execute a statement with the given parameters.
     *
     * @param string $query
     * @param array  $params
     * @return false|PDOStatement
     */
    private function execute(string $query, array $params = []): false|PDOStatement
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    /**
     * Execute a raw SQL query with no parameter binding (DDL statements).
     *
     * @param string $query
     */
    public function exec(string $query): void
    {
        $this->connection->exec($query);
    }
}