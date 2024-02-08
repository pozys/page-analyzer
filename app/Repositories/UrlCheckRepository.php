<?php

namespace App\Repositories;

use App\Models\UrlCheck;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class UrlCheckRepository extends AbstractRepository
{
    public function model(): string
    {
        return UrlCheck::class;
    }

    public function checksByUrl(int $urlId): array
    {
        $checks = $this->allByField('url_id', $urlId);

        return Arr::sortDesc($checks, 'created_at');
    }

    public function insertCheck(string $urlId): int
    {
        $sql = "INSERT INTO {$this->getTableName()}(url_id, created_at) VALUES(:url_id, :date)";
        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':url_id', $urlId);
        $statement->bindValue(':date', Carbon::now());

        $statement->execute();

        return $this->connection->lastInsertId();
    }
}
