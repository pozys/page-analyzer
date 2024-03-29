<?php

declare(strict_types=1);

namespace  Pozys\PageAnalyzer\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class UrlCheckRepository extends AbstractRepository
{
    public static function getTableName(): string
    {
        return 'url_checks';
    }

    public function checksByUrl(int $urlId): array
    {
        $checks = $this->allByField('url_id', $urlId);

        return Arr::sortDesc($checks, 'created_at');
    }

    public function insertCheck(array $data): string
    {
        $table = static::getTableName();

        $sql = <<<SQL
        INSERT INTO {$table}(url_id, status_code, h1, title, description, created_at)
        VALUES
            (:url_id, :status_code, :h1, :title, :description, :date)
        SQL;

        $statement = $this->connection->prepare($sql);

        $statement->bindValue(':url_id', $data['url_id']);
        $statement->bindValue(':status_code', $data['status_code']);
        $statement->bindValue(':h1', $data['h1'] ?? '');
        $statement->bindValue(':title', $data['title'] ?? '');
        $statement->bindValue(':description', $data['description'] ?? '');
        $statement->bindValue(':date', Carbon::now());

        $statement->execute();

        return $this->connection->lastInsertId();
    }
}
