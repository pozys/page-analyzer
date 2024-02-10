<?php

namespace App\Models;

class Url
{
    private const TABLE_NAME = 'urls';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function rules(): array
    {
        return [
            'required' => ['name'],
            'lengthMax' => [['name', 255]],
            'url' => ['name'],
        ];
    }

    public static function getName(array $data): string
    {
        $scheme = $data['scheme'] ?? '';
        $host = $data['host'] ?? '';

        return "$scheme://$host";
    }
}
