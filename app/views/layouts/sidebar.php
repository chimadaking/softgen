<div class="list-group">
    <a href="<?php echo site_url('dashboard'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <a href="<?php echo site_url('product'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-box me-2"></i> Products
    </a>
    <a href="<?php echo site_url('order'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-cart me-2"></i> Orders
    </a>
    <a href="<?php echo site_url('wallet'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-wallet2 me-2"></i> Wallet
    </a>
    <a href="<?php echo site_url('loyalty'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-star me-2"></i> Loyalty Points
    </a>
    <a href="<?php echo site_url('affiliate'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-people me-2"></i> Affiliate Program
    </a>
    <?php if (isAdmin()): ?>
    <div class="list-group-item list-group-item-secondary mt-3">Admin Menu</div>
    <a href="<?php echo site_url('admin'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-shield-lock me-2"></i> Admin Panel
    </a>
    <a href="<?php echo site_url('admin/users'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-person-gear me-2"></i> Manage Users
    </a>
    <a href="<?php echo site_url('admin/register'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-person-plus me-2"></i> Create Admin
    </a>
    <a href="<?php echo site_url('api'); ?>" class="list-group-item list-group-item-action">
        <i class="bi bi-link-45deg me-2"></i> API Settings
    </a>
    <?php endif; ?>
</div>