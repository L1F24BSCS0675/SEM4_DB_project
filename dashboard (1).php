<?php
session_start();

// check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/db.php');

// get total food items
$r1           = mysqli_query($conn, "select count(*) as total from food_items");
$total_food   = mysqli_fetch_assoc($r1)['total'];

// get total categories
$r2           = mysqli_query($conn, "select count(*) as total from categories");
$total_cats   = mysqli_fetch_assoc($r2)['total'];

// get total customers
$r3           = mysqli_query($conn, "select count(*) as total from customers");
$total_custs  = mysqli_fetch_assoc($r3)['total'];

// get total orders
$r4           = mysqli_query($conn, "select count(*) as total from orders");
$total_orders = mysqli_fetch_assoc($r4)['total'];

// get total revenue
$r5      = mysqli_query($conn, "select sum(total_amount) as revenue from orders where order_status = 'completed'");
$revenue = mysqli_fetch_assoc($r5)['revenue'];
if(!$revenue) $revenue = 0;

// get pending orders count
$r6      = mysqli_query($conn, "select count(*) as total from orders where order_status = 'pending'");
$pending = mysqli_fetch_assoc($r6)['total'];

// get latest 5 orders
$latest_orders = mysqli_query($conn,
    "select orders.id, customer_name, total_amount, order_status, order_date
     from orders
     inner join customers on orders.customer_id = customers.id
     order by order_date desc limit 5");

// get latest 5 customers
$latest_custs = mysqli_query($conn,
    "select * from customers order by created_at desc limit 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FoodieHub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<?php include('../includes/header.php'); ?>

<div class="container-fluid mt-4">
<div class="row">

<!-- sidebar -->
<div class="col-md-2">
    <div class="sidebar p-3">
        <h6 class="text-muted small fw-bold mb-3 text-uppercase">Admin Menu</h6>
        <ul class="nav flex-column">
            <li class="nav-item mb-1">
                <a href="dashboard.php" class="nav-link active-link">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_food.php" class="nav-link side-link">
                    <i class="bi bi-egg-fried me-2"></i>Food Items
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_categories.php" class="nav-link side-link">
                    <i class="bi bi-grid me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_orders.php" class="nav-link side-link">
                    <i class="bi bi-bag-check me-2"></i>Orders
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_customers.php" class="nav-link side-link">
                    <i class="bi bi-people me-2"></i>Customers
                </a>
            </li>
            <li class="nav-item mt-3">
                <a href="../auth/logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- main content -->
<div class="col-md-10">

    <!-- page heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <small class="text-muted">Welcome back, <?php echo $_SESSION['admin_name']; ?>!</small>
        </div>
        <span class="badge bg-warning text-dark p-2">
            <i class="bi bi-clock me-1"></i><?php echo date('d M Y'); ?>
        </span>
    </div>

    <!-- stats cards row 1 -->
    <div class="row mb-4">

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid #e8813a !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Food Items</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_food; ?></h3>
                        </div>
                        <div class="stat-icon" style="background:#fff3e0; color:#e8813a;">
                            <i class="bi bi-egg-fried fs-4"></i>
                        </div>
                    </div>
                    <a href="manage_food.php" class="small text-decoration-none" style="color:#e8813a;">
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Categories</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_cats; ?></h3>
                        </div>
                        <div class="stat-icon" style="background:#d1e7dd; color:#198754;">
                            <i class="bi bi-grid fs-4"></i>
                        </div>
                    </div>
                    <a href="manage_categories.php" class="small text-success text-decoration-none">
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Customers</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_custs; ?></h3>
                        </div>
                        <div class="stat-icon" style="background:#cfe2ff; color:#0d6efd;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                    <a href="manage_customers.php" class="small text-primary text-decoration-none">
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Orders</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_orders; ?></h3>
                        </div>
                        <div class="stat-icon" style="background:#f8d7da; color:#dc3545;">
                            <i class="bi bi-bag-check fs-4"></i>
                        </div>
                    </div>
                    <a href="manage_orders.php" class="small text-danger text-decoration-none">
                        View all <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- revenue and pending row -->
    <div class="row mb-4">

        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg,#b5451b,#e8813a); color:white;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 opacity-75">Total Revenue</p>
                            <h2 class="fw-bold">$<?php echo number_format($revenue, 2); ?></h2>
                            <small class="opacity-75">From completed orders</small>
                        </div>
                        <i class="bi bi-currency-dollar" style="font-size:60px; opacity:0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg,#0d6efd,#6610f2); color:white;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 opacity-75">Pending Orders</p>
                            <h2 class="fw-bold"><?php echo $pending; ?></h2>
                            <small class="opacity-75">Waiting to be processed</small>
                        </div>
                        <i class="bi bi-hourglass-split" style="font-size:60px; opacity:0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- latest orders table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-warning"></i>Latest Orders</h6>
            <a href="manage_orders.php" class="btn btn-sm btn-outline-warning">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($latest_orders) > 0): ?>
                    <?php while($o = mysqli_fetch_assoc($latest_orders)): ?>
                    <tr>
                        <td><strong>#<?php echo $o['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                        <td class="fw-bold text-success">$<?php echo number_format($o['total_amount'],2); ?></td>
                        <td>
                            <?php
                            $status = $o['order_status'];
                            $badge  = 'secondary';
                            if($status == 'completed')  $badge = 'success';
                            if($status == 'pending')    $badge = 'warning';
                            if($status == 'processing') $badge = 'info';
                            if($status == 'cancelled')  $badge = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                        </td>
                        <td class="text-muted small"><?php echo date('d M Y', strtotime($o['order_date'])); ?></td>
                        <td>
                            <a href="edit_order.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No orders yet</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- latest customers -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Latest Customers</h6>
            <a href="manage_customers.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($latest_custs) > 0): ?>
                    <?php while($c = mysqli_fetch_assoc($latest_custs)): ?>
                    <tr>
                        <td><strong>#<?php echo $c['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($c['customer_name']); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($c['email']); ?></td>
                        <td><?php echo htmlspecialchars($c['phone']); ?></td>
                        <td class="text-muted small"><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No customers yet</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- end main col -->
</div><!-- end row -->
</div><!-- end container -->

<?php include('../includes/footer.php'); ?>
</body>
</html>
