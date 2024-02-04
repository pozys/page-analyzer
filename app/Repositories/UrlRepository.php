<?php

namespace App\Repositories;

use App\Models\Url;
use Carbon\Carbon;
use PDO;
use PDOStatement;

class UrlRepository
{
    private PDOStatement $stmt;

    public function __construct(private PDO $connection, private string $model = Url::class)
    {
    }

    public function list(): PDOStatement
    {
        $sql = "SELECT * FROM {$this->getTableName()} ORDER BY created_at DESC";

        return $this->connection->query($sql);
    }

    public function insertUrl(array $url): int
    {
        $sql = "INSERT INTO {$this->getTableName()}(name, created_at) VALUES(:name, :date)";
        $stmt = $this->connection->prepare($sql);

        $urlParsed = parse_url($url['name']);
        $name = Url::getName($urlParsed);

        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':date', Carbon::now());

        $stmt->execute();

        return $this->connection->lastInsertId();
    }

    public function getUrl(int $id): array
    {
        return $this->findByField('id', $id)->first();
    }

    public function findByField(string $field, mixed $value): self
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE $field = :{$field}";
        $this->stmt = $this->connection->prepare($sql);
        $this->stmt->bindValue(":{$field}", $value);
        $this->stmt->execute();

        return $this;
    }

    public function first(): ?array
    {
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return $result;
    }

    private function getTableName(): string
    {
        return $this->model::getTableName();
    }
}
