<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

// search functionality
$search = "";
if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = trim($_GET['search']);
}

// get all food items with category name
if(!empty($search)){
    $sql = "select food_items.*, categories.category_name
            from food_items
            inner join categories on food_items.category_id = categories.id
            where food_items.food_name like ?
            order by food_items.id desc";
    $stmt = mysqli_prepare($conn, $sql);
    $like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql    = "select food_items.*, categories.category_name
               from food_items
               inner join categories on food_items.category_id = categories.id
               order by food_items.id desc";
    $result = mysqli_query($conn, $sql);
}

// success or error message
$msg = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'added')   $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Food item added successfully!</div>';
    if($_GET['msg'] == 'updated') $msg = '<div class="alert alert-info"><i class="bi bi-check-circle me-2"></i>Food item updated successfully!</div>';
    if($_GET['msg'] == 'deleted') $msg = '<div class="alert alert-warning"><i class="bi bi-trash me-2"></i>Food item deleted successfully!</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Food Items - FoodieHub</title>
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
                <a href="manage_food.php" class="nav-link active-link"><i class="bi bi-egg-fried me-2"></i>Food Items</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_categories.php" class="nav-link side-link"><i class="bi bi-grid me-2"></i>Categories</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_orders.php" class="nav-link side-link"><i class="bi bi-bag-check me-2"></i>Orders</a>
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

    <!-- heading -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Food Items</h4>
            <small class="text-muted">Manage all food items on the menu</small>
        </div>
        <a href="add_food.php" class="btn btn-warning fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Add New Food
        </a>
    </div>

    <!-- messages -->
    <?php echo $msg; ?>

    <!-- search bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="" class="d-flex gap-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Search food by name..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-search"></i>
                </button>
                <?php if(!empty($search)): ?>
                <a href="manage_food.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- food items table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Image</th>
                            <th>Food Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td>
                            <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>"
                                 onerror="this.src='../uploads/default_food.jpg'"
                                 width="55" height="55"
                                 style="border-radius:10px; object-fit:cover;"
                                 alt="food">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['food_name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo substr(htmlspecialchars($row['description']),0,40); ?>...</small>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                <?php echo htmlspecialchars($row['category_name']); ?>
                            </span>
                        </td>
                        <td class="fw-bold text-success">$<?php echo number_format($row['price'],2); ?></td>
                        <td>
                            <?php if($row['availability'] == 1): ?>
                            <span class="badge bg-success">Available</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Not Available</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_food.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete_food.php?id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Are you sure you want to delete this food item?')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No food items found.
                            <a href="add_food.php">Add one now</a>
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
