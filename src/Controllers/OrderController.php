<?php
class OrderController {
    private $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function menu() {
        $stmt = $this->pdo->query('SELECT id,name,description,price,available FROM products WHERE available=1');
        $products = $stmt->fetchAll();
        json_response($products);
    }

    public function createOrder() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['customer_name']) || !isset($data['items'])) return json_response(['error'=>'invalid_payload'],400);

        try {
            $this->pdo->beginTransaction();
            $total = 0;
            foreach ($data['items'] as $it) {
                $stmt = $this->pdo->prepare('SELECT price FROM products WHERE id = ? AND available=1');
                $stmt->execute([(int)$it['product_id']]);
                $p = $stmt->fetch();
                if (!$p) throw new Exception('Product not found or unavailable');
                $total += $p['price'] * (int)$it['quantity'];
            }
            $stmt = $this->pdo->prepare('INSERT INTO orders (customer_name, customer_phone, total, status) VALUES (?,?,?,?)');
            $stmt->execute([$data['customer_name'], $data['customer_phone'] ?? null, $total, 'pending']);
            $orderId = $this->pdo->lastInsertId();

            $ins = $this->pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)');
            foreach ($data['items'] as $it) {
                $stmt = $this->pdo->prepare('SELECT price FROM products WHERE id = ?');
                $stmt->execute([(int)$it['product_id']]);
                $p = $stmt->fetch();
                $ins->execute([$orderId, (int)$it['product_id'], (int)$it['quantity'], $p['price']]);
            }
            $this->pdo->commit();
            json_response(['order_id' => $orderId, 'total' => $total],201);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            json_response(['error' => $e->getMessage()],500);
        }
    }
}
