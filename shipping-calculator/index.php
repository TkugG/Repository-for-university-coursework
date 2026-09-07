<?php
/**
 * Smart Shipping Calculator — Application Entry Point (Front Controller)
 * 
 * Architecture: MVC (Model-View-Controller)
 * Pattern: Strategy Pattern (คำนวณราคาตามผู้ให้บริการขนส่ง Kerry, Flash, ไปรษณีย์ไทย)
 */

require_once __DIR__ . '/controllers/ShippingController.php';

$controller = new ShippingController();
$controller->handleRequest();