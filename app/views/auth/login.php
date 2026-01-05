<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5 shadow-sm">
            <?php flash('register_success'); ?>
            <h2 class="text-center">Login</h2>
            <p class="text-center">Please fill in your credentials to log in</p>
            <form method="POST" action="<?php echo site_url('auth/login'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email: <sup>*</sup></label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                    <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password: <sup>*</sup></label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                    <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                </div>
                <div class="row">
                    <div class="col">
                        <input type="submit" value="Login" class="btn btn-primary btn-block w-100">
                    </div>
                    <div class="col text-end">
                        <a href="<?php echo site_url('auth/register'); ?>" class="btn btn-light btn-block">No account? Register</a>
                    </div>
                </div>
            </form>
            <hr>
            <p class="text-center text-muted small">
                <a href="<?php echo site_url('auth/forgot-password'); ?>">Forgot your password?</a>
            </p>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>