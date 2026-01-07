<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
    $instance = $data['instance'] ?? null;
    if (!$instance) {
        echo '<div class="alert alert-danger">Instance not found.</div>';
        require BASE_PATH . '/app/views/layouts/footer.php';
        exit;
    }
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0">Instance Settings</h1>
            <small class="text-muted">Instance: <strong><?php echo h($instance->name); ?></strong></small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo site_url('api/services/' . (int)$instance->id); ?>" class="btn btn-secondary">
                Manage Services
            </a>
            <a href="<?php echo site_url('api'); ?>" class="btn btn-outline-secondary">
                ← Back to Instances
            </a>
        </div>
    </div>

    <?php flash('success'); ?>
    <?php flash('error', '', 'alert alert-danger'); ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- DEFAULT MARKUP CARD -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Default Markup Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Markup Format:</strong> Enter markup as a fractional value (e.g., 0.50 = 50%, 1.00 = 100%).
                        This markup will be applied to <strong>api_rate</strong> to calculate the <strong>final_price</strong>.
                    </div>

                    <form method="post" action="<?php echo site_url('api/instanceSettings/' . (int)$instance->id); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="update_default">

                        <div class="mb-3">
                            <label for="default_markup" class="form-label">
                                Default Markup <span class="text-muted">(fractional value)</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="1000"
                                class="form-control"
                                id="default_markup"
                                name="default_markup"
                                value="<?php echo number_format((float)($instance->default_markup ?? 0.00), 2, '.', ''); ?>"
                                required
                            >
                            <div class="form-text">
                                Example: 0.50 = 50% markup, 1.00 = 100% markup, 2.00 = 200% markup
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Calculation Preview:</h6>
                                    <p class="mb-0">
                                        If <strong>api_rate = 1.00</strong> and <strong>markup = <span id="markupPreview">0.50</span></strong>, then:
                                    </p>
                                    <p class="mb-0">
                                        <strong>final_price = 1.00 × (1 + <span id="markupPreview2">0.50</span>) = <span id="finalPricePreview">1.50</span></strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Default Markup
                        </button>
                    </form>
                </div>
            </div>

            <!-- BULK ACTIONS CARD -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Bulk Markup Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Apply Default Markup to All Services -->
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-arrow-repeat text-primary me-2"></i>Apply Default Markup
                                    </h6>
                                    <p class="card-text small">
                                        Apply the current default markup (<strong><?php echo number_format((float)($instance->default_markup ?? 0.00), 2); ?></strong>) to ALL services in this instance.
                                    </p>
                                    <form method="post" action="<?php echo site_url('api/instanceSettings/' . (int)$instance->id); ?>" onsubmit="return confirm('Apply default markup to ALL services? This will recalculate all final prices.');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="apply_to_all">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="bi bi-check-circle me-2"></i>Apply to All Services
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Clear All Markups -->
                        <div class="col-md-6">
                            <div class="card border-warning h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-eraser text-warning me-2"></i>Clear Markups
                                    </h6>
                                    <p class="card-text small">
                                        Set markup to <strong>0.00</strong> for ALL services. Final prices will equal api_rate.
                                    </p>
                                    <form method="post" action="<?php echo site_url('api/instanceSettings/' . (int)$instance->id); ?>" onsubmit="return confirm('Clear markup for ALL services? Final prices will be set to api_rate.');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="clear_markup">
                                        <button type="submit" class="btn btn-warning btn-sm w-100">
                                            <i class="bi bi-x-circle me-2"></i>Clear All Markups
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Bulk actions affect ALL services in this instance. Use with caution.
                    </div>
                </div>
            </div>
        </div>

        <!-- SIDEBAR INFO -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Instance Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th class="text-muted" style="width: 40%;">Name:</th>
                            <td><?php echo h($instance->name); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Provider:</th>
                            <td><?php echo h($instance->provider_name ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Base URL:</th>
                            <td class="text-break"><?php echo h($instance->base_url); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status:</th>
                            <td>
                                <?php if ($instance->status === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Default Markup:</th>
                            <td><strong><?php echo number_format((float)($instance->default_markup ?? 0.00), 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Quick Help</h6>
                </div>
                <div class="card-body">
                    <h6 class="small text-muted mb-2">Markup System:</h6>
                    <ul class="small mb-0">
                        <li>Markup is stored as a <strong>fractional value</strong></li>
                        <li><strong>0.00</strong> = No markup (price = api_rate)</li>
                        <li><strong>0.50</strong> = 50% markup</li>
                        <li><strong>1.00</strong> = 100% markup (doubles the price)</li>
                        <li>Formula: <code>final_price = api_rate × (1 + markup)</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Live preview of markup calculation
document.getElementById('default_markup')?.addEventListener('input', function() {
    const markup = parseFloat(this.value) || 0;
    const apiRate = 1.00;
    const finalPrice = apiRate * (1 + markup);
    
    document.getElementById('markupPreview').textContent = markup.toFixed(2);
    document.getElementById('markupPreview2').textContent = markup.toFixed(2);
    document.getElementById('finalPricePreview').textContent = finalPrice.toFixed(2);
});
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
