<?php
require __DIR__ . '/../src/config.php';
$config = require __DIR__ . '/../src/config.php';
$pdo = require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/lib/jwt.php';
require __DIR__ . '/../src/Controllers/OrderController.php';
require __DIR__ . '/../src/Controllers/AdminController.php';
require __DIR__ . '/../src/Controllers/ReportController.php';
require __DIR__ . '/../src/AuthMiddleware.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Public: menu
if ($uri === '/' || $uri === '/menu' || $uri === '/api/menu') {
    (new OrderController($pdo))->menu();
    exit;
}

// Public: create order
if ($uri === '/api/checkout' && $method === 'POST') {
    (new OrderController($pdo))->createOrder();
    exit;
}

// Reports
if ($uri === '/api/reports/daily') {
    (new ReportController($pdo))->dailySales();
    exit;
}

// Admin login
if ($uri === '/admin/login' && $method === 'POST') {
    (new AdminController($pdo))->login();
    exit;
}

// Admin protected endpoints
if (strpos($uri, '/admin') === 0) {
    $auth = new AuthMiddleware($config);
    $user = $auth->requireAuth();
    (new AdminController($pdo))->handle($uri, $method, $user);
    exit;
}

http_response_code(404);
echo json_encode(['error'=>'not_found']);
