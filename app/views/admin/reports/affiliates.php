<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Affiliate Reports</h1>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Affiliates</h5>
                <h2 class="display-6"><?php echo count($data['affiliates'] ?? []); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Active</h5>
                <h2 class="display-6"><?php echo count(array_filter($data['affiliates'] ?? [], function($a) { return $a->status == 'active'; })); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Referrals</h5>
                <h2 class="display-6"><?php echo array_sum(array_column($data['affiliates'] ?? [], 'total_referrals')); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Paid</h5>
                <h2 class="display-6">$<?php echo number_format(array_sum(array_column($data['affiliates'] ?? [], 'total_earnings')), 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Affiliate Performance</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['affiliates'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Referral Code</th>
                            <th>Commission Rate</th>
                            <th>Total Referrals</th>
                            <th>Total Earnings</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['affiliates'] as $affiliate): ?>
                            <tr>
                                <td><?php echo $affiliate->id; ?></td>
                                <td><?php echo h($affiliate->username ?? 'Unknown'); ?></td>
                                <td><code><?php echo h($affiliate->referral_code); ?></code></td>
                                <td><?php echo $affiliate->commission_rate; ?>%</td>
                                <td><?php echo $affiliate->total_referrals ?? 0; ?></td>
                                <td>$<?php echo number_format($affiliate->total_earnings ?? 0, 2); ?></td>
                                <td><span class="badge bg-<?php echo ($affiliate->status == 'active') ? 'success' : 'secondary'; ?>"><?php echo ucfirst($affiliate->status); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mb-0">No affiliates found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
