<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');                  // ชื่อผู้แจ้งซ่อม
            $table->string('phone');                          // เบอร์โทรศัพท์
            $table->string('device_type');                    // ประเภทอุปกรณ์ (PC / Notebook)
            $table->string('brand')->nullable();              // ยี่ห้อ/รุ่น (เว้นว่างได้)
            $table->text('problem_description');              // อาการเสีย/รายละเอียด
            $table->string('image')->nullable();              // รูปภาพอาการเสีย (เว้นว่างได้)
            $table->string('status')->default('รอดำเนินการ');  // สถานะเริ่มต้น
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
