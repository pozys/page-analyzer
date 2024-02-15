<?php

declare(strict_types=1);

namespace Pozys\PageAnalyzer\Services;

class UrlService
{
    public static function getName(array $urlParts): string
    {
        $scheme = $urlParts['scheme'] ?? '';
        $host = $urlParts['host'] ?? '';

        return "$scheme://$host";
    }
}
