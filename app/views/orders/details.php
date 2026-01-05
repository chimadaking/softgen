<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Order Details #<?php echo $data['order']->id; ?></h1>

<a href="<?php echo site_url('order'); ?>" class="btn btn-secondary mb-3">&larr; Back to Orders</a>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['items'])): ?>
                                <?php foreach ($data['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo h($item->name); ?></td>
                                        <td><?php echo $item->quantity; ?></td>
                                        <td>$<?php echo number_format($item->price, 2); ?></td>
                                        <td>$<?php echo number_format($item->quantity * $item->price, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No items found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($data['order']->total_amount, 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Status:</strong>
                    <span class="badge bg-<?php 
                        $statusColors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'refunded' => 'secondary'
                        ];
                        echo $statusColors[$data['order']->status] ?? 'secondary';
                    ?>"><?php echo ucfirst($data['order']->status); ?></span>
                </div>
                <div class="mb-3">
                    <strong>Order Date:</strong><br>
                    <?php echo date('M d, Y H:i:s', strtotime($data['order']->created_at)); ?>
                </div>
                <?php if ($data['order']->api_order_id): ?>
                <div class="mb-3">
                    <strong>API Order ID:</strong><br>
                    <?php echo h($data['order']->api_order_id); ?>
                </div>
                <?php endif; ?>
                <?php if ($data['order']->notes): ?>
                <div class="mb-3">
                    <strong>Notes:</strong><br>
                    <?php echo nl2br(h($data['order']->notes)); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($data['order']->status == 'pending' || $data['order']->status == 'processing'): ?>
                <hr>
                <form action="<?php echo BASE_DIR; ?>/order/cancel/<?php echo $data['order']->id; ?>" method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <button type="submit" class="btn btn-danger w-100">Cancel Order</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
