<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">My Wallet</h1>

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

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Available Balance</h5>
                <h1 class="display-4">$<?php echo number_format($data['balance'], 2); ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <a href="<?php echo site_url('wallet/fund'); ?>" class="btn btn-success w-100 mb-2">
    <i class="bi bi-plus-circle"></i> Fund Wallet
</a>

<a href="<?php echo site_url('wallet/withdraw'); ?>" class="btn btn-outline-primary w-100">
    <i class="bi bi-dash-circle"></i> Withdraw Funds
</a>

            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Transaction History</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['transactions'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['transactions'] as $transaction): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($transaction->created_at)); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($transaction->type == 'deposit' || $transaction->type == 'refund' || $transaction->type == 'commission') ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($transaction->type); ?>
                                    </span>
                                </td>
                                <td><?php echo h($transaction->description); ?></td>
                                <td class="<?php echo ($transaction->type == 'deposit' || $transaction->type == 'refund' || $transaction->type == 'commission') ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($transaction->type == 'deposit' || $transaction->type == 'refund' || $transaction->type == 'commission') ? '+' : '-'; ?>$<?php echo number_format($transaction->amount, 2); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        $statusColors = [
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'cancelled' => 'danger',
                                            'failed' => 'secondary'
                                        ];
                                        echo $statusColors[$transaction->status] ?? 'secondary';
                                    ?>"><?php echo ucfirst($transaction->status); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-wallet2 display-1 text-muted mb-3"></i>
                <h4>No transactions yet</h4>
                <p class="text-muted">Fund your wallet to start making purchases.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
