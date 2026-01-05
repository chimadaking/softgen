<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<h1 class="mb-4">Test API #<?php echo h($data['api_id']); ?></h1>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Test Request</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    
                    <div class="mb-3">
                        <label for="endpoint" class="form-label">Endpoint</label>
                        <input type="text" name="endpoint" id="endpoint" class="form-control" placeholder="/api/v1/data" required>
                    </div>

                    <div class="mb-3">
                        <label for="method" class="form-label">Method</label>
                        <select name="method" id="method" class="form-control" required>
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Test API</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Response</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>Response will appear here...</code></pre>
            </div>
        </div>
    </div>
</div>

<a href="<?php echo site_url('api'); ?>" class="btn btn-secondary mt-3">Back to API Management</a>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>