<?php
require_once __DIR__ . '/../lib/jwt.php';
class AdminController {
    private $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function login() {
        $d = json_decode(file_get_contents('php://input'), true);
        if (!$d || !isset($d['username']) || !isset($d['password'])) return json_response(['error'=>'invalid_payload'],400);
        $stmt = $this->pdo->prepare('SELECT id,username,password_hash FROM admins WHERE username = ?');
        $stmt->execute([$d['username']]);
        $u = $stmt->fetch();
        if (!$u || !password_verify($d['password'], $u['password_hash'])) return json_response(['error'=>'invalid_credentials'],401);
        $config = require __DIR__ . '/../config.php';
        $payload = ['sub'=>$u['id'],'username'=>$u['username'],'iat'=>time(),'exp'=>time()+3600*8];
        $token = jwt_encode($payload, $config['app']['jwt_secret']);
        json_response(['token'=>$token]);
    }

    public function handle($uri, $method, $user) {
        if (preg_match('#/admin/products$#', $uri)) {
            if ($method === 'GET') { $this->listProducts(); return; }
            if ($method === 'POST') { $this->createProduct(); return; }
        }
        if (preg_match('#/admin/products/([0-9]+)$#', $uri, $m)) {
            if ($method === 'PATCH') { $this->updateProduct($m[1]); return; }
            if ($method === 'DELETE') { $this->deleteProduct($m[1]); return; }
        }
        if (preg_match('#/admin/orders$#', $uri)) {
            if ($method === 'GET') { $this->listOrders(); return; }
        }
        if (preg_match('#/admin/orders/([0-9]+)/status#', $uri, $m) && $method === 'PATCH') {
            $this->updateOrderStatus($m[1]); return;
        }
        json_response(['error'=>'admin_route_not_found'],404);
    }

    private function listProducts() {
        $stmt = $this->pdo->query('SELECT * FROM products');
        json_response($stmt->fetchAll());
    }
    private function createProduct() {
        $d = json_decode(file_get_contents('php://input'), true);
        $stmt = $this->pdo->prepare('INSERT INTO products (name, description, price, available) VALUES (?,?,?,?)');
        $stmt->execute([$d['name'],$d['description'],$d['price'],$d['available'] ?? 1]);
        json_response(['id'=>$this->pdo->lastInsertId()]);
    }
    private function updateProduct($id) {
        $d = json_decode(file_get_contents('php://input'), true);
        $stmt = $this->pdo->prepare('UPDATE products SET name=?,description=?,price=?,available=? WHERE id=?');
        $stmt->execute([$d['name'],$d['description'],$d['price'],$d['available'] ?? 1,$id]);
        json_response(['ok'=>true]);
    }
    private function deleteProduct($id) {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id=?');
        $stmt->execute([$id]);
        json_response(['ok'=>true]);
    }
    private function listOrders() {
        $stmt = $this->pdo->query('SELECT * FROM orders ORDER BY created_at DESC');
        json_response($stmt->fetchAll());
    }
    private function updateOrderStatus($id) {
        $d = json_decode(file_get_contents('php://input'), true);
        $stmt = $this->pdo->prepare('UPDATE orders SET status=? WHERE id=?');
        $stmt->execute([$d['status'],$id]);
        json_response(['ok'=>true]);
    }
}
