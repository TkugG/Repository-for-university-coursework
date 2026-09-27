<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case TECHNICIAN = 'technician';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'ผู้แจ้งซ่อม',
            self::TECHNICIAN => 'ช่างเทคนิค',
            self::ADMIN => 'ผู้ดูแลระบบ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::USER => 'bg-sky-100 text-sky-800 border border-sky-200',
            self::TECHNICIAN => 'bg-amber-100 text-amber-800 border border-amber-200',
            self::ADMIN => 'bg-purple-100 text-purple-800 border border-purple-200',
        };
    }
}
