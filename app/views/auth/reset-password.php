<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5 shadow-sm">
            <?php flash('error', '', 'alert alert-danger'); ?>
            <h2 class="text-center">Reset Password</h2>
            <p class="text-center">Enter your new password below.</p>
            <form method="POST" action="<?php echo site_url('auth/reset-password/' . $data['token']); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password: <sup>*</sup></label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" required>
                    <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password: <sup>*</sup></label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" required>
                    <span class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></span>
                </div>
                <div class="d-grid">
                    <input type="submit" value="Update Password" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
