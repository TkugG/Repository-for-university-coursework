<?php
require_once __DIR__ . '/ShippingStrategy.php';
require_once __DIR__ . '/ShippingFeeBreakdown.php';

/**
 * Class FlashStrategy
 * กลยุทธ์คิดราคา Flash Express (เน้นราคาเริ่มต้นและค่าระยะทางประหยัด)
 */
class FlashStrategy implements ShippingStrategy {
    public function getProviderName(): string {
        return "Flash Express ⚡";
    }

    public function calculateFee(ShipmentParcel $parcel): ShippingFeeBreakdown {
        // 🎯 [หัวใจหลัก]: ใช้น้ำหนักคิดเงิน (Chargeable Weight) ที่ผ่านการเปรียบเทียบกับมิติกล่องแล้ว
        $w = $parcel->getChargeableWeight();
        $dist = $parcel->getDistanceKm();
        $items = [];

        // 1. ค่าบริการเริ่มต้น
        $baseFee = 28.0;
        $items[] = [
            'label' => 'ค่าบริการพัสดุราคาประหยัดเริ่มต้น (พิกัด 1 kg แรก)',
            'amount' => $baseFee,
            'note'  => 'เรทพิเศษสำหรับร้านค้าออนไลน์'
        ];

        // 2. ค่าน้ำหนักหรือขนาดกล่องส่วนเกิน
        $weightFee = 0.0;
        if ($w > 1.0 && $w <= 3.0) {
            $weightFee = ceil($w - 1.0) * 12.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกิน (' . number_format($w, 2) . ' kg คิดเพิ่ม ฿12/kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        } elseif ($w > 3.0 && $w <= 5.0) {
            $weightFee = 24.0 + ceil($w - 3.0) * 15.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกิน (' . number_format($w, 2) . ' kg เรท 3-5 kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        } elseif ($w > 5.0) {
            $weightFee = 54.0 + ceil($w - 5.0) * 18.0;
            $items[] = [
                'label' => 'ค่าน้ำหนักส่วนเกินระดับพัสดุใหญ่ (' . number_format($w, 2) . ' kg)',
                'amount' => $weightFee,
                'note'  => 'คิดจาก Chargeable Weight'
            ];
        }

        // 3. ค่าบริการตามระยะทางขับรถจริง
        // 💡 [ทำไมต้องแบบนี้]: ฟรี 50 กม. แรก และคิดค่าขนส่งเพิ่มทุกๆ 100 กม. ละ 8 บาท เพื่อสะท้อนต้นทุนน้ำมันขนส่งข้ามจังหวัด
        $distanceFee = 0.0;
        if ($dist > 50.0) {
            $distanceFee = ceil(($dist - 50.0) / 100.0) * 8.0; // ก้าวละ 8 บาท
            $items[] = [
                'label' => 'ค่าขนส่งตามระยะทางจริง (' . number_format($dist, 0) . ' กม.)',
                'amount' => $distanceFee,
                'note'  => $parcel->getOriginProvinceName() . ' → ' . $parcel->getDestProvinceName()
            ];
        }

        // 4. ค่าพื้นที่พิเศษ / เกาะ / ชายแดนใต้
        // ⚠️ [จุดระวัง]: เปรียบเทียบ DestinationZone ผ่าน Enum Type โดยตรง เพื่อความแม่นยำและป้องกัน Typo Bug
        $surchargeFee = 0.0;
        if ($parcel->getDestinationZone() === DestinationZone::REMOTE) {
            $surchargeFee = 50.0;
            $items[] = [
                'label' => 'ค่าธรรมเนียมพื้นที่ห่างไกล / ข้ามเกาะ / ชายแดนใต้',
                'amount' => $surchargeFee,
                'note'  => 'โซนบริการพิเศษ'
            ];
        }

        // 🔄 [การแปลงข้อมูล]: ห่อหุ้มรายการย่อยทั้งหมดลงใน DTO (ShippingFeeBreakdown) เพื่อนำไปคำนวณภาษี VAT 7% และแจกแจงบนใบเสร็จ
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