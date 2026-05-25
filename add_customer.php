<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

$errors = [];

if(isset($_POST['add_customer'])){

    $customer_name = trim($_POST['customer_name']);
    $email         = trim($_POST['email']);
    $phone         = trim($_POST['phone']);
    $address       = trim($_POST['address']);

    // validation
    if(empty($customer_name)) $errors[] = "Customer name is required.";
    if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if(!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) $errors[] = "Phone must be 10-15 digits only.";

    // check duplicate email
    if(!empty($email) && empty($errors)){
        $chk = mysqli_prepare($conn, "select id from customers where email = ?");
        mysqli_stmt_bind_param($chk, "s", $email);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if(mysqli_stmt_num_rows($chk) > 0) $errors[] = "This email is already registered.";
    }

    if(empty($errors)){
        $sql  = "insert into customers (customer_name, email, phone, address) values (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $customer_name, $email, $phone, $address);
        if(mysqli_stmt_execute($stmt)){
            header("Location: manage_customers.php?msg=added");
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
    <title>Add Customer - FoodieHub</title>
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
            <h4 class="fw-bold mb-0">Add New Customer</h4>
            <small class="text-muted">Fill the form to add a new customer</small>
        </div>
        <a href="manage_customers.php" class="btn btn-outline-secondary">
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

                <div class="row">

                    <!-- customer name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control"
                               placeholder="e.g. Ahmed Ali"
                               value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>"
                               required>
                    </div>

                    <!-- email -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="e.g. ahmed@gmail.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <!-- phone -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                               placeholder="e.g. 03001234567"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        <small class="text-muted">10-15 digits only</small>
                    </div>

                    <!-- address -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <input type="text" name="address" class="form-control"
                               placeholder="e.g. Lahore, Pakistan"
                               value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    </div>

                    <!-- submit -->
                    <div class="col-md-12 mt-2">
                        <button type="submit" name="add_customer" class="btn btn-warning px-5 fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>Add Customer
                        </button>
                        <a href="manage_customers.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>
</div>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>
