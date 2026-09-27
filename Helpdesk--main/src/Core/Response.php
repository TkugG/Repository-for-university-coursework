<?php

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url): void
    {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = url($url);
        }
        header("Location: {$url}");
        exit;
    }

    public static function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'type' => $type, // 'success', 'error', 'warning', 'info'
            'message' => $message,
        ];
    }

    public static function getFlash(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function view(string $viewName, array $data = [], ?string $layout = 'layouts/main'): void
    {
        extract($data);

        $viewFile = dirname(__DIR__, 2) . '/views/' . ltrim($viewName, '/') . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View file not found: views/{$viewName}.php";
            exit;
        }

        if ($layout) {
            $layoutFile = dirname(__DIR__, 2) . '/views/' . ltrim($layout, '/') . '.php';
            if (file_exists($layoutFile)) {
                // Buffer view content
                ob_start();
                require $viewFile;
                $content = ob_get_clean();

                // Render layout
                require $layoutFile;
                exit;
            }
        }

        // Render raw view
        require $viewFile;
        exit;
    }
}
