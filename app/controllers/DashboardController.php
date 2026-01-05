<?php
namespace App\Controllers;

class DashboardController extends BaseController {
    private $userModel;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userModel = $this->model('User');
    }

    public function index() {
        $user = getUser();
        if (!$user) {
            redirect('auth/login');
            return;
        }
        
        $walletModel = $this->model('Wallet');
        $orderModel = $this->model('Order');
        $loyaltyModel = $this->model('Loyalty');

        $data = [
            'title' => 'Dashboard',
            'balance' => $walletModel->getBalance($user['id']),
            'recent_orders' => $orderModel->getRecentOrders($user['id'], 5),
            'loyalty_points' => $loyaltyModel->getPoints($user['id'])
        ];

        $this->view('dashboard/index', $data);
    }

    public function profile() {
        $userData = getUser();
        if (!$userData) {
            redirect('auth/login');
            return;
        }
        $user = $this->userModel->findById($userData['id']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'user' => $user,
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'first_name_err' => '',
                'last_name_err' => '',
                'current_password_err' => '',
                'new_password_err' => '',
                'confirm_password_err' => ''
            ];

            if (empty($data['first_name_err']) && empty($data['last_name_err'])) {
                if ($this->userModel->updateProfile($userData['id'], $data)) {
                    $data['success'] = 'Profile updated successfully!';
                    $user = $this->userModel->findById($userData['id']);
                    $data['user'] = $user;
                } else {
                    $data['error'] = 'Something went wrong. Please try again.';
                }
            }
        } else {
            $data = [
                'user' => $user,
                'first_name' => '',
                'last_name' => '',
                'first_name_err' => '',
                'last_name_err' => '',
                'current_password_err' => '',
                'new_password_err' => '',
                'confirm_password_err' => ''
            ];
        }

        $data['title'] = 'My Profile';
        $this->view('dashboard/profile', $data);
    }

    public function password() {
        $userData = getUser();
        if (!$userData) {
            redirect('auth/login');
            return;
        }
        $user = $this->userModel->findById($userData['id']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'user' => $user,
                'current_password' => trim($_POST['current_password']),
                'new_password' => trim($_POST['new_password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'current_password_err' => '',
                'new_password_err' => '',
                'confirm_password_err' => ''
            ];

            if (empty($data['current_password'])) {
                $data['current_password_err'] = 'Please enter current password';
            }

            if (empty($data['new_password'])) {
                $data['new_password_err'] = 'Please enter new password';
            } elseif (strlen($data['new_password']) < 6) {
                $data['new_password_err'] = 'Password must be at least 6 characters';
            }

            if ($data['new_password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }

            if (empty($data['current_password_err']) && empty($data['new_password_err']) && empty($data['confirm_password_err'])) {
                if ($this->userModel->changePassword($userData['id'], $data['current_password'], $data['new_password'])) {
                    $data['success'] = 'Password changed successfully!';
                } else {
                    $data['current_password_err'] = 'Current password is incorrect';
                }
            }
        }

        $data['title'] = 'My Profile';
        $this->view('dashboard/profile', $data);
    }
}
