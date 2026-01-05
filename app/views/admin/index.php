<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Admin Dashboard</h1>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Users</h5>
                <h2 class="display-6"><?php echo $data['total_users']; ?></h2>
                <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-light btn-sm mt-2">Manage Users</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Revenue</h5>
                <?php echo number_format($data['total_revenue'] ?? 0, 2); ?>
                <a href="<?php echo site_url('order'); ?>" class="btn btn-light btn-sm mt-2">
    View Orders
</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Orders</h5>
                <h2 class="display-6"><?php echo $data['total_orders']; ?></h2>
                <a href="<?php echo site_url('admin/orders'); ?>" class="btn btn-light btn-sm mt-2">Manage Orders</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Products</h5>
                <h2 class="display-6"><?php echo $data['total_products']; ?></h2>
                <a href="<?php echo site_url('admin/products'); ?>" class="btn btn-light btn-sm mt-2">Manage Products</a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($data['recent_orders'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['recent_orders'] as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order->id; ?></td>
                                        <td><?php echo h($order->username); ?></td>
                                        <td>$<?php echo number_format($order->total_amount, 2); ?></td>
                                        <td><span class="badge bg-<?php echo ($order->status == 'completed') ? 'success' : 'warning'; ?>"><?php echo ucfirst($order->status); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center mb-0">No recent orders.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Users</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($data['recent_users'])): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($data['recent_users'] as $user): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo h($user->username); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo h($user->email); ?></small>
                                </div>
                                <span class="badge bg-primary"><?php echo ucfirst($user->role); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center mb-0">No recent users.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
