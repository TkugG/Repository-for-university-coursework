<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RepairController;

// หน้าแรกของเว็บ ให้เปิดหน้าฟอร์มแจ้งซ่อมเลย
Route::get('/', [RepairController::class, 'create']);
Route::get('/repair', [RepairController::class, 'create']);

// รับข้อมูลจากฟอร์มเพื่อบันทึกลงฐานข้อมูล
Route::post('/repairs', [RepairController::class, 'store']);

// หน้าค้นหาและติดตามสถานะงานซ่อม (สำหรับผู้ใช้งาน)
Route::get('/track', [RepairController::class, 'track']);
