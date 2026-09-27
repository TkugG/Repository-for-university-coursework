<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function patch(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middlewares);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '/' . trim($path, '/'),
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request): void
    {
        $requestMethod = $request->getMethod();
        $requestUri = '/' . trim($request->getUri(), '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route pattern: /tickets/{id} -> #^/tickets/([^/]+)$#
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match

                // Execute Middlewares
                foreach ($route['middlewares'] as $middleware) {
                    if (is_string($middleware)) {
                        $mw = new $middleware();
                        $mw->handle($request);
                    } elseif (is_object($middleware) && method_exists($middleware, 'handle')) {
                        $middleware->handle($request);
                    }
                }

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func_array($handler, array_merge([$request], $matches));
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $method] = $handler;
                    $controller = new $controllerClass();
                    call_user_func_array([$controller, $method], array_merge([$request], $matches));
                    return;
                }
            }
        }

        // 404 Not Found
        if ($request->isJson()) {
            Response::json(['error' => 'Endpoint not found', 'path' => $requestUri], 404);
        } else {
            http_response_code(404);
            echo "<!DOCTYPE html><html lang='th'><head><meta charset='UTF-8'><title>404 Not Found</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-slate-100 flex items-center justify-center min-h-screen'><div class='bg-white p-8 rounded-xl shadow-lg text-center max-w-md'><h1 class='text-6xl font-black text-indigo-600 mb-2'>404</h1><p class='text-slate-600 font-medium mb-6'>ไม่พบหน้าที่คุณต้องการเรียกดู ({$requestUri})</p><a href='/' class='px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition'>กลับสู่หน้าหลัก</a></div></body></html>";
            exit;
        }
    }
}
