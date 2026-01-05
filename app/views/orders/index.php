<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">My Orders</h1>

<?php if (isset($data['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $data['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($data['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $data['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (!empty($data['orders'])): ?>
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
                        <?php foreach ($data['orders'] as $order): ?>
                            <tr>
                                <td>#<?php echo $order->id; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order->created_at)); ?></td>
                                <td>$<?php echo number_format($order->total_amount, 2); ?></td>
                                <td><span class="badge bg-<?php 
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        'refunded' => 'secondary'
                                    ];
                                    echo $statusColors[$order->status] ?? 'secondary';
                                ?>"><?php echo ucfirst($order->status); ?></span></td>
                                <td>
                                    <a href="<?php echo site_url('order/details/' . $order->id); ?>" class="btn btn-sm btn-outline-primary">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-basket display-1 text-muted mb-3"></i>
                <h4>No orders yet</h4>
                <p class="text-muted">Start shopping to see your orders here.</p>
                <a href="<?php echo site_url('product'); ?>" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
