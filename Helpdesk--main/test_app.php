<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Config;
use App\Core\Database;
use App\Core\EventDispatcher;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\TicketRepository;
use App\Repositories\SparePartRepository;
use App\Repositories\TicketPartsRepository;
use App\Repositories\RepairLogRepository;
use App\Services\TicketStatusService;
use App\Observers\TicketObserver;

echo "=== STARTING SMART IT & FACILITY TEST SUITE ===\n";

Config::load(__DIR__ . '/.env');
$db = Database::getInstance();
echo "[OK] Database driver: " . $db->getDriver() . "\n";

$events = EventDispatcher::getInstance();
$observer = new TicketObserver();
$events->listen('ticket.created', [$observer, 'onTicketCreated']);
$events->listen('ticket.assigned', [$observer, 'onTicketAssigned']);
$events->listen('ticket.status_updated', [$observer, 'onStatusUpdated']);
$events->listen('ticket.resolved', [$observer, 'onTicketResolved']);
$events->listen('ticket.rejected', [$observer, 'onTicketRejected']);

$userRepo = new UserRepository();
$admin = $userRepo->findByEmail('admin@helpdesk.local') ?? ($userRepo->getAdmins()[0] ?? null);
$tech = $userRepo->findByEmail('tech1@helpdesk.local') ?? ($userRepo->getTechnicians()[0] ?? null);
$user = $userRepo->findByEmail('user@helpdesk.local') ?? ($userRepo->findByRole('user')[0] ?? null);
echo "[OK] Seed Users verified: Admin ({$admin['email']}), Tech ({$tech['email']}), User ({$user['email']})\n";

$catRepo = new CategoryRepository();
$categories = $catRepo->all();
echo "[OK] Categories count: " . count($categories) . "\n";

$spareRepo = new SparePartRepository();
$parts = $spareRepo->all();
echo "[OK] Spare Parts Inventory count: " . count($parts) . " items.\n";
assert(count($parts) > 0, 'No spare parts found');

// 1. Create Ticket with location
$ticketRepo = new TicketRepository();
$ticketId = $ticketRepo->create([
    'user_id' => $user['id'],
    'category_id' => $categories[0]['id'],
    'title' => 'ท่อแอร์รั่วซึม อาคาร B ชั้น 2',
    'location' => 'อาคาร B ชั้น 2 ห้อง 204',
    'description' => 'มีน้ำหยดลงบนฝ้าเพดาน ต้องการให้ช่างเข้าตรวจสอบด่วน',
    'priority' => 'urgent',
    'status' => 'pending_approval',
]);
$t = $ticketRepo->find($ticketId);
$events->dispatch('ticket.created', ['ticket' => $t]);
echo "[OK] Created Ticket #{$ticketId}: {$t['title']} | Location: {$t['location']} | Status: {$t['status']}\n";
assert($t['status'] === 'pending_approval');

// 2. Supervisor Screens & Approves
$statusService = new TicketStatusService();
$statusService->approveTicket($ticketId, $admin);
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 1 (Approve): Status is now '{$t['status']}'\n";
assert($t['status'] === 'approved');

// 3. Supervisor Assigns to Technician & sets SLA
$statusService->assignTechnician($ticketId, $tech['id'], $admin, 'urgent', 'เข้าตรวจสอบภายใน 2 ชั่วโมง');
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 2 (Assign): Assigned to {$t['technician_name']} | SLA Due: {$t['sla_due_at']}\n";
assert($t['status'] === 'assigned');

// 4. Technician moves En Route
$statusService->transition($ticketId, 'en_route', $tech, 'ช่างกำลังเดินทางไปอาคาร B');
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 3 (En Route): Status is '{$t['status']}'\n";
assert($t['status'] === 'en_route');

// 5. Technician arrives & Starts Repair
$statusService->transition($ticketId, 'in_progress', $tech, 'เริ่มแกะฝ้าเพดานตรวจสอบท่อน้ำทิ้ง');
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 4 (In Progress): Status is '{$t['status']}'\n";
assert($t['status'] === 'in_progress');

