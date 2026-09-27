<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>คุณได้รับมอบหมายงานซ่อมใหม่ (#TK-<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?>)</title>
    <!--[if mso]>
    <style type="text/css">
        table {border-collapse:collapse;border-spacing:0;margin:0;}
        div, td {padding:0;}
        div {margin:0 !important;}
    </style>
    <![endif]-->
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap');
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .content-padding { padding: 20px 16px !important; }
            .header-padding { padding: 28px 16px !important; }
            .btn-action { width: 100% !important; box-sizing: border-box !important; text-align: center !important; }
        }
    </style>
</head>
<body style="font-family: 'Prompt', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px 0; color: #1e293b; -webkit-font-smoothing: antialiased;">

    <!-- Preheader preview text -->
    <div style="display: none; font-size: 1px; color: #f1f5f9; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        คุณได้รับมอบหมายงานซ่อมใหม่ #TK-<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?>: <?= htmlspecialchars((string)($ticket['title'] ?? '')) ?> ณ <?= htmlspecialchars((string)($ticket['location'] ?? '-')) ?>
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9;">
        <tr>
            <td align="center" style="padding: 12px;">
                <table class="email-container" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04); max-width: 600px; width: 100%; border: 1px solid #e2e8f0;">
                    
                    <!-- 1. Header Banner -->
                    <tr style="background: linear-gradient(135deg, #d97706 0%, #b45309 50%, #78350f 100%); background-color: #d97706;">
                        <td class="header-padding" style="padding: 36px 32px; color: #ffffff; text-align: center;">
                            <div style="display: inline-block; padding: 5px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 9999px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); margin-bottom: 14px; color: #ffffff;">
                                🛠️ Smart IT Helpdesk &bull; Work Order Assigned
                            </div>
                            <div style="font-size: 48px; line-height: 1; margin-bottom: 10px;">👨‍🔧</div>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; line-height: 1.3; color: #ffffff;">
                                คุณได้รับมอบหมายงานซ่อมใหม่
                            </h1>
                            <p style="margin: 8px 0 0; font-size: 14px; color: #fef3c7; opacity: 0.95; line-height: 1.5;">
                                เรียนคุณ <strong><?= htmlspecialchars((string)($technician['name'] ?? 'ช่างเทคนิค')) ?></strong>, หัวหน้างานได้มอบหมายงานซ่อมนี้ให้คุณ
                            </p>
                        </td>
                    </tr>

                    <!-- 2. Email Body -->
                    <tr>
                        <td class="content-padding" style="padding: 32px;">
                            
                            <!-- Ticket Card Summary -->
                            <div style="background-color: #fffbeb; border-left: 5px solid #f59e0b; border-top: 1px solid #fef3c7; border-right: 1px solid #fef3c7; border-bottom: 1px solid #fef3c7; padding: 18px 20px; border-radius: 12px; margin-bottom: 24px;">
                                <div style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 700; text-transform: uppercase; border-radius: 6px; background-color: #fef3c7; color: #92400e; margin-bottom: 8px;">
                                    ใบแจ้งซ่อม #TK-<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?> &bull; สถานะ: มอบหมายแล้ว (Assigned)
                                </div>
                                <h2 style="margin: 4px 0 6px; font-size: 19px; color: #78350f; font-weight: 700; line-height: 1.4;">
                                    <?= htmlspecialchars((string)($ticket['title'] ?? '')) ?>
                                </h2>
                                <p style="margin: 0; font-size: 13px; color: #b45309; line-height: 1.5;">
                                    📍 สถานที่: <strong><?= htmlspecialchars((string)($ticket['location'] ?? '-')) ?></strong>
                                    <?php if (!empty($ticket['category_name'])): ?>
                                        &nbsp;&bull;&nbsp; หมวดหมู่: <strong><?= htmlspecialchars((string)$ticket['category_name']) ?></strong>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Admin Note Box (If provided) -->
                            <?php 
                            $noteText = $adminNote ?? ($ticket['admin_note'] ?? null);
                            if (!empty($noteText)): 
                            ?>
                            <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 14px 16px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                    💬 หมายเหตุคำสั่งการจาก Admin / Dispatcher:
                                </div>
                                <p style="margin: 0; font-size: 13px; color: #1e293b; line-height: 1.5;">
                                    <?= nl2br(htmlspecialchars($noteText)) ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <!-- 3. Details Table -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; border-collapse: separate; overflow: hidden;">
                                <tr style="background-color: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                                    <td colspan="2" style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #334155; border-bottom: 1px solid #e2e8f0;">
                                        📋 รายละเอียดใบสั่งงานซ่อม
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px 8px; font-size: 13px; color: #64748b; width: 140px; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        ผู้แจ้งซ่อม:
                                    </td>
                                    <td style="padding: 12px 16px 8px; font-size: 14px; font-weight: 600; color: #0f172a; border-bottom: 1px solid #f1f5f9;">
                                        👤 <?= htmlspecialchars((string)($ticket['user_name'] ?? 'ผู้ใช้งานทั่วไป')) ?>
                                        <?php if (!empty($ticket['user_email'])): ?>
                                            <span style="font-size: 12px; color: #64748b; font-weight: normal;">(<?= htmlspecialchars((string)$ticket['user_email']) ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #64748b; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        สถานที่หน้างาน:
                                    </td>
                                    <td style="padding: 8px 16px; font-size: 14px; font-weight: 600; color: #0f172a; border-bottom: 1px solid #f1f5f9;">
                                        📍 <?= htmlspecialchars((string)($ticket['location'] ?? '-')) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #64748b; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        หมวดหมู่งาน:
                                    </td>
                                    <td style="padding: 8px 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">
                                        🏷️ <?= htmlspecialchars((string)($ticket['category_name'] ?? '-')) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #64748b; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        ระดับความเร่งด่วน:
                                    </td>
                                    <td style="padding: 8px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                                        <?php
                                            $p = strtolower((string)($ticket['priority'] ?? 'normal'));
                                            $pColor = match($p) {
                                                'urgent' => '#dc2626',
                                                'high' => '#ea580c',
                                                'low' => '#16a34a',
                                                default => '#d97706'
                                            };
                                            $pBg = match($p) {
                                                'urgent' => '#fee2e2',
                                                'high' => '#ffedd5',
                                                'low' => '#dcfce7',
                                                default => '#fef3c7'
                                            };
                                        ?>
                                        <span style="display: inline-block; padding: 3px 10px; font-size: 11px; font-weight: 700; border-radius: 9999px; background-color: <?= $pBg ?>; color: <?= $pColor ?>;">
                                            ⚡ <?= strtoupper($p) ?> (<?= htmlspecialchars(\App\Enums\TicketPriority::tryFrom($p)?->label() ?? $p) ?>)
                                        </span>
                                    </td>
                                </tr>
                                <?php if (!empty($ticket['sla_due_at'])): ?>
                                <tr>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #64748b; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        กำหนดเวลาแก้ไข (SLA):
                                    </td>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #b45309; font-weight: 600; border-bottom: 1px solid #f1f5f9;">
                                        ⏰ ภายใน <?= date('d/m/Y H:i', strtotime($ticket['sla_due_at'])) ?> น.
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td style="padding: 10px 16px 12px; font-size: 13px; color: #64748b; vertical-align: top;">
                                        รายละเอียดปัญหา:
                                    </td>
                                    <td style="padding: 10px 16px 12px; font-size: 14px; color: #334155; line-height: 1.6;">
                                        <?= nl2br(htmlspecialchars((string)($ticket['description'] ?? ''))) ?>
                                    </td>
                                </tr>
                            </table>

                            <!-- 4. Action Button -->
                            <?php $targetUrl = $jobUrl ?? ($appUrl . '/technician/jobs/' . $ticket['id']); ?>
                            <div style="text-align: center; margin: 32px 0 20px 0;">
                                <table border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0 auto;">
                                    <tr>
                                        <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #d97706 0%, #b45309 100%); background-color: #d97706; box-shadow: 0 6px 18px rgba(217, 119, 6, 0.35);">
                                            <a href="<?= htmlspecialchars($targetUrl) ?>" 
                                               target="_blank" 
                                               class="btn-action"
                                               style="font-family: 'Prompt', 'Segoe UI', sans-serif; font-size: 16px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 18px 38px; border-radius: 12px; display: inline-block; letter-spacing: 0.3px; border: 1px solid rgba(255,255,255,0.2);">
                                                🛠️ เปิดหน้ารับงานและเริ่มการซ่อม &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="font-size: 12px; color: #64748b; margin: 12px 0 0 0;">
                                    คลิกเพื่อเข้าสู่ระบบช่างเทคนิคในการกดรับงาน เดินทาง และบันทึกการซ่อม
                                </p>
                            </div>

                            <!-- 5. Fallback Link -->
                            <div style="border-top: 1px dashed #e2e8f0; padding-top: 16px; margin-top: 24px; text-align: center;">
                                <p style="font-size: 11px; color: #94a3b8; margin: 0 0 4px 0;">
                                    หากปุ่มไม่ทำงาน สามารถคัดลอกลิงก์นี้เปิดในเบราว์เซอร์:
                                </p>
                                <a href="<?= htmlspecialchars($targetUrl) ?>" style="font-size: 11px; color: #d97706; word-break: break-all; text-decoration: underline;">
                                    <?= htmlspecialchars($targetUrl) ?>
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- 6. Footer -->
                    <tr style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <td style="padding: 22px 32px; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                            ข้อความนี้เป็นการแจ้งเตือนอัตโนมัติจากระบบ <strong>Smart IT Helpdesk &amp; Work Order Engine</strong><br>
                            สำหรับช่างเทคนิคผู้ได้รับมอบหมายงาน
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
