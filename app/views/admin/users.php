<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Manage Users</h1>
    <a href="<?php echo site_url('admin/users/create'); ?>" class="btn btn-primary">Create User</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (!empty($data['users'])): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['users'] as $user): ?>
                            <?php
                                $roles = array_filter(array_map('trim', explode(',', $user->role_names ?? '')));
                                if (empty($roles)) {
                                    $roles = ['user'];
                                }
                            ?>
                            <tr>
                                <td><?php echo $user->id; ?></td>
                                <td><?php echo h($user->username); ?></td>
                                <td><?php echo h($user->email); ?></td>
                                <td><?php echo h(trim($user->first_name . ' ' . $user->last_name)); ?></td>
                                <td>
                                    <?php foreach ($roles as $role): ?>
                                        <span class="badge bg-<?php echo $role === 'admin' ? 'danger' : 'secondary'; ?>">
                                            <?php echo h(ucfirst($role)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($user->status == 'active') ? 'success' : (($user->status === 'suspended') ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($user->status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user->created_at)); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('admin/users/edit/' . $user->id); ?>">Edit</a>
                                        <form method="POST" action="<?php echo site_url('admin/users/status/' . $user->id); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="<?php echo $user->status === 'active' ? 'suspended' : 'active'; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <?php echo $user->status === 'active' ? 'Suspend' : 'Activate'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo site_url('admin/users/delete/' . $user->id); ?>" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
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
