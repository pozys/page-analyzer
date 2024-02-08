<?php

namespace App\Models;

class UrlCheck
{
    private const TABLE_NAME = 'url_checks';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
