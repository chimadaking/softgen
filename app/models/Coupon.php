<?php
namespace App\Models;

class Coupon extends BaseModel {
    public function validate($code, $userId, $amount) {
        $coupon = $this->fetch("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW())", [$code]);
        if (!$coupon) return false;
        
        if ($amount < $coupon->min_spend) return false;
        
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) return false;
        
        return $coupon;
    }
}
