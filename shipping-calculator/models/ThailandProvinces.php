<?php

/**
 * Class ThailandProvinces
 * ฐานข้อมูล 77 จังหวัดในประเทศไทย พร้อมพิกัดภูมิศาสตร์ (Lat, Lon)
 * และระบบคำนวณระยะทางขับรถจริงโดยประมาณ (Haversine Formula + Road Factor)
 */
class ThailandProvinces {
    /**
     * รายชื่อ 77 จังหวัด พร้อมพิกัด Lat, Lon และประเภทโซน
     * @var array<string, array{name: string, name_en: string, lat: float, lon: float, zone: string}>
     */
    private static array $provinces = [
        'TH-10' => ['name' => 'กรุงเทพมหานคร', 'name_en' => 'Bangkok', 'lat' => 13.7563, 'lon' => 100.5018, 'zone' => 'bkk'],
        'TH-11' => ['name' => 'สมุทรปราการ', 'name_en' => 'Samut Prakan', 'lat' => 13.5991, 'lon' => 100.5998, 'zone' => 'bkk'],
        'TH-12' => ['name' => 'นนทบุรี', 'name_en' => 'Nonthaburi', 'lat' => 13.8621, 'lon' => 100.5144, 'zone' => 'bkk'],
        'TH-13' => ['name' => 'ปทุมธานี', 'name_en' => 'Pathum Thani', 'lat' => 14.0208, 'lon' => 100.5250, 'zone' => 'bkk'],
        'TH-14' => ['name' => 'พระนครศรีอยุธยา', 'name_en' => 'Phra Nakhon Si Ayutthaya', 'lat' => 14.3532, 'lon' => 100.5684, 'zone' => 'upcountry'],
        'TH-15' => ['name' => 'อ่างทอง', 'name_en' => 'Ang Thong', 'lat' => 14.5896, 'lon' => 100.4550, 'zone' => 'upcountry'],
        'TH-16' => ['name' => 'ลพบุรี', 'name_en' => 'Lopburi', 'lat' => 14.7995, 'lon' => 100.6534, 'zone' => 'upcountry'],
        'TH-17' => ['name' => 'สิงห์บุรี', 'name_en' => 'Sing Buri', 'lat' => 14.8936, 'lon' => 100.4015, 'zone' => 'upcountry'],
        'TH-18' => ['name' => 'ชัยนาท', 'name_en' => 'Chai Nat', 'lat' => 15.1852, 'lon' => 100.1251, 'zone' => 'upcountry'],
        'TH-19' => ['name' => 'สระบุรี', 'name_en' => 'Saraburi', 'lat' => 14.5289, 'lon' => 100.9101, 'zone' => 'upcountry'],
        'TH-20' => ['name' => 'ชลบุรี', 'name_en' => 'Chon Buri', 'lat' => 13.3611, 'lon' => 100.9847, 'zone' => 'upcountry'],
        'TH-21' => ['name' => 'ระยอง', 'name_en' => 'Rayong', 'lat' => 12.6815, 'lon' => 101.2816, 'zone' => 'upcountry'],
        'TH-22' => ['name' => 'จันทบุรี', 'name_en' => 'Chanthaburi', 'lat' => 12.6114, 'lon' => 102.1039, 'zone' => 'upcountry'],
        'TH-23' => ['name' => 'ตราด', 'name_en' => 'Trat', 'lat' => 12.2428, 'lon' => 102.5175, 'zone' => 'remote'],
        'TH-24' => ['name' => 'ฉะเชิงเทรา', 'name_en' => 'Chachoengsao', 'lat' => 13.6904, 'lon' => 101.0780, 'zone' => 'upcountry'],
        'TH-25' => ['name' => 'ปราจีนบุรี', 'name_en' => 'Prachin Buri', 'lat' => 14.0510, 'lon' => 101.3719, 'zone' => 'upcountry'],
        'TH-26' => ['name' => 'นครนายก', 'name_en' => 'Nakhon Nayok', 'lat' => 14.2069, 'lon' => 101.2131, 'zone' => 'upcountry'],
        'TH-27' => ['name' => 'สระแก้ว', 'name_en' => 'Sa Kaeo', 'lat' => 13.8140, 'lon' => 102.0718, 'zone' => 'upcountry'],
        'TH-30' => ['name' => 'นครราชสีมา', 'name_en' => 'Nakhon Ratchasima', 'lat' => 14.9799, 'lon' => 102.0978, 'zone' => 'upcountry'],
        'TH-31' => ['name' => 'บุรีรัมย์', 'name_en' => 'Buri Ram', 'lat' => 14.9930, 'lon' => 103.1029, 'zone' => 'upcountry'],
        'TH-32' => ['name' => 'สุรินทร์', 'name_en' => 'Surin', 'lat' => 14.8818, 'lon' => 103.4936, 'zone' => 'upcountry'],
        'TH-33' => ['name' => 'ศรีสะเกษ', 'name_en' => 'Si Sa Ket', 'lat' => 15.1186, 'lon' => 104.3220, 'zone' => 'upcountry'],
        'TH-34' => ['name' => 'อุบลราชธานี', 'name_en' => 'Ubon Ratchathani', 'lat' => 15.2448, 'lon' => 104.8473, 'zone' => 'upcountry'],
        'TH-35' => ['name' => 'ยโสธร', 'name_en' => 'Yasothon', 'lat' => 15.7926, 'lon' => 104.1453, 'zone' => 'upcountry'],
        'TH-36' => ['name' => 'ชัยภูมิ', 'name_en' => 'Chaiyaphum', 'lat' => 15.8105, 'lon' => 102.0315, 'zone' => 'upcountry'],
        'TH-37' => ['name' => 'อำนาจเจริญ', 'name_en' => 'Amnat Charoen', 'lat' => 15.8585, 'lon' => 104.6298, 'zone' => 'upcountry'],
        'TH-38' => ['name' => 'บึงกาฬ', 'name_en' => 'Bueng Kan', 'lat' => 18.3609, 'lon' => 103.6465, 'zone' => 'remote'],
        'TH-39' => ['name' => 'หนองบัวลำภู', 'name_en' => 'Nong Bua Lamphu', 'lat' => 17.2044, 'lon' => 102.4407, 'zone' => 'upcountry'],
        'TH-40' => ['name' => 'ขอนแก่น', 'name_en' => 'Khon Kaen', 'lat' => 16.4419, 'lon' => 102.8360, 'zone' => 'upcountry'],
        'TH-41' => ['name' => 'อุดรธานี', 'name_en' => 'Udon Thani', 'lat' => 17.4157, 'lon' => 102.7859, 'zone' => 'upcountry'],
        'TH-42' => ['name' => 'เลย', 'name_en' => 'Loei', 'lat' => 17.4860, 'lon' => 101.7223, 'zone' => 'upcountry'],
        'TH-43' => ['name' => 'หนองคาย', 'name_en' => 'Nong Khai', 'lat' => 17.8783, 'lon' => 102.7420, 'zone' => 'upcountry'],
        'TH-44' => ['name' => 'มหาสารคาม', 'name_en' => 'Maha Sarakham', 'lat' => 16.1852, 'lon' => 103.3007, 'zone' => 'upcountry'],
        'TH-45' => ['name' => 'ร้อยเอ็ด', 'name_en' => 'Roi Et', 'lat' => 16.0538, 'lon' => 103.6520, 'zone' => 'upcountry'],
        'TH-46' => ['name' => 'กาฬสินธุ์', 'name_en' => 'Kalasin', 'lat' => 16.4322, 'lon' => 103.5061, 'zone' => 'upcountry'],
        'TH-47' => ['name' => 'สกลนคร', 'name_en' => 'Sakon Nakhon', 'lat' => 17.1546, 'lon' => 104.1486, 'zone' => 'upcountry'],
        'TH-48' => ['name' => 'นครพนม', 'name_en' => 'Nakhon Phanom', 'lat' => 17.4042, 'lon' => 104.7797, 'zone' => 'upcountry'],
        'TH-49' => ['name' => 'มุกดาหาร', 'name_en' => 'Mukdahan', 'lat' => 16.5436, 'lon' => 104.7235, 'zone' => 'upcountry'],
        'TH-50' => ['name' => 'เชียงใหม่', 'name_en' => 'Chiang Mai', 'lat' => 18.7883, 'lon' => 98.9853, 'zone' => 'upcountry'],
        'TH-51' => ['name' => 'ลำพูน', 'name_en' => 'Lamphun', 'lat' => 18.5745, 'lon' => 99.0087, 'zone' => 'upcountry'],
        'TH-52' => ['name' => 'ลำปาง', 'name_en' => 'Lampang', 'lat' => 18.2888, 'lon' => 99.4928, 'zone' => 'upcountry'],
        'TH-53' => ['name' => 'อุตรดิตถ์', 'name_en' => 'Uttaradit', 'lat' => 17.6201, 'lon' => 100.0993, 'zone' => 'upcountry'],
        'TH-54' => ['name' => 'แพร่', 'name_en' => 'Phrae', 'lat' => 18.1446, 'lon' => 100.1410, 'zone' => 'upcountry'],
        'TH-55' => ['name' => 'น่าน', 'name_en' => 'Nan', 'lat' => 18.7756, 'lon' => 100.7730, 'zone' => 'upcountry'],
        'TH-56' => ['name' => 'พะเยา', 'name_en' => 'Phayao', 'lat' => 19.1664, 'lon' => 99.9022, 'zone' => 'upcountry'],
        'TH-57' => ['name' => 'เชียงราย', 'name_en' => 'Chiang Rai', 'lat' => 19.9105, 'lon' => 99.8406, 'zone' => 'upcountry'],
        'TH-58' => ['name' => 'แม่ฮ่องสอน', 'name_en' => 'Mae Hong Son', 'lat' => 19.3020, 'lon' => 97.9654, 'zone' => 'remote'],
        'TH-60' => ['name' => 'นครสวรรค์', 'name_en' => 'Nakhon Sawan', 'lat' => 15.6987, 'lon' => 100.1199, 'zone' => 'upcountry'],
        'TH-61' => ['name' => 'อุทัยธานี', 'name_en' => 'Uthai Thani', 'lat' => 15.3835, 'lon' => 100.0245, 'zone' => 'upcountry'],
        'TH-62' => ['name' => 'กำแพงเพชร', 'name_en' => 'Kamphaeng Phet', 'lat' => 16.4828, 'lon' => 99.5227, 'zone' => 'upcountry'],
        'TH-63' => ['name' => 'ตาก', 'name_en' => 'Tak', 'lat' => 16.8840, 'lon' => 99.1258, 'zone' => 'upcountry'],
        'TH-64' => ['name' => 'สุโขทัย', 'name_en' => 'Sukhothai', 'lat' => 17.0078, 'lon' => 99.8235, 'zone' => 'upcountry'],
        'TH-65' => ['name' => 'พิษณุโลก', 'name_en' => 'Phitsanulok', 'lat' => 16.8211, 'lon' => 100.2659, 'zone' => 'upcountry'],
        'TH-66' => ['name' => 'พิจิตร', 'name_en' => 'Phichit', 'lat' => 16.4419, 'lon' => 100.3488, 'zone' => 'upcountry'],
        'TH-67' => ['name' => 'เพชรบูรณ์', 'name_en' => 'Phetchabun', 'lat' => 16.4190, 'lon' => 101.1591, 'zone' => 'upcountry'],
        'TH-70' => ['name' => 'ราชบุรี', 'name_en' => 'Ratchaburi', 'lat' => 13.5283, 'lon' => 99.8134, 'zone' => 'upcountry'],
        'TH-71' => ['name' => 'กาญจนบุรี', 'name_en' => 'Kanchanaburi', 'lat' => 14.0228, 'lon' => 99.5328, 'zone' => 'upcountry'],
        'TH-72' => ['name' => 'สุพรรณบุรี', 'name_en' => 'Suphan Buri', 'lat' => 14.4745, 'lon' => 100.1177, 'zone' => 'upcountry'],
        'TH-73' => ['name' => 'นครปฐม', 'name_en' => 'Nakhon Pathom', 'lat' => 13.8196, 'lon' => 100.0601, 'zone' => 'bkk'],
        'TH-74' => ['name' => 'สมุทรสาคร', 'name_en' => 'Samut Sakhon', 'lat' => 13.5475, 'lon' => 100.2744, 'zone' => 'bkk'],
        'TH-75' => ['name' => 'สมุทรสงคราม', 'name_en' => 'Samut Songkhram', 'lat' => 13.4098, 'lon' => 99.9998, 'zone' => 'upcountry'],
        'TH-76' => ['name' => 'เพชรบุรี', 'name_en' => 'Phetchaburi', 'lat' => 13.1114, 'lon' => 99.9398, 'zone' => 'upcountry'],
        'TH-77' => ['name' => 'ประจวบคีรีขันธ์', 'name_en' => 'Prachuap Khiri Khan', 'lat' => 11.8126, 'lon' => 99.7972, 'zone' => 'upcountry'],
        'TH-80' => ['name' => 'นครศรีธรรมราช', 'name_en' => 'Nakhon Si Thammarat', 'lat' => 8.4304, 'lon' => 99.9631, 'zone' => 'upcountry'],
        'TH-81' => ['name' => 'กระบี่', 'name_en' => 'Krabi', 'lat' => 8.0863, 'lon' => 98.9063, 'zone' => 'upcountry'],
        'TH-82' => ['name' => 'พังงา', 'name_en' => 'Phangnga', 'lat' => 8.4501, 'lon' => 98.5255, 'zone' => 'upcountry'],
        'TH-83' => ['name' => 'ภูเก็ต', 'name_en' => 'Phuket', 'lat' => 7.8804, 'lon' => 98.3923, 'zone' => 'upcountry'],
        'TH-84' => ['name' => 'สุราษฎร์ธานี', 'name_en' => 'Surat Thani', 'lat' => 9.1382, 'lon' => 99.3217, 'zone' => 'upcountry'],
        'TH-85' => ['name' => 'ระนอง', 'name_en' => 'Ranong', 'lat' => 9.9529, 'lon' => 98.6348, 'zone' => 'upcountry'],
        'TH-86' => ['name' => 'ชุมพร', 'name_en' => 'Chumphon', 'lat' => 10.4930, 'lon' => 99.1800, 'zone' => 'upcountry'],
        'TH-90' => ['name' => 'สงขลา', 'name_en' => 'Songkhla', 'lat' => 7.1898, 'lon' => 100.5954, 'zone' => 'upcountry'],
        'TH-91' => ['name' => 'สตูล', 'name_en' => 'Satun', 'lat' => 6.6238, 'lon' => 100.0674, 'zone' => 'upcountry'],
        'TH-92' => ['name' => 'ตรัง', 'name_en' => 'Trang', 'lat' => 7.5563, 'lon' => 99.6114, 'zone' => 'upcountry'],
        'TH-93' => ['name' => 'พัทลุง', 'name_en' => 'Phatthalung', 'lat' => 7.6167, 'lon' => 100.0740, 'zone' => 'upcountry'],
        'TH-94' => ['name' => 'ปัตตานี', 'name_en' => 'Pattani', 'lat' => 6.8674, 'lon' => 101.2501, 'zone' => 'remote'],
        'TH-95' => ['name' => 'ยะลา', 'name_en' => 'Yala', 'lat' => 6.5411, 'lon' => 101.2804, 'zone' => 'remote'],
        'TH-96' => ['name' => 'นราธิวาส', 'name_en' => 'Narathiwat', 'lat' => 6.4255, 'lon' => 101.8253, 'zone' => 'remote'],
    ];

