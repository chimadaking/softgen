<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Place Order - <?php echo h($data['product']->name); ?></h1>

<a href="<?php echo site_url('product/details/' . $data['product']->id); ?>" class="btn btn-secondary mb-3">&larr; Back to Product</a>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Order Details</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_DIR; ?>/order/create/<?php echo $data['product']->id; ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="row align-items-center mb-4">
                        <div class="col-md-4">
                            <?php if ($data['product']->image): ?>
                            <img src="<?php echo BASE_DIR; ?>/uploads/products/<?php echo $data['product']->image; ?>" class="img-fluid" alt="<?php echo h($data['product']->name); ?>">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="bi bi-box-seam text-muted display-4"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h4><?php echo h($data['product']->name); ?></h4>
                            <p class="text-muted"><?php echo h($data['product']->short_description ?? substr(strip_tags($data['product']->description), 0, 100) . '...'); ?></p>
                            <h3 class="text-primary">$<?php echo number_format($data['product']->price, 2); ?></h3>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(-1)">-</button>
                            <input type="number" name="quantity" id="quantity" class="form-control text-center" 
                                   value="<?php echo h($data['quantity'] ?? $data['product']->min_order); ?>" 
                                   min="<?php echo $data['product']->min_order; ?>" 
                                   max="<?php echo $data['product']->max_order; ?>" 
                                   required>
                            <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(1)">+</button>
                        </div>
                        <small class="form-text text-muted">Min: <?php echo $data['product']->min_order; ?> | Max: <?php echo $data['product']->max_order; ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Order Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Any special instructions..."></textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Total:</h4>
                        <h3 id="total" class="text-primary">$<?php echo number_format($data['product']->price * ($data['quantity'] ?? $data['product']->min_order), 2); ?></h3>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-cart-check"></i> Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Your Wallet</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <p class="mb-1">Available Balance</p>
                    <h2 class="text-primary">$<?php echo number_format($data['balance'], 2); ?></h2>
                </div>
            </div>
        </div>

        <?php if ($data['balance'] < $data['product']->price): ?>
        <div class="card shadow-sm mt-4 bg-warning bg-opacity-10">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> Low Balance</h5>
                <p class="card-text">You don't have enough balance to complete this order.</p>
                <a href="<?php echo site_url('wallet/fund'); ?>" class="btn btn-warning w-100">Fund Wallet</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const price = <?php echo $data['product']->price; ?>;
const minOrder = <?php echo $data['product']->min_order; ?>;
const maxOrder = <?php echo $data['product']->max_order; ?>;

function updateQuantity(change) {
    const input = document.getElementById('quantity');
    let newValue = parseInt(input.value) + change;
    
    if (newValue < minOrder) newValue = minOrder;
    if (newValue > maxOrder) newValue = maxOrder;
    
    input.value = newValue;
    updateTotal();
}

function updateTotal() {
    const quantity = parseInt(document.getElementById('quantity').value);
    const total = (price * quantity).toFixed(2);
    document.getElementById('total').textContent = '$' + total;
}

document.getElementById('quantity').addEventListener('change', updateTotal);
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
