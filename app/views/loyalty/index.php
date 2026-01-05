<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>
<h1 class="mb-4">Loyalty Points</h1>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Your Points</h5>
                <h1 class="display-4"><?php echo $data['points']; ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title">Current Tier</h5>
                <h2 class="display-6"><?php echo $data['tier']; ?></h2>
                <p class="mb-0">Next tier: <?php echo $data['next_tier']; ?> points</p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Points History</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['history'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Points</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['history'] as $entry): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($entry->created_at)); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($entry->type == 'earned') ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($entry->type); ?>
                                    </span>
                                </td>
                                <td class="<?php echo ($entry->type == 'earned') ? 'text-success' : 'text-warning'; ?>">
                                    <?php echo ($entry->type == 'earned') ? '+' : '-'; ?><?php echo $entry->points; ?>
                                </td>
                                <td><?php echo h($entry->description); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mb-0">No points history yet. Start earning points by making purchases!</p>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
