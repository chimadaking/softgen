<?php
namespace App\Controllers;

class ReportController extends BaseController {
    public function __construct() {
        if (!isLoggedIn() || !isAdmin()) {
            redirect('dashboard');
        }
    }

    public function sales() {
        $orderModel = $this->model('Order');
        $data = [
            'title' => 'Sales Reports',
            'total_sales' => $orderModel->getTotalRevenue(),
            'recent_orders' => $orderModel->getRecentOrdersAdmin(50)
        ];
        $this->view('admin/reports/sales', $data);
    }

    public function revenue() {
        $orderModel = $this->model('Order');
        $data = [
            'title' => 'Revenue Reports',
            'total_revenue' => $orderModel->getTotalRevenue()
        ];
        $this->view('admin/reports/revenue', $data);
    }

    public function users() {
        $userModel = $this->model('User');
        $data = [
            'title' => 'User Reports',
            'users' => $userModel->getAllUsers()
        ];
        $this->view('admin/reports/users', $data);
    }

    public function affiliates() {
        $affiliateModel = $this->model('Affiliate');
        $data = [
            'title' => 'Affiliate Reports',
            'affiliates' => $affiliateModel->getAllAffiliates()
        ];
        $this->view('admin/reports/affiliates', $data);
    }
}
