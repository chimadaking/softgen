<?php
namespace App\Controllers;

class OrderController extends BaseController {
    private $orderModel;
    private $productModel;
    private $walletModel;
    private $loyaltyModel;
    private $userData;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userData = getUser();
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
        $this->walletModel = $this->model('Wallet');
        $this->loyaltyModel = $this->model('Loyalty');
    }

    public function index() {
        $orders = $this->orderModel->getRecentOrders($this->userData['id'], 50);
        $data = [
            'title' => 'My Orders',
            'orders' => $orders
        ];
        $this->view('orders/index', $data);
    }

    public function details($id) {
        $order = $this->orderModel->getOrderById($id);
        if (!$order || $order->user_id != $this->userData['id']) {
            flash('error', 'Order not found');
            redirect('order');
        }
        $items = $this->orderModel->getOrderItems($id);
        $data = [
            'title' => 'Order #' . $id,
            'order' => $order,
            'items' => $items
        ];
        $this->view('orders/details', $data);
    }

    public function create($productId) {
        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            flash('error', 'Product not found');
            redirect('product');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('order/create/' . $productId);
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'product' => $product,
                'quantity' => trim($_POST['quantity']),
                'quantity_err' => ''
            ];

            if (empty($data['quantity']) || $data['quantity'] < $product->min_order) {
                $data['quantity_err'] = 'Minimum quantity is ' . $product->min_order;
            } elseif ($data['quantity'] > $product->max_order) {
                $data['quantity_err'] = 'Maximum quantity is ' . $product->max_order;
            }

            if (empty($data['quantity_err'])) {
                $quantity = intval($data['quantity']);
                $totalAmount = $product->price * $quantity;
                $balance = $this->walletModel->getBalance($this->userData['id']);

                if ($balance < $totalAmount) {
                    flash('error', 'Insufficient balance. Please fund your wallet.');
                    redirect('wallet/fund');
                }

                $orderId = $this->orderModel->createOrderWithWalletDebit($this->userData['id'], $totalAmount, [[
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price
                ]]);

                if ($orderId) {
                    // Award loyalty points for purchase
                    $pointsRate = $this->loyaltyModel->getPointsRate('purchase');
                    $basePoints = $totalAmount * $pointsRate;
                    
                    // Add tier bonus
                    $bonusPoints = $this->loyaltyModel->calculateTierBonus($this->userData['id'], $basePoints);
                    $totalPointsEarned = $basePoints + $bonusPoints;
                    
                    if ($totalPointsEarned > 0) {
                        $reason = "Purchase - Order #$orderId";
                        if ($bonusPoints > 0) {
                            $reason .= " (includes {$bonusPoints} tier bonus)";
                        }
                        $this->loyaltyModel->addPoints($this->userData['id'], $totalPointsEarned, $reason, $orderId);
                        $this->loyaltyModel->updateTier($this->userData['id']);
                    }
                    
                    flash('success', 'Order placed successfully! You earned ' . number_format($totalPointsEarned, 2) . ' loyalty points.');
                    redirect('order');
                }

                flash('error', 'Unable to place order. Please check your wallet balance and try again.');
            }
        } else {
            $data = [
                'product' => $product,
                'quantity' => $product->min_order,
                'quantity_err' => ''
            ];
        }

        $data['title'] = 'Place Order - ' . $product->name;
        $data['balance'] = $this->walletModel->getBalance($this->userData['id']);
        $this->view('orders/create', $data);
    }

    public function cancel($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('order/details/' . $id);
        }

        if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            flash('error', 'Invalid CSRF token', 'alert alert-danger');
            redirect('order/details/' . $id);
        }

        $order = $this->orderModel->getOrderById($id);
        if ($order && $order->user_id == $this->userData['id'] && in_array($order->status, ['pending', 'processing'])) {
            if ($this->orderModel->updateStatus($id, 'cancelled')) {
                $this->walletModel->updateBalance($this->userData['id'], $order->total_amount, 'credit');
                $this->walletModel->addTransaction($this->userData['id'], $order->total_amount, 'refund', 'Refund for order #' . $id);
                flash('success', 'Order cancelled successfully. Amount refunded to your wallet.');
            }
        }
        redirect('order/details/' . $id);
    }

    public function checkout($productId) {
        $product = $this->productModel->getProductById($productId);
        if (!$product) redirect('product');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                flash('error', 'Invalid CSRF token', 'alert alert-danger');
                redirect('order/checkout/' . $productId);
            }
            $quantity = intval($_POST['quantity']);
            $totalAmount = $product->price * $quantity;
            $userBalance = $this->walletModel->getBalance($this->userData['id']);

            if ($userBalance >= $totalAmount) {
                $items = [['product_id' => $productId, 'quantity' => $quantity, 'price' => $product->price]];
                $orderId = $this->orderModel->createOrderWithWalletDebit($this->userData['id'], $totalAmount, $items);
                
                if ($orderId) {
                    // Award loyalty points for purchase
                    $pointsRate = $this->loyaltyModel->getPointsRate('purchase');
                    $basePoints = $totalAmount * $pointsRate;
                    
                    // Add tier bonus
                    $bonusPoints = $this->loyaltyModel->calculateTierBonus($this->userData['id'], $basePoints);
                    $totalPointsEarned = $basePoints + $bonusPoints;
                    
                    if ($totalPointsEarned > 0) {
                        $reason = "Purchase - Order #$orderId";
                        if ($bonusPoints > 0) {
                            $reason .= " (includes {$bonusPoints} tier bonus)";
                        }
                        $this->loyaltyModel->addPoints($this->userData['id'], $totalPointsEarned, $reason, $orderId);
                        $this->loyaltyModel->updateTier($this->userData['id']);
                    }
                    
                    flash('order_success', 'Order placed successfully! You earned ' . number_format($totalPointsEarned, 2) . ' loyalty points.');
                    redirect('order/index');
                }
                flash('order_error', 'Unable to place order. Please check your wallet balance.', 'alert alert-danger');
            } else {
                flash('order_error', 'Insufficient balance!', 'alert alert-danger');
            }
        }

        $data = [
            'title' => 'Checkout',
            'product' => $product
        ];
        $this->view('orders/checkout', $data);
    }
}
