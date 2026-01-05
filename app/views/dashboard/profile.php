<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">My Profile</h1>

<?php if (isset($data['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $data['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Update Profile</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_DIR; ?>/dashboard/profile" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo h($data['user']->username); ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo h($data['user']->email); ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control <?php echo (!empty($data['first_name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo h($data['first_name'] ?? $data['user']->first_name); ?>">
                            <span class="invalid-feedback"><?php echo $data['first_name_err'] ?? ''; ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control <?php echo (!empty($data['last_name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo h($data['last_name'] ?? $data['user']->last_name); ?>">
                            <span class="invalid-feedback"><?php echo $data['last_name_err'] ?? ''; ?></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_DIR; ?>/dashboard/password" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control <?php echo (!empty($data['current_password_err'])) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $data['current_password_err'] ?? ''; ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control <?php echo (!empty($data['new_password_err'])) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $data['new_password_err'] ?? ''; ?></span>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $data['confirm_password_err'] ?? ''; ?></span>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Account Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Role:</strong> <span class="badge bg-primary"><?php echo ucfirst($data['user']->role); ?></span>
                </div>
                <div class="mb-3">
                    <strong>Status:</strong> <span class="badge bg-<?php echo ($data['user']->status == 'active') ? 'success' : 'warning'; ?>"><?php echo ucfirst($data['user']->status); ?></span>
                </div>
                <div class="mb-3">
                    <strong>Member Since:</strong><br>
                    <?php echo date('M d, Y', strtotime($data['user']->created_at)); ?>
                </div>
                <?php if ($data['user']->email_verified_at): ?>
                <div class="mb-3">
                    <strong>Email Verified:</strong> <span class="badge bg-success">Yes</span>
                </div>
                <?php else: ?>
                <div class="mb-3">
                    <strong>Email Verified:</strong> <span class="badge bg-warning">No</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
