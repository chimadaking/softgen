<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Create Product</h1>

<?php if (isset($data['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $data['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($data['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $data['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?php echo BASE_DIR; ?>/product/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <sup>*</sup></label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo h($data['name'] ?? ''); ?>">
                        <span class="invalid-feedback"><?php echo $data['name_err'] ?? ''; ?></span>
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug <sup>*</sup></label>
                        <input type="text" name="slug" class="form-control <?php echo (!empty($data['slug_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo h($data['slug'] ?? ''); ?>">
                        <span class="invalid-feedback"><?php echo $data['slug_err'] ?? ''; ?></span>
                        <small class="form-text text-muted">URL-friendly version of the product name</small>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <sup>*</sup></label>
                        <select name="category_id" class="form-select <?php echo (!empty($data['category_id_err'])) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Category</option>
                            <?php foreach ($data['categories'] as $category): ?>
                            <option value="<?php echo $category->id; ?>" <?php echo (isset($data['category_id']) && $data['category_id'] == $category->id) ? 'selected' : ''; ?>><?php echo h($category->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="invalid-feedback"><?php echo $data['category_id_err'] ?? ''; ?></span>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?php echo h($data['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2"><?php echo h($data['short_description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price <sup>*</sup></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="price" step="0.01" class="form-control <?php echo (!empty($data['price_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo h($data['price'] ?? ''); ?>">
                        </div>
                        <span class="invalid-feedback"><?php echo $data['price_err'] ?? ''; ?></span>
                    </div>

                    <div class="mb-3">
                        <label for="original_price" class="form-label">Original Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="original_price" step="0.01" class="form-control" value="<?php echo h($data['original_price'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo h($data['stock'] ?? 0); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="min_order" class="form-label">Min Order</label>
                        <input type="number" name="min_order" class="form-control" value="<?php echo h($data['min_order'] ?? 1); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="max_order" class="form-label">Max Order</label>
                        <input type="number" name="max_order" class="form-control" value="<?php echo h($data['max_order'] ?? 1000); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo (isset($data['status']) && $data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($data['status']) && $data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="out_of_stock" <?php echo (isset($data['status']) && $data['status'] == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Product</button>
                </div>
            </div>
        </form>
    </div>
</div>

<a href="<?php echo site_url('product'); ?>" class="btn btn-secondary mt-3">Back to Products</a>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
