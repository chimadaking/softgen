<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5 shadow-sm">
            <h2 class="text-center">Create An Account</h2>
            <p class="text-center">Please fill out this form to register with us</p>
            <form method="POST" action="<?php echo site_url('auth/register'); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username: <sup>*</sup></label>
                    <input type="text" name="username" class="form-control" value="<?php echo $data['username']; ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email: <sup>*</sup></label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                    <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
                </div>
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name:</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo $data['first_name']; ?>">
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name:</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo $data['last_name']; ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password: <sup>*</sup></label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                    <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password: <sup>*</sup></label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['confirm_password']; ?>">
                    <span class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></span>
                </div>
                <div class="row">
                    <div class="col">
                        <input type="submit" value="Register" class="btn btn-primary btn-block w-100">
                    </div>
                    <div class="col text-end">
                        <a href="<?php echo site_url('auth/login'); ?>" class="btn btn-light btn-block">Have an account? Login</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>