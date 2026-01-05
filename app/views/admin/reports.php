<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Reports</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Sales Report</h5>
            </div>
            <div class="card-body">
                <p class="card-text">View sales statistics and trends over time.</p>
                <a href="<?php echo site_url('report/sales'); ?>" class="btn btn-primary">View Sales</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Revenue Report</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Track revenue and financial performance.</p>
                <a href="<?php echo site_url('report/revenue'); ?>" class="btn btn-success">View Revenue</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">User Report</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Analyze user registration and activity.</p>
                <a href="<?php echo site_url('report/users'); ?>" class="btn btn-info">View Users</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Affiliate Report</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Track affiliate performance and commissions.</p>
                <a href="<?php echo site_url('report/affiliates'); ?>" class="btn btn-warning">View Affiliates</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">API Logs</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Monitor API usage and errors.</p>
                <a href="<?php echo site_url('api/logs'); ?>" class="btn btn-secondary">View Logs</a>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
