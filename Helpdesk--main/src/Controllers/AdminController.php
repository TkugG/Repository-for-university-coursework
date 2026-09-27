<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\RatingRepository;
use App\Repositories\SparePartRepository;
use App\Repositories\TicketPartsRepository;
use App\Services\EmailNotificationService;
use App\Services\TicketStatusService;
use Exception;

class AdminController
{
    private TicketRepository $ticketRepo;
    private UserRepository $userRepo;
    private CategoryRepository $categoryRepo;
    private RatingRepository $ratingRepo;
    private SparePartRepository $sparePartRepo;
    private TicketPartsRepository $ticketPartsRepo;
    private TicketStatusService $statusService;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
        $this->userRepo = new UserRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->ratingRepo = new RatingRepository();
        $this->sparePartRepo = new SparePartRepository();
        $this->ticketPartsRepo = new TicketPartsRepository();
        $this->statusService = new TicketStatusService();
    }

    /**
     * Admin Analytical Dashboard with SLA Monitoring
     */
    public function dashboard(Request $request): void
    {
        $counts = $this->ticketRepo->getStatusCounts();
        $mttrHours = $this->ticketRepo->getAverageResolutionTimeHours();
        $avgRating = $this->ratingRepo->getAverageScore();
        $slaRate = $this->ticketRepo->getSlaComplianceRate();
        $categoryDistribution = $this->ticketRepo->getCategoryDistribution();
        $technicianPerformance = $this->ticketRepo->getTechnicianPerformance();
        $overdueTickets = $this->ticketRepo->search(['sla_filter' => 'overdue', 'limit' => 5]);
        $pendingPartsCount = count($this->ticketPartsRepo->getPendingRequests());

        Response::view('admin/dashboard', [
            'title' => 'แดชบอร์ดฝ่ายซ่อมบำรุง & บริหารจัดการ - Dispatcher Console',
            'user' => Auth::user(),
            'counts' => $counts,
            'mttrHours' => $mttrHours,
            'avgRating' => $avgRating,
            'slaRate' => $slaRate,
            'categoryDist' => $categoryDistribution,
            'techPerf' => $technicianPerformance,
            'overdueTickets' => $overdueTickets,
            'pendingPartsCount' => $pendingPartsCount,
            'flash' => Response::getFlash(),
        ]);
    }

    /**
     * Master Ticket Table with SLA and Granular Filters
     */
    public function tickets(Request $request): void
    {
        $filters = [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'technician_id' => $request->input('technician_id'),
            'category_id' => $request->input('category_id'),
            'sla_filter' => $request->input('sla_filter'),
            'keyword' => $request->input('keyword'),
        ];

        $tickets = $this->ticketRepo->search($filters);
        $technicians = $this->userRepo->getTechnicians();
        $categories = $this->categoryRepo->all();
        $counts = $this->ticketRepo->getStatusCounts();
        $unassignedCount = count($this->ticketRepo->search(['technician_id' => 'unassigned']));

        Response::view('admin/tickets', [
            'title' => 'ควบคุมใบงานซ่อมบำรุงทั้งหมด - Work Order Console',
            'user' => Auth::user(),
            'tickets' => $tickets,
            'technicians' => $technicians,
            'categories' => $categories,
            'filters' => $filters,
            'counts' => $counts,
            'unassignedCount' => $unassignedCount,
            'flash' => Response::getFlash(),
        ]);
    }

    /**
     * Screen & Approve Ticket
     */
    public function approveTicket(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $admin = Auth::user();

        try {
            $this->statusService->approveTicket($ticketId, $admin);
            Response::setFlash('success', "อนุมัติรับเรื่องใบแจ้งซ่อม #{$ticketId} เรียบร้อยแล้ว (สถานะ: อนุมัติแล้ว)");
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect('/admin/tickets');
    }

    /**
     * Screen & Reject Ticket
     */
    public function rejectTicket(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $reason = trim($request->input('reason', ''));
        $admin = Auth::user();

        if (empty($reason)) {
            Response::setFlash('error', 'กรุณาระบุเหตุผลในการไม่อนุมัติคำขอ');
            Response::redirect('/admin/tickets');
        }

        try {
            $this->statusService->transition($ticketId, 'cancelled', $admin, "Supervisor ไม่อนุมัติคำขอ: {$reason}");
            Response::setFlash('warning', "ปฏิเสธคำขอใบแจ้งซ่อม #{$ticketId} เรียบร้อยแล้ว");
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect('/admin/tickets');
    }

    /**
     * Assign Ticket to Technician & Set Priority / SLA
     */
    public function assignTicket(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $technicianId = (int)$request->input('technician_id', 0);
        $priority = $request->input('priority');
        $note = trim($request->input('note', ''));
        $admin = Auth::user();

        if ($technicianId <= 0) {
            Response::setFlash('error', 'กรุณาเลือกช่างเทคนิค');
            Response::redirect('/admin/tickets');
        }

        try {
            $this->statusService->assignTechnician($ticketId, $technicianId, $admin, $priority, $note);
            Response::setFlash('success', "จ่ายงานใบซ่อม #{$ticketId} ให้ช่างเทคนิคเรียบร้อยแล้ว พร้อมส่งอีเมลแจ้งเตือน");
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect('/admin/tickets');
    }

    /**
     * Spare Parts Inventory Management
     */
    public function inventory(Request $request): void
    {
        $parts = $this->sparePartRepo->all();

        Response::view('admin/inventory', [
            'title' => 'จัดการคลังอะไหล่และอุปกรณ์ - Spare Parts Inventory',
            'user' => Auth::user(),
            'parts' => $parts,
            'flash' => Response::getFlash(),
        ]);
    }

    public function storePart(Request $request): void
    {
        $code = trim($request->input('part_code', ''));
        $name = trim($request->input('name', ''));
        $stock = (int)$request->input('stock_quantity', 0);
        $price = (float)$request->input('unit_price', 0.0);

        if (empty($code) || empty($name)) {
            Response::setFlash('error', 'กรุณากรอกรหัสและชื่ออะไหล่');
            Response::redirect('/admin/inventory');
        }

        try {
            $this->sparePartRepo->create([
                'part_code' => $code,
                'name' => $name,
                'stock_quantity' => $stock,
                'unit_price' => $price,
            ]);
            Response::setFlash('success', "เพิ่มอะไหล่ '{$name}' เข้าคลังเรียบร้อยแล้ว");
        } catch (Exception $e) {
            Response::setFlash('error', 'รหัสอะไหล่ซ้ำ หรือเกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        Response::redirect('/admin/inventory');
    }

    public function updatePart(Request $request, string $id): void
    {
        $partId = (int)$id;
        $code = trim($request->input('part_code', ''));
        $name = trim($request->input('name', ''));
        $stock = (int)$request->input('stock_quantity', 0);
        $price = (float)$request->input('unit_price', 0.0);

        $this->sparePartRepo->update($partId, [
            'part_code' => $code,
            'name' => $name,
            'stock_quantity' => $stock,
            'unit_price' => $price,
        ]);

        Response::setFlash('success', 'บันทึกข้อมูลอะไหล่เรียบร้อยแล้ว');
        Response::redirect('/admin/inventory');
    }

    public function deletePart(Request $request, string $id): void
    {
        $partId = (int)$id;
        try {
            $this->sparePartRepo->delete($partId);
            Response::setFlash('success', 'ลบรายการอะไหล่เรียบร้อยแล้ว');
        } catch (Exception) {
            Response::setFlash('error', 'ไม่สามารถลบได้เนื่องจากมีประวัติการเบิกใช้งานในใบงาน');
        }
        Response::redirect('/admin/inventory');
    }

    /**
     * Spare Parts Requisition Approval Console
     */
    public function partsRequests(Request $request): void
    {
        $requests = $this->ticketPartsRepo->getPendingRequests();

        Response::view('admin/parts-requests', [
            'title' => 'อนุมัติการเบิกอะไหล่ - Spare Parts Approval',
            'user' => Auth::user(),
            'requests' => $requests,
            'flash' => Response::getFlash(),
        ]);
    }

    public function approvePartRequest(Request $request, string $id): void
    {
        $requestId = (int)$id;
        $admin = Auth::user();

        try {
            $this->statusService->approvePartRequest($requestId, $admin);
            Response::setFlash('success', 'อนุมัติการเบิกอะไหล่และตัดสต็อกสินค้าเรียบร้อยแล้ว');
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect('/admin/parts-requests');
    }

    public function rejectPartRequest(Request $request, string $id): void
    {
        $requestId = (int)$id;
        $reason = trim($request->input('reason', ''));
        $admin = Auth::user();

        try {
            $this->statusService->rejectPartRequest($requestId, $reason ?: 'Supervisor ไม่อนุมัติ', $admin);
            Response::setFlash('warning', 'ปฏิเสธคำขอเบิกอะไหล่เรียบร้อยแล้ว');
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect('/admin/parts-requests');
    }

    /**
     * User Management (CRUD)
     */
    public function users(Request $request): void
    {
        $users = $this->userRepo->all();

        Response::view('admin/users', [
            'title' => 'จัดการผู้ใช้งาน - User Management',
            'user' => Auth::user(),
            'users' => $users,
            'flash' => Response::getFlash(),
        ]);
    }

    public function storeUser(Request $request): void
    {
        $name = trim($request->input('name', ''));
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        $role = $request->input('role', 'user');

        if (empty($name) || empty($email) || empty($password)) {
            Response::setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วน');
            Response::redirect('/admin/users');
        }

        if ($this->userRepo->findByEmail($email)) {
            Response::setFlash('error', 'อีเมลนี้มีอยู่ในระบบแล้ว');
            Response::redirect('/admin/users');
        }

        $this->userRepo->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ]);

        Response::setFlash('success', "เพิ่มผู้ใช้งาน {$name} สำเร็จ");
        Response::redirect('/admin/users');
    }

    public function updateUser(Request $request, string $id): void
    {
        $userId = (int)$id;
        $name = trim($request->input('name', ''));
        $email = trim($request->input('email', ''));
        $role = $request->input('role', 'user');
        $password = $request->input('password', '');

        $data = ['name' => $name, 'email' => $email, 'role' => $role];
        if (!empty($password)) {
            $data['password'] = $password;
        }

        $this->userRepo->update($userId, $data);
        Response::setFlash('success', 'บันทึกการแก้ไขข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
        Response::redirect('/admin/users');
    }

    public function deleteUser(Request $request, string $id): void
    {
        $userId = (int)$id;
        if ($userId === (int)Auth::id()) {
            Response::setFlash('error', 'ไม่สามารถลบบัญชีของตนเองที่กำลังเข้าสู่ระบบอยู่ได้');
            Response::redirect('/admin/users');
        }

        $this->userRepo->delete($userId);
        Response::setFlash('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
        Response::redirect('/admin/users');
    }

    /**
     * Category Management (CRUD)
     */
    public function categories(Request $request): void
    {
        $categories = $this->categoryRepo->all();

        Response::view('admin/categories', [
            'title' => 'จัดการหมวดหมู่ปัญหา - Category Management',
            'user' => Auth::user(),
            'categories' => $categories,
            'flash' => Response::getFlash(),
        ]);
    }

    public function storeCategory(Request $request): void
    {
        $name = trim($request->input('name', ''));
        $desc = trim($request->input('description', ''));

        if (empty($name)) {
            Response::setFlash('error', 'กรุณาระบุชื่อหมวดหมู่');
            Response::redirect('/admin/categories');
        }

        $this->categoryRepo->create(['name' => $name, 'description' => $desc]);
        Response::setFlash('success', "เพิ่มหมวดหมู่ '{$name}' เรียบร้อยแล้ว");
        Response::redirect('/admin/categories');
    }

    public function updateCategory(Request $request, string $id): void
    {
        $categoryId = (int)$id;
        $name = trim($request->input('name', ''));
        $desc = trim($request->input('description', ''));

        $this->categoryRepo->update($categoryId, ['name' => $name, 'description' => $desc]);
        Response::setFlash('success', 'บันทึกการแก้ไขหมวดหมู่เรียบร้อยแล้ว');
        Response::redirect('/admin/categories');
    }

    public function deleteCategory(Request $request, string $id): void
    {
        $categoryId = (int)$id;
        try {
            $this->categoryRepo->delete($categoryId);
            Response::setFlash('success', 'ลบหมวดหมู่เรียบร้อยแล้ว');
        } catch (Exception) {
            Response::setFlash('error', 'ไม่สามารถลบหมวดหมู่นี้ได้เนื่องจากมี Ticket ผูกอยู่');
        }
        Response::redirect('/admin/categories');
    }
}

