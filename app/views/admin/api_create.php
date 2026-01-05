<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add New API Instance</h4>
                </div>
                <div class="card-body">
                    <?php flash('error', '', 'alert alert-danger'); ?>

                    <form method="POST" action="<?php echo site_url('api/create'); ?>" id="apiForm">
                        <?php echo csrf_field(); ?>

                        <!-- Provider Selection -->
                        <div class="mb-3">
                            <label for="provider_id" class="form-label">Select Provider <sup>*</sup></label>
                            <select name="provider_id" id="provider_id" class="form-control" required>
                                <option value="">-- Choose a Provider --</option>
                                <?php if (!empty($data['providers'])): ?>
                                    <?php foreach ($data['providers'] as $provider): ?>
                                        <option value="<?php echo $provider->id; ?>"
                                                data-base-url="<?php echo h($provider->base_url_template); ?>"
                                                <?php echo ($data['provider_id'] ?? '') == $provider->id ? 'selected' : ''; ?>>
                                            <?php echo h($provider->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">
                                Need to add a new provider? <a href="<?php echo site_url('admin/providers/create'); ?>" target="_blank">Create one here</a>
                            </small>
                            <?php if (!empty($data['provider_id_err'])): ?>
                                <div class="text-danger small"><?php echo $data['provider_id_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Instance Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Instance Name <sup>*</sup></label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="<?php echo h($data['name'] ?? ''); ?>"
                                   placeholder="e.g., My SMS-Man Account" required>
                            <small class="form-text text-muted">A friendly name to identify this API instance</small>
                            <?php if (!empty($data['name_err'])): ?>
                                <div class="text-danger small"><?php echo $data['name_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Base URL -->
                        <div class="mb-3">
                            <label for="base_url" class="form-label">API Base URL <sup>*</sup></label>
                            <input type="url" name="base_url" id="base_url" class="form-control"
                                   value="<?php echo h($data['base_url'] ?? ''); ?>"
                                   placeholder="https://api.provider.com" required>
                            <small class="form-text text-muted">The complete API base URL</small>
                            <?php if (!empty($data['base_url_err'])): ?>
                                <div class="text-danger small"><?php echo $data['base_url_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- API Key -->
                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key <sup>*</sup></label>
                            <input type="text" name="api_key" id="api_key" class="form-control"
                                   value="<?php echo h($data['api_key'] ?? ''); ?>"
                                   placeholder="Enter your API key" required>
                            <small class="form-text text-muted">Provider authentication key/token</small>
                            <?php if (!empty($data['api_key_err'])): ?>
                                <div class="text-danger small"><?php echo $data['api_key_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Default Country (Admin controls pricing base) -->
                        <div class="mb-3">
                            <label for="default_country" class="form-label">Default Country (optional)</label>
                            <input type="text" name="default_country" id="default_country" class="form-control"
                                   value="<?php echo h($data['default_country'] ?? ''); ?>"
                                   placeholder="e.g., 0 or 168">
                            <small class="form-text text-muted">
                                Used by providers whose pricing depends on country (e.g., SMS-Man). Leave blank if not applicable.
                            </small>
                            <?php if (!empty($data['default_country_err'])): ?>
                                <div class="text-danger small"><?php echo $data['default_country_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Default Markup (Pricing) -->
                        <div class="mb-3">
                            <label for="default_markup" class="form-label">Default Markup ($)</label>
                            <input type="number" name="default_markup" id="default_markup" class="form-control"
                                   value="<?php echo h($data['default_markup'] ?? '0.00'); ?>"
                                   placeholder="0.00" step="0.01" min="0" max="1000">
                            <small class="form-text text-muted">
                                Default markup added to all service prices during sync.
                                <br><strong>Formula: final_price = api_rate + markup</strong>
                            </small>
                        </div>

                        <!-- Status Toggle -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="status" id="status" class="form-check-input"
                                   value="active" <?php echo ($data['status'] ?? '') == 'active' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="status">
                                Active (Enable this instance immediately)
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">Save API Instance</button>
                            <button type="button" class="btn btn-info" id="testBtn">Test Connection</button>
                            <a href="<?php echo site_url('api'); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const providerSelect = document.getElementById('provider_id');
const baseUrlInput = document.getElementById('base_url');
const testBtn = document.getElementById('testBtn');

// Auto-populate base URL when provider is selected
providerSelect.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const baseUrlTemplate = selected.dataset.baseUrl;

    if (baseUrlTemplate && !baseUrlInput.value) {
        baseUrlInput.value = baseUrlTemplate;
    }
});

// Test connection
testBtn.addEventListener('click', function() {
    const baseUrl = baseUrlInput.value.trim();
    const apiKey = document.getElementById('api_key').value.trim();
    const csrfEl = document.querySelector('input[name="csrf_token"]');
    const csrfToken = csrfEl ? String(csrfEl.value || '').trim() : '';

    if (!baseUrl || !apiKey) {
        alert('Please enter Base URL and API Key first');
        return;
    }

    testBtn.disabled = true;
    testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';

    fetch('<?php echo site_url("api/test"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            ...(csrfToken ? {'X-CSRF-Token': csrfToken} : {})
        },
        body: JSON.stringify({
            base_url: baseUrl,
            api_key: apiKey,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Connection successful!');
        } else {
            alert('✗ Connection failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('✗ Error testing connection: ' + error.message);
    })
    .finally(() => {
        testBtn.disabled = false;
        testBtn.innerHTML = 'Test Connection';
    });
});
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
