<?php
namespace App\Controllers;

class LoyaltyController extends BaseController {
    private $loyaltyModel;
    private $userData;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userData = getUser();
        $this->loyaltyModel = $this->model('Loyalty');
    }

    public function index() {
        $loyaltyData = $this->loyaltyModel->getUserPoints($this->userData['id']);
        $points = $loyaltyData['points'];
        
        $tier = $this->loyaltyModel->getLoyaltyTier($points);
        $history = $this->loyaltyModel->getPointsHistory($this->userData['id'], 50, 0);
        
        $totalEarned = $this->loyaltyModel->getTotalPointsEarned($this->userData['id']);
        $totalRedeemed = $this->loyaltyModel->getTotalPointsRedeemed($this->userData['id']);
        
        $allTiers = $this->loyaltyModel->getAllTiers();
        
        $data = [
            'title' => 'My Loyalty Points',
            'points' => $points,
            'tier' => $tier,
            'history' => $history,
            'total_earned' => $totalEarned,
            'total_redeemed' => $totalRedeemed,
            'all_tiers' => $allTiers
        ];
        
        $this->view('loyalty/index', $data);
    }
    
    public function getStats() {
        header('Content-Type: application/json');
        
        $loyaltyData = $this->loyaltyModel->getUserPoints($this->userData['id']);
        $points = $loyaltyData['points'];
        
        $tier = $this->loyaltyModel->getLoyaltyTier($points);
        $history = $this->loyaltyModel->getPointsHistory($this->userData['id'], 10, 0);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'points' => $points,
                'tier' => $tier,
                'history' => $history
            ]
        ]);
    }
    
    public function redeem() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('loyalty/redeem');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $pointsToRedeem = floatval($_POST['points']);
            $currentPoints = $this->loyaltyModel->getBalance($this->userData['id']);
            
            if ($pointsToRedeem <= 0) {
                flash('error', 'Invalid points amount');
                redirect('loyalty');
            }
            
            if ($pointsToRedeem > $currentPoints) {
                flash('error', 'Insufficient points');
                redirect('loyalty');
            }
            
            // Redeem points for wallet credit (100 points = $1)
            $discountRate = 0.01; // 1 cent per point
            $walletCredit = $pointsToRedeem * $discountRate;
            
            $this->loyaltyModel->subtractPoints($this->userData['id'], $pointsToRedeem, "Redeemed for $" . number_format($walletCredit, 2) . " wallet credit");
            
            // Add credit to wallet
            $walletModel = $this->model('Wallet');
            $walletModel->updateBalance($this->userData['id'], $walletCredit, 'credit');
            $walletModel->addTransaction($this->userData['id'], $walletCredit, 'deposit', 'Loyalty points redemption');
            
            flash('success', "Successfully redeemed {$pointsToRedeem} points for $" . number_format($walletCredit, 2) . " wallet credit!");
            redirect('wallet');
        }
        
        $data = [
            'title' => 'Redeem Points',
            'points' => $this->loyaltyModel->getBalance($this->userData['id']),
            'redemption_rate' => 0.01
        ];
        $this->view('loyalty/redeem', $data);
    }
}
