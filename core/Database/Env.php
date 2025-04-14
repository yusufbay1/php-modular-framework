<?php

namespace Core\Database;

class Env
{
    private static ?array $config = null;

    private static function loadConfig(): void
    {
        if (self::$config !== null) return;

        self::reload();
    }

    public static function reload(): void
    {
        self::$config = [];

        if (!is_readable('.env')) return;

        $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            self::$config[$key] = $value;

            // Ortama da yaz
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        self::loadConfig();
        return self::$config[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        self::loadConfig();
        self::$config[$key] = $value;

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::loadConfig();
        return array_key_exists($key, self::$config);
    }

    public static function all(): array
    {
        self::loadConfig();
        return self::$config;
    }
}

