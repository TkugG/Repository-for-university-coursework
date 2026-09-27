<?php

declare(strict_types=1);

// Built-in PHP Development Server routing for static files
if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $uri;
    if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
        return false;
    }
}

/**
 * Smart IT & Facility Helpdesk & Work Order System
 * Entry Point (Custom OOP PHP 8.2+ MVC)
 */

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

require_once dirname(__DIR__) . '/src/Core/helpers.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Auth;
use App\Core\EventDispatcher;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\RoleMiddleware;
use App\Core\Middleware\CsrfMiddleware;

use App\Controllers\AuthController;
use App\Controllers\TicketController;
use App\Controllers\TechnicianController;
use App\Controllers\AdminController;
use App\Controllers\ApiController;

use App\Observers\TicketObserver;

Config::load();
Auth::init();

// Register Observers
$events = EventDispatcher::getInstance();
$observer = new TicketObserver();
$events->listen('ticket.created', [$observer, 'onTicketCreated']);
$events->listen('ticket.assigned', [$observer, 'onTicketAssigned']);
$events->listen('ticket.status_updated', [$observer, 'onStatusUpdated']);
$events->listen('ticket.resolved', [$observer, 'onTicketResolved']);
$events->listen('ticket.rejected', [$observer, 'onTicketRejected']);

$request = new Request();

// Serve Uploaded Files
if (str_starts_with($request->getUri(), '/uploads/')) {
    $filename = basename($request->getUri());
    $filePath = dirname(__DIR__) . '/storage/uploads/' . $filename;
    if (file_exists($filePath)) {
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        header("Content-Type: {$mime}");
        readfile($filePath);
        exit;
    }
}

$router = new Router();

// --- ROOT REDIRECT ---
$router->get('/', function (Request $req) {
    if (!Auth::check()) {
        Response::redirect('/login');
    }
    match (Auth::role()) {
        'admin' => Response::redirect('/admin/dashboard'),
        'technician' => Response::redirect('/technician/dashboard'),
        default => Response::redirect('/dashboard'),
    };
});

// --- AUTHENTICATION ---
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/quick-login', [AuthController::class, 'quickLogin']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);

// --- USER ROUTES (Requester) ---
$router->get('/dashboard', [TicketController::class, 'index'], [AuthMiddleware::class]);
$router->get('/tickets/create', [TicketController::class, 'create'], [AuthMiddleware::class]);
$router->post('/tickets', [TicketController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/tickets/{id}', [TicketController::class, 'show'], [AuthMiddleware::class]);
$router->post('/tickets/{id}/cancel', [TicketController::class, 'cancel'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/comments', [TicketController::class, 'addComment'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/confirm', [TicketController::class, 'confirmAndRate'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/reject', [TicketController::class, 'reject'], [AuthMiddleware::class, CsrfMiddleware::class]);

// --- TECHNICIAN ROUTES (Operator) ---
$router->get('/technician/dashboard', [TechnicianController::class, 'dashboard'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin')
]);
$router->get('/technician/jobs/{id}', [TechnicianController::class, 'jobDetail'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin')
]);
$router->post('/technician/jobs/{id}/en-route', [TechnicianController::class, 'startEnRoute'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin'),
    CsrfMiddleware::class
]);
$router->post('/technician/jobs/{id}/start', [TechnicianController::class, 'startRepair'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin'),
    CsrfMiddleware::class
]);
$router->post('/technician/jobs/{id}/waiting-parts', [TechnicianController::class, 'waitingParts'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin'),
    CsrfMiddleware::class
]);
$router->post('/technician/jobs/{id}/request-part', [TechnicianController::class, 'requestPart'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin'),
    CsrfMiddleware::class
]);
$router->post('/technician/jobs/{id}/close', [TechnicianController::class, 'closeJob'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin'),
    CsrfMiddleware::class
]);
$router->post('/technician/jobs/{id}/comments', [TechnicianController::class, 'addComment'], [
    AuthMiddleware::class,
    new RoleMiddleware('technician', 'admin'),
    CsrfMiddleware::class
]);

// --- ADMIN / SUPERVISOR ROUTES ---
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin')
]);
$router->get('/admin/tickets', [AdminController::class, 'tickets'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin')
]);
$router->post('/admin/tickets/{id}/approve', [AdminController::class, 'approveTicket'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/tickets/{id}/reject', [AdminController::class, 'rejectTicket'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/tickets/{id}/assign', [AdminController::class, 'assignTicket'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);

// Inventory & Parts Requests
$router->get('/admin/inventory', [AdminController::class, 'inventory'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin')
]);
$router->post('/admin/inventory', [AdminController::class, 'storePart'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/inventory/{id}', [AdminController::class, 'updatePart'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/inventory/{id}/delete', [AdminController::class, 'deletePart'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->get('/admin/parts-requests', [AdminController::class, 'partsRequests'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin')
]);
$router->post('/admin/parts-requests/{id}/approve', [AdminController::class, 'approvePartRequest'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/parts-requests/{id}/reject', [AdminController::class, 'rejectPartRequest'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);

// User & Category Management
$router->get('/admin/users', [AdminController::class, 'users'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin')
]);
$router->post('/admin/users', [AdminController::class, 'storeUser'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/users/{id}', [AdminController::class, 'updateUser'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->get('/admin/categories', [AdminController::class, 'categories'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin')
]);
$router->post('/admin/categories', [AdminController::class, 'storeCategory'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/categories/{id}', [AdminController::class, 'updateCategory'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);
$router->post('/admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'], [
    AuthMiddleware::class,
    new RoleMiddleware('admin'),
    CsrfMiddleware::class
]);



// --- API FETCH ROUTES ---
$router->get('/api/tickets', [ApiController::class, 'getTickets']);
$router->get('/api/tickets/{id}', [ApiController::class, 'getTicketDetail']);
$router->patch('/api/tickets/{id}/status', [ApiController::class, 'updateStatus']);
$router->get('/api/stats', [ApiController::class, 'getStats']);

// Dispatch
$router->dispatch($request);
