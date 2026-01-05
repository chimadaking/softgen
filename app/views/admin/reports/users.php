<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">User Reports</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Total Users</h5>
                <h2 class="display-6"><?php echo count($data['users'] ?? []); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Active Users</h5>
                <h2 class="display-6"><?php echo count(array_filter($data['users'] ?? [], function($u) { return $u->status == 'active'; })); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">New This Month</h5>
                <h2 class="display-6"><?php echo count(array_filter($data['users'] ?? [], function($u) { return strtotime($u->created_at) > strtotime('-30 days'); })); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">User Statistics</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['users'])): ?>
                        <?php foreach ($data['users'] as $user): ?>
                            <tr>
                                <td><?php echo $user->id; ?></td>
                                <td><?php echo h($user->username); ?></td>
                                <td><?php echo h($user->email); ?></td>
                                <td><span class="badge bg-<?php echo ($user->role == 'admin') ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user->role); ?></span></td>
                                <td><span class="badge bg-<?php echo ($user->status == 'active') ? 'success' : 'secondary'; ?>"><?php echo ucfirst($user->status); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($user->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
