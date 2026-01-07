<?php
namespace App\Controllers;

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('auth/register');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            } elseif ($this->userModel->findByEmail($data['email'])) {
                $data['email_err'] = 'Email is already taken';
            }

            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            if ($data['password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }

            if (empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                if ($this->userModel->register($data)) {
                    flash('register_success', 'You are registered and can log in');
                    redirect('auth/login');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('auth/register', $data);
            }
        } else {
            $data = [
                'username' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'first_name' => '',
                'last_name' => '',
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            $this->view('auth/register', $data);
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('auth/login');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'email_err' => '',
                'password_err' => '',
            ];

            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            }
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            }

            if (empty($data['email_err']) && empty($data['password_err'])) {
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);
                
                if ($loggedInUser) {
                    $this->createUserSession($loggedInUser);
                } else {
                    $data['password_err'] = 'Email or password is incorrect';
                    $this->view('auth/login', $data);
                }
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => '',
            ];
            $this->view('auth/login', $data);
        }
    }

    public function createUserSession($user) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->username;
        $_SESSION['user_roles'] = $this->extractRoleNames($user->roles);
        $_SESSION['last_activity'] = time();

        // Single login form - redirect based on roles
        // If user has admin role, go to admin dashboard
        // Otherwise go to user dashboard
        if ($this->hasAdminRole($user->roles)) {
            redirect('admin');
        } else {
            redirect('dashboard');
        }
    }

    /**
     * Extract role names from roles array
     */
    private function extractRoleNames($roles) {
        $roleNames = [];
        if (is_array($roles)) {
            foreach ($roles as $role) {
                $roleNames[] = $role->name;
            }
        }
        return $roleNames;
    }

    /**
     * Check if roles array contains admin role
     */
    private function hasAdminRole($roles) {
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

    public function logout() {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        redirect('auth/login');
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('auth/forgot-password');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $email = trim($_POST['email']);
            
            if (empty($email)) {
                flash('error', 'Please enter your email address');
                $this->view('auth/forgot-password', []);
                return;
            }

            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                flash('error', 'No account found with that email address');
                $this->view('auth/forgot-password', []);
                return;
            }

            $resetModel = $this->model('PasswordReset');
            $token = $resetModel->createToken($user->id);
            $resetLink = site_url('auth/reset-password/' . $token);

            flash('success', 'Password reset link generated. Use the link below to continue.');
            $this->view('auth/forgot-password', ['reset_link' => $resetLink]);
            return;
        } else {
            $this->view('auth/forgot-password', []);
        }
    }

    public function resetPassword($token) {
        $resetModel = $this->model('PasswordReset');
        $record = $resetModel->findValidToken($token);

        if (!$record) {
            flash('error', 'Reset link is invalid or expired', 'alert alert-danger');
            redirect('auth/forgot-password');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('auth/reset-password/' . $token);
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'password' => trim($_POST['password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter a new password';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            if ($data['password'] !== $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }

            if (empty($data['password_err']) && empty($data['confirm_password_err'])) {
                if ($this->userModel->updatePasswordById((int)$record->user_id, $data['password'])) {
                    $resetModel->markUsed((int)$record->id);
                    flash('success', 'Password reset successful. You can log in now.');
                    redirect('auth/login');
                }
                flash('error', 'Failed to reset password', 'alert alert-danger');
            }

            $this->view('auth/reset-password', [
                'token' => $token,
                'password_err' => $data['password_err'],
                'confirm_password_err' => $data['confirm_password_err']
            ]);
            return;
        }

        $this->view('auth/reset-password', [
            'token' => $token,
            'password_err' => '',
            'confirm_password_err' => ''
        ]);
    }

    public function referral($code) {
        if (!empty($code)) {
            $_SESSION['referral_code'] = $code;
        }
        redirect('auth/register');
    }
}
