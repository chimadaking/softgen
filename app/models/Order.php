<?php
namespace App\Models;

class Order extends BaseModel {
    public function getRecentOrders($userId, $limit = 5) {
        return $this->fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?", [$userId, $limit]);
    }

    public function createOrder($userId, $totalAmount, $items) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
            $this->query($sql, [$userId, $totalAmount]);
            $orderId = $this->lastInsertId();

            foreach ($items as $item) {
                $this->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)", 
                             [$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            }

            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function createOrderWithWalletDebit(int $userId, float $totalAmount, array $items) {
        $this->db->beginTransaction();
        try {
            $balanceRow = $this->fetch("SELECT balance FROM wallet_balances WHERE user_id = ? FOR UPDATE", [$userId]);
            $balance = $balanceRow ? (float)$balanceRow->balance : 0.00;
            if ($balance < $totalAmount) {
                $this->db->rollBack();
                return false;
            }

            $sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
            $this->query($sql, [$userId, $totalAmount]);
            $orderId = $this->lastInsertId();

            foreach ($items as $item) {
                $this->query(
                    "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)",
                    [$orderId, $item['product_id'], $item['quantity'], $item['price']]
                );
            }

            $updated = $this->query(
                "UPDATE wallet_balances SET balance = balance - ? WHERE user_id = ? AND balance >= ?",
                [$totalAmount, $userId, $totalAmount]
            );

            if ($updated->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            $this->query(
                "INSERT INTO wallet_transactions (user_id, amount, type, description, status)
                 VALUES (?, ?, 'purchase', ?, 'completed')",
                [$userId, $totalAmount, 'Order #' . $orderId]
            );

            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getTotalRevenue() {
        $result = $this->fetch("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
        return $result ? $result->total : 0.00;
    }

    public function getRecentOrdersAdmin($limit = 10) {
        return $this->fetchAll("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT ?", [$limit]);
    }

    public function getOrderById($id) {
        return $this->fetch("SELECT * FROM orders WHERE id = ?", [$id]);
    }

    public function getOrderItems($orderId) {
        return $this->fetchAll("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?", [$orderId]);
    }

    public function updateStatus($orderId, $status) {
        return $this->query("UPDATE orders SET status = ? WHERE id = ?", [$status, $orderId]);
    }

}
