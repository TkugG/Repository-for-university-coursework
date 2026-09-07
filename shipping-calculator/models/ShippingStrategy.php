<?php
require_once __DIR__ . '/ShipmentParcel.php';
require_once __DIR__ . '/ShippingFeeBreakdown.php';

/**
 * Interface ShippingStrategy
 * สัญญากลางของทุกผู้ให้บริการขนส่ง ส่งคืนผลลัพธ์เป็น ShippingFeeBreakdown DTO
 */
// 💡 [ทำไมต้องแบบนี้]: Strategy Pattern Interface ทำหน้าที่เป็นข้อตกลงร่วม (Contract) บังคับให้ทุกค่ายขนส่งต้องมีเมธอดคำนวณราคาแบบเดียวกัน
// 🎯 [หัวใจหลัก]: สอดคล้องกับหลัก Open/Closed Principle (OCP) เมื่อเพิ่มขนส่งเจ้าใหม่ ก็แค่ implement interface นี้โดยไม่ต้องแก้โค้ดเดิม
interface ShippingStrategy {
    // ดึงชื่อแบรนด์ผู้ให้บริการขนส่ง
    public function getProviderName(): string;

    // 🎯 [หัวใจหลัก]: รับอ็อบเจกต์พัสดุ (ShipmentParcel) เข้ามาคำนวณ แล้วส่งคืนสรุปแจกแจงค่าบริการ (ShippingFeeBreakdown)
    public function calculateFee(ShipmentParcel $parcel): ShippingFeeBreakdown;
}