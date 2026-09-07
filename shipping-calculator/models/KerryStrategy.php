<?php
require_once __DIR__ . '/ShippingStrategy.php';
require_once __DIR__ . '/ShippingFeeBreakdown.php';

/**
 * Class KerryStrategy
 * กลยุทธ์คิดราคา Kerry Express ตามระยะทางจริง (กม.) และน้ำหนักคิดเงิน
 */
class KerryStrategy implements ShippingStrategy {
    public function getProviderName(): string {
        return "Kerry Express 📦";
    }

    public function calculateFee(ShipmentParcel $parcel): ShippingFeeBreakdown {
        $w = $parcel->getChargeableWeight();
        $dist = $parcel->getDistanceKm();
        $items = [];

        // 1. ค่าบริการพื้นฐาน (พิกัด 1 kg แรก)
        $baseFee = 35.0;
        $items[] = [
            'label' => 'ค่าบริการพัสดุด่วนเริ่มต้น (พิกัด 1 kg แรก)',
            'amount' => $baseFee,
            'note'  => 'ครอบคลุมบริการเข้ารับและส่งมอบ'
        ];

        // 2. ค่าน้ำหนักหรือขนาดกล่องส่วนเกิน
        $weightFee = 0.0;
        if ($w > 1.0 && $w <= 3.0) {
            $weightFee = ceil($w - 1.0) * 15.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกิน (' . number_format($w, 2) . ' kg คิดเพิ่ม ฿15/kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        } elseif ($w > 3.0 && $w <= 5.0) {
            $weightFee = 30.0 + ceil($w - 3.0) * 18.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกิน (' . number_format($w, 2) . ' kg เรท 3-5 kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        } elseif ($w > 5.0) {
            $weightFee = 66.0 + ceil($w - 5.0) * 22.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกินระดับพัสดุใหญ่ (' . number_format($w, 2) . ' kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        }

        // 3. ค่าบริการตามระยะทางขับรถจริง
        $distanceFee = 0.0;
        if ($dist > 50.0) {
            // เกิน 50 กม. คิดค่าระยะทางก้าวละ 100 กม. ละ 10 บาท
            $distanceFee = ceil(($dist - 50.0) / 100.0) * 10.0;
            $items[] = [
                'label' => 'ค่าขนส่งตามระยะทางจริง (' . number_format($dist, 0) . ' กม.)',
                'amount' => $distanceFee,
                'note'  => $parcel->getOriginProvinceName() . ' → ' . $parcel->getDestProvinceName()
            ];
        }

        // 4. ค่าพื้นที่พิเศษ / เกาะ / ชายแดนใต้
        $surchargeFee = 0.0;
        if ($parcel->getDestinationZone() === DestinationZone::REMOTE) {
            $surchargeFee = 50.0;
            $items[] = [
                'label' => 'ค่าธรรมเนียมพื้นที่ห่างไกล / ข้ามเกาะ / ชายแดนใต้',
                'amount' => $surchargeFee,
                'note'  => 'โซนบริการพิเศษ'
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