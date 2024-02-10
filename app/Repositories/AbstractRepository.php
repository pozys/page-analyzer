<?php

namespace App\Repositories;

use PDO;
use PDOStatement;

abstract class AbstractRepository
{
    protected PDOStatement $statement;

    public function __construct(protected PDO $connection)
    {
    }

    abstract public function model(): string;

    public function firstByField(string $field, mixed $value): ?array
    {
        $result = $this->findByField($field, $value)->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function allByField(string $field, mixed $value): array
    {
        $result = $this->findByField($field, $value)->fetchAll(PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }

    protected function getTableName(): string
    {
        return $this->model()::getTableName();
    }

    private function findByField(string $field, mixed $value): PDOStatement
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE $field = :$field";
        $this->statement = $this->connection->prepare($sql);
        $this->statement->bindValue(":$field", $value);
        $this->statement->execute();

        return $this->statement;
    }
}
