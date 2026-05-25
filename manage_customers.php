<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

// search
$search = "";
if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = trim($_GET['search']);
}

if(!empty($search)){
    $sql  = "select * from customers where customer_name like ? or email like ? or phone like ? order by id desc";
    $stmt = mysqli_prepare($conn, $sql);
    $like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "select * from customers order by id desc");
}

// messages
$msg = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'added')   $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Customer added successfully!</div>';
    if($_GET['msg'] == 'updated') $msg = '<div class="alert alert-info"><i class="bi bi-check-circle me-2"></i>Customer updated successfully!</div>';
    if($_GET['msg'] == 'deleted') $msg = '<div class="alert alert-warning"><i class="bi bi-trash me-2"></i>Customer deleted successfully!</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - FoodieHub</title>
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
                <a href="manage_orders.php" class="nav-link side-link"><i class="bi bi-bag-check me-2"></i>Orders</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_customers.php" class="nav-link active-link"><i class="bi bi-people me-2"></i>Customers</a>
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
            <h4 class="fw-bold mb-0">Customers</h4>
            <small class="text-muted">Manage all registered customers</small>
        </div>
        <a href="add_customer.php" class="btn btn-warning fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Add New Customer
        </a>
    </div>

    <?php echo $msg; ?>

    <!-- search bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by name, email or phone..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-warning px-4"><i class="bi bi-search"></i></button>
                <?php if(!empty($search)): ?>
                <a href="manage_customers.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- customers table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle">
                                    <?php echo strtoupper(substr($row['customer_name'], 0, 1)); ?>
                                </div>
                                <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                            </div>
                        </td>
                        <td class="text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td class="text-muted small"><?php echo htmlspecialchars($row['address']); ?></td>
                        <td class="text-muted small"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="edit_customer.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete_customer.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Are you sure you want to delete this customer?')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-people fs-3 d-block mb-2"></i>
                            No customers found. <a href="add_customer.php">Add one now</a>
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
