<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Affiliate Program</h1>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Your Referral Link</h5>
                <div class="input-group mt-3">
                    <input type="text" class="form-control" value="<?php echo BASE_DIR; ?>/ref/<?php echo $data['referral_code']; ?>" readonly>
                    <button class="btn btn-light" onclick="copyReferralLink()">Copy</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Earnings</h5>
                <h2 class="display-6">$<?php echo number_format($data['total_earnings'] ?? 0, 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Referrals</h5>
                <h3><?php echo $data['total_referrals']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Commission Rate</h5>
                <h3><?php echo $data['commission_rate']; ?>%</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Pending Commissions</h5>
                <h3>$<?php echo number_format($data['pending_commissions'] ?? 0, 2); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent Referrals</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['referrals'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['referrals'] as $referral): ?>
                            <tr>
                                <td><?php echo h($referral->username); ?></td>
                                <td><?php echo date('M d, Y', strtotime($referral->referred_at)); ?></td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mb-0">No referrals yet. Start sharing your link!</p>
        <?php endif; ?>
    </div>
</div>

<script>
function copyReferralLink() {
    const input = document.querySelector('input[readonly]');
    input.select();
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
