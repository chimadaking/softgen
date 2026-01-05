<?php
namespace App\Models;

class Affiliate extends BaseModel {
    public function getAffiliateByUserId($userId) {
        return $this->fetch("SELECT * FROM affiliates WHERE user_id = ?", [$userId]);
    }

    public function registerAffiliate($userId) {
        $referralCode = strtoupper(substr(md5(uniqid()), 0, 8));
        $sql = "INSERT INTO affiliates (user_id, referral_code) VALUES (?, ?)";
        return $this->query($sql, [$userId, $referralCode]);
    }

    public function getReferrals($affiliateId) {
        return $this->fetchAll("SELECT r.*, u.username, u.created_at as joined_at 
                               FROM affiliate_referrals r 
                               JOIN users u ON r.referred_user_id = u.id 
                               WHERE r.affiliate_id = ?", [$affiliateId]);
    }

    public function getPendingCommissions($affiliateId) {
        $result = $this->fetch("SELECT SUM(amount) as total FROM affiliate_commissions WHERE affiliate_id = ? AND status = 'pending'", [$affiliateId]);
        return $result ? $result->total : 0;
    }

    public function getAllAffiliates() {
        return $this->fetchAll("SELECT a.*, u.username, u.email, a.created_at as affiliate_since 
                               FROM affiliates a 
                               JOIN users u ON a.user_id = u.id 
                               ORDER BY a.created_at DESC");
    }
}
