<?php
namespace App\Controllers;

class HomeController extends BaseController {
    private $productModel;

    public function __construct() {
        $this->productModel = $this->model('Product');
    }

    public function index() {
        $products = $this->productModel->getAllProducts();
        $data = [
            'title' => 'Welcome to ' . SITE_NAME,
            'products' => array_slice($products, 0, 6) // Show first 6 products
        ];
        $this->view('home/index', $data);
    }
}
