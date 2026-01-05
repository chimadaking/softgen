<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Provider</h4>
                </div>
                <div class="card-body">
                    <?php flash('error', '', 'alert alert-danger'); ?>
                    
                    <form method="POST" action="<?php echo site_url('admin/providers/edit/' . $data['id']); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Provider Name <sup>*</sup></label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="<?php echo h($data['name'] ?? ''); ?>" 
                                   required>
                            <?php if (!empty($data['name_err'])): ?>
                                <div class="text-danger small"><?php echo $data['name_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" 
                                      placeholder="Brief description of this service provider"><?php echo h($data['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="base_url_template" class="form-label">Base URL Template</label>
                            <input type="url" name="base_url_template" id="base_url_template" class="form-control" 
                                   value="<?php echo h($data['base_url_template'] ?? ''); ?>" 
                                   placeholder="e.g., https://api.provider.com">
                            <small class="form-text text-muted">Default API base URL for this provider</small>
                            <?php if (!empty($data['base_url_template_err'])): ?>
                                <div class="text-danger small"><?php echo $data['base_url_template_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="documentation_url" class="form-label">Documentation URL</label>
                            <input type="url" name="documentation_url" id="documentation_url" class="form-control" 
                                   value="<?php echo h($data['documentation_url'] ?? ''); ?>" 
                                   placeholder="https://docs.provider.com">
                            <small class="form-text text-muted">Link to API documentation</small>
                        </div>

                        <!-- ✅ Service Schema JSON -->
                        <div class="mb-3">
                            <label for="service_schema" class="form-label">Service Schema (JSON)</label>
                            <textarea name="service_schema" id="service_schema" class="form-control" rows="10"
                                      placeholder='{"services": {...}}'><?php echo h($data['service_schema'] ?? ''); ?></textarea>
                            <small class="form-text text-muted">
                                Required for syncing services. For SMS-Man, you can use a schema that fetches “applications”.
                            </small>
                            <?php if (!empty($data['service_schema_err'])): ?>
                                <div class="text-danger small"><?php echo $data['service_schema_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="status" id="status" class="form-check-input" 
                                   value="active" <?php echo ($data['status'] ?? '') == 'active' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="status">
                                Active
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">Update Provider</button>
                            <a href="<?php echo site_url('admin/providers'); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
