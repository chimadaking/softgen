<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">Create Admin User</h4>
                </div>
                <div class="card-body">
                    <?php flash('register_success'); ?>
                    <?php flash('error', '', 'alert alert-danger'); ?>
                    
                    <form method="POST" action="<?php echo site_url('admin/register'); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control"
                                   value="<?php echo h($data['username'] ?? ''); ?>" required>
                            <?php if (!empty($data['username_err'])): ?>
                                <div class="text-danger small"><?php echo $data['username_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="<?php echo h($data['email'] ?? ''); ?>" required>
                            <?php if (!empty($data['email_err'])): ?>
                                <div class="text-danger small"><?php echo $data['email_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <?php if (!empty($data['password_err'])): ?>
                                <div class="text-danger small"><?php echo $data['password_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <?php if (!empty($data['confirm_password_err'])): ?>
                                <div class="text-danger small"><?php echo $data['confirm_password_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">Create Admin</button>
                        <a href="<?php echo site_url('admin'); ?>" class="btn btn-secondary w-100 mt-2">
                            Back to Admin
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>