<?php

namespace App\Repositories;

use App\Models\{Url, UrlCheck};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PDO;

class UrlRepository extends AbstractRepository
{
    public function model(): string
    {
        return Url::class;
    }

    public function listUrls(): array
    {
        $urlTable = $this->getTableName();
        $urlTableAlias = $urlTable . '_alias';
        $checksTable = UrlCheck::getTableName();
        $checksTableAlias = $checksTable . '_alias';
        $checkDateColumn = 'check_date';

        $sql = "SELECT {$urlTableAlias}.*,
        {$checksTableAlias}.created_at AS $checkDateColumn
        FROM $urlTable AS $urlTableAlias
        LEFT JOIN $checksTable AS $checksTableAlias
        ON {$urlTableAlias}.id = {$checksTableAlias}.url_id";

        $rows = $this->connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return collect($rows)->groupBy('id')
            ->map(
                fn (Collection $urlData) => $urlData->sortByDesc($checkDateColumn)->first() ?? []
            )
            ->sortByDesc('created_at')
            ->all();
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

    public function getUrlById(int $id): ?array
    {
        return $this->firstByField('id', $id);
    }
}
