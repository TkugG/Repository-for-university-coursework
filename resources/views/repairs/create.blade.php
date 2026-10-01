<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มแจ้งซ่อมคอมพิวเตอร์และโน้ตบุ๊ก</title>
    <!-- ใช้งาน Tailwind CSS ผ่าน CDN เพื่อความง่าย สไตล์นักศึกษา IT -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">

    <!-- แถบด้านบน (Navbar) -->
    <header class="bg-indigo-600 text-white shadow-md">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">💻</span>
                <h1 class="text-xl font-bold tracking-wide">IT Repair Service</h1>
            </div>
            <a href="/track" class="text-sm bg-indigo-700 hover:bg-indigo-800 px-3 py-1.5 rounded-lg transition">
                🔍 ตรวจสอบสถานะงานซ่อม
            </a>
        </div>
    </header>

    <!-- เนื้อหาฟอร์มแจ้งซ่อม -->
    <main class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
            
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-slate-800">แบบฟอร์มแจ้งซ่อมคอมพิวเตอร์ / โน้ตบุ๊ก</h2>
                <p class="text-sm text-slate-500 mt-1">กรุณากรอกข้อมูลปัญหาเพื่อความรวดเร็วในการตรวจเช็คและประเมินงานซ่อม</p>
            </div>

            <!-- ฟอร์มส่งข้อมูล (สำคัญ: ต้องมี @csrf และ enctype สำหรับอัปโหลดรูป) -->
            <form action="/repairs" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- ข้อมูลผู้ติดต่อ -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-slate-700 mb-1">
                            ชื่อ-นามสกุล ผู้แจ้ง <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="customer_name" name="customer_name" required
                            placeholder="เช่น นายสมชาย ใจดี"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">
                            เบอร์โทรศัพท์ติดต่อ <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" required
                            placeholder="เช่น 0812345678"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition">
                    </div>
                </div>

                <!-- ข้อมูลอุปกรณ์ -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="device_type" class="block text-sm font-medium text-slate-700 mb-1">
                            ประเภทอุปกรณ์ <span class="text-red-500">*</span>
                        </label>
                        <select id="device_type" name="device_type" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm bg-white transition">
                            <option value="Notebook">โน้ตบุ๊ก (Notebook / Laptop)</option>
                            <option value="PC">คอมพิวเตอร์ตั้งโต๊ะ (PC Desktop)</option>
                        </select>
                    </div>

                    <div>
                        <label for="brand" class="block text-sm font-medium text-slate-700 mb-1">
                            ยี่ห้อ / รุ่น (ถ้าทราบ)
                        </label>
                        <input type="text" id="brand" name="brand"
                            placeholder="เช่น ASUS ROG, Acer Swift, คอมประกอบ"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition">
                    </div>
                </div>

                <!-- อาการเสีย -->
                <div>
                    <label for="problem_description" class="block text-sm font-medium text-slate-700 mb-1">
                        อาการเสีย / รายละเอียดปัญหา <span class="text-red-500">*</span>
                    </label>
                    <textarea id="problem_description" name="problem_description" rows="4" required
                        placeholder="ระบุอาการ เช่น เปิดไม่ติด มีเสียงติ๊ด 3 ครั้ง, หน้าจอฟ้า code: CRITICAL_PROCESS_DIED"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition"></textarea>
                </div>

                <!-- อัปโหลดรูปภาพ -->
                <div>
                    <label for="image" class="block text-sm font-medium text-slate-700 mb-1">
                        รูปถ่ายอาการเสีย / ตัวเครื่อง (ไม่บังคับ)
                    </label>
                    <input type="file" id="image" name="image" accept="image/*"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1">รองรับไฟล์รูปภาพ เช่น JPG, PNG</p>
                </div>

                <!-- ปุ่มกดยืนยัน -->
                <div class="pt-3">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-lg shadow-sm hover:shadow transition duration-200">
                        🚀 ยืนยันการแจ้งซ่อม
                    </button>
                </div>
            </form>

        </div>
    </main>

</body>
</html>
