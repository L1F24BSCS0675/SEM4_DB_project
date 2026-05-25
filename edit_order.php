<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage_orders.php");
    exit();
}

$id = intval($_GET['id']);

// get order
$stmt = mysqli_prepare($conn, "select orders.*, customers.customer_name from orders inner join customers on orders.customer_id = customers.id where orders.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    header("Location: manage_orders.php");
    exit();
}
$order = mysqli_fetch_assoc($result);

// get order details
$details = mysqli_query($conn,
    "select order_details.*, food_items.food_name, food_items.price as unit_price
     from order_details
     inner join food_items on order_details.food_id = food_items.id
     where order_details.order_id = $id");

// get all customers
$customers = mysqli_query($conn, "select * from customers order by customer_name");

$errors = [];

if(isset($_POST['update_order'])){

    $customer_id  = intval($_POST['customer_id']);
    $order_status = trim($_POST['order_status']);
    $total_amount = floatval($_POST['total_amount']);

    if(empty($customer_id))  $errors[] = "Please select a customer.";
    if(empty($order_status)) $errors[] = "Please select order status.";

    if(empty($errors)){
        $sql  = "update orders set customer_id=?, order_status=?, total_amount=? where id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isdi", $customer_id, $order_status, $total_amount, $id);
        if(mysqli_stmt_execute($stmt)){
            header("Location: manage_orders.php?msg=updated");
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
    <title>Edit Order - FoodieHub</title>
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
            <h4 class="fw-bold mb-0">Edit Order #<?php echo $id; ?></h4>
            <small class="text-muted">Update order details and status</small>
        </div>
        <a href="manage_orders.php" class="btn btn-outline-secondary">
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

    <div class="row">

        <!-- edit form -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-warning"></i>Edit Order Info</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">

                        <!-- customer -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">-- Select Customer --</option>
                                <?php while($c = mysqli_fetch_assoc($customers)):
                                    $sel = ($c['id'] == (isset($_POST['customer_id']) ? $_POST['customer_id'] : $order['customer_id'])) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $sel; ?>>
                                    <?php echo htmlspecialchars($c['customer_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- order status -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Order Status <span class="text-danger">*</span></label>
                            <select name="order_status" class="form-select" required>
                                <?php
                                $statuses = ['pending','processing','completed','cancelled'];
                                foreach($statuses as $s):
                                    $sel = ($s == (isset($_POST['order_status']) ? $_POST['order_status'] : $order['order_status'])) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $s; ?>" <?php echo $sel; ?>>
                                    <?php echo ucfirst($s); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- total amount -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Total Amount ($)</label>
                            <input type="number" name="total_amount" class="form-control"
                                   step="0.01" min="0"
                                   value="<?php echo isset($_POST['total_amount']) ? htmlspecialchars($_POST['total_amount']) : $order['total_amount']; ?>">
                        </div>

                        <button type="submit" name="update_order" class="btn btn-primary px-5 fw-bold">
                            <i class="bi bi-save me-2"></i>Update Order
                        </button>
                        <a href="manage_orders.php" class="btn btn-outline-secondary ms-2">Cancel</a>

                    </form>
                </div>
            </div>
        </div>

        <!-- order details summary -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-warning"></i>Order Items</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Food Item</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($details) > 0): ?>
                        <?php while($d = mysqli_fetch_assoc($details)): ?>
                        <tr>
                            <td class="small"><?php echo htmlspecialchars($d['food_name']); ?></td>
                            <td class="small"><?php echo $d['quantity']; ?></td>
                            <td class="small fw-bold text-success">$<?php echo number_format($d['subtotal'],2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted small py-3">No items found</td></tr>
                        <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="fw-bold">Total</td>
                                <td class="fw-bold text-success">$<?php echo number_format($order['total_amount'],2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer bg-white small text-muted">
                    Order Date: <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>
