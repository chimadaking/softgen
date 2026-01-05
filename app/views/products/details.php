<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo site_url('product'); ?>">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo $data['product']->name; ?></li>
  </ol>
</nav>

<div class="row">
    <div class="col-md-6 mb-4">
        <?php if ($data['product']->image): ?>
            <img src="<?php echo BASE_DIR . '/uploads/products/' . $data['product']->image; ?>" class="img-fluid rounded shadow-sm" alt="<?php echo h($data['product']->name); ?>">
        <?php else: ?>
            <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded shadow-sm" style="height: 400px;">
                <i class="bi bi-image" style="font-size: 5rem;"></i>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h1 class="display-5 fw-bold"><?php echo h($data['product']->name); ?></h1>
        <p class="text-muted mb-4"><?php echo h($data['product']->category_name); ?></p>
        <h2 class="text-primary mb-4">$<?php echo number_format($data['product']->price, 2); ?></h2>
        
        <div class="card mb-4">
            <div class="card-body">
                <form action="<?php echo BASE_DIR; ?>/order/checkout/<?php echo $data['product']->id; ?>" method="get">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="100">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Buy Now</button>
                    </div>
                </form>
            </div>
        </div>
        
        <h4>Description</h4>
        <p><?php echo nl2br(h($data['product']->description)); ?></p>
    </div>
</div>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
