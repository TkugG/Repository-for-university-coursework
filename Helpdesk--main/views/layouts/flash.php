<?php
$flash = $flash ?? \App\Core\Response::getFlash();
if ($flash):
    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';

    [$bgColor, $borderColor, $textColor, $iconName, $iconColor] = match ($type) {
        'success' => ['bg-emerald-50', 'border-emerald-300', 'text-emerald-900', 'check-circle', 'text-emerald-600'],
        'error' => ['bg-rose-50', 'border-rose-300', 'text-rose-900', 'alert-triangle', 'text-rose-600'],
        'warning' => ['bg-amber-50', 'border-amber-300', 'text-amber-900', 'alert-triangle', 'text-amber-600'],
        default => ['bg-blue-50', 'border-blue-300', 'text-blue-900', 'ticket', 'text-blue-600'],
    };
?>
<div id="flash-alert" class="mb-6 <?= $bgColor ?> border <?= $borderColor ?> <?= $textColor ?> px-4 py-3.5 rounded-2xl shadow-sm flex items-center justify-between transition-all duration-300">
    <div class="flex items-center gap-3">
        <span class="<?= $iconColor ?> shrink-0"><?= svg_icon($iconName, 'w-5 h-5') ?></span>
        <span class="font-bold text-base"><?= htmlspecialchars($message) ?></span>
    </div>
    <button onclick="document.getElementById('flash-alert').remove()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg text-xl leading-none">&times;</button>
</div>
<?php endif; ?>
