<?php

namespace App\Repositories;

use App\Models\Url;
use Carbon\Carbon;
use PDO;

class UrlRepository
{
    public function __construct(private PDO $connection, private string $model = Url::class)
    {
    }

    public function list()
    {
        $sql = "SELECT * FROM {$this->getTableName()}";

        $urls = $this->connection->query($sql);

        return $urls;
    }

    public function insertUrl($url)
    {
        $sql = "INSERT INTO {$this->getTableName()}(name, created_at) VALUES(:name, :date)";
        $stmt = $this->connection->prepare($sql);

        $urlParsed = parse_url($url['name']);
        $scheme = $urlParsed['scheme'];
        $host = $urlParsed['host'];

        $stmt->bindValue(':name', "{$scheme}://{$host}");
        $stmt->bindValue(':date', Carbon::now());

        $stmt->execute();

        return $this->connection->lastInsertId();
    }

    private function getTableName(): string
    {
        return $this->model::getTableName();
    }
}
