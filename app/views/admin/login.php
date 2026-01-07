<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0">Admin Login</h4>
                </div>
                <div class="card-body">
                    <?php flash('error', '', 'alert alert-danger'); ?>

                    <form method="POST" action="<?php echo site_url('admin/login'); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>"
                                value="<?php echo h($data['email'] ?? ''); ?>"
                                required
                            >
                            <?php if (!empty($data['email_err'])): ?>
                                <div class="invalid-feedback"><?php echo $data['email_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>"
                                required
                            >
                            <?php if (!empty($data['password_err'])): ?>
                                <div class="invalid-feedback"><?php echo $data['password_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            Login
                        </button>

                        <a href="<?php echo site_url('/'); ?>" class="btn btn-secondary w-100 mt-2">
                            Back to Home
                        </a>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
