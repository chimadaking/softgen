<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<h1 class="mb-4">Create User</h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php flash('error', '', 'alert alert-danger'); ?>
        <form method="POST" action="<?php echo site_url('admin/users/create'); ?>">
            <?php echo csrf_field(); ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo h($data['user']['username'] ?? ''); ?>" required>
                    <?php if (!empty($data['user']['username_err'])): ?>
                        <div class="text-danger small"><?php echo h($data['user']['username_err']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo h($data['user']['email'] ?? ''); ?>" required>
                    <?php if (!empty($data['user']['email_err'])): ?>
                        <div class="text-danger small"><?php echo h($data['user']['email_err']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo h($data['user']['first_name'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo h($data['user']['last_name'] ?? ''); ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <?php if (!empty($data['user']['password_err'])): ?>
                        <div class="text-danger small"><?php echo h($data['user']['password_err']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                    <?php if (!empty($data['user']['confirm_password_err'])): ?>
                        <div class="text-danger small"><?php echo h($data['user']['confirm_password_err']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['active', 'pending', 'suspended'] as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo ($data['user']['status'] ?? '') === $status ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Roles</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($data['roles'] as $role): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="<?php echo $role->id; ?>"
                                    <?php echo in_array($role->id, $data['user']['roles'] ?? [], true) ? 'checked' : ''; ?>>
                                <label class="form-check-label"><?php echo h(ucfirst($role->name)); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="<?php echo site_url('admin/users'); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