// 6. Technician Requisitions Spare Part
$part = $parts[0];
$spareRepo->increaseStock($part['id'], 10);
$ticketPartsRepo = new TicketPartsRepository();
$partReqId = $ticketPartsRepo->requestPart($ticketId, $part['id'], 2);
$statusService->transition($ticketId, 'waiting_parts', $tech, 'รอเบิกอะไหล่');
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 5 (Waiting Parts): Requested part '{$part['name']}' x 2. Ticket status: '{$t['status']}'\n";

// 7. Supervisor Approves Part Request & Stock decreases
$stockBefore = (int)$spareRepo->find($part['id'])['stock_quantity'];
$statusService->approvePartRequest($partReqId, $admin);
$stockAfter = (int)$spareRepo->find($part['id'])['stock_quantity'];
echo "[OK] Step 6 (Parts Approval): Part approved! Stock decreased from {$stockBefore} to {$stockAfter}\n";
assert($stockAfter === $stockBefore - 2);

// 8. Technician Resumes & Resolves with Repair Log
$statusService->resolveTicketWithLog($ticketId, [
    'root_cause' => 'ท่อน้ำทิ้งแอร์หลุดออกจากข้อต่อสามทาง',
    'solution_note' => 'ต่อท่อ PVC พร้อมทากาวประสานท่ออย่างแน่นหนา ทดสอบเปิดแอร์ 30 นาทีไม่พบน้ำรั่ว',
    'actual_hours' => 1.5,
    'proof_image_path' => 'uploads/proof_test.jpg',
], $tech);
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 7 (Resolved): Ticket resolved! Repair log recorded. Root cause: {$t['root_cause']}\n";
assert($t['status'] === 'resolved');

// 8.5. Requester Rejects Resolution -> triggers ticket.rejected
echo "[TEST] Requester rejects repair resolution...\n";
$statusService->transition(
    ticketId: $ticketId,
    newStatusStr: 'in_progress',
    actor: $user,
    note: 'น้ำยังคงหยดอยู่เล็กน้อย ต้องการให้ช่างตรวจเช็คซีลยางเพิ่มเติม'
);
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 7.5 (Rejected): Ticket reverted to '{$t['status']}'\n";
assert($t['status'] === 'in_progress');

// 8.6. Technician fixes and Resolves again
$statusService->resolveTicketWithLog($ticketId, [
    'root_cause' => 'ซีลยางข้อต่อสามทางเสื่อมสภาพ',
    'solution_note' => 'เปลี่ยนซีลยางใหม่และขันข้อต่อให้แน่นขึ้น ทดสอบเปิดแอร์ 1 ชม. แห้งสนิท',
    'actual_hours' => 0.5,
    'proof_image_path' => 'uploads/proof_test.jpg',
], $tech);
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 7.6 (Resolved Again): Ticket resolved again! Status: '{$t['status']}'\n";
assert($t['status'] === 'resolved');

// 9. User Confirms and Rates 5 Stars
$statusService->transition(
    ticketId: $ticketId, 
    newStatusStr: 'closed', 
    actor: $user, 
    note: 'ผู้แจ้งยืนยันว่าฝ้าแห้งสนิทและปิดงาน', 
    ratingData: ['score' => 5, 'feedback' => 'ช่างเข้าเร็วมาก แก้ไขปัญหาได้ตรงจุด']
);
$t = $ticketRepo->find($ticketId);
echo "[OK] Step 8 (Closed): Ticket closed! Rating score: {$t['rating_score']}/5\n";
assert($t['status'] === 'closed');

// 10. Check SLA Compliance Metric
$slaRate = $ticketRepo->getSlaComplianceRate();
echo "[OK] SLA Compliance Rate: {$slaRate}%\n";

echo "=== ALL TESTS COMPLETED SUCCESSFULLY! ===\n";
