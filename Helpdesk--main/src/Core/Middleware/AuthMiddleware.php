<?php

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Response;
use App\Core\Request;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            if ($request->isJson()) {
                Response::json(['error' => 'Unauthorized. Please login.'], 401);
            }
            if ($request->getMethod() === 'GET') {
                $_SESSION['intended_url'] = $request->fullUri();
            }
            Response::setFlash('warning', 'กรุณาเข้าสู่ระบบก่อนเข้าใช้งาน');
            Response::redirect('/login');
        }
    }
}
