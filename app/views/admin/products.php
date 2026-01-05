<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Manage Products</h1>

<a href="<?php echo site_url('product/create'); ?>" class="btn btn-success mb-3">
    <i class="bi bi-plus-circle"></i> Add New Product
</a>

<div class="card shadow-sm">
    <div class="card-body">

        <?php flash('success'); ?>
        <?php flash('error', '', 'alert alert-danger'); ?>

        <?php if (!empty($data['products'])): ?>

            <!-- Bulk Actions Bar -->
            <form method="POST" action="<?php echo site_url('product/bulk'); ?>" id="bulkForm" class="mb-3">
                <?php echo csrf_field(); ?>

                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Bulk Action</label>
                        <select name="bulk_action" class="form-select" id="bulkAction" required>
                            <option value="">-- Select action --</option>
                            <option value="status_active">Set Status: Active</option>
                            <option value="status_inactive">Set Status: Inactive</option>
                            <option value="status_out_of_stock">Set Status: Out of Stock</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="confirmBox" style="display:none;">
                        <label class="form-label mb-1">Confirm</label>
                        <input type="text" class="form-control" id="confirmText" placeholder='Type DELETE to confirm'>
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <input type="hidden" name="ids_csv" id="idsCsv" value="">
                        <button type="submit" class="btn btn-primary w-100" id="bulkApplyBtn" disabled>
                            Apply to Selected
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="bulkResetBtn">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="small text-muted mt-2">
                    Tip: Use the checkbox in the header to select all visible products.
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th style="width:170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['products'] as $product): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="rowCheck" value="<?php echo (int)$product->id; ?>">
                                </td>
                                <td><?php echo (int)$product->id; ?></td>
                                <td><?php echo h($product->name); ?></td>
                                <td><?php echo h($product->category_name); ?></td>
                                <td>$<?php echo number_format((float)$product->price, 2); ?></td>
                                <td><?php echo (int)$product->stock; ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        $statusColors = [
                                            'active' => 'success',
                                            'inactive' => 'secondary',
                                            'out_of_stock' => 'danger'
                                        ];
                                        echo $statusColors[$product->status] ?? 'secondary';
                                    ?>">
                                        <?php echo ucfirst((string)$product->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo site_url('product/edit/' . (int)$product->id); ?>"
                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="<?php echo site_url('product/delete/' . (int)$product->id); ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <script>
                (function () {
                    const checkAll = document.getElementById('checkAll');
                    const rowChecks = () => Array.from(document.querySelectorAll('.rowCheck'));
                    const idsCsv = document.getElementById('idsCsv');
                    const bulkAction = document.getElementById('bulkAction');
                    const bulkApplyBtn = document.getElementById('bulkApplyBtn');
                    const bulkResetBtn = document.getElementById('bulkResetBtn');
                    const confirmBox = document.getElementById('confirmBox');
                    const confirmText = document.getElementById('confirmText');

                    function selectedIds() {
                        return rowChecks().filter(c => c.checked).map(c => c.value);
                    }

                    function refreshState() {
                        const ids = selectedIds();
                        idsCsv.value = ids.join(',');
                        const action = bulkAction.value;

                        // delete confirmation
                        if (action === 'delete') {
                            confirmBox.style.display = '';
                            bulkApplyBtn.disabled = !(ids.length > 0 && confirmText.value.trim().toUpperCase() === 'DELETE');
                        } else {
                            confirmBox.style.display = 'none';
                            confirmText.value = '';
                            bulkApplyBtn.disabled = !(ids.length > 0 && action);
                        }
                    }

                    if (checkAll) {
                        checkAll.addEventListener('change', function () {
                            rowChecks().forEach(c => c.checked = checkAll.checked);
                            refreshState();
                        });
                    }

                    rowChecks().forEach(c => c.addEventListener('change', refreshState));
                    bulkAction.addEventListener('change', refreshState);
                    confirmText.addEventListener('input', refreshState);

                    bulkResetBtn.addEventListener('click', function () {
                        bulkAction.value = '';
                        confirmText.value = '';
                        confirmBox.style.display = 'none';
                        if (checkAll) checkAll.checked = false;
                        rowChecks().forEach(c => c.checked = false);
                        refreshState();
                    });

                    // init
                    refreshState();
                })();
            </script>

        <?php else: ?>
            <p class="text-center mb-0">
                No products found. <a href="<?php echo site_url('product/create'); ?>">Add your first product</a>.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
