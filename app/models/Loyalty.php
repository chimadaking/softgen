<?php
namespace App\Models;

class Loyalty extends BaseModel {
    public function getPoints($userId) {
        $account = $this->fetch("SELECT points FROM loyalty_accounts WHERE user_id = ?", [$userId]);
        return $account ? $account->points : 0;
    }

    public function addPoints($userId, $points, $description) {
        $this->query("UPDATE loyalty_accounts SET points = points + ? WHERE user_id = ?", [$points, $userId]);
        return $this->query("INSERT INTO loyalty_points (user_id, points, type, description) VALUES (?, ?, 'earned', ?)", 
                             [$userId, $points, $description]);
    }

    public function getTier($points) {
        $tiers = $this->fetchAll("SELECT * FROM loyalty_tiers ORDER BY min_points DESC");
        foreach ($tiers as $tier) {
            if ($points >= $tier->min_points) {
                $result = array();
                $result['name'] = $tier->name;
                $result['next_points'] = $tier->min_points;
                return $result;
            }
        }
        $result = array();
        $result['name'] = 'Bronze';
        $result['next_points'] = 1000;
        return $result;
    }

    public function getHistory($userId) {
        return $this->fetchAll("SELECT * FROM loyalty_points WHERE user_id = ? ORDER BY created_at DESC LIMIT 20", [$userId]);
    }
}
