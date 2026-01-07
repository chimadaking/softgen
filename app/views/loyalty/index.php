<?php $this->view('layouts/header', ['title' => $title]); ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">My Loyalty Points</h2>
            
            <?php echo display_flash(); ?>
            
            <!-- Current Status Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Your Loyalty Status</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Total Points:</strong> <span class="h4 text-success"><?php echo number_format($points, 2); ?></span>
                            </p>
                            <p class="text-muted small">
                                <?php if ($tier['points_until_next'] > 0): ?>
                                    You're <?php echo number_format($tier['points_until_next'], 0); ?> points away from <?php echo $tier['next_tier']; ?> tier
                                <?php else: ?>
                                    You've reached the highest tier!
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Current Tier:</strong> 
                                <?php 
                                $badgeColor = 'primary';
                                if ($tier['name'] == 'Silver') $badgeColor = 'secondary';
                                elseif ($tier['name'] == 'Gold') $badgeColor = 'warning';
                                elseif ($tier['name'] == 'Platinum') $badgeColor = 'info';
                                ?>
                                <span class="badge bg-<?php echo $badgeColor; ?> fs-6"><?php echo $tier['name']; ?></span>
                            </p>
                            <p class="text-muted small">
                                <?php echo $tier['discount_percent']; ?>% bonus points on purchases
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted">Total Earned</h6>
                                <h4 class="text-success"><?php echo number_format($total_earned, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted">Total Redeemed</h6>
                                <h4 class="text-danger"><?php echo number_format($total_redeemed, 2); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted">Current Balance</h6>
                                <h4 class="text-primary"><?php echo number_format($points, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="<?php echo site_url('loyalty/redeem'); ?>" class="btn btn-primary">Redeem Points</a>
                    </div>
                </div>
            </div>
            
            <!-- Loyalty Tiers Progress Bar -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6>Progress to Next Tier</h6>
                    <?php if ($tier['points_until_next'] > 0): ?>
                        <?php 
                        $currentPoints = $points;
                        $nextTierPoints = $tier['next_points'];
                        $percentage = ($currentPoints / $nextTierPoints) * 100;
                        ?>
                        <div class="progress mb-2" style="height: 30px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%;">
                                <?php echo number_format($points, 0); ?> / <?php echo number_format($nextTierPoints, 0); ?> points
                            </div>
                        </div>
                        <small class="text-muted">You're <?php echo number_format($percentage, 1); ?>% of the way to <?php echo $tier['next_tier']; ?></small>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <strong>Congratulations!</strong> You've reached the highest loyalty tier!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Loyalty History Table -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6>Loyalty Points History</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Points</th>
                                    <th>Balance After</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($history)): ?>
                                    <?php foreach ($history as $index => $item): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y g:i A', strtotime($item->created_at)); ?></td>
                                            <td><?php echo htmlspecialchars($item->description); ?></td>
                                            <td>
                                                <?php if ($item->type == 'earned'): ?>
                                                    <span class="text-success">+<?php echo number_format($item->points, 2); ?></span>
                                                <?php elseif ($item->type == 'redeemed'): ?>
                                                    <span class="text-danger">-<?php echo number_format($item->points, 2); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo number_format($item->points, 2); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                // Calculate balance after this transaction
                                                $balance = $points;
                                                for ($i = 0; $i <= $index; $i++) {
                                                    if ($history[$i]->type == 'earned') {
                                                        $balance -= $history[$i]->points;
                                                    } else {
                                                        $balance += $history[$i]->points;
                                                    }
                                                }
                                                echo number_format($balance, 2);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No history yet. Start earning points by making purchases!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Tier Benefits -->
            <div class="card">
                <div class="card-body">
                    <h6>Loyalty Tiers</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Points Required</th>
                                <th>Benefits</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_tiers as $t): ?>
                                <tr class="<?php echo ($t->name == $tier['name']) ? 'table-primary' : ''; ?>">
                                    <td>
                                        <?php 
                                        $tBadgeColor = 'primary';
                                        if ($t->name == 'Silver') $tBadgeColor = 'secondary';
                                        elseif ($t->name == 'Gold') $tBadgeColor = 'warning';
                                        elseif ($t->name == 'Platinum') $tBadgeColor = 'info';
                                        ?>
                                        <span class="badge bg-<?php echo $tBadgeColor; ?>"><?php echo $t->name; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($t->name == 'Bronze') echo "0 - 999";
                                        elseif ($t->name == 'Silver') echo "1,000 - 4,999";
                                        elseif ($t->name == 'Gold') echo "5,000 - 9,999";
                                        elseif ($t->name == 'Platinum') echo "10,000+";
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($t->name == 'Bronze') echo "Base earning rate";
                                        elseif ($t->name == 'Silver') echo "+2% bonus points on purchases";
                                        elseif ($t->name == 'Gold') echo "+5% bonus points + priority support";
                                        elseif ($t->name == 'Platinum') echo "+10% bonus points + VIP support";
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($t->name == $tier['name']): ?>
                                            <span class="badge bg-success">Current</span>
                                        <?php else: ?>
                                            <span class="text-muted">Locked</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->view('layouts/footer'); ?>
