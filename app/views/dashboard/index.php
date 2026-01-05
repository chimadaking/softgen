<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Dashboard</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Wallet Balance</h5>
                <h2 class="display-6">$<?php echo number_format($data['balance'], 2); ?></h2>
                <a href="<?php echo site_url('wallet/fund'); ?>" class="btn btn-light btn-sm mt-2">Fund Wallet</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Loyalty Points</h5>
                <h2 class="display-6"><?php echo $data['loyalty_points']; ?></h2>
                <p class="mb-0">Keep it up!</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Orders</h5>
                <h2 class="display-6"><?php echo count($data['recent_orders']); ?></h2>
                <a href="<?php echo site_url('order'); ?>" class="btn btn-light btn-sm mt-2">View Orders</a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent Orders</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['recent_orders'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recent_orders'] as $order): ?>
                            <tr>
                                <td>#<?php echo $order->id; ?></td>
                                <td><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                                <td>$<?php echo number_format($order->total_amount, 2); ?></td>
                                <td><span class="badge bg-<?php echo ($order->status == 'completed') ? 'success' : 'warning'; ?>"><?php echo ucfirst($order->status); ?></span></td>
                                <td><a href="<?php echo site_url('order/details/' . $order->id); ?>" class="btn btn-sm btn-outline-primary">Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mb-0">No orders found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
