<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Providers</h1>
        <a href="<?php echo site_url('admin/providers/create'); ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add Provider
        </a>
    </div>

    <?php flash('success'); ?>
    <?php flash('error', '', 'alert alert-danger'); ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!empty($data['providers']) && count($data['providers']) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Provider Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['providers'] as $provider): ?>
                                <tr>
                                    <td><strong><?php echo $provider->id; ?></strong></td>
                                    <td><?php echo h($provider->name); ?></td>
                                    <td>
                                        <small><?php echo h(substr($provider->description ?? 'N/A', 0, 50)); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo ($provider->status == 'active') ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($provider->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($provider->created_at)); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('admin/providers/edit/' . $provider->id); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="<?php echo site_url('admin/providers/delete/' . $provider->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this provider?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center mb-0">
                    <p class="mb-2">No providers found.</p>
                    <a href="<?php echo site_url('admin/providers/create'); ?>" class="btn btn-sm btn-primary">Add Your First Provider</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>