<?php

if (!function_exists('db_config')) {
    function db_config(): array
    {
        return [
            'host' => app_env('LOCALLINK_DB_HOST', '127.0.0.1'),
            'port' => (int) app_env('LOCALLINK_DB_PORT', '3306'),
            'name' => app_env('LOCALLINK_DB_NAME', 'locallink_market'),
            'user' => app_env('LOCALLINK_DB_USER', 'root'),
            'pass' => app_env('LOCALLINK_DB_PASS', ''),
        ];
    }
}

if (!function_exists('db_get_connection')) {
    function db_get_connection(): PDO
    {
        static $connection = null;

        if ($connection instanceof PDO) {
            return $connection;
        }

        $config = db_config();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['name']
        );

        $connection = new PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $connection;
    }
}

if (!function_exists('db_try_get_connection')) {
    function db_try_get_connection(): ?PDO
    {
        static $attempted = false;
        static $connection = null;

        if ($attempted) {
            return $connection;
        }

        $attempted = true;

        try {
            $connection = db_get_connection();
        } catch (Throwable $exception) {
            $connection = null;
        }

        return $connection;
    }
}

if (!function_exists('db_is_available')) {
    function db_is_available(): bool
    {
        return db_try_get_connection() instanceof PDO;
    }
}

