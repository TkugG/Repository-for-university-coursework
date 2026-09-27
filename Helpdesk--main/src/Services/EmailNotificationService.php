<?php

namespace App\Services;

use App\Core\Config;
use App\Repositories\UserRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailNotificationService
{
    private string $mailer;
    private string $fromAddress;
    private string $fromName;
    private string $appUrl;

    public function __construct()
    {
        $this->mailer = Config::get('MAIL_MAILER', 'log');
        $this->fromAddress = Config::get('MAIL_FROM_ADDRESS', 'support@helpdesk.local');
        $this->fromName = Config::get('MAIL_FROM_NAME', 'Smart IT Helpdesk');

        $dynamicUrl = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $dynamicUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . \App\Core\Request::basePath();
        }
        $this->appUrl = rtrim($dynamicUrl ?: Config::get('APP_URL', 'http://localhost/smart-it-helpdesk'), '/');
    }

    /**
     * Send email using configured driver (log, smtp, mail)
     * Supports optional embedded CID images for real email client delivery
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlContent, array $embeddedImages = [], array $imageUrls = []): bool
    {
        $logDir = dirname(__DIR__, 2) . '/storage/mail';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Always save a copy of outgoing HTML email for inspection / testing
        $sanitizedSubject = preg_replace('/[^a-zA-Z0-9_-]/', '_', substr($subject, 0, 30));
        $mailFile = sprintf('%s/%s_%s_%s.html', $logDir, date('Ymd_His'), preg_replace('/[^a-zA-Z0-9]/', '_', $toEmail), $sanitizedSubject);
        
        $metaHeader = "<!-- RECIPIENT: {$toName} <{$toEmail}> | SUBJECT: {$subject} | SENT_AT: " . date('Y-m-d H:i:s') . " -->\n";
        file_put_contents($mailFile, $metaHeader . $htmlContent);

        // Append to app log
        $appLogFile = dirname(__DIR__, 2) . '/storage/logs/email.log';
        $logEntry = sprintf("[%s] [MAIL SENT via %s] To: %s <%s> | Subject: %s | File: %s\n", 
            date('Y-m-d H:i:s'), $this->mailer, $toName, $toEmail, $subject, basename($mailFile));
        file_put_contents($appLogFile, $logEntry, FILE_APPEND);

        if ($this->mailer === 'log') {
            return true;
        }

        // PHPMailer SMTP Driver
        if ($this->mailer === 'smtp' && class_exists(PHPMailer::class)) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = Config::get('MAIL_HOST', 'smtp.gmail.com');
                $mail->SMTPAuth = !empty(Config::get('MAIL_USERNAME'));
                $mail->Username = Config::get('MAIL_USERNAME', '');
                $mail->Password = Config::get('MAIL_PASSWORD', '');
                
                $port = (int)Config::get('MAIL_PORT', 465);
                $encryption = strtolower(Config::get('MAIL_ENCRYPTION', 'ssl'));
                if ($port === 465 || $encryption === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($port === 587 || $encryption === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPSecure = false;
                    $mail->SMTPAutoTLS = false;
                }
                $mail->Port = $port;
                $mail->Timeout = 12;
                $mail->CharSet = 'UTF-8';

                // Allow self-signed or local certificates on XAMPP Windows
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];

                $fromEmail = Config::get('MAIL_FROM_ADDRESS') ?: $this->fromAddress;
                $fromName = Config::get('MAIL_FROM_NAME') ?: $this->fromName;
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($toEmail, $toName);
                $mail->isHTML(true);
                $mail->Subject = $subject;

                // Handle embedded CID images for SMTP delivery
                $smtpBody = $htmlContent;
                if (!empty($embeddedImages)) {
                    foreach ($embeddedImages as $cid => $imgPath) {
                        if (file_exists($imgPath)) {
                            $mimeType = mime_content_type($imgPath) ?: 'image/jpeg';
                            $mail->addEmbeddedImage($imgPath, $cid, basename($imgPath), 'base64', $mimeType);
                            if (!empty($imageUrls[$cid])) {
                                $smtpBody = str_replace($imageUrls[$cid], 'cid:' . $cid, $smtpBody);
                            }
                        }
                    }
                }

                $mail->Body = $smtpBody;
                $mail->send();

                $smtpLog = sprintf("[%s] [SMTP DELIVERED] To: %s <%s> | Subject: %s\n", 
                    date('Y-m-d H:i:s'), $toName, $toEmail, $subject);
                file_put_contents($appLogFile, $smtpLog, FILE_APPEND);
                return true;
            } catch (\Throwable $e) {
                $errLog = sprintf("[%s] [SMTP ERROR] To: %s <%s> | Error: %s\n", 
                    date('Y-m-d H:i:s'), $toName, $toEmail, $e->getMessage());
                file_put_contents($appLogFile, $errLog, FILE_APPEND);
                error_log("PHPMailer Error: " . $e->getMessage());
                return false;
            }
        }

        // Native PHP mail() Driver
        if ($this->mailer === 'mail' && function_exists('mail')) {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                "From: {$this->fromName} <{$this->fromAddress}>",
                "Reply-To: {$this->fromAddress}",
                'X-Mailer: PHP/' . phpversion(),
            ];
            return @mail($toEmail, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlContent, implode("\r\n", $headers));
        }

        return true;
    }

    /**
     * Test SMTP Connection & live delivery with detailed debug output
     */
    public function testSmtpConnection(string $toEmail, array $cfg = []): array
    {
        if (!class_exists(PHPMailer::class)) {
            return ['success' => false, 'message' => 'PHPMailer is not installed in vendor.', 'debug' => ''];
        }

        $host = $cfg['host'] ?? Config::get('MAIL_HOST', 'smtp.gmail.com');
        $port = (int)($cfg['port'] ?? Config::get('MAIL_PORT', 465));
        $username = $cfg['username'] ?? Config::get('MAIL_USERNAME', '');
        $password = $cfg['password'] ?? Config::get('MAIL_PASSWORD', '');
        $encryption = strtolower($cfg['encryption'] ?? Config::get('MAIL_ENCRYPTION', 'ssl'));
        $fromAddress = $cfg['from_address'] ?? Config::get('MAIL_FROM_ADDRESS', $username);
        $fromName = $cfg['from_name'] ?? Config::get('MAIL_FROM_NAME', 'Smart IT Helpdesk');

        $debugOutput = '';

        if (empty($password)) {
            return [
                'success' => false,
                'message' => "ไม่สามารถส่งอีเมลได้: ยังไม่ได้ระบุรหัสผ่าน SMTP Password ในไฟล์ .env หรือแบบฟอร์ม (สำหรับ Gmail ต้องใช้ 'รหัสผ่านสำหรับแอป (App Password)' 16 หลัก)",
                'debug' => "คำแนะนำ: Google ปิดระบบรับรหัสผ่านปกติเพื่อความปลอดภัย\nกรุณาสร้าง App Password 16 หลักที่ https://myaccount.google.com/apppasswords แล้วนำมากรอกในช่อง SMTP Password ด้านซ้าย แล้วกด 'บันทึกการตั้งค่าลงไฟล์ .env'",
            ];
        }

        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= $str . "\n";
        };

        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = !empty($username);
            $mail->Username = $username;
            $mail->Password = $password;

            if ($port === 465 || $encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($port === 587 || $encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            $mail->Port = $port;
            $mail->Timeout = 12;
            $mail->CharSet = 'UTF-8';

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($toEmail, 'Helpdesk Customer');
            $mail->isHTML(true);
            $mail->Subject = "🚀 [Test Live Email] ทดสอบส่งอีเมลจริงจากระบบ Smart IT Helpdesk";
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;'>
                    <h2 style='color: #4f46e5; margin-top: 0;'>🎉 ทดสอบส่งอีเมลจริงสำเร็จ!</h2>
                    <p style='color: #334155; font-size: 14px; line-height: 1.6;'>
                        ข้อความนี้ส่งตรงจากระบบ <strong>Smart IT Helpdesk</strong> บนเครื่องของคุณ ผ่านบริการ SMTP <strong>{$host}:{$port}</strong> เพื่อยืนยันว่าระบบสามารถส่งอีเมลจริงถึงกล่องจดหมายของลูกค้าได้อย่างสมบูรณ์แบบ
                    </p>
                    <div style='background: #f8fafc; border-left: 4px solid #4f46e5; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #475569;'>
                        <strong>รายละเอียดการส่ง:</strong><br>
                        • ผู้รับ: {$toEmail}<br>
                        • ผู้ส่ง: {$fromName} ({$fromAddress})<br>
                        • เวลาที่ส่ง: " . date('Y-m-d H:i:s') . "<br>
                        • สถานะ: เชื่อมต่อและยืนยันตัวตนสำเร็จ (SMTP Authenticated)
                    </div>
                    <p style='color: #64748b; font-size: 12px;'>ระบบ Smart IT Helpdesk พร้อมส่งแจ้งเตือนงานซ่อมถึงอีเมลลูกค้าจริงแล้ว</p>
                </div>
            ";
            $mail->send();

            return [
                'success' => true,
                'message' => "ส่งอีเมลจริงสำเร็จ! ข้อความทดสอบถูกส่งไปยัง {$toEmail} เรียบร้อยแล้ว กรุณาเปิดเช็คที่กล่องจดหมาย Inbox (หรือโฟลเดอร์ Junk/Spam)",
                'debug' => $debugOutput,
            ];
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $hint = "";
            if (str_contains($msg, '535') || str_contains($msg, 'BadCredentials') || str_contains($msg, 'Username and Password not accepted') || str_contains($msg, 'Could not authenticate')) {
                $hint = " Google ปฏิเสธการยืนยันตัวตน: สำหรับ Gmail ห้ามใช้รหัสผ่านล็อกอินปกติ ต้องใช้ 'รหัสผ่านสำหรับแอป (App Password)' 16 หลักที่สร้างจาก https://myaccount.google.com/apppasswords";
            } elseif (str_contains($msg, 'Connection: Failed to connect to server') || str_contains($msg, 'Connection timed out')) {
                $hint = " การเชื่อมต่อล้มเหลว: กรุณาใช้ Port 465 (SSL) เนื่องจากเครือข่ายบางแห่ง (เช่น มหาวิทยาลัย) บล็อก Port 587";
            }
            return [
                'success' => false,
                'message' => "ไม่สามารถส่งอีเมลได้: " . $msg . ($hint ? " | คำแนะนำ:" . $hint : ""),
                'debug' => $debugOutput,
            ];
        }
    }

    /**
     * 1. Ticket Created Event: Email to ALL Administrators and IT Team
     */
    public function notifyTicketCreated(array $ticket): void
    {
        try {
            $subject = "[Smart IT Helpdesk] 📥 มีใบแจ้งซ่อมใหม่เข้ามา (#TK-{$ticket['id']}) - {$ticket['title']}";
            $viewUrl = $this->appUrl . '/admin/tickets/' . $ticket['id'];

            // 1. Gather all admin accounts from database
            $userRepo = new \App\Repositories\UserRepository();
            $adminUsers = $userRepo->getAdmins();

            $recipients = [];
            foreach ($adminUsers as $admin) {
                if (!empty($admin['email']) && filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) {
                    $emailKey = strtolower(trim($admin['email']));
                    $recipients[$emailKey] = [
                        'name' => $admin['name'] ?? 'ผู้ดูแลระบบ',
                        'email' => trim($admin['email']),
                    ];
                }
            }

            // 2. Also include ADMIN_NOTIFICATION_EMAIL from .env if configured
            $envAdminEmail = Config::get('ADMIN_NOTIFICATION_EMAIL');
            if (!empty($envAdminEmail) && filter_var($envAdminEmail, FILTER_VALIDATE_EMAIL)) {
                $emailKey = strtolower(trim($envAdminEmail));
                if (!isset($recipients[$emailKey])) {
                    $recipients[$emailKey] = [
                        'name' => 'IT Admin Team',
                        'email' => trim($envAdminEmail),
                    ];
                }
            }

            // Fallback if no admin email exists
            if (empty($recipients)) {
                $recipients['admin@helpdesk.local'] = [
                    'name' => 'IT Admin Team',
                    'email' => 'admin@helpdesk.local',
                ];
            }

            // 3. Send notification to every administrator individually
            foreach ($recipients as $recipient) {
                try {
                    $html = $this->renderTemplate('ticket_created', [
                        'ticket' => $ticket,
                        'appUrl' => $this->appUrl,
                        'adminName' => $recipient['name'],
                        'adminEmail' => $recipient['email'],
                        'viewUrl' => $viewUrl,
                    ]);

                    $this->send($recipient['email'], $recipient['name'], $subject, $html);
                } catch (\Throwable $sendErr) {
                    error_log("Failed to send ticket.created email to {$recipient['email']}: " . $sendErr->getMessage());
                }
            }
        } catch (\Throwable $e) {
            error_log("notifyTicketCreated Error: " . $e->getMessage());
        }
    }

    /**
     * 2. Ticket Assigned Event: Email to Technician
     */
    public function notifyTicketAssigned(array $ticket, array $technician, ?string $adminNote = null): void
    {
        try {
            $subject = "[Smart IT Helpdesk] 🛠 คุณได้รับมอบหมายงานซ่อมใหม่ (#TK-{$ticket['id']})";

            $html = $this->renderTemplate('ticket_assigned', [
                'ticket' => $ticket,
                'technician' => $technician,
                'adminNote' => $adminNote,
                'appUrl' => $this->appUrl,
                'jobUrl' => $this->appUrl . '/technician/jobs/' . $ticket['id'],
            ]);

            $this->send($technician['email'], $technician['name'], $subject, $html);
        } catch (\Throwable $e) {
            error_log("notifyTicketAssigned Error: " . $e->getMessage());
        }
    }

    /**
     * 3. Status Updated Event: Email to User
     */
    public function notifyStatusUpdated(array $ticket, string $fromStatus, string $toStatus, ?string $note = null): void
    {
        try {
            // Don't send duplicate if it's 'resolved' or 'in_progress' from 'resolved'
            if ($toStatus === 'resolved' || ($fromStatus === 'resolved' && $toStatus === 'in_progress')) {
                return;
            }

            $subject = "🔄 [Ticket #{$ticket['id']}] อัปเดตสถานะ: " . strtoupper($toStatus);

            $html = $this->renderTemplate('status_updated', [
                'ticket' => $ticket,
                'fromStatus' => $fromStatus,
                'toStatus' => $toStatus,
                'note' => $note,
                'appUrl' => $this->appUrl,
            ]);

            $this->send($ticket['user_email'], $ticket['user_name'], $subject, $html);
        } catch (\Throwable $e) {
            error_log("notifyStatusUpdated Error: " . $e->getMessage());
        }
    }

    /**
     * 4. Ticket Resolved Event: Email to User with confirmation & rating link
     */
    public function notifyTicketResolved(array $ticket): void
    {
        try {
            $subject = "✅ [Ticket #{$ticket['id']}] งานซ่อมเสร็จสิ้นแล้ว - กรุณายืนยันปิดงานและให้คะแนน";

            // URL for action button that navigates to ticket detail and auto-opens rating modal
            $confirmUrl = $this->appUrl . '/tickets/' . $ticket['id'] . '?action=rate';

            $embeddedImages = [];
            $imageUrls = [];
            $proofImageUrl = null;

            if (!empty($ticket['proof_image_path'])) {
                $proofRelative = ltrim($ticket['proof_image_path'], '/');
                $proofImageUrl = $this->appUrl . '/' . $proofRelative;

                // Check potential local storage locations for inline CID embedding
                $baseDir = dirname(__DIR__, 2);
                $possiblePaths = [
                    $baseDir . '/storage/' . $proofRelative,
                    $baseDir . '/storage/uploads/' . basename($proofRelative),
                    $baseDir . '/public/' . $proofRelative,
                ];

                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && is_file($path)) {
                        $embeddedImages['proof_photo'] = $path;
                        $imageUrls['proof_photo'] = $proofImageUrl;
                        break;
                    }
                }
            }

            $html = $this->renderTemplate('ticket_resolved', [
                'ticket' => $ticket,
                'appUrl' => $this->appUrl,
                'confirmUrl' => $confirmUrl,
                'proofImageUrl' => $proofImageUrl,
            ]);

            $this->send($ticket['user_email'], $ticket['user_name'], $subject, $html, $embeddedImages, $imageUrls);
        } catch (\Throwable $e) {
            error_log("notifyTicketResolved Error: " . $e->getMessage());
        }
    }

    /**
     * 5. Ticket Rejected Event: Email to Technician when Requester Rejects Work
     */
    public function notifyTicketRejected(array $ticket, string $reason, array $actor): void
    {
        try {
            $subject = "[Smart IT Helpdesk] ⚠️ ผู้แจ้งซ่อมขอให้แก้ไขงานซ่อมเพิ่มเติม (#TK-{$ticket['id']})";

            $techEmail = $ticket['technician_email'] ?? null;
            $techName = $ticket['technician_name'] ?? 'ช่างเทคนิคผู้ปฏิบัติงาน';

            if (empty($techEmail) && !empty($ticket['technician_id'])) {
                $userRepo = new \App\Repositories\UserRepository();
                $techUser = $userRepo->find((int)$ticket['technician_id']);
                if ($techUser) {
                    $techEmail = $techUser['email'];
                    $techName = $techUser['name'];
                }
            }

            if (empty($techEmail)) {
                $techEmail = Config::get('ADMIN_NOTIFICATION_EMAIL', 'admin@helpdesk.local');
                $techName = 'IT Support Team';
            }

            $jobUrl = $this->appUrl . '/technician/jobs/' . $ticket['id'];

            $html = $this->renderTemplate('ticket_rejected', [
                'ticket' => $ticket,
                'reason' => $reason,
                'actor' => $actor,
                'technicianName' => $techName,
                'appUrl' => $this->appUrl,
                'jobUrl' => $jobUrl,
            ]);

            $this->send($techEmail, $techName, $subject, $html);
        } catch (\Throwable $e) {
            error_log("notifyTicketRejected Error: " . $e->getMessage());
        }
    }

    private function renderTemplate(string $templateName, array $data): string
    {
        extract($data);
        $file = dirname(__DIR__, 2) . "/views/emails/{$templateName}.php";
        if (file_exists($file)) {
            ob_start();
            require $file;
            return ob_get_clean();
        }

        // Fallback default HTML if template file is missing
        return "<h2>Smart IT Helpdesk Notification</h2><p>Ticket #{$ticket['id']}: {$ticket['title']}</p>";
    }
}
