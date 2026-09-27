<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\TicketRepository;
use App\Repositories\CommentRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\SparePartRepository;
use App\Repositories\TicketPartsRepository;
use App\Repositories\RepairLogRepository;
use App\Services\TicketStatusService;
use App\Services\FileUploadService;
use Exception;

class TechnicianController
{
    private TicketRepository $ticketRepo;
    private CommentRepository $commentRepo;
    private StatusLogRepository $logRepo;
    private SparePartRepository $sparePartRepo;
    private TicketPartsRepository $ticketPartsRepo;
    private RepairLogRepository $repairLogRepo;
    private TicketStatusService $statusService;
    private FileUploadService $uploadService;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
        $this->commentRepo = new CommentRepository();
        $this->logRepo = new StatusLogRepository();
        $this->sparePartRepo = new SparePartRepository();
        $this->ticketPartsRepo = new TicketPartsRepository();
        $this->repairLogRepo = new RepairLogRepository();
        $this->statusService = new TicketStatusService();
        $this->uploadService = new FileUploadService();
    }

    /**
     * Technician Mobile-Friendly Workspace & Dashboard
     */
    public function dashboard(Request $request): void
    {
        $user = Auth::user();
        $assignedJobs = $this->ticketRepo->findByTechnician($user['id']);
        $counts = $this->ticketRepo->getStatusCounts(techId: $user['id']);

        Response::view('technician/dashboard', [
            'title' => 'แดชบอร์ดงานช่อมบำรุง - Technician Workspace',
            'user' => $user,
            'jobs' => $assignedJobs,
            'counts' => $counts,
            'flash' => Response::getFlash(),
        ]);
    }

    /**
     * Job Processing View (Ticket Detail Breakdown & Operations)
     */
    public function jobDetail(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketRepo->find($ticketId);

        if (!$ticket) {
            Response::setFlash('error', 'ไม่พบข้อมูลงานซ่อมที่ระบุ');
            Response::redirect('/technician/dashboard');
        }

        $comments = $this->commentRepo->findByTicket($ticketId);
        $logs = $this->logRepo->findByTicket($ticketId);
        $spareParts = $this->sparePartRepo->all();
        $partsUsed = $this->ticketPartsRepo->findByTicket($ticketId);
        $repairLog = $this->repairLogRepo->findByTicket($ticketId);

        Response::view('technician/job-detail', [
            'title' => "จัดการงานซ่อม #{$ticketId} - {$ticket['title']}",
            'user' => Auth::user(),
            'ticket' => $ticket,
            'comments' => $comments,
            'logs' => $logs,
            'spareParts' => $spareParts,
            'partsUsed' => $partsUsed,
            'repairLog' => $repairLog,
            'flash' => Response::getFlash(),
        ]);
    }

    /**
     * Quick Action: En Route (ช่างเริ่มเดินทาง)
     */
    public function startEnRoute(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $user = Auth::user();

        try {
            $this->statusService->transition($ticketId, 'en_route', $user, 'ช่างกดเริ่มเดินทางไปยังสถานที่หน้างาน');
            Response::setFlash('info', 'อัปเดตสถานะเป็น "กำลังเดินทาง" เรียบร้อยแล้ว');
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect("/technician/jobs/{$ticketId}");
    }

    /**
     * Quick Action: Start Repair (กำลังซ่อม)
     */
    public function startRepair(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $user = Auth::user();

        try {
            $this->statusService->transition($ticketId, 'in_progress', $user, 'ช่างถึงหน้างานและเริ่มดำเนินการตรวจสอบ/ซ่อมแซม');
            Response::setFlash('success', 'เริ่มดำเนินการซ่อมแซมเรียบร้อยแล้ว สถานะถูกปรับเป็น "กำลังดำเนินการ"');
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect("/technician/jobs/{$ticketId}");
    }

    /**
     * Quick Action: Waiting for Parts (รออะไหล่)
     */
    public function waitingParts(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $user = Auth::user();
        $note = trim($request->input('note', ''));

        try {
            $this->statusService->transition($ticketId, 'waiting_parts', $user, $note ?: 'อยู่ระหว่างรอการจัดหาหรือเบิกอะไหล่เพิ่มเติม');
            Response::setFlash('warning', 'ปรับสถานะเป็น "รออะไหล่" เรียบร้อยแล้ว');
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect("/technician/jobs/{$ticketId}");
    }

    /**
     * Spare Parts Requisition (ช่างขอเบิกอะไหล่)
     */
    public function requestPart(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $partId = (int)$request->input('part_id', 0);
        $quantity = (int)$request->input('quantity', 1);
        $user = Auth::user();

        if ($partId <= 0 || $quantity <= 0) {
            Response::setFlash('error', 'กรุณาเลือกรายการอะไหล่และระบุจำนวนที่ถูกต้อง');
            Response::redirect("/technician/jobs/{$ticketId}");
        }

        try {
            $part = $this->sparePartRepo->find($partId);
            if (!$part) {
                throw new Exception("ไม่พบข้อมูลอะไหล่ที่เลือก");
            }

            $this->ticketPartsRepo->requestPart($ticketId, $partId, $quantity);
            
            // Auto transition to waiting_parts if in progress
            $ticket = $this->ticketRepo->find($ticketId);
            if ($ticket['status'] === 'in_progress') {
                $this->statusService->transition($ticketId, 'waiting_parts', $user, "ส่งคำขอเบิกอะไหล่: {$part['name']} ({$quantity} ชิ้น)");
            }

            Response::setFlash('success', "ส่งคำขอเบิก {$part['name']} จำนวน {$quantity} ชิ้นให้หัวหน้างานเรียบร้อยแล้ว");
        } catch (Exception $e) {
            Response::setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        Response::redirect("/technician/jobs/{$ticketId}");
    }

    /**
     * Log & Close Job (บันทึกการซ่อมจริง + แนบรูปถ่ายหลังซ่อม + ส่งมอบงาน)
     */
    public function closeJob(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $user = Auth::user();
        $rootCause = trim($request->input('root_cause', ''));
        $solutionNote = trim($request->input('solution_note', ''));
        $actualHours = (float)$request->input('actual_hours', 1.0);

        if (empty($rootCause) || empty($solutionNote)) {
            Response::setFlash('error', 'กรุณาระบุสาเหตุที่แท้จริงและวิธีการแก้ไขปัญหาให้ครบถ้วน');
            Response::redirect("/technician/jobs/{$ticketId}");
        }

        try {
            $proofImagePath = null;
            if ($file = $request->file('proof_photo')) {
                $proofImagePath = $this->uploadService->upload($file);
            }

            $this->statusService->resolveTicketWithLog($ticketId, [
                'root_cause' => $rootCause,
                'solution_note' => $solutionNote,
                'actual_hours' => $actualHours,
                'proof_image_path' => $proofImagePath,
            ], $user);

            Response::setFlash('success', 'บันทึกการซ่อมแซมและส่งมอบงานให้ผู้แจ้งตรวจสอบเรียบร้อยแล้ว!');
        } catch (Exception $e) {
            Response::setFlash('error', 'ไม่สามารถส่งมอบงานได้: ' . $e->getMessage());
        }

        Response::redirect("/technician/jobs/{$ticketId}");
    }

    /**
     * Add Technician Comment
     */
    public function addComment(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $body = trim($request->input('body', ''));
        $user = Auth::user();

        if (empty($body) && !$request->file('image')) {
            Response::setFlash('error', 'กรุณากรอกข้อความ');
            Response::redirect("/technician/jobs/{$ticketId}");
        }

        try {
            $imagePath = null;
            if ($file = $request->file('image')) {
                $imagePath = $this->uploadService->upload($file);
            }

            $this->commentRepo->create([
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'body' => $body ?: 'แนบรูปภาพประกอบ',
                'image_path' => $imagePath,
            ]);

            Response::setFlash('success', 'ส่งข้อความตอบกลับแล้ว');
        } catch (Exception $e) {
            Response::setFlash('error', $e->getMessage());
        }

        Response::redirect("/technician/jobs/{$ticketId}");
    }
}
