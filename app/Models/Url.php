<?php

namespace  Pozys\PageAnalyzer\Models;

use Valitron\Validator;

class Url
{
    private const TABLE_NAME = 'urls';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function setRules(Validator $validator): Validator
    {
        $validator
            ->rule('required', 'name')->message('URL не должен быть пустым')
            ->rule('lengthMax', 'name', 255)->message('URL не должен быть длиннее 255 символов')
            ->rule('url', 'name')->message('Некорректный URL');

        return $validator;
    }

    public static function getName(array $data): string
    {
        $scheme = $data['scheme'] ?? '';
        $host = $data['host'] ?? '';

        return "$scheme://$host";
    }
}
