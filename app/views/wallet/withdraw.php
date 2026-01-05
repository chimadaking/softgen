<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Withdraw Funds</h1>

<a href="<?php echo site_url('wallet'); ?>" class="btn btn-secondary mb-3">
    &larr; Back to Wallet
</a>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Withdraw Funds</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Enter the amount you want to withdraw from your wallet.</p>
                
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

                <form action="<?php echo BASE_DIR; ?>/wallet/withdraw" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount ($)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" id="amount" class="form-control <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo h($data['amount'] ?? ''); ?>" min="10" step="0.01" required>
                        </div>
                        <span class="invalid-feedback"><?php echo $data['amount_err'] ?? ''; ?></span>
                        <small class="form-text text-muted">Available: $<?php echo number_format($data['balance'], 2); ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="method" class="form-label">Withdrawal Method</label>
                        <select name="method" id="method" class="form-select">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="paypal">PayPal</option>
                            <option value="crypto">Cryptocurrency</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100">
                        <i class="bi bi-cash-coin"></i> Withdraw Funds
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Current Balance</h5>
            </div>
            <div class="card-body text-center py-5">
                <h1 class="display-4 text-primary">$<?php echo number_format($data['balance'], 2); ?></h1>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Withdrawal Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-clock text-warning me-2"></i> Processing time: 1-3 business days</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Secure transactions</li>
                    <li class="mb-2"><i class="bi bi-info-circle text-primary me-2"></i> Minimum withdrawal: $10.00</li>
                    <li><i class="bi bi-exclamation-circle text-danger me-2"></i> Bank transfer fees may apply</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
