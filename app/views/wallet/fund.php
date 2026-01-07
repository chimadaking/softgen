<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Fund Wallet</h1>

<a href="<?php echo site_url('wallet'); ?>" class="btn btn-secondary mb-3">
    &larr; Back to Wallet
</a>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Fund Your Wallet</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Enter the amount you want to add to your wallet. Funding requests are reviewed and approved manually.</p>
                
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

                <form action="<?php echo BASE_DIR; ?>/wallet/fund" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-4">
                        <label class="form-label mb-3">Quick Amounts</label>
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <button type="button" class="btn btn-outline-primary quick-amount" data-amount="10">$10</button>
                            <button type="button" class="btn btn-outline-primary quick-amount" data-amount="25">$25</button>
                            <button type="button" class="btn btn-outline-primary quick-amount" data-amount="50">$50</button>
                            <button type="button" class="btn btn-outline-primary quick-amount" data-amount="100">$100</button>
                            <button type="button" class="btn btn-outline-primary quick-amount" data-amount="250">$250</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount ($)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" id="amount" class="form-control <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo h($data['amount'] ?? ''); ?>" min="1" step="0.01" required>
                        </div>
                        <span class="invalid-feedback"><?php echo $data['amount_err'] ?? ''; ?></span>
                    </div>

                    <div class="mb-3">
                        <label for="method" class="form-label">Payment Method</label>
                        <select name="method" id="method" class="form-select">
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="crypto">Cryptocurrency</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-wallet2"></i> Fund Wallet
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
                <h5 class="mb-0">Important Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Manual approval required</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Secure transactions once approved</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> No hidden fees</li>
                    <li class="mb-2"><i class="bi bi-info-circle text-primary me-2"></i> Minimum deposit: $1.00</li>
                    <li><i class="bi bi-info-circle text-primary me-2"></i> Maximum deposit: $10,000.00</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.quick-amount').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('amount').value = this.dataset.amount;
    });
});
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
