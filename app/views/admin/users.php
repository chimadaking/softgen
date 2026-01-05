<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Manage Users</h1>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (!empty($data['users'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['users'] as $user): ?>
                            <tr>
                                <td><?php echo $user->id; ?></td>
                                <td><?php echo h($user->username); ?></td>
                                <td><?php echo h($user->email); ?></td>
                                <td><?php echo h($user->first_name . ' ' . $user->last_name); ?></td>
                                <td><span class="badge bg-<?php echo ($user->role == 'admin') ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user->role); ?></span></td>
                                <td><span class="badge bg-<?php echo ($user->status == 'active') ? 'success' : 'warning'; ?>"><?php echo ucfirst($user->status); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($user->created_at)); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mb-0">No users found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
