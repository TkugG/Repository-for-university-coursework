<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>ผู้แจ้งซ่อมขอให้แก้ไขงานซ่อมเพิ่มเติม (#TK-<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?>)</title>
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
        ผู้แจ้งปฏิเสธผลงาน #TK-<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?> และขอให้เข้าแก้ไขเพิ่มเติม: <?= htmlspecialchars((string)($reason ?? 'ปัญหายังไม่หาย')) ?>
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9;">
        <tr>
            <td align="center" style="padding: 12px;">
                <table class="email-container" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04); max-width: 600px; width: 100%; border: 1px solid #e2e8f0;">
                    
                    <!-- 1. Header Banner -->
                    <tr style="background: linear-gradient(135deg, #e11d48 0%, #be123c 50%, #881337 100%); background-color: #e11d48;">
                        <td class="header-padding" style="padding: 36px 32px; color: #ffffff; text-align: center;">
                            <div style="display: inline-block; padding: 5px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 9999px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); margin-bottom: 14px; color: #ffffff;">
                                ⚠️ Smart IT Helpdesk &bull; Work Order Re-Opened
                            </div>
                            <div style="font-size: 48px; line-height: 1; margin-bottom: 10px;">🔄</div>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; line-height: 1.3; color: #ffffff;">
                                ผู้แจ้งซ่อมขอให้แก้ไขงานซ่อมเพิ่มเติม
                            </h1>
                            <p style="margin: 8px 0 0; font-size: 14px; color: #ffe4e6; opacity: 0.95; line-height: 1.5;">
                                เรียนคุณ <strong><?= htmlspecialchars((string)($technicianName ?? 'ช่างเทคนิคผู้ปฏิบัติงาน')) ?></strong>, ผู้แจ้งตรวจสอบแล้วพบว่าปัญหายังไม่หาย
                            </p>
                        </td>
                    </tr>

                    <!-- 2. Email Body -->
                    <tr>
                        <td class="content-padding" style="padding: 32px;">
                            
                            <!-- Ticket Card Summary -->
                            <div style="background-color: #fff1f2; border-left: 5px solid #e11d48; border-top: 1px solid #ffe4e6; border-right: 1px solid #ffe4e6; border-bottom: 1px solid #ffe4e6; padding: 18px 20px; border-radius: 12px; margin-bottom: 24px;">
                                <div style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 700; text-transform: uppercase; border-radius: 6px; background-color: #ffe4e6; color: #9f1239; margin-bottom: 8px;">
                                    ใบแจ้งซ่อม #TK-<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?> &bull; สถานะ: ตีกลับดำเนินการต่อ (In Progress)
                                </div>
                                <h2 style="margin: 4px 0 6px; font-size: 19px; color: #881337; font-weight: 700; line-height: 1.4;">
                                    <?= htmlspecialchars((string)($ticket['title'] ?? '')) ?>
                                </h2>
                                <p style="margin: 0; font-size: 13px; color: #be123c; line-height: 1.5;">
                                    📍 สถานที่: <strong><?= htmlspecialchars((string)($ticket['location'] ?? '-')) ?></strong>
                                    <?php if (!empty($ticket['category_name'])): ?>
                                        &nbsp;&bull;&nbsp; หมวดหมู่: <strong><?= htmlspecialchars((string)$ticket['category_name']) ?></strong>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Rejection Reason Callout Box -->
                            <div style="background-color: #fef2f2; border: 2px solid #fecdd3; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                                <div style="font-size: 13px; font-weight: 700; color: #991b1b; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                    <span>⚠️</span> เหตุผลที่ผู้แจ้งขอให้เข้าดำเนินการแก้ไขเพิ่มเติม:
                                </div>
                                <div style="background-color: #ffffff; border-radius: 8px; border: 1px solid #fee2e2; padding: 12px 16px; font-size: 14px; color: #b91c1c; line-height: 1.6; font-weight: 500;">
                                    &ldquo;<?= nl2br(htmlspecialchars((string)($reason ?: 'ผู้แจ้งทดสอบแล้วระบุว่ายังพบอาการเดิม อุปกรณ์ยังทำงานไม่สมบูรณ์'))) ?>&rdquo;
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 8px;">
                                    ส่งเรื่องโดย: <strong><?= htmlspecialchars((string)($actor['name'] ?? ($ticket['user_name'] ?? 'ผู้แจ้งซ่อม'))) ?></strong> ณ วันที่ <?= date('d/m/Y H:i') ?> น.
                                </div>
                            </div>

                            <!-- 3. Details Table -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; border-collapse: separate; overflow: hidden;">
                                <tr style="background-color: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                                    <td colspan="2" style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #334155; border-bottom: 1px solid #e2e8f0;">
                                        📋 รายละเอียดใบงาน
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
                                <?php if (!empty($ticket['solution_note'])): ?>
                                <tr>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #64748b; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        การซ่อมครั้งก่อนหน้า:
                                    </td>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #475569; line-height: 1.5; border-bottom: 1px solid #f1f5f9;">
                                        <?= nl2br(htmlspecialchars((string)$ticket['solution_note'])) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td style="padding: 10px 16px 12px; font-size: 13px; color: #64748b; vertical-align: top;">
                                        อาการเริ่มต้น:
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
                                        <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); background-color: #e11d48; box-shadow: 0 6px 18px rgba(225, 29, 72, 0.35);">
                                            <a href="<?= htmlspecialchars($targetUrl) ?>" 
                                               target="_blank" 
                                               class="btn-action"
                                               style="font-family: 'Prompt', 'Segoe UI', sans-serif; font-size: 16px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 18px 38px; border-radius: 12px; display: inline-block; letter-spacing: 0.3px; border: 1px solid rgba(255,255,255,0.2);">
                                                🔧 เปิดหน้างานเพื่อตรวจสอบและแก้ไขต่อ &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="font-size: 12px; color: #64748b; margin: 12px 0 0 0;">
                                    สถานะงานถูกปรับกลับเป็น In Progress ให้ท่านเข้าตรวจสอบและแก้ไขปัญหาอีกครั้ง
                                </p>
                            </div>

                            <!-- 5. Fallback Link -->
                            <div style="border-top: 1px dashed #e2e8f0; padding-top: 16px; margin-top: 24px; text-align: center;">
                                <p style="font-size: 11px; color: #94a3b8; margin: 0 0 4px 0;">
                                    หากปุ่มไม่ทำงาน สามารถคัดลอกลิงก์นี้เปิดในเบราว์เซอร์:
                                </p>
                                <a href="<?= htmlspecialchars($targetUrl) ?>" style="font-size: 11px; color: #e11d48; word-break: break-all; text-decoration: underline;">
                                    <?= htmlspecialchars($targetUrl) ?>
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- 6. Footer -->
                    <tr style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <td style="padding: 22px 32px; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                            ข้อความนี้เป็นการแจ้งเตือนอัตโนมัติจากระบบ <strong>Smart IT Helpdesk &amp; Work Order Engine</strong><br>
                            สำหรับช่างเทคนิคผู้ปฏิบัติงาน
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
