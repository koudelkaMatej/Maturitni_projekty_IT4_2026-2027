<?php

class Database
{
    protected Configuration $configuration;
    protected PDO $connection;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->connection = new PDO(
            "mysql:host=" . $configuration->databaseHost . ";dbname=" . $configuration->databaseName . ";charset=utf8",
            $configuration->databaseUser,
            $configuration->databasePassword,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function select(string $query, array $parameters = []): array
    {
        $statement = $this->execute($query, $parameters);
        return $statement->fetchAll();
    }

    public function selectOne(string $query, array $parameters = []): array
    {
        $statement = $this->execute($query, $parameters);
        return $statement->fetch();
    }

    public function insert(string $query, array $parameters = []): false|string
    {
        $statement = $this->execute($query, $parameters);
        return $this->connection->lastInsertId();
    }

    public function update(string $query, array $parameters = []): void
    {
        $this->execute($query, $parameters);
    }

    public function delete(string $query, array $parameters = []): void
    {
        $this->execute($query, $parameters);
    }

    private function execute(string $query, array $params = []): false|PDOStatement
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }
}