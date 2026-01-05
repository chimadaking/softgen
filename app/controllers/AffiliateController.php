<?php
namespace App\Controllers;

class AffiliateController extends BaseController {
    private $userData;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userData = getUser();
    }

    public function index() {
        $affiliateModel = $this->model('Affiliate');
        $affiliate = $affiliateModel->getAffiliateByUserId($this->userData['id']);
        
        if (!$affiliate) {
            $affiliateModel->registerAffiliate($this->userData['id']);
            $affiliate = $affiliateModel->getAffiliateByUserId($this->userData['id']);
        }

        $referrals = $affiliateModel->getReferrals($affiliate->id);
        
        $data = [
            'title' => 'Affiliate Program',
            'referral_code' => $affiliate->referral_code,
            'total_earnings' => $affiliate->total_earnings ?? 0,
            'commission_rate' => $affiliate->commission_rate ?? 5,
            'total_referrals' => count($referrals),
            'pending_commissions' => $affiliateModel->getPendingCommissions($affiliate->id),
            'referrals' => $referrals
        ];
        $this->view('affiliate/index', $data);
    }
}
