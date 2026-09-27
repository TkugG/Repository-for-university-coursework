<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปเดตสถานะการแจ้งซ่อม</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #1e293b;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr style="background: linear-gradient(135deg, #4f46e5, #4338ca);">
                        <td style="padding: 24px 32px; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 20px; font-weight: 700;">Smart IT Helpdesk — แจ้งความคืบหน้า</h1>
                            <p style="margin: 4px 0 0; font-size: 14px; opacity: 0.9;">ใบงานของคุณมีการอัปเดตสถานะการดำเนินงาน</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <div style="background-color: #eef2ff; border-left: 4px solid #6366f1; padding: 16px; border-radius: 6px; margin-bottom: 24px;">
                                <span style="display: inline-block; padding: 4px 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; border-radius: 4px; background-color: #e0e7ff; color: #3730a3;">
                                    ใบงาน #<?= htmlspecialchars($ticket['id']) ?>
                                </span>
                                <h2 style="margin: 8px 0 0; font-size: 18px; color: #312e81;"><?= htmlspecialchars($ticket['title']) ?></h2>
                            </div>

                            <table width="100%" border="0" cellspacing="0" cellpadding="10" style="font-size: 14px; border-collapse: collapse; margin-bottom: 20px;">
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td width="30%" style="color: #64748b; font-weight: 600;">สถานะเดิม:</td>
                                    <td style="color: #64748b; text-decoration: line-through;"><?= htmlspecialchars(\App\Enums\TicketStatus::tryFrom($fromStatus)?->label() ?? $fromStatus) ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="color: #64748b; font-weight: 600;">สถานะใหม่:</td>
                                    <td>
                                        <strong style="color: #4f46e5; font-size: 16px;"><?= htmlspecialchars(\App\Enums\TicketStatus::tryFrom($toStatus)?->label() ?? $toStatus) ?></strong>
                                    </td>
                                </tr>
                                <?php if (!empty($ticket['technician_name'])): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="color: #64748b; font-weight: 600;">ช่างผู้รับผิดชอบ:</td>
                                    <td style="color: #0f172a; font-weight: 600;"><?= htmlspecialchars($ticket['technician_name']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($note)): ?>
                                <tr>
                                    <td style="color: #64748b; font-weight: 600; vertical-align: top;">บันทึกจากช่าง/ระบบ:</td>
                                    <td style="color: #1e293b; background-color: #f8fafc; padding: 12px; border-radius: 6px; font-style: italic;">
                                        "<?= nl2br(htmlspecialchars($note)) ?>"
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>

                            <div style="margin-top: 32px; text-align: center;">
                                <a href="<?= $appUrl ?>/tickets/<?= $ticket['id'] ?>" style="display: inline-block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 12px 28px; font-size: 14px; font-weight: 600; border-radius: 8px; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);">
                                    ดูรายละเอียด &amp; ติดตามความคืบหน้า &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <td style="padding: 16px 32px; text-align: center; font-size: 12px; color: #94a3b8;">
                            ข้อความนี้ถูกส่งอัตโนมัติจากระบบ Smart IT Helpdesk & Notification Engine
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
