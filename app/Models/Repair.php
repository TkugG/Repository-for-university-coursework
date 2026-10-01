<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    // กำหนดฟิลด์ที่อนุญาตให้บันทึกลงฐานข้อมูลได้ (สไตล์ IT ปลอดภัย ไม่ซับซ้อน)
    protected $fillable = [
        'customer_name',
        'phone',
        'device_type',
        'brand',
        'problem_description',
        'image',
        'status',
    ];
}
