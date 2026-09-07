<?php
require_once __DIR__ . '/ShippingStrategy.php';
require_once __DIR__ . '/KerryStrategy.php';
require_once __DIR__ . '/FlashStrategy.php';
require_once __DIR__ . '/ThaiPostEmsStrategy.php';

/**
 * Class ShippingStrategyFactory
 * Factory & Registry สำหรับสร้างและบริหาร Strategy ทั้งหมด
 * ช่วยให้ระบบบรรลุหลักการ Open/Closed Principle (OCP) อย่างสมบูรณ์
 */
class ShippingStrategyFactory {
    /**
     * @var array<string, array{class: class-string<ShippingStrategy>, name: string, desc: string}>
     */
    private static array $registry = [
        'kerry' => [
            'class' => KerryStrategy::class,
            'name'  => 'Kerry Express 📦',
            'desc'  => 'บริการพรีเมียม ส่งด่วน เรทเริ่มต้น ฿35 (ตจว. +฿15, เกาะ +฿50)',
        ],
        'flash' => [
            'class' => FlashStrategy::class,
            'name'  => 'Flash Express ⚡',
            'desc'  => 'ราคาประหยัดสำหรับร้านค้า เรทเริ่มต้น ฿28 (ตจว. +฿10, เกาะ +฿50)',
        ],
        'ems'   => [
            'class' => ThaiPostEmsStrategy::class,
            'name'  => 'ไปรษณีย์ไทย EMS 📮',
            'desc'  => 'มาตรฐาน ปณท. เข้าถึงทุกพื้นที่ เรทเริ่มต้น ฿32 (ตจว. +฿5, เกาะ +฿20)',
        ],
    ];

    /**
     * สร้าง Instance ของ ShippingStrategy ตาม Provider Key
     * 
     * @throws InvalidArgumentException หากไม่พบ Provider ที่ระบุ
     */
    public static function create(string $providerKey): ShippingStrategy {
        if (!isset(self::$registry[$providerKey])) {
            throw new InvalidArgumentException("ไม่พบผู้ให้บริการขนส่งรหัส '{$providerKey}' ในระบบ");
        }

        $class = self::$registry[$providerKey]['class'];
        return new $class();
    }

    /**
     * ดึง Instance ของ Strategy ทั้งหมดในระบบ (ใช้สำหรับเปรียบเทียบราคาแบบ All-in-one)
     * 
     * @return ShippingStrategy[]
     */
    public static function getAll(): array {
        $instances = [];
        foreach (self::$registry as $item) {
            $class = $item['class'];
            $instances[] = new $class();
        }
        return $instances;
    }

    /**
     * ดึงข้อมูล Metadata ของ Provider ทั้งหมดสำหรับนำไป Render บน UI
     * 
     * @return array<string, array{name: string, desc: string}>
     */
    public static function getAvailableProviders(): array {
        return self::$registry;
    }

    /**
     * ลงทะเบียน Strategy เจ้าใหม่เข้าสู่ระบบในแบบ Dynamic (Open for Extension)
     * 
     * @param string $key รหัสระบุขนส่ง เช่น 'jnt', 'dhl'
     * @param class-string<ShippingStrategy> $className
     */
    public static function register(string $key, string $className, string $name, string $desc = ''): void {
        if (!is_subclass_of($className, ShippingStrategy::class)) {
            throw new InvalidArgumentException("Class '{$className}' ต้อง implement ShippingStrategy interface");
        }

        self::$registry[$key] = [
            'class' => $className,
            'name'  => $name,
            'desc'  => $desc,
        ];
    }
}
