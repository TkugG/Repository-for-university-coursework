<?php
$currentUser = \App\Core\Auth::user();
$currentRole = $currentUser['role'] ?? 'guest';
$roleEnum = \App\Enums\UserRole::tryFrom($currentRole);
$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = \App\Core\Request::basePath();
$currentPath = ($basePath !== '' && str_starts_with($rawUri, $basePath)) ? substr($rawUri, strlen($basePath)) : $rawUri;
?>
<nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-18 py-2">
            <!-- Brand & Primary Nav -->
            <div class="flex items-center gap-5">
                <a href="<?= url('/') ?>" class="flex items-center gap-3 text-indigo-600 font-bold text-lg tracking-tight hover:opacity-95 transition shrink-0 whitespace-nowrap">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center shadow-md shadow-indigo-200 shrink-0">
                        <?= svg_icon('building', 'w-6 h-6') ?>
                    </div>
                    <span class="text-base sm:text-lg font-extrabold leading-tight">ระบบแจ้งซ่อมและงานอาคาร</span>
                </a>

                <?php if ($currentUser): ?>
                <div class="hidden xl:flex items-center gap-1">
                    <?php if ($currentRole === 'user'): ?>
                        <a href="<?= url('/dashboard') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('clipboard', 'w-4 h-4 text-indigo-600') ?>
                            <span>แดชบอร์ดของฉัน</span>
                        </a>
                        <a href="<?= url('/tickets/create') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/tickets/create') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('plus-circle', 'w-4 h-4 text-indigo-600') ?>
                            <span>แจ้งซ่อมใหม่</span>
                        </a>
                    <?php elseif ($currentRole === 'technician'): ?>
                        <a href="<?= url('/technician/dashboard') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/technician') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('wrench', 'w-4 h-4 text-indigo-600') ?>
                            <span>คิวงานซ่อมของฉัน</span>
                        </a>
                    <?php elseif ($currentRole === 'admin'): ?>
                        <a href="<?= url('/admin/dashboard') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= $currentPath === '/admin/dashboard' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('chart-bar', 'w-4 h-4 text-indigo-600') ?>
                            <span>สถิติภาพรวม</span>
                        </a>
                        <a href="<?= url('/admin/tickets') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/admin/tickets') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('ticket', 'w-4 h-4 text-indigo-600') ?>
                            <span>จ่ายงานและติดตาม</span>
                        </a>
                        <a href="<?= url('/admin/parts-requests') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/admin/parts-requests') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('package', 'w-4 h-4 text-indigo-600') ?>
                            <span>อนุมัติอะไหล่</span>
                        </a>
                        <a href="<?= url('/admin/inventory') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/admin/inventory') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('wrench', 'w-4 h-4 text-indigo-600') ?>
                            <span>คลังอะไหล่</span>
                        </a>
                        <a href="<?= url('/admin/users') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/admin/users') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('users', 'w-4 h-4 text-indigo-600') ?>
                            <span>ผู้ใช้งาน</span>
                        </a>
                        <a href="<?= url('/admin/categories') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl text-sm font-semibold whitespace-nowrap <?= str_starts_with($currentPath, '/admin/categories') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' ?> transition">
                            <?= svg_icon('tag', 'w-4 h-4 text-indigo-600') ?>
                            <span>หมวดหมู่</span>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Profile Area -->
            <div class="flex items-center gap-2.5 shrink-0">
                <?php if ($currentUser): ?>

                    <!-- Role Badge -->
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold shadow-xs whitespace-nowrap <?= $roleEnum ? $roleEnum->badgeClass() : 'bg-slate-100 text-slate-800' ?>">
                        <?= htmlspecialchars($roleEnum ? $roleEnum->label() : $currentRole) ?>
                    </span>

                    <!-- User Name & Logout -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200 shrink-0 whitespace-nowrap">
                        <span class="text-sm text-slate-800 hidden md:inline font-semibold whitespace-nowrap"><?= htmlspecialchars($currentUser['name']) ?></span>
                        <a href="<?= url('/logout') ?>" title="ออกจากระบบ" class="inline-flex items-center gap-1.5 text-sm bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-3 py-1.5 rounded-xl transition border border-rose-200 whitespace-nowrap">
                            <?= svg_icon('logout', 'w-4 h-4 text-rose-600') ?>
                            <span>ออกจากระบบ</span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="text-base font-semibold text-indigo-600 hover:text-indigo-800 px-4 py-2">เข้าสู่ระบบ</a>
                    <a href="<?= url('/register') ?>" class="text-base font-bold bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-sm">สมัครสมาชิก</a>
                <?php endif; ?>

                <!-- Mobile Menu Button (Toggle) -->
                <?php if ($currentUser): ?>
                <button type="button" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="xl:hidden p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" aria-label="Toggle Navigation">
                    <?= svg_icon('filter', 'w-6 h-6') ?>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <?php if ($currentUser): ?>
        <div id="mobile-nav" class="hidden xl:hidden border-t border-slate-200 py-3 space-y-1 bg-white">
            <?php if ($currentRole === 'user'): ?>
                <a href="<?= url('/dashboard') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('clipboard', 'w-5 h-5 text-indigo-600') ?> แดชบอร์ดของฉัน
                </a>
                <a href="<?= url('/tickets/create') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/tickets/create') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('plus-circle', 'w-5 h-5 text-indigo-600') ?> แจ้งซ่อมใหม่
                </a>
            <?php elseif ($currentRole === 'technician'): ?>
                <a href="<?= url('/technician/dashboard') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/technician') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('wrench', 'w-5 h-5 text-indigo-600') ?> คิวงานซ่อมของฉัน
                </a>
            <?php elseif ($currentRole === 'admin'): ?>
                <a href="<?= url('/admin/dashboard') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= $currentPath === '/admin/dashboard' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('chart-bar', 'w-5 h-5 text-indigo-600') ?> สถิติภาพรวม
                </a>
                <a href="<?= url('/admin/tickets') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/admin/tickets') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('ticket', 'w-5 h-5 text-indigo-600') ?> จ่ายงานและติดตาม
                </a>
                <a href="<?= url('/admin/parts-requests') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/admin/parts-requests') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('package', 'w-5 h-5 text-indigo-600') ?> อนุมัติเบิกอะไหล่
                </a>
                <a href="<?= url('/admin/inventory') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/admin/inventory') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('wrench', 'w-5 h-5 text-indigo-600') ?> คลังอะไหล่
                </a>
                <a href="<?= url('/admin/users') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/admin/users') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('users', 'w-5 h-5 text-indigo-600') ?> ผู้ใช้งาน
                </a>
                <a href="<?= url('/admin/categories') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-base font-semibold <?= str_starts_with($currentPath, '/admin/categories') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
                    <?= svg_icon('tag', 'w-5 h-5 text-indigo-600') ?> หมวดหมู่
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</nav>

