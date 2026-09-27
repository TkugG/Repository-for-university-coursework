<?php

namespace App\Core;

use App\Repositories\UserRepository;

class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function attempt(string $email, string $password): bool
    {
        self::init();
        $userRepo = new UserRepository();
        $user = $userRepo->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            self::loginAs($user);
            return true;
        }

        return false;
    }

    public static function loginAs(array $user): void
    {
        self::init();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
    }

    public static function logout(): void
    {
        self::init();
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        self::init();
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::init();
        if (!self::check()) {
            return null;
        }

        return [
            'id' => (int) $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user',
        ];
    }

    public static function id(): ?int
    {
        self::init();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        self::init();
        return $_SESSION['user_role'] ?? null;
    }

    public static function hasRole(string|array $roles): bool
    {
        self::init();
        $currentRole = self::role();
        if (!$currentRole) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }

        return $currentRole === $roles;
    }

    public static function csrfToken(): string
    {
        self::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token): bool
    {
        self::init();
        if (!$token || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
