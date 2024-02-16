<?php

declare(strict_types=1);

namespace  Pozys\PageAnalyzer\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PDO;
use Pozys\PageAnalyzer\Services\UrlService;

class UrlRepository extends AbstractRepository
{
    public static function getTableName(): string
    {
        return 'urls';
    }

    public function listUrls(): array
    {
        $urlTable = static::getTableName();
        $urlTableAlias = $urlTable . '_alias';
        $checksTable = UrlCheckRepository::getTableName();
        $checksTableAlias = $checksTable . '_alias';
        $checkDateColumn = 'check_date';

        $sql = <<<SQL
        SELECT $urlTableAlias.*,
            $checksTableAlias.status_code,
            $checksTableAlias.created_at AS $checkDateColumn
        FROM
            $urlTable AS $urlTableAlias
            LEFT JOIN $checksTable AS $checksTableAlias ON $urlTableAlias.id = $checksTableAlias.url_id
        SQL;

        $rows = $this->connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return collect($rows)->groupBy('id')
            ->map(
                fn (Collection $urlData) => $urlData->sortByDesc($checkDateColumn)->first() ?? []
            )
            ->sortByDesc('created_at')
            ->all();
    }

    public function insertUrl(array $urlData): string
    {
        $table = static::getTableName();
        $sql = "INSERT INTO $table(name, created_at) VALUES(:name, :date)";
        $statement = $this->connection->prepare($sql);

        $urlParsed = parse_url($urlData['name']);

        if ($urlParsed === false) {
            throw new \Exception('Invalid URL');
        }

        $name = UrlService::getName($urlParsed);

        $statement->bindValue(':name', $name);
        $statement->bindValue(':date', Carbon::now());

        $statement->execute();

        return $this->connection->lastInsertId();
    }

    public function getUrlById(int $id): ?array
    {
        return $this->firstByField('id', $id);
    }
}
