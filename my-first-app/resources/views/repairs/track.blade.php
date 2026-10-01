<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบสถานะงานซ่อม - IT Repair Service</title>
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

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-4 py-8 w-full flex-grow">
        
        <!-- กล่องค้นหา -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">🔍 ค้นหาและติดตามสถานะงานซ่อม</h2>
                <p class="text-sm text-slate-500 mt-1">กรอกเบอร์โทรศัพท์ที่ใช้แจ้ง หรือรหัสติดตามงาน เพื่อดูความคืบหน้า</p>
            </div>

            <form action="/track" method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ $search ?? '' }}" required
                    placeholder="พิมพ์เบอร์โทรศัพท์ (เช่น 0812345678) หรือรหัสงานซ่อม (เช่น 1)"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg text-sm transition">
                    ค้นหาข้อมูล
                </button>
            </form>
        </div>

        <!-- ส่วนแสดงผลการค้นหา -->
        @if($repairs !== null)
            @if($repairs->count() > 0)
                <div class="space-y-4">
                    <p class="text-sm text-slate-500 font-medium">พบรายการแจ้งซ่อมทั้งหมด {{ $repairs->count() }} รายการ:</p>
                    
                    @foreach($repairs as $item)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 md:p-6 transition hover:shadow-md">
                            
                            <!-- แถวหัวการ์ด: รหัส + วันที่ + สถานะ -->
                            <div class="flex flex-wrap justify-between items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                                <div>
                                    <span class="text-lg font-bold text-indigo-600">#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-xs text-slate-400 ml-2">วันที่แจ้ง: {{ $item->created_at->format('d/m/Y H:i น.') }}</span>
                                </div>

                                <!-- ป้ายสีสถานะ -->
                                @if($item->status == 'รอดำเนินการ')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                        ⏳ รอดำเนินการ
                                    </span>
                                @elseif($item->status == 'กำลังซ่อม')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-sky-100 text-sky-800">
                                        🔧 กำลังดำเนินการซ่อม
                                    </span>
                                @elseif($item->status == 'ซ่อมเสร็จ')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                        ✅ ซ่อมเสร็จเรียบร้อย
                                    </span>
                                @elseif($item->status == 'ปิดงาน')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">
                                        📦 ส่งมอบเครื่องแล้ว (ปิดงาน)
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">
                                        ❌ ยกเลิก
                                    </span>
                                @endif
                            </div>

                            <!-- รายละเอียดข้อมูล -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-600">
                                <div>
                                    <p><span class="font-medium text-slate-700">ชื่อผู้แจ้ง:</span> {{ $item->customer_name }}</p>
                                    <p><span class="font-medium text-slate-700">เบอร์โทร:</span> {{ $item->phone }}</p>
                                    <p class="mt-1">
                                        <span class="font-medium text-slate-700">อุปกรณ์:</span> 
                                        <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs">
                                            {{ $item->device_type }}
                                        </span>
                                        {{ $item->brand ? "($item->brand)" : '' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-700">อาการเสียที่ระบุ:</p>
                                    <p class="text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 mt-1 text-xs">
                                        {{ $item->problem_description }}
                                    </p>
                                </div>
                            </div>

                            <!-- รูปภาพแนบ (ถ้ามี) -->
                            @if($item->image)
                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-3">
                                    <span class="text-xs text-slate-400">รูปภาพประกอบ:</span>
                                    <a href="{{ asset('storage/' . $item->image) }}" target="_blank"
                                       class="text-xs text-indigo-600 hover:underline flex items-center gap-1 font-medium">
                                        🖼️ คลิกเพื่อดูรูปขนาดเต็ม
                                    </a>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            @else
                <!-- ไม่พบข้อมูล -->
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                    <span class="text-4xl block mb-2">🔍</span>
                    <h3 class="text-base font-semibold text-slate-700">ไม่พบรายการแจ้งซ่อม</h3>
                    <p class="text-sm text-slate-400 mt-1">กรุณาตรวจสอบเบอร์โทรศัพท์หรือรหัสงานซ่อมใหม่อีกครั้ง</p>
                </div>
            @endif
        @endif

    </main>

    <!-- Footer -->
    <footer class="text-center py-4 text-xs text-slate-400">
        IT Repair System &copy; 2026 - สไตล์นักศึกษา IT
    </footer>

</body>
</html>
