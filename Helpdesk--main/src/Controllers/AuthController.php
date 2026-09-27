<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;

class AuthController
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            $this->redirectBasedOnRole(Auth::role());
        }

        Response::view('auth/login', [
            'title' => 'เข้าสู่ระบบ - Smart IT Helpdesk',
            'flash' => Response::getFlash(),
        ], null);
    }

    public function login(Request $request): void
    {
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');

        if (empty($email) || empty($password)) {
            Response::setFlash('error', 'กรุณากรอกอีเมลและรหัสผ่านให้ครบถ้วน');
            Response::redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            $user = Auth::user();
            Response::setFlash('success', "ยินดีต้อนรับคุณ {$user['name']} เข้าสู่ระบบ");
            $this->redirectBasedOnRole($user['role']);
        } else {
            Response::setFlash('error', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
            Response::redirect('/login');
        }
    }

    public function quickLogin(Request $request): void
    {
        Response::redirect('/login');
    }

    public function showRegister(Request $request): void
    {
        if (Auth::check()) {
            $this->redirectBasedOnRole(Auth::role());
        }

        Response::view('auth/register', [
            'title' => 'ลงทะเบียนผู้ใช้งาน - Smart IT Helpdesk',
            'flash' => Response::getFlash(),
        ], null);
    }

    public function register(Request $request): void
    {
        $name = trim($request->input('name', ''));
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        $passwordConfirm = $request->input('password_confirmation', '');

        if (empty($name) || empty($email) || empty($password)) {
            Response::setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
            Response::redirect('/register');
        }

        if ($password !== $passwordConfirm) {
            Response::setFlash('error', 'รหัสผ่านยืนยันไม่ตรงกัน');
            Response::redirect('/register');
        }

        if ($this->userRepo->findByEmail($email)) {
            Response::setFlash('error', 'อีเมลนี้ถูกลงทะเบียนไว้แล้วในระบบ');
            Response::redirect('/register');
        }

        $userId = $this->userRepo->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'user',
        ]);

        $newUser = $this->userRepo->find($userId);
        Auth::loginAs($newUser);

        Response::setFlash('success', 'ลงทะเบียนสำเร็จ! ยินดีต้อนรับเข้าสู่ระบบ');
        Response::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::setFlash('info', 'คุณได้ออกจากระบบเรียบร้อยแล้ว');
        Response::redirect('/login');
    }

    private function redirectBasedOnRole(string $role): void
    {
        if (!empty($_SESSION['intended_url'])) {
            $intended = $_SESSION['intended_url'];
            unset($_SESSION['intended_url']);
            Response::redirect($intended);
            return;
        }

        match ($role) {
            'admin' => Response::redirect('/admin/dashboard'),
            'technician' => Response::redirect('/technician/dashboard'),
            default => Response::redirect('/dashboard'),
        };
    }
}
