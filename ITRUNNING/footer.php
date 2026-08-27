    </main>

    <!-- ส่วนท้ายของเว็บไซต์ (Footer) - ซ่อนเมื่อสั่งพิมพ์ no-print -->
    <footer class="bg-slate-900 text-slate-400 mt-16 border-t border-slate-800 no-print print:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                
                <!-- ข้อมูลเว็บ -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-white font-bold text-lg">
                        <i data-lucide="trees" class="w-5 h-5 text-brand-500"></i>
                        <span>IT <span class="text-brand-400">วิ่งเข้าป่ามัน</span></span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        ระบบจัดการและรับสมัครงานวิ่งเทรลและงานวิ่งธรรมชาติ (Web Application ด้วย PHP + MySQL) พัฒนาขึ้นเพื่อการเรียนรู้และใช้งานจริง
                    </p>
                </div>

                <!-- เมนูด่วน -->
                <div>
                    <h4 class="font-bold text-white mb-3">เมนูหลัก</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="index.php" class="hover:text-white transition-colors">หน้าหลัก (Home)</a></li>
                        <li><a href="calendar.php" class="hover:text-white transition-colors">ปฏิทินงานวิ่ง (Calendar)</a></li>
                        <li><a href="results.php" class="hover:text-white transition-colors">ผลการแข่งขัน (Results)</a></li>
                        <li><a href="organizer.php" class="hover:text-white transition-colors">สำหรับผู้จัดงานและประชาสัมพันธ์ (Organizer & PR)</a></li>
                        <li><a href="contact.php" class="hover:text-white transition-colors">ติดต่อเรา (Contact)</a></li>
                        <li><a href="confirmation.php" class="hover:text-white transition-colors">ค้นหาบัตร E-Ticket</a></li>
                    </ul>
                </div>

                <!-- ข้อมูลติดต่อ -->
                <div class="space-y-2 text-xs">
                    <h4 class="font-bold text-white mb-3">ติดต่อสอบถาม</h4>
                    <p>📍 สำนักงาน: เขตป่ามันข้างๆ สำนักเทคโนโลยีสาขาสนเทศ</p>
                    <p>📞 โทรศัพท์: 088-123-4567</p>
                    <p>✉️ อีเมล: support@itwingkhaopaman.com</p>
                </div>

            </div>

            <div class="border-t border-slate-800 mt-8 pt-6 text-center text-xs text-slate-500">
                <p>&copy; 2026 IT วิ่งเข้าป่ามัน (RunLan Platform). พัฒนาด้วย PHP + MySQL</p>
            </div>
        </div>
    </footer>

    <!-- เรียกใช้งาน Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
