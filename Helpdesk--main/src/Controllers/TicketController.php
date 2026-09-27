<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\EventDispatcher;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\TicketRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\RatingRepository;
use App\Repositories\TicketPartsRepository;
use App\Repositories\RepairLogRepository;
use App\Services\TicketStatusService;
use App\Services\FileUploadService;
use Exception;

class TicketController
{
    private TicketRepository $ticketRepo;
    private CategoryRepository $categoryRepo;
    private CommentRepository $commentRepo;
    private StatusLogRepository $logRepo;
    private RatingRepository $ratingRepo;
    private TicketPartsRepository $ticketPartsRepo;
    private RepairLogRepository $repairLogRepo;
    private TicketStatusService $statusService;
    private FileUploadService $uploadService;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->commentRepo = new CommentRepository();
        $this->logRepo = new StatusLogRepository();
        $this->ratingRepo = new RatingRepository();
        $this->ticketPartsRepo = new TicketPartsRepository();
        $this->repairLogRepo = new RepairLogRepository();
        $this->statusService = new TicketStatusService();
        $this->uploadService = new FileUploadService();
    }

    /**
     * User Dashboard
     */
    public function index(Request $request): void
    {
        $user = Auth::user();
        $tickets = $this->ticketRepo->findByUser($user['id']);
        $counts = $this->ticketRepo->getStatusCounts(userId: $user['id']);

        Response::view('user/dashboard', [
            'title' => 'แดชบอร์ดงานแจ้งซ่อม - User Portal',
            'user' => $user,
            'tickets' => $tickets,
            'counts' => $counts,
            'flash' => Response::getFlash(),
        ]);
    }

    /**
     * Create Ticket View
     */
    public function create(Request $request): void
    {
        $categories = $this->categoryRepo->all();

        Response::view('user/create', [
            'title' => 'เปิดใบแจ้งซ่อมบำรุง / IT Helpdesk',
            'user' => Auth::user(),
            'categories' => $categories,
            'flash' => Response::getFlash(),
        ]);
    }

    /**
     * Store new Ticket
     */
    public function store(Request $request): void
    {
        $user = Auth::user();
        $title = trim($request->input('title', ''));
        $categoryId = (int)$request->input('category_id', 0);
        $location = trim($request->input('location', ''));
        $priority = $request->input('priority', 'normal');
        $description = trim($request->input('description', ''));

        if (empty($title) || empty($description) || empty($location) || $categoryId === 0) {
            Response::setFlash('error', 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง (รวมถึงสถานที่/อาคาร/ชั้น)');
            Response::redirect('/tickets/create');
        }

        try {
            $imagePath = null;
            if ($file = $request->file('attachment')) {
                $imagePath = $this->uploadService->upload($file);
            }

            $ticketId = $this->ticketRepo->create([
                'user_id' => $user['id'],
                'category_id' => $categoryId,
                'title' => $title,
                'location' => $location,
                'description' => $description,
                'priority' => $priority,
                'status' => 'pending_approval',
            ]);

            $this->logRepo->create([
                'ticket_id' => $ticketId,
                'changed_by' => $user['id'],
                'from_status' => 'none',
                'to_status' => 'pending_approval',
                'note' => "ผู้แจ้งเปิดใบงาน (สถานที่: {$location})",
            ]);

            if ($imagePath) {
                $this->commentRepo->create([
                    'ticket_id' => $ticketId,
                    'user_id' => $user['id'],
                    'body' => 'แนบรูปภาพ/เอกสารประกอบการแจ้งซ่อม',
                    'image_path' => $imagePath,
                ]);
            }

            $ticketData = $this->ticketRepo->find($ticketId);
            EventDispatcher::getInstance()->dispatch('ticket.created', [
                'ticket' => $ticketData,
            ]);

            Response::setFlash('success', "เปิดใบแจ้งซ่อม #{$ticketId} เรียบร้อยแล้ว! ส่งคำขอให้ Supervisor พิจารณาอนุมัติแล้ว");
            Response::redirect("/tickets/{$ticketId}");
        } catch (Exception $e) {
            Response::setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            Response::redirect('/tickets/create');
        }
    }

    /**
     * Ticket Tracking View
     */
    public function show(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketRepo->find($ticketId);

        if (!$ticket) {
            Response::setFlash('error', 'ไม่พบข้อมูล Ticket');
            Response::redirect('/dashboard');
        }

        $user = Auth::user();
        if ($user['role'] === 'user' && (int)$ticket['user_id'] !== (int)$user['id']) {
            Response::setFlash('error', 'คุณไม่มีสิทธิ์เข้าดู Ticket ของผู้อื่น');
            Response::redirect('/dashboard');
        }

        $comments = $this->commentRepo->findByTicket($ticketId);
        $logs = $this->logRepo->findByTicket($ticketId);
        $rating = $this->ratingRepo->findByTicket($ticketId);
        $partsUsed = $this->ticketPartsRepo->findByTicket($ticketId);
        $repairLog = $this->repairLogRepo->findByTicket($ticketId);

        Response::view('user/show', [
            'title' => "ใบงาน #{$ticketId} - {$ticket['title']}",
            'user' => $user,
            'ticket' => $ticket,
            'comments' => $comments,
            'logs' => $logs,
            'rating' => $rating,
            'partsUsed' => $partsUsed,
            'repairLog' => $repairLog,
            'flash' => Response::getFlash(),
        ]);
    }

    /**
     * Cancel Ticket Action (User)
     */
    public function cancel(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $reason = trim($request->input('reason', ''));
        $user = Auth::user();

        if (empty($reason)) {
            Response::setFlash('error', 'กรุณาระบุเหตุผลในการยกเลิกคำขอ');
            Response::redirect("/tickets/{$ticketId}");
        }

        try {
            $this->statusService->cancelTicket($ticketId, $reason, $user);
            Response::setFlash('warning', 'ยกเลิกใบแจ้งซ่อมเรียบร้อยแล้ว');
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect("/tickets/{$ticketId}");
    }

    /**
     * Add Comment to Ticket
     */
    public function addComment(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $body = trim($request->input('body', ''));
        $user = Auth::user();

        if (empty($body) && !$request->file('image')) {
            Response::setFlash('error', 'กรุณากรอกข้อความ');
            Response::redirect("/tickets/{$ticketId}");
        }

        try {
            $imagePath = null;
            if ($file = $request->file('image')) {
                $imagePath = $this->uploadService->upload($file);
            }

            $this->commentRepo->create([
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'body' => $body ?: 'แนบรูปภาพเพิ่มเติม',
                'image_path' => $imagePath,
            ]);

            Response::setFlash('success', 'ส่งข้อความเรียบร้อยแล้ว');
        } catch (Exception $e) {
            Response::setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        Response::redirect("/tickets/{$ticketId}");
    }

    /**
     * Action Panel: Confirm Completion & Rate
     */
    public function confirmAndRate(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $user = Auth::user();
        $score = (int)$request->input('score', 5);
        $feedback = trim($request->input('feedback', ''));

        if ($score < 1 || $score > 5) {
            Response::setFlash('error', 'คะแนนความพึงพอใจต้องอยู่ระหว่าง 1 ถึง 5 ดาว');
            Response::redirect("/tickets/{$ticketId}");
        }

        try {
            $this->statusService->transition(
                ticketId: $ticketId,
                newStatusStr: 'closed',
                actor: $user,
                note: 'ผู้แจ้งยืนยันผลการตรวจรับและประเมินผลงานเรียบร้อย',
                ratingData: ['score' => $score, 'feedback' => $feedback]
            );

            Response::setFlash('success', 'ขอบพระคุณสำหรับการยืนยันปิดงานและให้คะแนนการประเมินครับ!');
        } catch (Exception $e) {
            Response::setFlash('error', 'ไม่สามารถปิดงานได้: ' . $e->getMessage());
        }

        Response::redirect("/tickets/{$ticketId}");
    }

    /**
     * Action Panel: Reject Resolution (Reverts to In Progress)
     */
    public function reject(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $user = Auth::user();
        $reason = trim($request->input('reason', ''));

        if (empty($reason)) {
            Response::setFlash('error', 'กรุณาระบุเหตุผลที่ปฏิเสธผลการซ่อม');
            Response::redirect("/tickets/{$ticketId}");
        }

        try {
            $this->statusService->transition(
                ticketId: $ticketId,
                newStatusStr: 'in_progress',
                actor: $user,
                note: "ผู้แจ้งระบุว่าปัญหายังไม่หาย: {$reason}"
            );

            Response::setFlash('warning', 'แจ้งปฏิเสธการแก้ไขแล้ว ใบงานถูกส่งกลับไปให้ช่างเข้าตรวจสอบต่อ (In Progress)');
        } catch (Exception $e) {
            Response::setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        Response::redirect("/tickets/{$ticketId}");
    }
}
