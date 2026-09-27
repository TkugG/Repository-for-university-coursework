<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>งานซ่อมเสร็จสิ้นแล้ว - กรุณายืนยันและประเมินความพึงพอใจ (Ticket #<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?>)</title>
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
            .stack-cell { display: block !important; width: 100% !important; }
        }
    </style>
</head>
<body style="font-family: 'Prompt', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px 0; color: #1e293b; -webkit-font-smoothing: antialiased;">

    <!-- Preheader preview text (visible in inbox summary, hidden in email body) -->
    <div style="display: none; font-size: 1px; color: #f1f5f9; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        งานซ่อมใบแจ้งซ่อม #<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?> เสร็จสิ้นแล้ว กรุณาคลิกเพื่อประเมินความพึงพอใจและตรวจสอบภาพถ่ายผลงานหลังซ่อม
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9;">
        <tr>
            <td align="center" style="padding: 12px;">
                <table class="email-container" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04); max-width: 600px; width: 100%; border: 1px solid #e2e8f0;">
                    
                    <!-- 1. Header Banner -->
                    <tr style="background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #047857 100%); background-color: #059669;">
                        <td class="header-padding" style="padding: 36px 32px; color: #ffffff; text-align: center;">
                            <!-- System Brand Pill -->
                            <div style="display: inline-block; padding: 5px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 9999px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); margin-bottom: 14px; color: #ffffff;">
                                🛠️ Smart IT Helpdesk &bull; แจ้งเตือนสถานะงาน
                            </div>
                            <!-- Success Icon -->
                            <div style="font-size: 50px; line-height: 1; margin-bottom: 10px;">🎉</div>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; line-height: 1.3; color: #ffffff;">
                                การซ่อมแซมเสร็จสิ้นเรียบร้อยแล้ว
                            </h1>
                            <p style="margin: 8px 0 0; font-size: 14px; color: #ecfdf5; opacity: 0.95; line-height: 1.5;">
                                ช่างเทคนิคได้ดำเนินการแก้ไขปัญหาและส่งมอบงานให้ท่านตรวจสอบ
                            </p>
                        </td>
                    </tr>

                    <!-- 2. Email Body -->
                    <tr>
                        <td class="content-padding" style="padding: 32px;">
                            
                            <!-- Ticket Card Summary -->
                            <div style="background-color: #f0fdf4; border-left: 5px solid #10b981; border-top: 1px solid #dcfce7; border-right: 1px solid #dcfce7; border-bottom: 1px solid #dcfce7; padding: 18px 20px; border-radius: 12px; margin-bottom: 24px;">
                                <div style="display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 700; text-transform: uppercase; border-radius: 6px; background-color: #d1fae5; color: #065f46; margin-bottom: 8px;">
                                    ใบแจ้งซ่อม #<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?> &bull; สถานะ: ซ่อมเสร็จแล้ว (Resolved)
                                </div>
                                <h2 style="margin: 4px 0 6px; font-size: 19px; color: #064e3b; font-weight: 700; line-height: 1.4;">
                                    <?= htmlspecialchars((string)($ticket['title'] ?? '')) ?>
                                </h2>
                                <p style="margin: 0; font-size: 13px; color: #047857; line-height: 1.5;">
                                    📍 สถานที่: <strong><?= htmlspecialchars((string)($ticket['location'] ?? '-')) ?></strong>
                                    <?php if (!empty($ticket['category_name'])): ?>
                                        &nbsp;&bull;&nbsp; หมวดหมู่: <strong><?= htmlspecialchars((string)$ticket['category_name']) ?></strong>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Greeting -->
                            <p style="font-size: 15px; line-height: 1.7; color: #334155; margin: 0 0 20px 0;">
                                เรียนคุณ <strong><?= htmlspecialchars((string)($ticket['user_name'] ?? 'ผู้ใช้งาน')) ?></strong>,<br>
                                ช่างเทคนิคผู้รับผิดชอบได้ดำเนินการตรวจสอบและแก้ไขปัญหาตามใบแจ้งซ่อมของท่านเรียบร้อยแล้ว โดยมีรายละเอียดรายงานการปฏิบัติงานดังต่อไปนี้:
                            </p>

                            <!-- 3. Repair Report Table -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; border-collapse: separate; overflow: hidden;">
                                <tr style="background-color: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                                    <td colspan="2" style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #334155; border-bottom: 1px solid #e2e8f0;">
                                        📋 รายละเอียดรายงานผลการซ่อมแซมจากช่าง
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px 6px; font-size: 13px; color: #64748b; width: 140px; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        ช่างผู้ปฏิบัติงาน:
                                    </td>
                                    <td style="padding: 12px 16px 6px; font-size: 14px; font-weight: 600; color: #0f172a; border-bottom: 1px solid #f1f5f9;">
                                        👨‍🔧 <?= htmlspecialchars((string)($ticket['technician_name'] ?? 'IT Support Team')) ?>
                                        <?php if (!empty($ticket['technician_email'])): ?>
                                            <span style="font-size: 12px; color: #64748b; font-weight: normal;">(<?= htmlspecialchars((string)$ticket['technician_email']) ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (!empty($ticket['root_cause'])): ?>
                                <tr>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #64748b; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        สาเหตุของปัญหา:
                                    </td>
                                    <td style="padding: 8px 16px; font-size: 14px; color: #334155; line-height: 1.5; border-bottom: 1px solid #f1f5f9;">
                                        <?= nl2br(htmlspecialchars((string)$ticket['root_cause'])) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($ticket['solution_note'])): ?>
                                <tr>
                                    <td style="padding: 8px 16px; font-size: 13px; color: #64748b; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                        วิธีการแก้ไขปัญหา:
                                    </td>
                                    <td style="padding: 8px 16px; font-size: 14px; color: #047857; font-weight: 600; line-height: 1.5; border-bottom: 1px solid #f1f5f9;">
                                        <?= nl2br(htmlspecialchars((string)$ticket['solution_note'])) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($ticket['actual_hours'])): ?>
                                <tr>
                                    <td style="padding: 8px 16px 12px; font-size: 13px; color: #64748b; vertical-align: top;">
                                        เวลาที่ใช้ซ่อมจริง:
                                    </td>
                                    <td style="padding: 8px 16px 12px; font-size: 14px; font-weight: 600; color: #334155;">
                                        ⏱️ <?= number_format((float)$ticket['actual_hours'], 1) ?> ชั่วโมง
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>

                            <!-- 4. Proof Photo Section (รูปภาพหลังซ่อม) -->
                            <?php 
                            $displayProofUrl = $proofImageUrl ?? (!empty($ticket['proof_image_path']) ? ($appUrl . '/' . ltrim($ticket['proof_image_path'], '/')) : null);
                            if (!empty($displayProofUrl)): 
                            ?>
                            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 18px; margin-bottom: 28px; text-align: center;">
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>📸</span> รูปถ่ายผลงานหลังการซ่อมแซมเสร็จสิ้น
                                </div>
                                <div style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; background-color: #ffffff; padding: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                    <img src="<?= htmlspecialchars($displayProofUrl) ?>" 
                                         alt="ภาพถ่ายหลังซ่อมแซม ใบแจ้งซ่อม #<?= htmlspecialchars((string)($ticket['id'] ?? '')) ?>" 
                                         style="max-width: 100%; width: 100%; height: auto; max-height: 320px; object-fit: cover; border-radius: 8px; display: block; margin: 0 auto;">
                                </div>
                                <p style="font-size: 11px; color: #64748b; margin: 8px 0 0 0;">
                                    หลักฐานการส่งมอบงานจริง ณ หน้างาน โดยช่างเทคนิค
                                </p>
                            </div>
                            <?php endif; ?>

                            <!-- 5. Primary Action Button (คลิกเพื่อประเมินความพึงพอใจ) -->
                            <div style="text-align: center; margin: 36px 0 24px 0;">
                                <table border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0 auto;">
                                    <tr>
                                        <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #059669 0%, #047857 100%); background-color: #059669; box-shadow: 0 6px 18px rgba(5, 150, 105, 0.35);">
                                            <a href="<?= htmlspecialchars($confirmUrl) ?>" 
                                               target="_blank" 
                                               class="btn-action"
                                               style="font-family: 'Prompt', 'Segoe UI', sans-serif; font-size: 16px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 18px 38px; border-radius: 12px; display: inline-block; letter-spacing: 0.3px; border: 1px solid rgba(255,255,255,0.2);">
                                                ⭐ คลิกเพื่อประเมินความพึงพอใจ &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="font-size: 13px; color: #475569; margin: 16px auto 0; max-width: 480px; line-height: 1.6;">
                                    กดปุ่มเพื่อเปิดหน้าแบบฟอร์มประเมินความพึงพอใจ (ให้คะแนน 1-5 ดาว และข้อเสนอแนะ) โดยอัตโนมัติ
                                </p>
                                
                                <div style="margin-top: 14px; padding: 12px 16px; background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; font-size: 12px; color: #92400e; line-height: 1.5; text-align: left;">
                                    <strong>💡 หมายเหตุ:</strong> หากตรวจสอบแล้วอุปกรณ์ยังทำงานไม่สมบูรณ์หรือไม่สามารถใช้งานได้ ท่านสามารถกด <strong>"ปฏิเสธการแก้ไข (Reject)"</strong> ในระบบ เพื่อส่งเรื่องให้ช่างเข้าดำเนินการต่อได้ทันที
                                </div>
                            </div>

                            <!-- 6. Direct Link Fallback -->
                            <div style="border-top: 1px dashed #e2e8f0; padding-top: 18px; margin-top: 24px; text-align: center;">
                                <p style="font-size: 11px; color: #94a3b8; margin: 0 0 6px 0; line-height: 1.4;">
                                    หากปุ่มด้านบนไม่ทำงาน สามารถคลิกหรือคัดลอกลิงก์ด้านล่างไปเปิดในเบราว์เซอร์:
                                </p>
                                <a href="<?= htmlspecialchars($confirmUrl) ?>" style="font-size: 11px; color: #059669; word-break: break-all; text-decoration: underline;">
                                    <?= htmlspecialchars($confirmUrl) ?>
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- 7. Footer -->
                    <tr style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <td style="padding: 22px 32px; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                            ข้อความนี้เป็นการแจ้งเตือนอัตโนมัติจากระบบ <strong>Smart IT Helpdesk &amp; Work Order Engine</strong><br>
                            หากมีข้อสงสัยหรือต้องการความช่วยเหลือ สามารถเข้าสู่ระบบได้ที่ 
                            <a href="<?= htmlspecialchars($appUrl) ?>" style="color: #059669; text-decoration: none; font-weight: 600;">
                                <?= htmlspecialchars($appUrl) ?>
                            </a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
