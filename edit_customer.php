<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage_customers.php");
    exit();
}

$id = intval($_GET['id']);

// get customer
$stmt = mysqli_prepare($conn, "select * from customers where id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    header("Location: manage_customers.php");
    exit();
}
$customer = mysqli_fetch_assoc($result);

$errors = [];

if(isset($_POST['update_customer'])){

    $customer_name = trim($_POST['customer_name']);
    $email         = trim($_POST['email']);
    $phone         = trim($_POST['phone']);
    $address       = trim($_POST['address']);

    // validation
    if(empty($customer_name)) $errors[] = "Customer name is required.";
    if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if(!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) $errors[] = "Phone must be 10-15 digits only.";

    // check duplicate email excluding current
    if(!empty($email) && empty($errors)){
        $chk = mysqli_prepare($conn, "select id from customers where email = ? and id != ?");
        mysqli_stmt_bind_param($chk, "si", $email, $id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if(mysqli_stmt_num_rows($chk) > 0) $errors[] = "This email is already used by another customer.";
    }

    if(empty($errors)){
        $sql  = "update customers set customer_name=?, email=?, phone=?, address=? where id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $customer_name, $email, $phone, $address, $id);
        if(mysqli_stmt_execute($stmt)){
            header("Location: manage_customers.php?msg=updated");
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
    <title>Edit Customer - FoodieHub</title>
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
            <h4 class="fw-bold mb-0">Edit Customer</h4>
            <small class="text-muted">Update customer information</small>
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

                    <!-- name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control"
                               value="<?php echo htmlspecialchars(isset($_POST['customer_name']) ? $_POST['customer_name'] : $customer['customer_name']); ?>"
                               required>
                    </div>

                    <!-- email -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : $customer['email']); ?>">
                    </div>

                    <!-- phone -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?php echo htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : $customer['phone']); ?>">
                        <small class="text-muted">10-15 digits only</small>
                    </div>

                    <!-- address -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <input type="text" name="address" class="form-control"
                               value="<?php echo htmlspecialchars(isset($_POST['address']) ? $_POST['address'] : $customer['address']); ?>">
                    </div>

                    <!-- submit -->
                    <div class="col-md-12 mt-2">
                        <button type="submit" name="update_customer" class="btn btn-primary px-5 fw-bold">
                            <i class="bi bi-save me-2"></i>Update Customer
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
