<?php

declare(strict_types=1);

namespace  Pozys\PageAnalyzer\Database;

use PDO;

class Connection
{
    private static ?Connection $connection = null;

    public function connect(): PDO
    {
        $params = $this->getParams();

        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $params['host'],
            $params['port'],
            $params['database'],
            $params['user'],
            $params['password']
        );

        $pdo = new PDO($conStr);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function get(): self
    {
        if (null === self::$connection) {
            self::$connection = new self();
        }

        return self::$connection;
    }

    private function getParams(): array
    {
        return $this->getParamsFromEnv() ?? $this->getParamsFromFile();
    }

    private function getParamsFromEnv(): ?array
    {
        $envVar = $_ENV['DATABASE_URL'] ?? null;

        if (is_null($envVar)) {
            return null;
        }

        $databaseUrl = parse_url($envVar);
        $user = $databaseUrl['user'];
        $password = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $port = $databaseUrl['port'] ?? 5432;
        $database = ltrim($databaseUrl['path'], '/');

        return compact('user', 'password', 'host', 'port', 'database');
    }

    private function getParamsFromFile(): array
    {
        $params = parse_ini_file('database.ini');

        if ($params === false) {
            throw new \Exception("Error reading database configuration file");
        }

        $user = $params['user'];
        $password = $params['password'];
        $host = $params['host'];
        $port = $params['port'];
        $database = $params['database'];

        return compact('user', 'password', 'host', 'port', 'database');
    }
}
