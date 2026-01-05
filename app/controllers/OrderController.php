<?php
namespace App\Controllers;

class OrderController extends BaseController {
    private $orderModel;
    private $productModel;
    private $walletModel;
    private $userData;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->userData = getUser();
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
        $this->walletModel = $this->model('Wallet');
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

                $orderId = $this->orderModel->createOrder($this->userData['id'], $totalAmount, [[
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price
                ]]);

                if ($orderId) {
                    $this->walletModel->updateBalance($this->userData['id'], $totalAmount, 'debit');
                    $this->walletModel->addTransaction($this->userData['id'], $totalAmount, 'purchase', 'Order #' . $orderId);
                    flash('success', 'Order placed successfully!');
                    redirect('order');
                }
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
            $quantity = intval($_POST['quantity']);
            $totalAmount = $product->price * $quantity;
            $userBalance = $this->walletModel->getBalance($this->userData['id']);

            if ($userBalance >= $totalAmount) {
                $items = [['product_id' => $productId, 'quantity' => $quantity, 'price' => $product->price]];
                $orderId = $this->orderModel->createOrder($this->userData['id'], $totalAmount, $items);
                
                if ($orderId) {
                    $this->walletModel->updateBalance($this->userData['id'], $totalAmount, 'debit');
                    $this->walletModel->addTransaction($this->userData['id'], $totalAmount, 'purchase', "Order #$orderId");
                    
                    flash('order_success', 'Order placed successfully!');
                    redirect('order/index');
                }
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
