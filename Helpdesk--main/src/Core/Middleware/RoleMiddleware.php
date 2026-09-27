<?php

namespace App\Core\Middleware;

use App\Core\Auth;
use App\Core\Response;
use App\Core\Request;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(string|array ...$roles)
    {
        $this->allowedRoles = is_array($roles[0] ?? null) ? $roles[0] : $roles;
    }

    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        $userRole = Auth::role();
        if (!in_array($userRole, $this->allowedRoles, true)) {
            if ($request->isJson()) {
                Response::json(['error' => 'Forbidden: You do not have permission.'], 403);
            }
            Response::setFlash('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            
            // Redirect to appropriate dashboard based on actual role
            match ($userRole) {
                'admin' => Response::redirect('/admin/dashboard'),
                'technician' => Response::redirect('/technician/dashboard'),
                default => Response::redirect('/dashboard'),
            };
        }
    }
}
