<?php

namespace App\Repositories;

use App\Models\Url;
use Carbon\Carbon;
use PDO;
use PDOStatement;

class UrlRepository
{
    private PDOStatement $statement;

    public function __construct(private PDO $connection, private string $model = Url::class)
    {
    }

    public function listUrls(): PDOStatement
    {
        $sql = "SELECT * FROM {$this->getTableName()} ORDER BY created_at DESC";

        return $this->connection->query($sql);
    }

    public function insertUrl(array $urlData): int
    {
        $sql = "INSERT INTO {$this->getTableName()}(name, created_at) VALUES(:name, :date)";
        $statement = $this->connection->prepare($sql);

        $urlParsed = parse_url($urlData['name']);
        $name = Url::getName($urlParsed);

        $statement->bindValue(':name', $name);
        $statement->bindValue(':date', Carbon::now());

        $statement->execute();

        return $this->connection->lastInsertId();
    }

    public function getUrlById(int $id): array
    {
        return $this->firstByField('id', $id);
    }

    public function firstByField(string $field, mixed $value): ?array
    {
        $result = $this->findByField($field, $value)->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    private function findByField(string $field, mixed $value): PDOStatement
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE $field = :{$field}";
        $this->statement = $this->connection->prepare($sql);
        $this->statement->bindValue(":{$field}", $value);
        $this->statement->execute();

        return $this->statement;
    }

    private function getTableName(): string
    {
        return $this->model::getTableName();
    }
}
