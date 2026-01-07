<?php
namespace App\Models;

class Loyalty extends BaseModel {
    
    public function getUserPoints($userId) {
        $account = $this->fetch("SELECT points, tier_id, total_spent FROM loyalty_accounts WHERE user_id = ?", [$userId]);
        
        if (!$account) {
            $this->query("INSERT INTO loyalty_accounts (user_id, points, tier_id, total_spent) VALUES (?, 0, 1, 0.00)", [$userId]);
            return ['points' => 0, 'tier_id' => 1, 'total_spent' => 0.00];
        }
        
        return [
            'points' => $account->points,
            'tier_id' => $account->tier_id,
            'total_spent' => $account->total_spent
        ];
    }
    
    public function getBalance($userId) {
        $account = $this->fetch("SELECT points FROM loyalty_accounts WHERE user_id = ?", [$userId]);
        return $account ? $account->points : 0;
    }
    
    public function addPoints($userId, $amount, $reason, $orderId = null) {
        $amount = floatval($amount);
        
        $this->query("UPDATE loyalty_accounts SET points = points + ? WHERE user_id = ?", [$amount, $userId]);
        
        $sql = "INSERT INTO loyalty_points (user_id, points, type, description) VALUES (?, ?, 'earned', ?)";
        return $this->query($sql, [$userId, $amount, $reason]);
    }
    
    public function subtractPoints($userId, $amount, $reason) {
        $amount = floatval($amount);
        
        $currentPoints = $this->getBalance($userId);
        if ($currentPoints < $amount) {
            return false;
        }
        
        $this->query("UPDATE loyalty_accounts SET points = points - ? WHERE user_id = ?", [$amount, $userId]);
        
        $sql = "INSERT INTO loyalty_points (user_id, points, type, description) VALUES (?, ?, 'redeemed', ?)";
        return $this->query($sql, [$userId, $amount, $reason]);
    }
    
    public function getPointsHistory($userId, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM loyalty_points WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        return $this->fetchAll($sql, [$userId, $limit, $offset]);
    }
    
    public function getLoyaltyTier($points) {
        $tier = $this->fetch("SELECT * FROM loyalty_tiers WHERE min_points <= ? ORDER BY min_points DESC LIMIT 1", [$points]);
        
        if (!$tier) {
            return ['name' => 'Bronze', 'min_points' => 0, 'discount_percent' => 0, 'next_points' => 1000];
        }
        
        $nextTier = $this->fetch("SELECT * FROM loyalty_tiers WHERE min_points > ? ORDER BY min_points ASC LIMIT 1", [$tier->min_points]);
        $pointsUntilNext = $nextTier ? ($nextTier->min_points - $points) : 0;
        
        return [
            'id' => $tier->id,
            'name' => $tier->name,
            'min_points' => $tier->min_points,
            'discount_percent' => $tier->discount_percent,
            'next_tier' => $nextTier ? $nextTier->name : 'Max Tier',
            'next_points' => $nextTier ? $nextTier->min_points : $points,
            'points_until_next' => $pointsUntilNext
        ];
    }
    
    public function getPointsRate($type) {
        $settingKey = 'points_per_dollar_' . $type;
        $setting = $this->fetch("SELECT setting_value FROM loyalty_settings WHERE setting_key = ?", [$settingKey]);
        return $setting ? floatval($setting->setting_value) : 1.0;
    }
    
    public function getAllSettings() {
        $settings = $this->fetchAll("SELECT * FROM loyalty_settings");
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->setting_key] = $setting->setting_value;
        }
        return $result;
    }
    
    public function updateSettings($settings) {
        foreach ($settings as $key => $value) {
            $this->query("UPDATE loyalty_settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        }
        return true;
    }
    
    public function updateTier($userId) {
        $points = $this->getBalance($userId);
        $tier = $this->getLoyaltyTier($points);
        
        $this->query("UPDATE loyalty_accounts SET tier_id = ? WHERE user_id = ?", [$tier['id'], $userId]);
        
        return $tier;
    }
    
    public function calculateTierBonus($userId, $basePoints) {
        $account = $this->getUserPoints($userId);
        $tier = $this->getLoyaltyTier($account['points']);
        
        $bonusPercent = $tier['discount_percent'];
        return $basePoints * ($bonusPercent / 100);
    }
    
    public function getTotalPointsEarned($userId) {
        $result = $this->fetch("SELECT COALESCE(SUM(points), 0) as total FROM loyalty_points WHERE user_id = ? AND type = 'earned'", [$userId]);
        return $result ? floatval($result->total) : 0;
    }
    
    public function getTotalPointsRedeemed($userId) {
        $result = $this->fetch("SELECT COALESCE(SUM(points), 0) as total FROM loyalty_points WHERE user_id = ? AND type = 'redeemed'", [$userId]);
        return $result ? floatval($result->total) : 0;
    }
    
    public function getAllUsersWithLoyalty($limit = 50, $offset = 0) {
        $sql = "SELECT la.*, u.username, u.email 
                FROM loyalty_accounts la
                JOIN users u ON la.user_id = u.id
                ORDER BY la.points DESC
                LIMIT ? OFFSET ?";
        return $this->fetchAll($sql, [$limit, $offset]);
    }
    
    public function getUserLoyaltyById($userId) {
        $sql = "SELECT la.*, u.username, u.email, lt.name as tier_name, lt.min_points as tier_min_points, lt.discount_percent
                FROM loyalty_accounts la
                JOIN users u ON la.user_id = u.id
                LEFT JOIN loyalty_tiers lt ON la.tier_id = lt.id
                WHERE la.user_id = ?";
        return $this->fetch($sql, [$userId]);
    }
    
    public function getAllTiers() {
        return $this->fetchAll("SELECT * FROM loyalty_tiers ORDER BY min_points ASC");
    }
    
    public function getStats() {
        $totalMembers = $this->fetch("SELECT COUNT(*) as count FROM loyalty_accounts WHERE points > 0");
        $avgPoints = $this->fetch("SELECT AVG(points) as avg FROM loyalty_accounts WHERE points > 0");
        $totalPointsIssued = $this->fetch("SELECT COALESCE(SUM(points), 0) as total FROM loyalty_points WHERE type = 'earned' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        
        $tierBreakdown = $this->fetchAll("SELECT lt.name, COUNT(*) as count 
                                          FROM loyalty_accounts la
                                          JOIN loyalty_tiers lt ON la.tier_id = lt.id
                                          GROUP BY lt.name");
        
        return [
            'total_members' => $totalMembers ? $totalMembers->count : 0,
            'average_points' => $avgPoints ? round($avgPoints->avg, 2) : 0,
            'points_issued_month' => $totalPointsIssued ? floatval($totalPointsIssued->total) : 0,
            'tier_breakdown' => $tierBreakdown
        ];
    }
}
