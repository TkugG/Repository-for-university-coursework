<?php
require_once __DIR__ . '/../models/DestinationZone.php';
require_once __DIR__ . '/../models/ThailandProvinces.php';
require_once __DIR__ . '/../models/ShipmentParcel.php';
require_once __DIR__ . '/../models/ShippingFeeBreakdown.php';
require_once __DIR__ . '/../models/ShippingStrategyFactory.php';
require_once __DIR__ . '/../models/ShippingCalculator.php';

/**
 * Class ShippingController
 * Controller หลักสำหรับประมวลผลคำขอคำนวณค่าจัดส่งพัสดุ (MVC Pattern)
 */
class ShippingController {
    /**
     * รับคำขอ ตรวจสอบข้อมูล เรียก Model คำนวณ และส่งผลลัพธ์ไปยัง View
     */
    public function handleRequest(): void {
        $allProvinces = ThailandProvinces::getAll();
        $availableProviders = ShippingStrategyFactory::getAvailableProviders();

        // 💡 [ทำไมต้องแบบนี้]: กำหนดค่าเริ่มต้น (Default Values) ไว้ล่วงหน้า เพื่อให้เมื่อเปิดหน้าเว็บครั้งแรก (GET Request) ก็มีข้อมูลพร้อมแสดงผลตัวอย่างทันที
        $inputWeight = isset($_POST['weight']) ? (float)$_POST['weight'] : 1.5;
        $inputWidth  = isset($_POST['width'])  ? (float)$_POST['width']  : 14.0;
        $inputLength = isset($_POST['length']) ? (float)$_POST['length'] : 20.0;
        $inputHeight = isset($_POST['height']) ? (float)$_POST['height'] : 12.0;
        $inputOrigin = $_POST['origin_code'] ?? 'TH-10'; // ค่าเริ่มต้น: กรุงเทพมหานคร
        $inputDest   = $_POST['dest_code']   ?? 'TH-61'; // ค่าเริ่มต้น: อุทัยธานี
        $selectedProvider = $_POST['provider'] ?? 'kerry';
        $currentStep = isset($_POST['step']) ? (int)$_POST['step'] : 1;

        $error = null;
        $serverResult = null;
        $allBreakdowns = [];

        try {
            // 🎯 [หัวใจหลัก]: สร้าง Object พัสดุ (ShipmentParcel) ซึ่งจะคำนวณระยะทางและน้ำหนักคิดเงินโดยอัตโนมัติ
            $parcel = new ShipmentParcel($inputWeight, $inputWidth, $inputLength, $inputHeight, $inputOrigin, $inputDest);
            
            // 🔄 [การแปลงข้อมูล]: วนลูปคำนวณราคาทุกขนส่งด้วย Strategy Pattern เพื่อเตรียมตารางเปรียบเทียบราคาในขั้นตอนที่ 2
            $allStrategies = ShippingStrategyFactory::getAll();
            foreach ($allStrategies as $st) {
                $calc = new ShippingCalculator($st);
                $bd = $calc->calculate($parcel);
                $allBreakdowns[$st->getProviderName()] = [
                    'strategy'  => $st,
                    'breakdown' => $bd,
                    'cost'      => $bd->getTotalFee(),
                ];
            }

            // 🎯 [หัวใจหลัก]: เมื่อผู้ใช้กดสรุปรายการ (Step 3) นำ Strategy ของเจ้าที่เลือกมาออกใบเสร็จแจกแจงค่าบริการ
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['calculate_final'])) {
                $activeStrategy = ShippingStrategyFactory::create($selectedProvider);
                $calc = new ShippingCalculator($activeStrategy);
                $finalBreakdown = $calc->calculate($parcel);
                $serverResult = [
                    'provider'  => $activeStrategy->getProviderName(),
                    'breakdown' => $finalBreakdown,
                    'parcel'    => $parcel
                ];
                $currentStep = 3;
            }
        } catch (Exception $e) {
            // ⚠️ [จุดระวัง]: ดักจับ Validation Exception จาก Model (เช่น พัสดุใหญ่เกินเกณฑ์) ส่งต่อไปแสดงกล่องแจ้งเตือนบนหน้าเว็บ
            $error = $e->getMessage();
        }

        // 🔄 [การแปลงข้อมูล]: ตามสถาปัตยกรรม MVC โค้ดส่วน Controller จะส่งต่อตัวแปรผลลัพธ์ทั้งหมดไปแสดงผลที่ View Layer
        require __DIR__ . '/../views/calculator.php';
    }
}
