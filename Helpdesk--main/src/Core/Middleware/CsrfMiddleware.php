<?php

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Response;
use App\Core\Request;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input('_csrf') 
                  ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

            if (!Auth::validateCsrf($token)) {
                if ($request->isJson()) {
                    Response::json(['error' => 'Invalid CSRF token'], 419);
                }
                Response::setFlash('error', 'Session หมดอายุ หรือ CSRF Token ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
                Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }
    }
}
