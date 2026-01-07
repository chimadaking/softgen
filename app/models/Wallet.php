<?php
namespace App\Models;

class Wallet extends BaseModel {
    public function getBalance($userId) {
        $wallet = $this->fetch("SELECT balance FROM wallet_balances WHERE user_id = ?", [$userId]);
        return $wallet ? $wallet->balance : 0.00;
    }

    public function addTransaction($userId, $amount, $type, $description, $status = 'completed') {
        $sql = "INSERT INTO wallet_transactions (user_id, amount, type, description, status) VALUES (?, ?, ?, ?, ?)";
        return $this->query($sql, [$userId, $amount, $type, $description, $status]);
    }

    public function updateBalance($userId, $amount, $type) {
        if ($type == 'credit') {
            $sql = "UPDATE wallet_balances SET balance = balance + ? WHERE user_id = ?";
            return $this->query($sql, [$amount, $userId]);
        }

        $stmt = $this->query(
            "UPDATE wallet_balances SET balance = balance - ? WHERE user_id = ? AND balance >= ?",
            [$amount, $userId, $amount]
        );
        return $stmt->rowCount() > 0;
    }

    public function getTransactions($userId) {
        return $this->fetchAll("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    }

    public function createPendingDeposit(int $userId, float $amount, string $description): bool {
        return $this->addTransaction($userId, $amount, 'deposit', $description, 'pending') !== false;
    }

    public function createPendingWithdrawal(int $userId, float $amount, string $description): bool {
        $this->db->beginTransaction();
        try {
            $balanceRow = $this->fetch("SELECT balance FROM wallet_balances WHERE user_id = ? FOR UPDATE", [$userId]);
            $balance = $balanceRow ? (float)$balanceRow->balance : 0.00;
            if ($balance < $amount) {
                $this->db->rollBack();
                return false;
            }

            $updated = $this->query(
                "UPDATE wallet_balances SET balance = balance - ? WHERE user_id = ? AND balance >= ?",
                [$amount, $userId, $amount]
            );

            if ($updated->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            $this->query(
                "INSERT INTO wallet_transactions (user_id, amount, type, description, status)
                 VALUES (?, ?, 'withdrawal', ?, 'pending')",
                [$userId, $amount, $description]
            );

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
