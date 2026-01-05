<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Sales Reports</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Sales</h5>
                <h2 class="display-6">$<?php echo number_format($data['total_sales'] ?? 0, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Orders</h5>
                <h2 class="display-6"><?php echo count($data['recent_orders'] ?? []); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Avg Order Value</h5>
                <h2 class="display-6">$<?php echo number_format(($data['total_sales'] ?? 0) / max(1, count($data['recent_orders'] ?? [])), 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent Sales</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['recent_orders'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recent_orders'] as $order): ?>
                            <tr>
                                <td>#<?php echo $order->id; ?></td>
                                <td><?php echo h($order->username ?? 'Unknown'); ?></td>
                                <td>$<?php echo number_format($order->total_amount, 2); ?></td>
                                <td><span class="badge bg-<?php echo ($order->status == 'completed') ? 'success' : 'warning'; ?>"><?php echo ucfirst($order->status); ?></span></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mb-0">No sales data available.</p>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
