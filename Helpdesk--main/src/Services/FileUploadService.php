<?php

namespace App\Services;

class FileUploadService
{
    private string $uploadDir;
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'zip'];
    private int $maxSize = 5242880; // 5MB

    public function __construct()
    {
        $this->uploadDir = dirname(__DIR__, 2) . '/storage/uploads/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function upload(?array $file): ?string
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > $this->maxSize) {
            throw new \RuntimeException('ขนาดไฟล์เกินขีดจำกัด (สูงสุด 5MB)');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new \RuntimeException('ประเภทไฟล์ไม่ได้รับอนุญาต (อนุญาตเฉพาะรูปภาพ, PDF, TXT, ZIP)');
        }

        $filename = uniqid('proof_', true) . '.' . $extension;
        $destination = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('ไม่สามารถบันทึกไฟล์ได้ กรุณาตรวจสอบสิทธิ์การเขียนโฟลเดอร์ storage/uploads');
        }

        return 'uploads/' . $filename;
    }
}