    public static function getAll(): array {
        return self::$provinces;
    }

    public static function get(string $code): ?array {
        return self::$provinces[$code] ?? null;
    }

    /**
     * คำนวณระยะทางขับรถโดยประมาณ (km) ระหว่าง 2 จังหวัด
     * ใช้สูตร Haversine คำนวณระยะเส้นตรง แล้วคูณปัจจัยความคดเคี้ยวของถนนไทย (~1.25)
     */
    public static function calculateDistance(string $originCode, string $destCode): float {
        // 💡 [ทำไมต้องแบบนี้]: กรณีส่งภายในจังหวัดเดียวกัน กำหนดระยะทางเฉลี่ย 25 กม. สำหรับรถวิ่งกระจายพัสดุในเขตอำเภอ
        if ($originCode === $destCode) {
            return 25.0;
        }

        $p1 = self::get($originCode);
        $p2 = self::get($destCode);

        if (!$p1 || !$p2) {
            return 100.0;
        }

        $earthRadius = 6371.0; // รัศมีเฉลี่ยของโลก (กม.)
        $latFrom = deg2rad($p1['lat']);
        $lonFrom = deg2rad($p1['lon']);
        $latTo = deg2rad($p2['lat']);
        $lonTo = deg2rad($p2['lon']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        // 🎯 [หัวใจหลัก]: สูตร Haversine Formula คำนวณระยะทางโค้งผิวโลก (Great-Circle Distance) จากพิกัด Lat/Lon
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        $straightDistance = $angle * $earthRadius;

        // 💡 [ทำไมต้องแบบนี้]: เส้นทางขับรถจริงในไทยไม่ได้เป็นเส้นตรง จึงคูณ Road Winding Factor (x1.25) ให้ใกล้เคียงระยะทางถนนทางหลวงจริง
        $roadDistance = $straightDistance * 1.25;

        // ⚠️ [จุดระวัง]: ตั้งเกณฑ์ขั้นต่ำ 20 กม. เสมอเพื่อป้องกันค่าเป็น 0 สำหรับจังหวัดที่มีพื้นที่ติดกัน
        return round(max(20.0, $roadDistance), 0);
    }
}
