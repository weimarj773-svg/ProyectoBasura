<?php
$config = require __DIR__ . '/config.php';
$db = $config['db'];
$dsn = "mysql:host={$db['host']};dbname={$db['dbname']};port={$db['port']};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}
return $pdo;
