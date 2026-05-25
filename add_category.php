<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

$errors = [];

if(isset($_POST['add_category'])){

    $category_name = trim($_POST['category_name']);
    $description   = trim($_POST['description']);
    $status        = isset($_POST['status']) ? 1 : 0;

    // validation
    if(empty($category_name)) $errors[] = "Category name is required.";

    // check duplicate
    if(empty($errors)){
        $chk  = mysqli_prepare($conn, "select id from categories where category_name = ?");
        mysqli_stmt_bind_param($chk, "s", $category_name);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if(mysqli_stmt_num_rows($chk) > 0){
            $errors[] = "This category already exists.";
        }
    }

    if(empty($errors)){
        $sql  = "insert into categories (category_name, description, status) values (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $category_name, $description, $status);
        if(mysqli_stmt_execute($stmt)){
            header("Location: manage_categories.php?msg=added");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - FoodieHub</title>
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
                <a href="manage_categories.php" class="nav-link active-link"><i class="bi bi-grid me-2"></i>Categories</a>
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Add New Category</h4>
            <small class="text-muted">Create a new food category</small>
        </div>
        <a href="manage_categories.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if(!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix these errors:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach($errors as $e): ?>
            <li><?php echo $e; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="">

                <!-- category name -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="category_name" class="form-control"
                           placeholder="e.g. Burgers"
                           value="<?php echo isset($_POST['category_name']) ? htmlspecialchars($_POST['category_name']) : ''; ?>"
                           required>
                </div>

                <!-- description -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Short description of this category..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <!-- status -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status"
                               id="statusSwitch"
                               <?php echo (!isset($_POST['add_category']) || isset($_POST['status'])) ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="statusSwitch">
                            Active (visible on menu)
                        </label>
                    </div>
                </div>

                <button type="submit" name="add_category" class="btn btn-warning px-5 fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>Add Category
                </button>
                <a href="manage_categories.php" class="btn btn-outline-secondary ms-2">Cancel</a>

            </form>
        </div>
    </div>

</div>
</div>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>
