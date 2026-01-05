<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Manage Orders</h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (!empty($data['orders'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['orders'] as $order): ?>
                            <tr>
                                <td>#<?php echo $order->id; ?></td>
                                <td><?php echo h($order->username); ?></td>
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
                                    <a href="<?php echo site_url('order/details/' . $order->id); ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
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
