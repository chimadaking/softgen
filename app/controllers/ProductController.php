<?php
namespace App\Controllers;

class ProductController extends BaseController {
    private $productModel;

    public function __construct() {
        $this->productModel = $this->model('Product');
    }

    public function index() {
        $q = trim($_GET['q'] ?? '');

        $perPage = (int)($_GET['per_page'] ?? 12);
        if ($perPage < 6) $perPage = 6;
        if ($perPage > 48) $perPage = 48;

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;

        // Category filter (slug)
        $categories = $this->productModel->getActiveCategories();
        $categorySlug = trim($_GET['category'] ?? '');

        // Default to API Services category if it exists and category isn't explicitly set
        if ($categorySlug === '') {
            $apiCat = $this->productModel->getCategoryBySlug('api-services');
            if ($apiCat) {
                $categorySlug = 'api-services';
            }
        }

        $categoryId = null;
        if ($categorySlug !== '') {
            $cat = $this->productModel->getCategoryBySlug($categorySlug);
            if ($cat) {
                $categoryId = (int)$cat->id;
            }
        }

        $total = $this->productModel->countActiveProducts($q, $categoryId);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $products = $this->productModel->getActiveProductsPaginated($q, $categoryId, $perPage, $offset);

        $data = [
            'title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'q' => $q,
            'category' => $categorySlug,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ];

        $this->view('products/index', $data);
    }

    public function details($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            redirect('product');
        }
        $data = [
            'title' => $product->name,
            'product' => $product
        ];
        $this->view('products/details', $data);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'name' => trim($_POST['name']),
                'slug' => trim($_POST['slug']),
                'category_id' => trim($_POST['category_id']),
                'description' => trim($_POST['description']),
                'short_description' => trim($_POST['short_description']),
                'price' => trim($_POST['price']),
                'original_price' => trim($_POST['original_price']),
                'stock' => trim($_POST['stock']),
                'min_order' => trim($_POST['min_order']),
                'max_order' => trim($_POST['max_order']),
                'status' => trim($_POST['status']),
                'name_err' => '',
                'slug_err' => '',
                'category_id_err' => '',
                'price_err' => ''
            ];

            if (empty($data['name'])) $data['name_err'] = 'Product name is required';
            if (empty($data['slug'])) $data['slug_err'] = 'Slug is required';
            if (empty($data['category_id'])) $data['category_id_err'] = 'Category is required';
            if (empty($data['price'])) $data['price_err'] = 'Price is required';

            if (empty($data['name_err']) && empty($data['slug_err']) && empty($data['category_id_err']) && empty($data['price_err'])) {
                if ($this->productModel->create($data)) {
                    $data['success'] = 'Product created successfully!';
                    redirect('product');
                } else {
                    $data['error'] = 'Something went wrong';
                }
            }
        } else {
            $data = [
                'name' => '',
                'slug' => '',
                'category_id' => '',
                'description' => '',
                'short_description' => '',
                'price' => '',
                'original_price' => '',
                'stock' => '',
                'min_order' => '',
                'max_order' => '',
                'status' => 'active',
                'name_err' => '',
                'slug_err' => '',
                'category_id_err' => '',
                'price_err' => ''
            ];
        }

        $data['title'] = 'Create Product';
        $data['categories'] = $this->productModel->getCategories();
        $this->view('products/create', $data);
    }

    public function edit($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            redirect('product');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'slug' => trim($_POST['slug']),
                'category_id' => trim($_POST['category_id']),
                'description' => trim($_POST['description']),
                'short_description' => trim($_POST['short_description']),
                'price' => trim($_POST['price']),
                'original_price' => trim($_POST['original_price']),
                'stock' => trim($_POST['stock']),
                'min_order' => trim($_POST['min_order']),
                'max_order' => trim($_POST['max_order']),
                'status' => trim($_POST['status']),
                'name_err' => '',
                'slug_err' => '',
                'category_id_err' => '',
                'price_err' => ''
            ];

            if (empty($data['name'])) $data['name_err'] = 'Product name is required';
            if (empty($data['slug'])) $data['slug_err'] = 'Slug is required';
            if (empty($data['category_id'])) $data['category_id_err'] = 'Category is required';
            if (empty($data['price'])) $data['price_err'] = 'Price is required';

            if (empty($data['name_err']) && empty($data['slug_err']) && empty($data['category_id_err']) && empty($data['price_err'])) {
                if ($this->productModel->update($data)) {
                    $data['success'] = 'Product updated successfully!';
                    redirect('product');
                } else {
                    $data['error'] = 'Something went wrong';
                }
            }
        } else {
            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_id' => $product->category_id,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'stock' => $product->stock,
                'min_order' => $product->min_order,
                'max_order' => $product->max_order,
                'status' => $product->status,
                'name_err' => '',
                'slug_err' => '',
                'category_id_err' => '',
                'price_err' => ''
            ];
        }

        $data['title'] = 'Edit Product';
        $data['categories'] = $this->productModel->getCategories();
        $this->view('products/edit', $data);
    }

    public function delete($id) {
        if ($this->productModel->delete($id)) {
            flash('success', 'Product deleted successfully');
        } else {
            flash('error', 'Something went wrong');
        }
        redirect('product');
    }
}
