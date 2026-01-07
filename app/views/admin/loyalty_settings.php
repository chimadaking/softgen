<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin'); ?>">Admin</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/loyalty'); ?>">Loyalty</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </nav>
            
            <?php echo display_flash(); ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo site_url('admin/loyalty/settings'); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <h6 class="mb-3">Point Earning Rates</h6>
                        
                        <div class="mb-3">
                            <label for="points_per_dollar_purchase" class="form-label">Points per $1 spent on orders</label>
                            <input 
                                type="number" 
                                name="points_per_dollar_purchase"
                                id="points_per_dollar_purchase"
                                class="form-control"
                                step="0.1"
                                min="0"
                                value="<?php echo $settings['points_per_dollar_purchase'] ?? 1.0; ?>"
                                required
                            >
                            <small class="form-text text-muted">
                                Example: 1.0 means 1 point per $1 spent
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="points_per_dollar_funding" class="form-label">Points per $1 funded to wallet</label>
                            <input 
                                type="number" 
                                name="points_per_dollar_funding"
                                id="points_per_dollar_funding"
                                class="form-control"
                                step="0.1"
                                min="0"
                                value="<?php echo $settings['points_per_dollar_funding'] ?? 0.5; ?>"
                                required
                            >
                            <small class="form-text text-muted">
                                Example: 0.5 means 0.5 points per $1 funded
                            </small>
                        </div>
                        
                        <hr>
                        <h6 class="mb-3">Loyalty Tiers (points threshold)</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="loyalty_tier_bronze" class="form-label">Bronze Tier (minimum points)</label>
                                <input 
                                    type="number" 
                                    name="loyalty_tier_bronze"
                                    id="loyalty_tier_bronze"
                                    class="form-control"
                                    value="<?php echo $settings['loyalty_tier_bronze'] ?? 0; ?>"
                                    required
                                >
                                <small class="form-text text-muted">
                                    Default: 0 points
                                </small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="loyalty_tier_silver" class="form-label">Silver Tier (minimum points)</label>
                                <input 
                                    type="number" 
                                    name="loyalty_tier_silver"
                                    id="loyalty_tier_silver"
                                    class="form-control"
                                    value="<?php echo $settings['loyalty_tier_silver'] ?? 1000; ?>"
                                    required
                                >
                                <small class="form-text text-muted">
                                    Default: 1,000 points (+2% bonus)
                                </small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="loyalty_tier_gold" class="form-label">Gold Tier (minimum points)</label>
                                <input 
                                    type="number" 
                                    name="loyalty_tier_gold"
                                    id="loyalty_tier_gold"
                                    class="form-control"
                                    value="<?php echo $settings['loyalty_tier_gold'] ?? 5000; ?>"
                                    required
                                >
                                <small class="form-text text-muted">
                                    Default: 5,000 points (+5% bonus + priority support)
                                </small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="loyalty_tier_platinum" class="form-label">Platinum Tier (minimum points)</label>
                                <input 
                                    type="number" 
                                    name="loyalty_tier_platinum"
                                    id="loyalty_tier_platinum"
                                    class="form-control"
                                    value="<?php echo $settings['loyalty_tier_platinum'] ?? 10000; ?>"
                                    required
                                >
                                <small class="form-text text-muted">
                                    Default: 10,000 points (+10% bonus + VIP support)
                                </small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Tier Benefits:</strong>
                            <ul class="mb-0">
                                <li><strong>Bronze (0+ pts):</strong> Base earning rate</li>
                                <li><strong>Silver (1,000+ pts):</strong> +2% bonus points on purchases</li>
                                <li><strong>Gold (5,000+ pts):</strong> +5% bonus points + priority support</li>
                                <li><strong>Platinum (10,000+ pts):</strong> +10% bonus points + VIP support + exclusive offers</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Save Settings</button>
                            <a href="<?php echo site_url('admin/loyalty'); ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">How It Works</h5>
                    <p class="text-muted">Users earn points by making purchases and funding their wallet. Points can be redeemed for discounts or stored to reach higher loyalty tiers.</p>
                    
                    <h6 class="mt-3">Earning Rates</h6>
                    <ul>
                        <li><strong>Orders:</strong> <?php echo $settings['points_per_dollar_purchase'] ?? 1.0; ?> points per $1</li>
                        <li><strong>Wallet Funding:</strong> <?php echo $settings['points_per_dollar_funding'] ?? 0.5; ?> points per $1</li>
                    </ul>
                    
                    <h6 class="mt-3">Redemption</h6>
                    <p>100 points = $1.00 wallet credit</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Current Tier Structure</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Points</th>
                                <th>Bonus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">Bronze</span></td>
                                <td><?php echo number_format($settings['loyalty_tier_bronze'] ?? 0); ?>+</td>
                                <td>0%</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-secondary">Silver</span></td>
                                <td><?php echo number_format($settings['loyalty_tier_silver'] ?? 1000); ?>+</td>
                                <td>+2%</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">Gold</span></td>
                                <td><?php echo number_format($settings['loyalty_tier_gold'] ?? 5000); ?>+</td>
                                <td>+5%</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Platinum</span></td>
                                <td><?php echo number_format($settings['loyalty_tier_platinum'] ?? 10000); ?>+</td>
                                <td>+10%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
