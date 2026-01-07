<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>API Instances</h1>
        <div class="btn-group">
            <a href="<?php echo site_url('api/create'); ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Instance
            </a>
            <a href="<?php echo site_url('admin/providers'); ?>" class="btn btn-info">
                <i class="bi bi-gear"></i> Manage Providers
            </a>
        </div>
    </div>

    <?php flash('success'); ?>
    <?php flash('error', '', 'alert alert-danger'); ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">API Instances</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($data['instances'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Instance Name</th>
                                <th>Provider</th>
                                <th>Base URL</th>
                                <th>Default Markup</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($data['instances'] as $instance): ?>
                            <tr>
                                <td><strong><?php echo $instance->id; ?></strong></td>
                                <td><?php echo h($instance->name); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo h($instance->provider_name); ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="small">
                                        <?php echo h(substr($instance->base_url, 0, 40)); ?>…
                                    </code>
                                </td>
                                <td>
                                    <?php $defaultMarkup = (float)($instance->default_markup ?? 0.00); ?>
                                    <span class="badge bg-<?php echo $defaultMarkup > 0 ? 'success' : 'secondary'; ?>">
                                        $<?php echo number_format($defaultMarkup, 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $instance->status === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($instance->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($instance->created_at)); ?>
                                    </small>
                                </td>
                                <td class="d-flex gap-1 flex-wrap">
                                    <a href="<?php echo site_url('api/services/' . $instance->id); ?>" class="btn btn-sm btn-outline-primary">
                                        Manage Services
                                    </a>

                                    <a href="<?php echo site_url('api/instanceSettings/' . $instance->id); ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-sliders"></i> Settings
                                    </a>

                                    <a href="<?php echo site_url('api/syncServices/' . $instance->id); ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Sync services from this API?');">
                                        Sync Services
                                    </a>

                                    <a href="<?php echo site_url('api/edit/' . $instance->id); ?>" class="btn btn-sm btn-outline-info">
                                        Edit
                                    </a>

                                    <a href="<?php echo site_url('api/delete/' . $instance->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this API instance?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center mb-0">
                    <p class="mb-2">No API instances configured yet.</p>
                    <a href="<?php echo site_url('api/create'); ?>" class="btn btn-sm btn-primary">
                        Add Your First Instance
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Quick Links</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <a href="<?php echo site_url('admin/providers'); ?>" class="btn btn-outline-primary w-100">
                        <i class="bi bi-list"></i> View All Providers
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="<?php echo site_url('api/logs'); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-file-text"></i> API Logs
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="<?php echo site_url('api/create'); ?>" class="btn btn-outline-success w-100">
                        <i class="bi bi-plus"></i> New Instance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>