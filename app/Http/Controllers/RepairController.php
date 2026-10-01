<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repair;

class RepairController extends Controller
{
    // แสดงหน้าฟอร์มแจ้งซ่อม
    public function create()
    {
        return view('repairs.create');
    }

    // บันทึกข้อมูลการแจ้งซ่อมลงฐานข้อมูล
    public function store(Request $request)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (Validation)
        $request->validate([
            'customer_name' => 'required',
            'phone' => 'required',
            'device_type' => 'required',
            'problem_description' => 'required',
            'image' => 'nullable|image|max:2048', // ไม่เกิน 2MB
        ]);

        // 2. จัดการอัปโหลดรูปภาพ (ถ้ามีแนบมา)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('repairs', 'public');
        }

        // 3. บันทึกลงตาราง repairs
        $repair = Repair::create([
            'customer_name' => $request->customer_name,
            'phone' => $request->phone,
            'device_type' => $request->device_type,
            'brand' => $request->brand,
            'problem_description' => $request->problem_description,
            'image' => $imagePath,
            'status' => 'รอดำเนินการ',
        ]);

        // 4. แสดงหน้าแจ้งผลสำเร็จ พร้อมส่งข้อมูลไปแสดง
        return view('repairs.success', ['repair' => $repair]);
    }

    // ตรวจสอบและค้นหาสถานะงานซ่อม (สำหรับผู้ใช้งาน)
    public function track(Request $request)
    {
        $search = $request->input('search');
        $repairs = null;

        if ($search) {
            // ค้นหาจากเบอร์โทร หรือรหัส ID
            $repairs = Repair::where('phone', 'like', "%{$search}%")
                ->orWhere('id', $search)
                ->latest()
                ->get();
        }

        return view('repairs.track', compact('repairs', 'search'));
    }
}
