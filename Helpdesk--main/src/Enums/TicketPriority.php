<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'ต่ำ',
            self::NORMAL => 'ปกติ',
            self::HIGH => 'สูง',
            self::URGENT => 'ด่วนที่สุด',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::LOW => 'bg-slate-100 text-slate-800 border border-slate-300 font-medium shadow-2xs',
            self::NORMAL => 'bg-blue-50 text-blue-900 border border-blue-200 font-bold shadow-2xs',
            self::HIGH => 'bg-orange-100 text-orange-950 border border-orange-300 font-bold shadow-2xs',
            self::URGENT => 'bg-rose-100 text-rose-950 border border-rose-300 font-black shadow-2xs',
        };
    }

    public function slaHours(): int
    {
        return match ($this) {
            self::URGENT => 4,
            self::HIGH => 8,
            self::NORMAL => 24,
            self::LOW => 48,
        };
    }

    public function sortWeight(): int
    {
        return match ($this) {
            self::URGENT => 4,
            self::HIGH => 3,
            self::NORMAL => 2,
            self::LOW => 1,
        };
    }
}
