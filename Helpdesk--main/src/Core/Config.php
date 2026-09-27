<?php

namespace App\Core;

class Config
{
    private static array $env = [];
    private static bool $loaded = false;

    public static function load(string $envPath = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envPath = $envPath ?? dirname(__DIR__, 2) . '/.env';

        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (str_contains($line, '=')) {
                    [$key, $val] = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val);
                    $val = trim($val, "\"'");
                    self::$env[$key] = $val;
                    if (!isset($_ENV[$key])) {
                        $_ENV[$key] = $val;
                    }
                    putenv("{$key}={$val}");
                }
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        return self::$env[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function updateEnv(array $updates): bool
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($envPath)) {
            return false;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES);
        $updatedKeys = [];
        $newLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '' && !str_starts_with($trimmed, '#') && str_contains($trimmed, '=')) {
                [$key, ] = explode('=', $trimmed, 2);
                $key = trim($key);
                if (array_key_exists($key, $updates)) {
                    $val = $updates[$key];
                    // Quote if contains spaces
                    if (str_contains($val, ' ') && !str_starts_with($val, '"')) {
                        $val = '"' . $val . '"';
                    }
                    $newLines[] = "{$key}={$val}";
                    $updatedKeys[$key] = true;
                    continue;
                }
            }
            $newLines[] = $line;
        }

        // Add any new keys that were not in .env yet
        foreach ($updates as $key => $val) {
            if (!isset($updatedKeys[$key])) {
                if (str_contains($val, ' ') && !str_starts_with($val, '"')) {
                    $val = '"' . $val . '"';
                }
                $newLines[] = "{$key}={$val}";
            }
        }

        $success = (file_put_contents($envPath, implode("\n", $newLines) . "\n") !== false);
        if ($success) {
            self::$loaded = false;
            self::$env = [];
            self::load();
        }
        return $success;
    }
}
