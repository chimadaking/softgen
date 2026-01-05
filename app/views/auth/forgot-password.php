<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5 shadow-sm">
            <h2 class="text-center">Forgot Password</h2>
            <p class="text-center">Enter your email address and we'll send you a link to reset your password.</p>
            <form action="<?php echo BASE_DIR; ?>/auth/forgot-password" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email: <sup>*</sup></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="d-grid">
                    <input type="submit" value="Reset Password" class="btn btn-primary">
                </div>
                <div class="text-center mt-3">
                    <a href="<?php echo site_url('auth/login'); ?>" class="text-decoration-none">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
