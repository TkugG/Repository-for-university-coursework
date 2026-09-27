<?php

namespace App\Enums;

enum TicketStatus: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case ASSIGNED = 'assigned';
    case EN_ROUTE = 'en_route';
    case IN_PROGRESS = 'in_progress';
    case WAITING_PARTS = 'waiting_parts';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'รออนุมัติรับเรื่อง',
            self::APPROVED => 'อนุมัติรับเรื่องแล้ว',
            self::ASSIGNED => 'มอบหมายช่างแล้ว',
            self::EN_ROUTE => 'ช่างกำลังเดินทาง',
            self::IN_PROGRESS => 'กำลังดำเนินการซ่อม',
            self::WAITING_PARTS => 'รออะไหล่หรืออุปกรณ์',
            self::RESOLVED => 'ซ่อมเสร็จแล้ว รอตรวจรับ',
            self::CLOSED => 'ปิดงานสมบูรณ์',
            self::CANCELLED => 'ยกเลิกคำขอ',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'รออนุมัติ',
            self::APPROVED => 'อนุมัติแล้ว',
            self::ASSIGNED => 'มอบหมายแล้ว',
            self::EN_ROUTE => 'กำลังเดินทาง',
            self::IN_PROGRESS => 'กำลังซ่อม',
            self::WAITING_PARTS => 'รออะไหล่',
            self::RESOLVED => 'ซ่อมเสร็จแล้ว',
            self::CLOSED => 'ปิดงานแล้ว',
            self::CANCELLED => 'ยกเลิกแล้ว',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'bg-amber-100/90 text-amber-950 border border-amber-300 font-bold shadow-2xs',
            self::APPROVED => 'bg-sky-100/90 text-sky-950 border border-sky-300 font-bold shadow-2xs',
            self::ASSIGNED => 'bg-purple-100/90 text-purple-950 border border-purple-300 font-bold shadow-2xs',
            self::EN_ROUTE => 'bg-violet-100/90 text-violet-950 border border-violet-300 font-bold animate-pulse shadow-2xs',
            self::IN_PROGRESS => 'bg-blue-100/90 text-blue-950 border border-blue-300 font-bold shadow-2xs',
            self::WAITING_PARTS => 'bg-orange-100/90 text-orange-950 border border-orange-300 font-bold shadow-2xs',
            self::RESOLVED => 'bg-teal-100/90 text-teal-950 border border-teal-300 font-bold shadow-2xs',
            self::CLOSED => 'bg-emerald-100/90 text-emerald-950 border border-emerald-300 font-bold shadow-2xs',
            self::CANCELLED => 'bg-rose-100/90 text-rose-950 border border-rose-300 font-bold shadow-2xs',
        };
    }

    public function iconName(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'clock',
            self::APPROVED => 'check-circle',
            self::ASSIGNED => 'user',
            self::EN_ROUTE => 'truck',
            self::IN_PROGRESS => 'wrench',
            self::WAITING_PARTS => 'package',
            self::RESOLVED => 'check-circle',
            self::CLOSED => 'check',
            self::CANCELLED => 'x-circle',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'bg-amber-500',
            self::APPROVED => 'bg-cyan-500',
            self::ASSIGNED => 'bg-blue-500',
            self::EN_ROUTE => 'bg-purple-500',
            self::IN_PROGRESS => 'bg-indigo-500',
            self::WAITING_PARTS => 'bg-orange-500',
            self::RESOLVED => 'bg-emerald-500',
            self::CLOSED => 'bg-slate-400',
            self::CANCELLED => 'bg-rose-500',
        };
    }

    public function stepIndex(): int
    {
        return match ($this) {
            self::PENDING_APPROVAL => 1,
            self::APPROVED => 2,
            self::ASSIGNED => 2,
            self::EN_ROUTE => 3,
            self::IN_PROGRESS => 4,
            self::WAITING_PARTS => 4,
            self::RESOLVED => 5,
            self::CLOSED => 6,
            self::CANCELLED => 0,
        };
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING_APPROVAL, self::APPROVED, self::ASSIGNED], true);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING_APPROVAL => [self::APPROVED, self::ASSIGNED, self::CANCELLED],
            self::APPROVED => [self::ASSIGNED, self::CANCELLED],
            self::ASSIGNED => [self::EN_ROUTE, self::IN_PROGRESS, self::ASSIGNED, self::CANCELLED],
            self::EN_ROUTE => [self::IN_PROGRESS, self::WAITING_PARTS, self::CANCELLED],
            self::IN_PROGRESS => [self::WAITING_PARTS, self::RESOLVED],
            self::WAITING_PARTS => [self::IN_PROGRESS, self::RESOLVED],
            self::RESOLVED => [self::CLOSED, self::IN_PROGRESS],
            self::CLOSED => [],
            self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
