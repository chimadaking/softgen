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
        } else {
            $sql = "UPDATE wallet_balances SET balance = balance - ? WHERE user_id = ?";
        }
        return $this->query($sql, [$amount, $userId]);
    }

    public function getTransactions($userId) {
        return $this->fetchAll("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    }
}
