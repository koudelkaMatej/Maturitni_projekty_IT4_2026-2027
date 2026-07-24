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
 * Supports both MySQL and SQLite drivers with automatic SQL translation
 * via helper methods (now, curdate, dateSub, concat, month, year, etc.).
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

        $user = $configuration->databaseUser ?? null;
        $password = $configuration->databasePassword ?? null;

        $this->connection = new PDO(
            $configuration->databaseDsn,
            $user,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 1]
        );

        if ($this->isSQLite()) {
            $this->connection->exec("PRAGMA foreign_keys = ON");
        }
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

    public function getDriverName(): string
    {
        return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function isMySQL(): bool
    {
        return $this->getDriverName() === 'mysql';
    }

    public function isSQLite(): bool
    {
        return $this->getDriverName() === 'sqlite';
    }

    public function now(): string
    {
        return $this->isSQLite() ? "datetime('now')" : "NOW()";
    }

    public function curdate(): string
    {
        return $this->isSQLite() ? "date('now')" : "CURDATE()";
    }

    public function dateSub(string $expr, int $n, string $unit = 'MONTH'): string
    {
        if ($this->isSQLite()) {
            $map = ['MONTH' => 'months', 'DAY' => 'days', 'YEAR' => 'years',
                'HOUR' => 'hours', 'MINUTE' => 'minutes', 'SECOND' => 'seconds'];
            $sqliteUnit = $map[strtoupper($unit)] ?? 'months';
            return "date($expr, '-$n $sqliteUnit')";
        }
        return "DATE_SUB($expr, INTERVAL $n $unit)";
    }

    public function concat(string ...$parts): string
    {
        if ($this->isSQLite()) {
            return implode(' || ', $parts);
        }
        return 'CONCAT(' . implode(', ', $parts) . ')';
    }

    public function lpad(string $expr, int $length, string $pad = ' '): string
    {
        if ($this->isSQLite()) {
            $padRepeated = str_repeat($pad, $length);
            return "substr('$padRepeated' || $expr, -$length)";
        }
        return "LPAD($expr, $length, '$pad')";
    }

    public function month(string $expr): string
    {
        return $this->isSQLite() ? "CAST(strftime('%m', $expr) AS INTEGER)" : "MONTH($expr)";
    }

    public function year(string $expr): string
    {
        return $this->isSQLite() ? "CAST(strftime('%Y', $expr) AS INTEGER)" : "YEAR($expr)";
    }

    public function groupConcat(string $expr, string $separator = ','): string
    {
        if ($this->isSQLite()) {
            return "GROUP_CONCAT($expr, '$separator')";
        }
        return "GROUP_CONCAT($expr SEPARATOR '$separator')";
    }

    public function insertIgnore(string $query, array $parameters = []): false|string
    {
        if ($this->isSQLite()) {
            $query = str_ireplace('INSERT IGNORE INTO', 'INSERT OR IGNORE INTO', $query);
        }
        return $this->insert($query, $parameters);
    }

    public function deleteIgnore(string $query, array $parameters = []): void
    {
        if ($this->isSQLite()) {
            $query = str_ireplace('DELETE IGNORE FROM', 'DELETE FROM', $query);
        }
        $this->delete($query, $parameters);
    }
}