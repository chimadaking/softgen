<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
$q = $data['q'] ?? '';
$category = $data['category'] ?? '';
$page = (int)($data['page'] ?? 1);
$perPage = (int)($data['per_page'] ?? 12);
$total = (int)($data['total'] ?? 0);
$totalPages = (int)($data['total_pages'] ?? 1);

function build_products_url(array $overrides = []): string {
    $params = array_merge([
        'q' => $_GET['q'] ?? '',
        'category' => $_GET['category'] ?? '',
        'page' => $_GET['page'] ?? 1,
        'per_page' => $_GET['per_page'] ?? 12,
    ], $overrides);

    // Clean empty params
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) {
            unset($params[$k]);
        }
    }

    $base = site_url('product');
    if (empty($params)) {
        return $base;
    }
    $sep = (strpos($base, '?') !== false) ? '&' : '?';
    return $base . $sep . http_build_query($params);
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="mb-1">Products</h1>
        <div class="text-muted small">
            <?php echo number_format($total); ?> result<?php echo $total === 1 ? '' : 's'; ?>
        </div>
    </div>

    <form method="GET" action="<?php echo site_url('product'); ?>" class="d-flex flex-wrap gap-2 align-items-center">
        <input type="hidden" name="page" value="1">

        <div class="input-group" style="min-width: 280px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Search products..."
                value="<?php echo h($q); ?>"
                autocomplete="off"
            >
        </div>

        <select name="category" class="form-select" style="min-width: 220px;" onchange="this.form.submit()">
            <option value="">All categories</option>
            <?php foreach (($data['categories'] ?? []) as $c): ?>
                <option value="<?php echo h($c->slug); ?>" <?php echo ($category === $c->slug) ? 'selected' : ''; ?>>
                    <?php echo h($c->name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="per_page" class="form-select" style="width: 120px;" onchange="this.form.submit()">
            <?php foreach ([6, 12, 18, 24, 36, 48] as $n): ?>
                <option value="<?php echo $n; ?>" <?php echo ($perPage === $n) ? 'selected' : ''; ?>><?php echo $n; ?>/page</option>
            <?php endforeach; ?>
        </select>

        <a class="btn btn-outline-secondary" href="<?php echo site_url('product'); ?>">Reset</a>
    </form>
</div>

<div class="row">
    <?php if (!empty($data['products'])): ?>
        <?php foreach ($data['products'] as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($product->image)): ?>
                        <img
                            src="<?php echo BASE_DIR; ?>/uploads/products/<?php echo h($product->image); ?>"
                            class="card-img-top"
                            alt="<?php echo h($product->name); ?>"
                        >
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title mb-1"><?php echo h($product->name); ?></h5>
                        <p class="card-text text-muted small mb-2"><?php echo h($product->category_name); ?></p>

                        <?php if (!empty($product->short_description)): ?>
                            <p class="card-text text-muted mb-2"><?php echo h(substr($product->short_description, 0, 90)); ?>...</p>
                        <?php endif; ?>

                        <p class="card-text text-primary fw-bold fs-5 mb-3">$<?php echo number_format((float)$product->price, 2); ?></p>

                        <div class="d-grid gap-2">
                            <a href="<?php echo site_url('product/details/' . $product->id); ?>" class="btn btn-primary">View Details</a>
                            <a href="<?php echo site_url('order/create/' . $product->id); ?>" class="btn btn-success">
                                <i class="bi bi-cart-plus"></i> Order Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center">No products found.</div>
        </div>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <nav aria-label="Products pagination" class="mt-2">
        <ul class="pagination justify-content-center flex-wrap">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo build_products_url(['page' => max(1, $page - 1)]); ?>">&laquo;</a>
            </li>

            <?php
            $window = 2;
            $start = max(1, $page - $window);
            $end = min($totalPages, $page + $window);

            if ($start > 1) {
                echo '<li class="page-item"><a class="page-link" href="' . h(build_products_url(['page' => 1])) . '">1</a></li>';
                if ($start > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }
            }

            for ($p = $start; $p <= $end; $p++) {
                $active = ($p === $page) ? 'active' : '';
                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . h(build_products_url(['page' => $p])) . '">' . $p . '</a></li>';
            }

            if ($end < $totalPages) {
                if ($end < $totalPages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="' . h(build_products_url(['page' => $totalPages])) . '">' . $totalPages . '</a></li>';
            }
            ?>

            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo build_products_url(['page' => min($totalPages, $page + 1)]); ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
