<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<h1 class="mb-4">API Logs</h1>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Recent API Requests</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>API Instance</th>
                        <th>Endpoint</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['logs'])): ?>
                        <?php foreach ($data['logs'] as $log): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($log->created_at)); ?></td>
                                <td><?php echo h($log->instance_name ?? 'Unknown'); ?></td>
                                <td><?php echo h($log->endpoint); ?></td>
                                <td><?php echo h(strtoupper($log->request_method)); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($log->response_code >= 200 && $log->response_code < 300) ? 'success' : 'danger'; ?>">
                                        <?php echo h($log->response_code); ?>
                                    </span>
                                </td>
                                <td><?php echo h($log->ip_address); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No logs available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<a href="<?php echo site_url('api'); ?>" class="btn btn-secondary mt-3">Back to API Management</a>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
