<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Loyalty Program Management</h2>
            
            <?php echo display_flash(); ?>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h6 class="text-muted">Total Members</h6>
                            <h3><?php echo $total_users; ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Avg Points per User</h6>
                            <h3><?php echo number_format($total_users > 0 ? (array_sum(array_column($users, 'points')) / $total_users) : 0, 2); ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Total Points Issued</h6>
                            <h3><?php echo number_format(array_sum(array_column($users, 'points')), 0); ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Highest Balance</h6>
                            <h3><?php echo count($users) > 0 ? number_format(max(array_column($users, 'points')), 0) : 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Users</h5>
                    <div>
                        <a href="<?php echo site_url('admin/loyalty/settings'); ?>" class="btn btn-sm btn-primary">Settings</a>
                        <button class="btn btn-sm btn-outline-success" onclick="window.print()">Export</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Points</th>
                                    <th>Tier</th>
                                    <th>Total Spent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($user->username); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($user->email); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo number_format($user->points, 2); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $badgeColor = 'primary';
                                                if ($user->tier_id == 2) $badgeColor = 'secondary';
                                                elseif ($user->tier_id == 3) $badgeColor = 'warning';
                                                elseif ($user->tier_id == 4) $badgeColor = 'info';
                                                
                                                $tierName = 'Bronze';
                                                if ($user->tier_id == 2) $tierName = 'Silver';
                                                elseif ($user->tier_id == 3) $tierName = 'Gold';
                                                elseif ($user->tier_id == 4) $tierName = 'Platinum';
                                                ?>
                                                <span class="badge bg-<?php echo $badgeColor; ?>"><?php echo $tierName; ?></span>
                                            </td>
                                            <td>$<?php echo number_format($user->total_spent, 2); ?></td>
                                            <td>
                                                <a href="<?php echo site_url('admin/loyalty/' . $user->user_id); ?>" class="btn btn-sm btn-info">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No loyalty members yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo site_url('admin/loyalty?page=' . ($page - 1)); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo site_url('admin/loyalty?page=' . $i); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo site_url('admin/loyalty?page=' . ($page + 1)); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
