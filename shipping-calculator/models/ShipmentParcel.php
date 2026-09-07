<?php
require_once __DIR__ . '/DestinationZone.php';
require_once __DIR__ . '/ThailandProvinces.php';

/**
 * Class ShipmentParcel (DTO)
 * รวบรวมข้อมูลพัสดุ ขนาด มิติ น้ำหนักจริง จังหวัดต้นทาง-ปลายทาง และระยะทางขับรถจริง
 */
class ShipmentParcel {
    private string $originProvinceName;
    private string $destProvinceName;
    private float $distanceKm;
    private DestinationZone $destinationZone;
    private DestinationZone $originZone;

    public function __construct(
        private float $weight,
        private float $width,
        private float $length,
        private float $height,
        private string $originCode = 'TH-10',  // กรุงเทพมหานคร
        private string $destCode = 'TH-61'    // อุทัยธานี
    ) {
        $pOrigin = ThailandProvinces::get($this->originCode) ?? ThailandProvinces::get('TH-10');
        $pDest   = ThailandProvinces::get($this->destCode) ?? ThailandProvinces::get('TH-61');

        // 💡 [ทำไมต้องแบบนี้]: แมปข้อมูลพิกัดและโซนของจังหวัดต้นทาง-ปลายทางทันทีตั้งแต่ตอนสร้าง Object
        $this->originProvinceName = $pOrigin['name'];
        $this->destProvinceName   = $pDest['name'];

        $this->originZone      = DestinationZone::fromValue($pOrigin['zone']);
        $this->destinationZone = DestinationZone::fromValue($pDest['zone']);

        // 🎯 [หัวใจหลัก]: คำนวณระยะทางขับรถจริง (กม.) ผ่านโมเดล ThailandProvinces เตรียมไว้ให้ Strategy นำไปคิดค่าส่ง
        $this->distanceKm = ThailandProvinces::calculateDistance($this->originCode, $this->destCode);
    }

    public function getWeight(): float {
        return $this->weight;
    }

    public function getWidth(): float {
        return $this->width;
    }

    public function getLength(): float {
        return $this->length;
    }

    public function getHeight(): float {
        return $this->height;
    }

    public function getOriginCode(): string {
        return $this->originCode;
    }

    public function getDestCode(): string {
        return $this->destCode;
    }

    public function getOriginProvinceName(): string {
        return $this->originProvinceName;
    }

    public function getDestProvinceName(): string {
        return $this->destProvinceName;
    }

    public function getDistanceKm(): float {
        return $this->distanceKm;
    }

    public function getOriginZone(): DestinationZone {
        return $this->originZone;
    }

    public function getDestinationZone(): DestinationZone {
        return $this->destinationZone;
    }

    // 🎯 [หัวใจหลัก]: สูตรคำนวณน้ำหนักปริมาตรตามมาตรฐานสากล IATA (กว้าง x ยาว x สูง / 5000)
    // 💡 [ทำไมต้องแบบนี้]: สำหรับพัสดุกล่องใหญ่แต่น้ำหนักเบา (เช่น หมอน/โมเดล) เพื่อชดเชยพื้นที่บรรทุกบนรถขนส่ง
    public function getVolumetricWeight(): float {
        $volumeWeight = ($this->width * $this->length * $this->height) / 5000.0;
        return round($volumeWeight, 2);
    }

    // 🎯 [หัวใจหลัก]: คัดเลือกค่าน้ำหนักที่มากกว่าระหว่าง "น้ำหนักชั่งจริง" กับ "น้ำหนักปริมาตร" เพื่อใช้คิดเงิน (Chargeable Weight)
    public function getChargeableWeight(): float {
        return max($this->weight, $this->getVolumetricWeight());
    }

    public function getTotalDimension(): float {
        return $this->width + $this->length + $this->height;
    }

    public function isCrossZone(): bool {
        return $this->originCode !== $this->destCode;
    }
}
