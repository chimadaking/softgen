<?php
namespace App\Controllers;

class WalletController extends BaseController {
    private $walletModel;
    private $loyaltyModel;
    private $userData;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userData = getUser();
        $this->walletModel = $this->model('Wallet');
        $this->loyaltyModel = $this->model('Loyalty');
    }

    public function index() {
        $data = [
            'title' => 'My Wallet',
            'balance' => $this->walletModel->getBalance($this->userData['id']),
            'transactions' => $this->walletModel->getTransactions($this->userData['id'])
        ];
        $this->view('wallet/index', $data);
    }

    public function fund() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('wallet/fund');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'amount' => trim($_POST['amount']),
                'method' => trim($_POST['method']),
                'amount_err' => ''
            ];

            if (empty($data['amount'])) {
                $data['amount_err'] = 'Please enter an amount';
            } elseif ($data['amount'] < 1) {
                $data['amount_err'] = 'Minimum deposit amount is $1.00';
            } elseif ($data['amount'] > 10000) {
                $data['amount_err'] = 'Maximum deposit amount is $10,000.00';
            }

            if (empty($data['amount_err'])) {
                $amount = floatval($data['amount']);
                $created = $this->walletModel->createPendingDeposit(
                    $this->userData['id'],
                    $amount,
                    'Wallet funding request via ' . ucfirst($data['method'])
                );

                if ($created) {
                    $data['success'] = 'Funding request submitted and pending review.';
                } else {
                    $data['amount_err'] = 'Unable to submit funding request. Please try again.';
                    $this->view('wallet/fund', $data);
                    return;
                }
                redirect('wallet');
            }
        } else {
            $data = [
                'amount' => '',
                'method' => 'credit_card',
                'amount_err' => ''
            ];
        }

        $data['title'] = 'Fund Wallet';
        $data['balance'] = $this->walletModel->getBalance($this->userData['id']);
        $this->view('wallet/fund', $data);
    }

    public function withdraw() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('wallet/withdraw');
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'amount' => trim($_POST['amount']),
                'method' => trim($_POST['method']),
                'amount_err' => ''
            ];

            $currentBalance = $this->walletModel->getBalance($this->userData['id']);

            if (empty($data['amount'])) {
                $data['amount_err'] = 'Please enter an amount';
            } elseif ($data['amount'] < 10) {
                $data['amount_err'] = 'Minimum withdrawal amount is $10.00';
            } elseif ($data['amount'] > $currentBalance) {
                $data['amount_err'] = 'Insufficient balance';
            }

            if (empty($data['amount_err'])) {
                $amount = floatval($data['amount']);
                $success = $this->walletModel->createPendingWithdrawal(
                    $this->userData['id'],
                    $amount,
                    'Withdrawal request via ' . ucfirst($data['method'])
                );

                if ($success) {
                    $data['success'] = 'Withdrawal request submitted and pending review.';
                } else {
                    $data['amount_err'] = 'Unable to submit withdrawal request.';
                    $this->view('wallet/withdraw', $data);
                    return;
                }
                redirect('wallet');
            }
        } else {
            $data = [
                'amount' => '',
                'method' => 'bank_transfer',
                'amount_err' => ''
            ];
        }

        $data['title'] = 'Withdraw Funds';
        $data['balance'] = $this->walletModel->getBalance($this->userData['id']);
        $this->view('wallet/withdraw', $data);
    }
}
