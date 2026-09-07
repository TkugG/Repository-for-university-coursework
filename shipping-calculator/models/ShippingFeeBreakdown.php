<?php

/**
 * Class ShippingFeeBreakdown (DTO)
 * ใบสรุปรายการแจกแจงค่าบริการจัดส่งพัสดุอย่างเป็นทางการ
 */
class ShippingFeeBreakdown {
    /**
     * @param float $baseFee ค่าบริการพื้นฐานเริ่มต้น
     * @param float $weightFee ค่าน้ำหนักหรือขนาดกล่องส่วนเกิน
     * @param float $distanceFee ค่าบริการตามระยะทางจริง (กม.)
     * @param float $surchargeFee ค่าบริการพื้นที่พิเศษ/เกาะ (ถ้ามี)
     * @param float $distanceKm ระยะทางจริงที่คำนวณได้ (กม.)
     * @param string $originName จังหวัดต้นทาง
     * @param string $destName จังหวัดปลายทาง
     * @param array<array{label: string, amount: float, note?: string}> $items รายการแจกแจง
     */
    public function __construct(
        private float $baseFee,
        private float $weightFee,
        private float $distanceFee,
        private float $surchargeFee,
        private float $distanceKm = 0.0,
        private string $originName = '',
        private string $destName = '',
        private array $items = []
    ) {}

    public function getBaseFee(): float {
        return round($this->baseFee, 2);
    }

    public function getWeightFee(): float {
        return round($this->weightFee, 2);
    }

    public function getDistanceFee(): float {
        return round($this->distanceFee, 2);
    }

    public function getSurchargeFee(): float {
        return round($this->surchargeFee, 2);
    }

    public function getSubtotal(): float {
        return round($this->baseFee + $this->weightFee + $this->distanceFee + $this->surchargeFee, 2);
    }

    /**
     * ภาษีมูลค่าเพิ่ม VAT 7% (รวมในราคา หรือคำนวณแยกตามมาตรฐานพาณิชย์)
     */
    public function getVatAmount(): float {
        return round($this->getSubtotal() * 0.07, 2);
    }

    public function getTotalFee(): float {
        return round($this->getSubtotal() + $this->getVatAmount(), 2);
    }

    public function getDistanceKm(): float {
        return $this->distanceKm;
    }

    public function getOriginName(): string {
        return $this->originName;
    }

    public function getDestName(): string {
        return $this->destName;
    }

    /**
     * @return array<array{label: string, amount: float, note?: string}>
     */
    public function getItems(): array {
        return $this->items;
    }
}
