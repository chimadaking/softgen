<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin'); ?>">Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/loyalty'); ?>">Loyalty</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($user->username); ?></li>
                </ol>
            </nav>
            
            <?php echo display_flash(); ?>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Current Points</h6>
                            <h2><?php echo number_format($user->points, 2); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Earned</h6>
                            <h2><?php echo number_format($total_earned, 2); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Redeemed</h6>
                            <h2><?php echo number_format($total_redeemed, 2); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">User Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Username:</strong> <?php echo htmlspecialchars($user->username); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Email:</strong> <?php echo htmlspecialchars($user->email); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Tier:</strong>
                            <?php 
                            $badgeColor = 'primary';
                            if ($user->tier_id == 2) $badgeColor = 'secondary';
                            elseif ($user->tier_id == 3) $badgeColor = 'warning';
                            elseif ($user->tier_id == 4) $badgeColor = 'info';
                            ?>
                            <span class="badge bg-<?php echo $badgeColor; ?>">
                                <?php echo htmlspecialchars($user->tier_name); ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Spent:</strong> $<?php echo number_format($user->total_spent, 2); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Manual Point Adjustments -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Manual Point Adjustments</h5>
                    <form method="POST" action="<?php echo site_url('admin/loyalty/' . $user->user_id); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="action" class="form-label">Action</label>
                                <select name="action" id="action" class="form-select" required>
                                    <option value="add">Add Points</option>
                                    <option value="subtract">Deduct Points</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="points" class="form-label">Points Amount</label>
                                <input 
                                    type="number" 
                                    name="points" 
                                    id="points" 
                                    class="form-control" 
                                    min="0.01" 
                                    step="0.01"
                                    required
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="reason" class="form-label">Reason</label>
                                <input 
                                    type="text" 
                                    name="reason" 
                                    id="reason" 
                                    class="form-control" 
                                    placeholder="e.g., Bonus for promotion"
                                    required
                                >
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit Adjustment</button>
                            <a href="<?php echo site_url('admin/loyalty'); ?>" class="btn btn-outline-secondary">Back to List</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Transaction History -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Transaction History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Points</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($history)): ?>
                                    <?php foreach ($history as $item): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y g:i A', strtotime($item->created_at)); ?></td>
                                            <td>
                                                <?php if ($item->type == 'earned'): ?>
                                                    <span class="badge bg-success">Earned</span>
                                                <?php elseif ($item->type == 'redeemed'): ?>
                                                    <span class="badge bg-danger">Redeemed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo ucfirst($item->type); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item->type == 'earned'): ?>
                                                    <span class="text-success">+<?php echo number_format($item->points, 2); ?></span>
                                                <?php elseif ($item->type == 'redeemed'): ?>
                                                    <span class="text-danger">-<?php echo number_format($item->points, 2); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo number_format($item->points, 2); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item->description); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No transaction history</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
