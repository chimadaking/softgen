<?php
namespace App\Controllers;

use App\Models\User;

class AdminController extends BaseController {

    protected $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            if (empty($data['username'])) {
                $data['username_err'] = 'Please enter username';
            }
            
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            } elseif ($this->model('User')->findByEmail($data['email'])) {
                $data['email_err'] = 'Email already exists';
            }

            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            if ($data['password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }

            if (empty($data['username_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
                $userModel = $this->model('User');
                if ($userModel->createAdmin($data)) {
                    flash('register_success', 'Admin user created successfully!');
                    redirect('admin');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('admin/register', $data);
            }
        } else {
            $data = [
                'username' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            $this->view('admin/register', $data);
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'email_err' => '',
                'password_err' => ''
            ];

            if (empty($data['email'])) {
                $data['email_err'] = 'Email is required';
            }

            if (empty($data['password'])) {
                $data['password_err'] = 'Password is required';
            }

            if (empty($data['email_err']) && empty($data['password_err'])) {
                $user = $this->userModel->findByEmail($data['email']);

                if ($user && password_verify($data['password'], $user->password) && $user->role === 'admin') {
                    $_SESSION['admin_id'] = $user->id;
                    $_SESSION['admin_email'] = $user->email;

                    redirect('admin');
                } else {
                    $data['password_err'] = 'Invalid credentials or not an admin';
                }
            }

            $this->view('admin/login', $data);
        } else {
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => ''
            ];

            $this->view('admin/login', $data);
        }
    }

    protected function requireAdmin() {
        if (!isLoggedIn() || !isAdmin()) {
            flash('error', 'Access denied. Admin only.');
            redirect('dashboard');
        }
    }

    public function index() {
        $this->requireAdmin();
        
        $userModel = $this->model('User');
        $orderModel = $this->model('Order');
        $productModel = $this->model('Product');
        $loyaltyModel = $this->model('Loyalty');
        
        $users = $userModel->getAllUsers();
        $loyaltyStats = $loyaltyModel->getStats();
        
        $data = [
            'title' => 'Admin Dashboard',
            'total_users' => count($users),
            'total_revenue' => $orderModel->getTotalRevenue(),
            'total_orders' => count($orderModel->getRecentOrdersAdmin(1000)),
            'total_products' => count($productModel->getAllProductsAdmin()),
            'recent_orders' => $orderModel->getRecentOrdersAdmin(5),
            'recent_users' => array_slice($users, 0, 5),
            'loyalty_stats' => $loyaltyStats
        ];
        $this->view('admin/index', $data);
    }

    public function users() {
        $this->requireAdmin();
        
        $userModel = $this->model('User');
        $data = [
            'title' => 'Manage Users',
            'users' => $userModel->getAllUsers()
        ];
        $this->view('admin/users', $data);
    }

    public function products() {
        $this->requireAdmin();
        
        $productModel = $this->model('Product');
        $data = [
            'title' => 'Manage Products',
            'products' => $productModel->getAllProductsAdmin()
        ];
        $this->view('admin/products', $data);
    }

    public function orders() {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        $data = [
            'title' => 'Manage Orders',
            'orders' => $orderModel->getRecentOrdersAdmin(50)
        ];
        $this->view('admin/orders', $data);
    }

    public function reports() {
        $this->requireAdmin();
        
        $data = [
            'title' => 'Reports'
        ];
        $this->view('admin/reports', $data);
    }
    
    public function loyalty() {
        $this->requireAdmin();
        
        $loyaltyModel = $this->model('Loyalty');
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $users = $loyaltyModel->getAllUsersWithLoyalty($limit, $offset);
        $totalUsers = count($loyaltyModel->getAllUsersWithLoyalty(10000, 0));
        $totalPages = ceil($totalUsers / $limit);
        
        $data = [
            'title' => 'Loyalty Program',
            'users' => $users,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_users' => $totalUsers
        ];
        $this->view('admin/loyalty', $data);
    }
    
    public function loyaltyDetail($userId) {
        $this->requireAdmin();
        
        $loyaltyModel = $this->model('Loyalty');
        
        $user = $loyaltyModel->getUserLoyaltyById($userId);
        if (!$user) {
            flash('error', 'User not found');
            redirect('admin/loyalty');
        }
        
        $history = $loyaltyModel->getPointsHistory($userId, 100, 0);
        $totalEarned = $loyaltyModel->getTotalPointsEarned($userId);
        $totalRedeemed = $loyaltyModel->getTotalPointsRedeemed($userId);
        
        // Handle manual point adjustments
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $action = $_POST['action'];
            $points = floatval($_POST['points']);
            $reason = trim($_POST['reason']);
            
            if ($points > 0 && !empty($reason)) {
                if ($action == 'add') {
                    $loyaltyModel->addPoints($userId, $points, $reason);
                    flash('success', "Successfully added {$points} points to user");
                } elseif ($action == 'subtract') {
                    if ($loyaltyModel->subtractPoints($userId, $points, $reason)) {
                        flash('success', "Successfully deducted {$points} points from user");
                    } else {
                        flash('error', 'Insufficient points');
                    }
                }
                redirect('admin/loyalty/' . $userId);
            } else {
                flash('error', 'Invalid input');
            }
        }
        
        $data = [
            'title' => 'Loyalty Details - ' . $user->username,
            'user' => $user,
            'history' => $history,
            'total_earned' => $totalEarned,
            'total_redeemed' => $totalRedeemed
        ];
        $this->view('admin/loyalty_detail', $data);
    }
    
    public function loyaltySettings() {
        $this->requireAdmin();
        
        $loyaltyModel = $this->model('Loyalty');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $settings = [
                'points_per_dollar_purchase' => floatval($_POST['points_per_dollar_purchase']),
                'points_per_dollar_funding' => floatval($_POST['points_per_dollar_funding']),
                'loyalty_tier_bronze' => intval($_POST['loyalty_tier_bronze']),
                'loyalty_tier_silver' => intval($_POST['loyalty_tier_silver']),
                'loyalty_tier_gold' => intval($_POST['loyalty_tier_gold']),
                'loyalty_tier_platinum' => intval($_POST['loyalty_tier_platinum'])
            ];
            
            $loyaltyModel->updateSettings($settings);
            flash('success', 'Loyalty settings updated successfully!');
        }
        
        $settings = $loyaltyModel->getAllSettings();
        
        $data = [
            'title' => 'Loyalty Settings',
            'settings' => $settings
        ];
        $this->view('admin/loyalty_settings', $data);
    }
}
