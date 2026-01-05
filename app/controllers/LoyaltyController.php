<?php
namespace App\Controllers;

class LoyaltyController extends BaseController {
    private $userData;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userData = getUser();
    }

    public function index() {
        $loyaltyModel = $this->model('Loyalty');
        $points = $loyaltyModel->getPoints($this->userData['id']);
        $tier = $loyaltyModel->getTier($points);
        
        $data = [
            'title' => 'Loyalty Points',
            'points' => $points,
            'tier' => $tier['name'],
            'next_tier' => $tier['next_points'],
            'history' => $loyaltyModel->getHistory($this->userData['id'])
        ];
        $this->view('loyalty/index', $data);
    }
}
