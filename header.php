<?php
// start session if not started
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo isset($base_url) ? $base_url : '../'; ?>css/style.css" rel="stylesheet">
</head>
<body>

<!-- top navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #b5451b, #e8813a);">
    <div class="container-fluid">

        <!-- brand logo -->
        <a class="navbar-brand fw-bold fs-4" href="#">
            <i class="bi bi-shop me-2"></i>
            FoodieHub
        </a>

        <!-- toggle button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- nav links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <?php if(isset($_SESSION['admin_id'])): ?>
                <!-- admin is logged in -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : '../'; ?>admin/dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : '../'; ?>admin/manage_food.php">
                        <i class="bi bi-egg-fried me-1"></i>Food Items
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : '../'; ?>admin/manage_categories.php">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : '../'; ?>admin/manage_orders.php">
                        <i class="bi bi-bag-check me-1"></i>Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : '../'; ?>admin/manage_customers.php">
                        <i class="bi bi-people me-1"></i>Customers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold" href="<?php echo isset($base_url) ? $base_url : '../'; ?>auth/logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>

                <?php else: ?>
                <!-- guest / customer -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : ''; ?>customer/index.php">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : ''; ?>customer/menu.php">
                        <i class="bi bi-menu-button-wide me-1"></i>Menu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : ''; ?>customer/cart.php">
                        <i class="bi bi-cart3 me-1"></i>Cart
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge bg-warning text-dark"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : ''; ?>auth/login.php">
                        <i class="bi bi-person-circle me-1"></i>Admin Login
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>
<!-- end navbar -->
