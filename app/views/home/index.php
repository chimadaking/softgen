<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<div class="text-center mb-5">
    <h1 class="display-4 mb-3">Welcome to <?php echo SITE_NAME; ?></h1>
    <p class="lead mb-4">Your One-Stop Multi-API Reseller Platform</p>
    <p class="mb-4">Access premium digital products, manage orders, and grow your business with our powerful reseller tools.</p>
    <div class="d-flex justify-content-center gap-3">
        <?php if (!isLoggedIn()): ?>
            <a href="<?php echo site_url('auth/register'); ?>" class="btn btn-primary btn-lg">Get Started</a>
            <a href="<?php echo site_url('auth/login'); ?>" class="btn btn-outline-primary btn-lg">Login</a>
        <?php else: ?>
            <a href="<?php echo site_url('dashboard'); ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <div class="display-1 text-primary mb-3">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h4 class="card-title">Premium Products</h4>
                <p class="card-text">Access a wide range of digital products from top providers.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <div class="display-1 text-success mb-3">
                    <i class="bi bi-wallet2"></i>
                </div>
                <h4 class="card-title">Easy Payments</h4>
                <p class="card-text">Secure wallet system with multiple payment options.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <div class="display-1 text-warning mb-3">
                    <i class="bi bi-people"></i>
                </div>
                <h4 class="card-title">Affiliate Program</h4>
                <p class="card-text">Earn commissions by referring new customers.</p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($data['products'])): ?>
<div class="mb-5">
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="row">
        <?php foreach ($data['products'] as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if ($product->image): ?>
                <img src="<?php echo BASE_DIR; ?>/uploads/products/<?php echo $product->image; ?>" class="card-img-top" alt="<?php echo h($product->name); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-box-seam text-muted display-4"></i>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo h($product->name); ?></h5>

                    <?php
                        // ✅ Fix PHP 8.1+ deprecation: strip_tags(NULL)
                        $desc = (string)($product->description ?? '');
                        $fallback = substr(strip_tags($desc), 0, 100) . '...';
                        $short = $product->short_description ?? $fallback;
                    ?>

                    <p class="card-text text-muted small"><?php echo h($short); ?></p>
                    <p class="card-text"><strong>$<?php echo number_format($product->price, 2); ?></strong></p>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="<?php echo site_url('auth/login'); ?>" class="btn btn-primary w-100">Login to Order</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="row mb-5">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body text-center py-5">
                <h2 class="mb-3">Ready to Get Started?</h2>
                <p class="mb-4">Join thousands of satisfied customers today.</p>
                <a href="<?php echo site_url('auth/register'); ?>" class="btn btn-light btn-lg">Create Free Account</a>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
