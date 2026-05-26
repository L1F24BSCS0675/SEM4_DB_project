<?php
session_start();
include('../config/db.php');

// success message after placing order
$success  = false;
$order_id = null;
if(isset($_GET['success']) && isset($_GET['order_id'])){
    $success  = true;
    $order_id = intval($_GET['order_id']);
}

// search orders by phone or name
$search  = "";
$orders  = null;
$searched = false;

if(isset($_POST['search_orders'])){
    $search   = trim($_POST['search']);
    $searched = true;

    if(!empty($search)){
        $sql  = "select orders.*, customers.customer_name, customers.phone, customers.email
                 from orders
                 inner join customers on orders.customer_id = customers.id
                 where customers.phone like ? or customers.customer_name like ?
                 order by orders.order_date desc";
        $stmt = mysqli_prepare($conn, $sql);
        $like = "%" . $search . "%";
        mysqli_stmt_bind_param($stmt, "ss", $like, $like);
        mysqli_stmt_execute($stmt);
        $orders = mysqli_stmt_get_result($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - FoodieHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<?php include('../includes/header.php'); ?>

<!-- page banner -->
<div class="page-banner py-4 text-white text-center"
     style="background:linear-gradient(135deg,#b5451b,#e8813a);">
    <h2 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Order History</h2>
    <p class="mb-0 opacity-75">Track your past orders</p>
</div>

<div class="container py-4">

    <!-- success message -->
    <?php if($success && $order_id): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4 p-4">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size:40px;"></i>
            <div>
                <h5 class="fw-bold mb-1">Order Placed Successfully! 🎉</h5>
                <p class="mb-1">Your Order ID is: <strong>#<?php echo $order_id; ?></strong></p>
                <p class="mb-0 text-muted small">
                    Our staff will contact you soon to confirm your order.
                    Thank you for choosing FoodieHub!
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- search form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-search me-2 text-warning"></i>Track Your Order
            </h6>
            <form method="POST" action="">
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">Enter your name or phone number</label>
                        <input type="text" name="search" class="form-control form-control-lg"
                               placeholder="e.g. Ahmed Ali or 03001234567"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="search_orders"
                                class="btn btn-warning btn-lg w-100 fw-bold">
                            <i class="bi bi-search me-2"></i>Find Orders
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- search results -->
    <?php if($searched): ?>

        <?php if(!empty($search) && $orders && mysqli_num_rows($orders) > 0): ?>

        <h6 class="fw-bold mb-3">
            Orders found for: <span style="color:#e8813a;">"<?php echo htmlspecialchars($search); ?>"</span>
        </h6>

        <?php while($order = mysqli_fetch_assoc($orders)):

            // get order details for this order
            $det_sql    = "select order_details.*, food_items.food_name
                           from order_details
                           inner join food_items on order_details.food_id = food_items.id
                           where order_details.order_id = " . intval($order['id']);
            $det_result = mysqli_query($conn, $det_sql);

            // status badge color
            $badge = 'secondary';
            if($order['order_status'] == 'completed')  $badge = 'success';
            if($order['order_status'] == 'pending')    $badge = 'warning text-dark';
            if($order['order_status'] == 'processing') $badge = 'info text-dark';
            if($order['order_status'] == 'cancelled')  $badge = 'danger';
        ?>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <span class="fw-bold">#Order <?php echo $order['id']; ?></span>
                    <span class="text-muted small ms-2">
                        <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?>
                    </span>
                </div>
                <span class="badge bg-<?php echo $badge; ?> px-3 py-2">
                    <?php echo ucfirst($order['order_status']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1 small">
                            <i class="bi bi-person me-1 text-warning"></i>
                            <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                        </p>
                        <p class="mb-1 small text-muted">
                            <i class="bi bi-telephone me-1"></i>
                            <?php echo htmlspecialchars($order['phone']); ?>
                        </p>
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-envelope me-1"></i>
                            <?php echo htmlspecialchars($order['email']); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Item</th>
                                    <th class="small">Qty</th>
                                    <th class="small">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($det = mysqli_fetch_assoc($det_result)): ?>
                            <tr>
                                <td class="small"><?php echo htmlspecialchars($det['food_name']); ?></td>
                                <td class="small">x<?php echo $det['quantity']; ?></td>
                                <td class="small fw-bold text-success">
                                    $<?php echo number_format($det['subtotal'], 2); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="fw-bold small">Total</td>
                                    <td class="fw-bold text-success">
                                        $<?php echo number_format($order['total_amount'], 2); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php endwhile; ?>

        <?php elseif($searched && !empty($search)): ?>
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-search fs-2 d-block mb-2"></i>
            <h6>No orders found for "<strong><?php echo htmlspecialchars($search); ?></strong>"</h6>
            <p class="mb-0 small text-muted">Make sure you enter the correct name or phone number.</p>
        </div>
        <?php endif; ?>

    <?php else: ?>
    <!-- default state - show info -->
    <div class="text-center py-5 text-muted">
        <i class="bi bi-clock-history" style="font-size:70px; color:#ddd;"></i>
        <h5 class="mt-3">Search for your orders above</h5>
        <p class="small">Enter your name or phone number to see your order history</p>
        <a href="menu.php" class="btn btn-warning fw-bold px-4 mt-2">
            <i class="bi bi-bag-plus me-2"></i>Place New Order
        </a>
    </div>
    <?php endif; ?>

</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
