<?php
namespace App\Controllers;

class WalletController extends BaseController {
    private $walletModel;
    private $userData;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userData = getUser();
        $this->walletModel = $this->model('Wallet');
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
                // In a real app, redirect to payment gateway (Binance, PayPal, etc.)
                // For this demo, we'll simulate a successful payment
                $this->walletModel->updateBalance($this->userData['id'], $amount, 'credit');
                $this->walletModel->addTransaction($this->userData['id'], $amount, 'deposit', 'Wallet funding via ' . ucfirst($data['method']));
                $data['success'] = 'Wallet funded successfully!';
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
                // In a real app, initiate withdrawal request
                $this->walletModel->updateBalance($this->userData['id'], $amount, 'debit');
                $this->walletModel->addTransaction($this->userData['id'], $amount, 'withdrawal', 'Withdrawal via ' . ucfirst($data['method']));
                $data['success'] = 'Withdrawal request submitted successfully!';
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
