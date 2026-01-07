<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['title']) ? $data['title'] . ' | ' . SITE_NAME : SITE_NAME; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_DIR; ?>/css/style.css">
</head>
<body class="bg-light app-body">

<?php
$usdNgnRate = number_format(getUsdToNgnRate(), 2);
?>

<?php if (isLoggedIn()): ?>
<nav class="navbar navbar-expand-md navbar-dark bg-primary mb-4 app-navbar">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo site_url(''); ?>">
            <?php echo SITE_NAME; ?>
            <span class="badge bg-light text-dark fw-normal">
                $1 = ₦<?php echo $usdNgnRate; ?>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url('dashboard'); ?>">Dashboard</a>
                </li>

                <li class="nav-item d-md-none border-top mt-2 pt-2">
                    <a class="nav-link" href="<?php echo site_url('product'); ?>">
                        <i class="bi bi-box me-2"></i> Products
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link" href="<?php echo site_url('order'); ?>">
                        <i class="bi bi-cart me-2"></i> Orders
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link" href="<?php echo site_url('wallet'); ?>">
                        <i class="bi bi-wallet2 me-2"></i> Wallet
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link" href="<?php echo site_url('loyalty'); ?>">
                        <i class="bi bi-star me-2"></i> Loyalty Points
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link" href="<?php echo site_url('affiliate'); ?>">
                        <i class="bi bi-people me-2"></i> Affiliate Program
                    </a>
                </li>

                <?php if (isAdmin()): ?>
                <li class="nav-item d-md-none border-top mt-2 pt-2">
                    <a class="nav-link" href="<?php echo site_url('admin'); ?>">
                        <i class="bi bi-shield-lock me-2"></i> Admin Panel
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link" href="<?php echo site_url('admin/users'); ?>">
                        <i class="bi bi-person-gear me-2"></i> Manage Users
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link" href="<?php echo site_url('admin/register'); ?>">
                        <i class="bi bi-person-plus me-2"></i> Create Admin
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link" href="<?php echo site_url('api'); ?>">
                        <i class="bi bi-link-45deg me-2"></i> API Settings
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item border-top mt-2 pt-2">
                    <span class="navbar-text text-white d-block d-md-inline">
                        Welcome <?php echo h(getUserName()); ?> |
                        <a class="text-white text-decoration-none" href="<?php echo site_url('auth/logout'); ?>">Logout</a>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 d-none d-md-block app-sidebar">
            <?php require_once BASE_PATH . '/app/views/layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9 app-content">

<?php else: ?>
<nav class="navbar navbar-expand-md navbar-dark bg-primary mb-4 app-navbar">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo site_url(''); ?>">
            <?php echo SITE_NAME; ?>
            <span class="badge bg-light text-dark fw-normal">
                $1 = ₦<?php echo $usdNgnRate; ?>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url('auth/login'); ?>">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo site_url('auth/register'); ?>">Register</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
<?php endif; ?>
