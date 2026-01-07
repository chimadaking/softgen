<?php $this->view('layouts/header', ['title' => $title]); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Redeem Points</h2>
            
            <?php echo display_flash(); ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Redemption Rate:</strong> 100 points = $1.00 wallet credit
                    </div>
                    
                    <div class="text-center mb-4">
                        <p class="mb-2">Your Available Points</p>
                        <h1 class="display-4 text-success"><?php echo number_format($points, 2); ?></h1>
                        <p class="text-muted">= $<?php echo number_format($points * $redemption_rate, 2); ?> wallet credit</p>
                    </div>
                    
                    <form method="POST" action="<?php echo site_url('loyalty/redeem'); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="points" class="form-label">Points to Redeem</label>
                            <input 
                                type="number" 
                                name="points" 
                                id="points" 
                                class="form-control" 
                                min="100" 
                                max="<?php echo $points; ?>"
                                step="1"
                                required
                            >
                            <small class="form-text text-muted">
                                Minimum 100 points (equivalent to $1.00)
                            </small>
                        </div>
                        
                        <div id="credit-preview" class="mb-3 p-3 bg-light rounded">
                            <strong>You will receive:</strong> $<span id="credit-amount">0.00</span> wallet credit
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Redeem Points</button>
                            <a href="<?php echo site_url('loyalty'); ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pointsInput = document.getElementById('points');
    const creditAmount = document.getElementById('credit-amount');
    const redemptionRate = <?php echo $redemption_rate; ?>;
    
    pointsInput.addEventListener('input', function() {
        const points = parseFloat(this.value) || 0;
        const credit = points * redemptionRate;
        creditAmount.textContent = credit.toFixed(2);
    });
});
</script>

<?php $this->view('layouts/footer'); ?>
