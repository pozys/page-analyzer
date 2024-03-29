<?php

declare(strict_types=1);

namespace  Pozys\PageAnalyzer\Repositories;

use PDO;
use PDOStatement;

abstract class AbstractRepository
{
    protected PDOStatement $statement;

    public function __construct(protected PDO $connection)
    {
    }

    abstract public static function getTableName(): string;

    public function firstByField(string $field, mixed $value): ?array
    {
        $result = $this->findByField($field, $value)->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function allByField(string $field, mixed $value): array
    {
        return $this->findByField($field, $value)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findByField(string $field, mixed $value): PDOStatement
    {
        $table = static::getTableName();
        $sql = "SELECT * FROM $table WHERE $field = :$field";
        $this->statement = $this->connection->prepare($sql);
        $this->statement->bindValue(":$field", $value);
        $this->statement->execute();

        return $this->statement;
    }
}
