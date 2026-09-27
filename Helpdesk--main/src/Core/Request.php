<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $queryParams;
    private array $bodyParams;
    private array $files;
    private array $headers;

    private static ?string $cachedBasePath = null;

    public static function basePath(): string
    {
        if (self::$cachedBasePath !== null) {
            return self::$cachedBasePath;
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($scriptName));
        $dir = rtrim($dir, '/');
        
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            $dir = '';
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        if (str_ends_with($dir, '/public') && !str_starts_with($requestUri, $dir)) {
            $parentDir = substr($dir, 0, -7);
            if ($parentDir && str_starts_with($requestUri, $parentDir)) {
                $dir = $parentDir;
            }
        }

        self::$cachedBasePath = $dir;
        return self::$cachedBasePath;
    }

    public static function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . self::basePath();
    }

    public function __construct()
    {
        $this->method = strtoupper($_POST['_method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $basePath = self::basePath();
        
        // Strip base folder if hosted in subfolder (like /smart-it-helpdesk)
        if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        $parsedUrl = parse_url($requestUri);
        $this->uri = '/' . ltrim($parsedUrl['path'] ?? '/', '/');
        
        $this->queryParams = $_GET;
        $this->files = $_FILES;
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];

        // Parse body params (Form data or JSON)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $this->bodyParams = json_decode($raw, true) ?? [];
        } else {
            $this->bodyParams = $_POST;
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function fullUri(): string
    {
        $uri = $this->uri;
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }
        return $uri;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->bodyParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams);
    }

    public function getBody(): array
    {
        return $this->all();
    }

    public function file(string $key): ?array
    {
        if (isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE) {
            return $this->files[$key];
        }
        return null;
    }

    public function isJson(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($contentType, 'application/json') || str_contains($accept, 'application/json');
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }
}
