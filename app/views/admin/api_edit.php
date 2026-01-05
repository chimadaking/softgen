<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h1>Edit API Instance</h1>

    <form method="post">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label>Instance Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?php echo h($data['instance']->name); ?>" required>
        </div>

        <div class="mb-3">
            <label>Base URL</label>
            <input type="url" name="base_url" class="form-control"
                   value="<?php echo h($data['instance']->base_url); ?>" required>
        </div>

        <div class="mb-3">
            <label>API Key</label>
            <input type="text" name="api_key" class="form-control"
                   value="<?php echo h($data['instance']->api_key); ?>" required>
        </div>

        <div class="mb-3">
            <label>Default Country (optional)</label>
            <input type="text" name="default_country" class="form-control"
                   value="<?php echo h($data['instance']->default_country ?? ''); ?>"
                   placeholder="e.g., 0 or 168">
            <small class="form-text text-muted">
                Used by providers whose pricing depends on country (e.g., SMS-Man). Leave blank if not applicable.
            </small>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php if ($data['instance']->status==='active') echo 'selected'; ?>>Active</option>
                <option value="inactive" <?php if ($data['instance']->status==='inactive') echo 'selected'; ?>>Inactive</option>
            </select>
        </div>

        <button class="btn btn-success">Save Changes</button>
        <a href="<?php echo site_url('api'); ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
