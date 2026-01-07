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
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('admin/register');
            }
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
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('admin/login');
            }
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
                $user = $this->userModel->login($data['email'], $data['password']);

                if ($user && $this->hasAdminRole($user->roles ?? [])) {
                    $this->createUserSession($user);
                    redirect('admin');
                }

                $data['password_err'] = 'Invalid credentials or not an admin';
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

    private function createUserSession($user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->username;
        $_SESSION['user_roles'] = $this->extractRoleNames($user->roles ?? []);
        $_SESSION['last_activity'] = time();
    }

    private function extractRoleNames($roles): array
    {
        $roleNames = [];
        if (is_array($roles)) {
            foreach ($roles as $role) {
                $roleNames[] = $role->name;
            }
        }
        return $roleNames;
    }

    private function hasAdminRole($roles): bool
    {
        if (!is_array($roles)) {
            return false;
        }
        foreach ($roles as $role) {
            if ($role->name === 'admin') {
                return true;
            }
        }
        return false;
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
        
        $users = $userModel->getAllUsersWithRoles();
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
            'users' => $userModel->getAllUsersWithRoles()
        ];
        $this->view('admin/users', $data);
    }

    public function userCreate() {
        $this->requireAdmin();

        $userModel = $this->model('User');
        $roles = $userModel->getAllRoles();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('admin/users/create');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'roles' => array_map('intval', $_POST['roles'] ?? []),
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
            } elseif ($userModel->findByEmail($data['email'])) {
                $data['email_err'] = 'Email already exists';
            }

            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            if ($data['password'] !== $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }

            if (empty($data['roles'])) {
                $data['roles'] = [ROLE_USER];
            }

            if (empty($data['username_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                if ($userModel->createUserWithRoles($data, $data['roles'])) {
                    flash('success', 'User created successfully');
                    redirect('admin/users');
                }
                flash('error', 'Failed to create user', 'alert alert-danger');
            }

            $this->view('admin/user_create', [
                'title' => 'Create User',
                'user' => $data,
                'roles' => $roles
            ]);
            return;
        }

        $this->view('admin/user_create', [
            'title' => 'Create User',
            'user' => [
                'username' => '',
                'email' => '',
                'first_name' => '',
                'last_name' => '',
                'status' => 'active',
                'roles' => [ROLE_USER],
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ],
            'roles' => $roles
        ]);
    }

    public function userEdit($id) {
        $this->requireAdmin();

        $userModel = $this->model('User');
        $user = $userModel->findById($id);
        if (!$user) {
            flash('error', 'User not found', 'alert alert-danger');
            redirect('admin/users');
        }

        $roles = $userModel->getAllRoles();
        $currentRoles = $userModel->getUserRoleIds($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('admin/users/edit/' . $id);
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'status' => $_POST['status'] ?? $user->status,
                'roles' => array_map('intval', $_POST['roles'] ?? []),
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
            } elseif ($user->email !== $data['email'] && $userModel->findByEmail($data['email'])) {
                $data['email_err'] = 'Email already exists';
            }

            if (!empty($data['password']) && strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            if (!empty($data['password']) && $data['password'] !== $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }

            if (empty($data['roles'])) {
                $data['roles'] = [ROLE_USER];
            }

            if (empty($data['username_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                if ($userModel->updateUser($id, $data) && $userModel->setUserRoles($id, $data['roles'])) {
                    flash('success', 'User updated successfully');
                    redirect('admin/users');
                }
                flash('error', 'Failed to update user', 'alert alert-danger');
            }

            $this->view('admin/user_edit', [
                'title' => 'Edit User',
                'user' => (object)array_merge((array)$user, $data),
                'roles' => $roles,
                'selected_roles' => $data['roles']
            ]);
            return;
        }

        $this->view('admin/user_edit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles,
            'selected_roles' => $currentRoles
        ]);
    }

    public function userDelete($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users');
        }

        if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            flash('error', 'Invalid CSRF token', 'alert alert-danger');
            redirect('admin/users');
        }

        $userModel = $this->model('User');
        if ($userModel->deleteUser($id)) {
            flash('success', 'User deleted successfully');
        } else {
            flash('error', 'Failed to delete user', 'alert alert-danger');
        }
        redirect('admin/users');
    }

    public function userStatus($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users');
        }

        if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            flash('error', 'Invalid CSRF token', 'alert alert-danger');
            redirect('admin/users');
        }

        $status = $_POST['status'] ?? 'active';
        $status = in_array($status, ['active', 'pending', 'suspended'], true) ? $status : 'active';

        $userModel = $this->model('User');
        if ($userModel->updateStatus($id, $status)) {
            flash('success', 'User status updated');
        } else {
            flash('error', 'Failed to update status', 'alert alert-danger');
        }
        redirect('admin/users');
    }

    public function userRoles($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users/edit/' . $id);
        }

        if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            flash('error', 'Invalid CSRF token', 'alert alert-danger');
            redirect('admin/users/edit/' . $id);
        }

        $roleIds = array_map('intval', $_POST['roles'] ?? []);
        if (empty($roleIds)) {
            $roleIds = [ROLE_USER];
        }

        $userModel = $this->model('User');
        if ($userModel->setUserRoles($id, $roleIds)) {
            flash('success', 'User roles updated');
        } else {
            flash('error', 'Failed to update roles', 'alert alert-danger');
        }

        redirect('admin/users/edit/' . $id);
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
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('admin/loyalty/' . $userId);
            }
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
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('admin/loyalty/settings');
            }
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
