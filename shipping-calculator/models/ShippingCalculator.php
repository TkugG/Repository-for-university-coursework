<?php
require_once __DIR__ . '/ShippingStrategy.php';
require_once __DIR__ . '/ShipmentParcel.php';
require_once __DIR__ . '/ShippingFeeBreakdown.php';

/**
 * Class ShippingCalculator
 * Context Class: ตัวควบคุมและประมวลผลค่าจัดส่ง
 * รองรับ Dependency Injection และมี Guard Clauses ตรวจสอบความถูกต้องของพัสดุ
 */
class ShippingCalculator {
    public function __construct(
        private ShippingStrategy $strategy
    ) {}

    public function setStrategy(ShippingStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    public function getProviderName(): string {
        return $this->strategy->getProviderName();
    }

    /**
     * คำนวณค่าจัดส่ง พร้อมตรวจสอบความถูกต้องของข้อมูลพัสดุ (Validation Guard)
     * 
     * @throws InvalidArgumentException หากข้อมูลพัสดุไม่ถูกต้อง
     */
    public function calculate(ShipmentParcel $parcel): ShippingFeeBreakdown {
        $weight = $parcel->getWeight();
        $w = $parcel->getWidth();
        $l = $parcel->getLength();
        $h = $parcel->getHeight();

        // ตรวจสอบน้ำหนักชั่งจริง
        if (is_nan($weight) || is_infinite($weight) || $weight <= 0.0 || $weight > 100.0) {
            throw new InvalidArgumentException("น้ำหนักพัสดุต้องอยู่ระหว่าง 0.01 ถึง 100.00 กิโลกรัม");
        }

        // ตรวจสอบขนาดมิติกล่อง
        foreach (['ความกว้าง' => $w, 'ความยาว' => $l, 'ความสูง' => $h] as $dimName => $dimVal) {
            if (is_nan($dimVal) || is_infinite($dimVal) || $dimVal <= 0.0 || $dimVal > 250.0) {
                throw new InvalidArgumentException("{$dimName} ต้องเป็นตัวเลขระหว่าง 0.1 ถึง 250.0 ซม.");
            }
        }

        if ($parcel->getTotalDimension() > 400.0) {
            throw new InvalidArgumentException("ขนาดรวม 3 ด้าน (กว้าง+ยาว+สูง) ต้องไม่เกิน 400 ซม.");
        }

        return $this->strategy->calculateFee($parcel);
    }
}