<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

// search and filter
$search = "";
$filter = "";
if(isset($_GET['search']) && !empty($_GET['search'])) $search = trim($_GET['search']);
if(isset($_GET['filter']) && !empty($_GET['filter'])) $filter = trim($_GET['filter']);

// build query
$sql  = "select orders.*, customers.customer_name, customers.phone
         from orders
         inner join customers on orders.customer_id = customers.id
         where 1=1";
$params = [];
$types  = "";

if(!empty($search)){
    $sql   .= " and (customers.customer_name like ? or orders.id like ?)";
    $like   = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $types   .= "ss";
}
if(!empty($filter)){
    $sql   .= " and orders.order_status = ?";
    $params[] = $filter;
    $types   .= "s";
}
$sql .= " order by orders.id desc";

if(!empty($params)){
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

// messages
$msg = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'added')   $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Order added successfully!</div>';
    if($_GET['msg'] == 'updated') $msg = '<div class="alert alert-info"><i class="bi bi-check-circle me-2"></i>Order updated successfully!</div>';
    if($_GET['msg'] == 'deleted') $msg = '<div class="alert alert-warning"><i class="bi bi-trash me-2"></i>Order deleted successfully!</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - FoodieHub</title>
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
                <a href="dashboard.php" class="nav-link side-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_food.php" class="nav-link side-link"><i class="bi bi-egg-fried me-2"></i>Food Items</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_categories.php" class="nav-link side-link"><i class="bi bi-grid me-2"></i>Categories</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_orders.php" class="nav-link active-link"><i class="bi bi-bag-check me-2"></i>Orders</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_customers.php" class="nav-link side-link"><i class="bi bi-people me-2"></i>Customers</a>
            </li>
            <li class="nav-item mt-3">
                <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </li>
        </ul>
    </div>
</div>

<!-- main content -->
<div class="col-md-10">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Orders</h4>
            <small class="text-muted">Manage all customer orders</small>
        </div>
        <a href="add_order.php" class="btn btn-warning fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Add New Order
        </a>
    </div>

    <?php echo $msg; ?>

    <!-- search and filter bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2 flex-wrap">
                <input type="text" name="search" class="form-control" style="max-width:250px;"
                       placeholder="Search by customer or order ID..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <select name="filter" class="form-select" style="max-width:180px;">
                    <option value="">All Status</option>
                    <option value="pending"    <?php echo $filter=='pending'    ? 'selected':''; ?>>Pending</option>
                    <option value="processing" <?php echo $filter=='processing' ? 'selected':''; ?>>Processing</option>
                    <option value="completed"  <?php echo $filter=='completed'  ? 'selected':''; ?>>Completed</option>
                    <option value="cancelled"  <?php echo $filter=='cancelled'  ? 'selected':''; ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-warning px-4"><i class="bi bi-search"></i> Search</button>
                <?php if(!empty($search) || !empty($filter)): ?>
                <a href="manage_orders.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- orders table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td class="fw-bold text-success">$<?php echo number_format($row['total_amount'],2); ?></td>
                        <td>
                            <?php
                            $status = $row['order_status'];
                            $badge  = 'secondary';
                            if($status == 'completed')  $badge = 'success';
                            if($status == 'pending')    $badge = 'warning text-dark';
                            if($status == 'processing') $badge = 'info text-dark';
                            if($status == 'cancelled')  $badge = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?php echo date('d M Y, h:i A', strtotime($row['order_date'])); ?>
                        </td>
                        <td>
                            <a href="edit_order.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete_order.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Are you sure you want to delete this order?')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-bag fs-3 d-block mb-2"></i>
                            No orders found. <a href="add_order.php">Add one now</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small">
            Total records: <strong><?php echo mysqli_num_rows($result); ?></strong>
        </div>
    </div>

</div>
</div>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>
