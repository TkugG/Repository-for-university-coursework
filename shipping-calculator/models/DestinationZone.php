<?php

/**
 * Enum DestinationZone
 * กำหนดโซนพื้นที่จัดส่งในประเทศไทยตามมาตรฐานโลจิสติกส์จริง
 */
// 💡 [ทำไมต้องแบบนี้]: ใช้ PHP 8.1+ Backed Enum (string) เพื่อให้มี Type Safety ป้องกัน Bug จากการพิมพ์ชื่อโซนผิด (กำจัด Magic String)
enum DestinationZone: string {
    case BKK = 'bkk';               // กรุงเทพฯ และปริมณฑล
    case UPCOUNTRY = 'upcountry';   // ต่างจังหวัดทั่วไป
    case REMOTE = 'remote';         // พื้นที่ห่างไกล / ข้ามเกาะ / 3 จังหวัดชายแดนใต้

    // 🔄 [การแปลงข้อมูล]: แปลงค่า Enum ให้เป็นข้อความภาษาไทยพร้อมไอคอนสำหรับนำไปแสดงผลบนหน้าจอและใบเสร็จ
    public function getLabel(): string {
        return match($this) {
            self::BKK       => 'กรุงเทพฯ และปริมณฑล 🏙️',
            self::UPCOUNTRY => 'ต่างจังหวัดทั่วไป 🏞️',
            self::REMOTE    => 'พื้นที่ห่างไกล / เกาะ / ชายแดนใต้ 🏝️',
        };
    }

    // 🎯 [หัวใจหลัก]: ตัวแปลงค่า String จากฟอร์มหรือฐานข้อมูลกลับมาเป็น Enum Case
    // ⚠️ [จุดระวัง]: ใช้ tryFrom() คู่กับ Fallback (?? self::BKK) ป้องกันแอปรวนกรณีรับค่าโซนที่ไม่รู้จักเข้ามา
    public static function fromValue(string $value): self {
        return self::tryFrom($value) ?? self::BKK;
    }
}

