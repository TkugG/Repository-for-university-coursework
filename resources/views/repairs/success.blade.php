<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งซ่อมสำเร็จ - IT Repair Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col justify-between">

    <!-- Header -->
    <header class="bg-indigo-600 text-white shadow-md">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">💻</span>
                <h1 class="text-xl font-bold tracking-wide">IT Repair Service</h1>
            </div>
            <a href="/repair" class="text-sm bg-indigo-700 hover:bg-indigo-800 px-3 py-1.5 rounded-lg transition">
                + แจ้งซ่อมรายการใหม่
            </a>
        </div>
    </header>

    <!-- Main Card -->
    <main class="max-w-xl mx-auto px-4 py-10 w-full">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8 text-center">
            
            <!-- ไอคอนสำเร็จ -->
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                ✓
            </div>

            <h2 class="text-2xl font-bold text-slate-800">ส่งข้อมูลแจ้งซ่อมสำเร็จ!</h2>
            <p class="text-sm text-slate-500 mt-1">ระบบได้บันทึกข้อมูลของท่านเรียบร้อยแล้ว ช่างจะรีบดำเนินการตรวจสอบ</p>

            <!-- กล่องแสดงรหัสติดตามงาน -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 my-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-200 pb-3 mb-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">รหัสติดตามงาน (Repair ID)</span>
                    <span class="text-xl font-bold text-indigo-600">#{{ str_pad($repair->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div class="space-y-2 text-sm text-slate-600">
                    <p><span class="font-medium text-slate-700">ชื่อผู้แจ้ง:</span> {{ $repair->customer_name }}</p>
                    <p><span class="font-medium text-slate-700">เบอร์โทรศัพท์:</span> {{ $repair->phone }}</p>
                    <p><span class="font-medium text-slate-700">อุปกรณ์:</span> {{ $repair->device_type }} {{ $repair->brand ? "($repair->brand)" : '' }}</p>
                    <p><span class="font-medium text-slate-700">อาการเสีย:</span> {{ $repair->problem_description }}</p>
                    <div class="pt-2 flex items-center justify-between">
                        <span class="font-medium text-slate-700">สถานะปัจจุบัน:</span>
                        <span class="px-2.5 py-1 text-xs font-medium bg-amber-100 text-amber-800 rounded-full">
                            ⏳ {{ $repair->status }}
                        </span>
                    </div>
                </div>

                @if($repair->image)
                    <div class="mt-4 pt-3 border-t border-slate-200">
                        <span class="text-xs text-slate-400 block mb-2">รูปถ่ายที่แนบมา:</span>
                        <img src="{{ asset('storage/' . $repair->image) }}" alt="รูปอาการเสีย" class="max-h-48 rounded-lg mx-auto border border-slate-200">
                    </div>
                @endif
            </div>

            <!-- ปุ่มดำเนินการต่อ -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="/repair" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg text-sm transition">
                    แจ้งซ่อมรายการอื่นเพิ่ม
                </a>
                <a href="/track" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-2.5 rounded-lg text-sm transition">
                    ตรวจสอบสถานะงานซ่อม
                </a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center py-4 text-xs text-slate-400">
        IT Repair System &copy; 2026 - สไตล์นักศึกษา IT
    </footer>

</body>
</html>
