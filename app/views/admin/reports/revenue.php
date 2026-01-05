<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Revenue Reports</h1>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Revenue</h5>
                <h2 class="display-4">$<?php echo number_format($data['total_revenue'] ?? 0, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Net Profit</h5>
                <h2 class="display-4">$<?php echo number_format(($data['total_revenue'] ?? 0) * 0.8, 2); ?></h2>
                <p class="mb-0">Estimated (80% of revenue)</p>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <h5>Revenue Breakdown</h5>
    <p>Detailed revenue analytics will be displayed here with charts and graphs.</p>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
