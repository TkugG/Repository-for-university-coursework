<?php
require_once __DIR__ . '/ShippingStrategy.php';
require_once __DIR__ . '/ShippingFeeBreakdown.php';

/**
 * Class ThaiPostEmsStrategy
 * กลยุทธ์คิดราคา ไปรษณีย์ไทย EMS (เรทมาตรฐานแห่งชาติ เข้าถึงทุกพื้นที่ทั่วไทย)
 */
class ThaiPostEmsStrategy implements ShippingStrategy {
    public function getProviderName(): string {
        return "ไปรษณีย์ไทย EMS 📮";
    }

    public function calculateFee(ShipmentParcel $parcel): ShippingFeeBreakdown {
        $w = $parcel->getChargeableWeight();
        $dist = $parcel->getDistanceKm();
        $items = [];

        // 1. ค่าบริการพื้นฐาน (ครอบคลุม 1 กก. แรก)
        $baseFee = 32.0;
        $items[] = [
            'label' => 'ค่าบริการ EMS ด่วนพิเศษเริ่มต้น (พิกัด 1 kg แรก)',
            'amount' => $baseFee,
            'note'  => 'บริการมาตรฐานไปรษณีย์ไทย'
        ];

        // 2. ค่าน้ำหนักหรือขนาดกล่องส่วนเกิน
        $weightFee = 0.0;
        if ($w > 1.0 && $w <= 2.0) {
            $weightFee = 10.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกิน (' . number_format($w, 2) . ' kg เรท 1-2 kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        } elseif ($w > 2.0 && $w <= 5.0) {
            $weightFee = 10.0 + ceil($w - 2.0) * 15.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกิน (' . number_format($w, 2) . ' kg เรท 2-5 kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        } elseif ($w > 5.0) {
            $weightFee = 55.0 + ceil($w - 5.0) * 20.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกินระดับพัสดุใหญ่ (' . number_format($w, 2) . ' kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        }

        // 3. ค่าบริการระยะทางข้ามโซน/ต่างจังหวัด
        $distanceFee = 0.0;
        if ($dist > 80.0) {
            $distanceFee = ceil(($dist - 80.0) / 150.0) * 6.0;
            $items[] = [
                'label' => 'ค่าขนส่งตามระยะทางข้ามภูมิภาค (' . number_format($dist, 0) . ' กม.)',
                'amount' => $distanceFee,
                'note'  => $parcel->getOriginProvinceName() . ' → ' . $parcel->getDestProvinceName()
            ];
        }

        // 4. ค่าธรรมเนียมเส้นทาง / พื้นที่ห่างไกล (EMS เข้าถึงทุกพื้นที่ เรทประหยัดพิเศษ)
        $surchargeFee = 0.0;
        if ($parcel->getDestinationZone() === DestinationZone::REMOTE) {
            $surchargeFee = 20.0;
            $items[] = [
                'label' => 'ค่าบริการพื้นที่พิเศษ / เกาะ / ชายแดนใต้ (เรท ปณท. แห่งชาติ)',
                'amount' => $surchargeFee,
                'note'  => 'เข้าถึงครอบคลุมทุกรหัสไปรษณีย์'
            ];
        }

        return new ShippingFeeBreakdown(
            $baseFee,
            $weightFee,
            $distanceFee,
            $surchargeFee,
            $dist,
            $parcel->getOriginProvinceName(),
            $parcel->getDestProvinceName(),
            $items
        );
    }
}