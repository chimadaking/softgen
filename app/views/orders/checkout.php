<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Checkout</h1>

<?php flash('order_error'); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Product Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <?php if ($data['product']->image): ?>
                            <img src="<?php echo BASE_DIR . '/uploads/products/' . $data['product']->image; ?>" alt="" style="width: 80px;" class="rounded">
                        <?php else: ?>
                            <div class="bg-light p-3 rounded"><i class="bi bi-box" style="font-size: 2rem;"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0"><?php echo h($data['product']->name); ?></h5>
                        <p class="text-muted mb-0"><?php echo h($data['product']->category_name); ?></p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 fw-bold">$<?php echo number_format($data['product']->price, 2); ?></p>
                        <p class="text-muted small">Qty: <?php echo $_GET['quantity'] ?? 1; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Payment Method</h5>
            </div>
            <div class="card-body">
                <div class="form-check p-3 border rounded mb-3 bg-light">
                    <input class="form-check-input" type="radio" name="payment_method" id="wallet" checked>
                    <label class="form-check-label d-flex justify-content-between w-100" for="wallet">
                        <span><i class="bi bi-wallet2 me-2"></i> Wallet Balance</span>
                        <span class="fw-bold">$<?php echo number_format($this->walletModel->getBalance($_SESSION['user_id']), 2); ?></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($data['product']->price * ($_GET['quantity'] ?? 1), 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Discount</span>
                    <span>-$0.00</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold h4 text-primary">$<?php echo number_format($data['product']->price * ($_GET['quantity'] ?? 1), 2); ?></span>
                </div>
                
                <form action="<?php echo BASE_DIR; ?>/order/checkout/<?php echo $data['product']->id; ?>" method="post">
                    <input type="hidden" name="quantity" value="<?php echo $_GET['quantity'] ?? 1; ?>">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
