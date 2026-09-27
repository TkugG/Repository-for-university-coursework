<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Smart IT Helpdesk') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    },
                    fontFamily: {
                        sans: ['Sarabun', 'Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts: Sarabun & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js CDN for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="<?= asset('/assets/css/style.css') ?>?v=<?= time() ?>">
    <!-- Embedded Capsule & Filter Ergonomics -->
    <style>
        .capsule-track {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 0.625rem !important;
            padding: 0.5rem !important;
            background-color: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 1.25rem !important;
            width: 100% !important;
        }

        /* Admin Grid Ergonomics: 2 rows of 4 on standard screens, 8 on ultra-wide */
        .admin-capsule-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.625rem !important;
            padding: 0.75rem !important;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 1.25rem !important;
            width: 100% !important;
        }
        @media (min-width: 640px) {
            .admin-capsule-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }
        @media (min-width: 1536px) {
            .admin-capsule-grid {
                grid-template-columns: repeat(8, minmax(0, 1fr)) !important;
            }
        }

        .filter-chip,
        .capsule-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            padding: 0.625rem 1.25rem !important;
            border-radius: 9999px !important;
            font-size: 0.9375rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            cursor: pointer !important;
            white-space: nowrap !important;
            min-height: 46px !important;
            text-decoration: none !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.15s ease-in-out !important;
        }
        .admin-capsule-grid .capsule-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
            padding: 0.625rem 1rem !important;
        }
        .filter-chip:hover,
        .capsule-btn:hover {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
            transform: translateY(-1px);
        }
        .filter-chip.active,
        .capsule-btn.active {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            border-color: #4338ca !important;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35) !important;
        }
        .chip-count,
        .capsule-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 1.625rem !important;
            height: 1.5rem !important;
            padding: 0 0.5rem !important;
            border-radius: 9999px !important;
            font-size: 0.8125rem !important;
            font-weight: 800 !important;
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
            line-height: 1 !important;
        }
        .filter-chip.active .chip-count,
        .capsule-btn.active .capsule-badge {
            background-color: #ffffff !important;
            color: #4338ca !important;
        }

        /* Master Table Column Typography & Contrast */
        .admin-ticket-table th {
            font-size: 0.875rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.025em !important;
            color: #334155 !important;
            white-space: nowrap !important;
            background-color: #f8fafc !important;
        }
        .admin-ticket-table td {
            font-size: 0.9375rem !important;
            color: #0f172a !important;
            vertical-align: middle !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-full flex flex-col antialiased">

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="flex-1 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php require __DIR__ . '/flash.php'; ?>
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/footer.php'; ?>

    <!-- Core App JS -->
    <script src="<?= asset('/assets/js/app.js') ?>"></script>
</body>
</html>
