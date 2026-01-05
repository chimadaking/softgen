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
                        <th>API</th>
                        <th>Endpoint</th>
                        <th>Status</th>
                        <th>Response Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center">No logs available</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<a href="<?php echo site_url('api'); ?>" class="btn btn-secondary mt-3">Back to API Management</a>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>