<?php
session_start();
include('../config/db.php');

// if cart is empty go back to menu
if(empty($_SESSION['cart'])){
    header("Location: menu.php");
    exit();
}

// calculate total
$cart_total = 0;
foreach($_SESSION['cart'] as $ci){
    $cart_total += $ci['price'] * $ci['qty'];
}

$errors  = [];
$success = false;

if(isset($_POST['place_order'])){

    $customer_name = trim($_POST['customer_name']);
    $email         = trim($_POST['email']);
    $phone         = trim($_POST['phone']);
    $address       = trim($_POST['address']);

    // validation
    if(empty($customer_name))  $errors[] = "Your name is required.";
    if(empty($phone))          $errors[] = "Phone number is required.";
    if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if(!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone))    $errors[] = "Phone must be 10-15 digits only.";
    if(empty($address))        $errors[] = "Delivery address is required.";

    if(empty($errors)){

        // check if customer exists by email or insert new
        $customer_id = null;

        if(!empty($email)){
            $chk = mysqli_prepare($conn, "select id from customers where email = ?");
            mysqli_stmt_bind_param($chk, "s", $email);
            mysqli_stmt_execute($chk);
            $chk_result = mysqli_stmt_get_result($chk);
            if(mysqli_num_rows($chk_result) > 0){
                $existing    = mysqli_fetch_assoc($chk_result);
                $customer_id = $existing['id'];
                // update info
                $upd = mysqli_prepare($conn, "update customers set customer_name=?, phone=?, address=? where id=?");
                mysqli_stmt_bind_param($upd, "sssi", $customer_name, $phone, $address, $customer_id);
                mysqli_stmt_execute($upd);
            }
        }

        if(!$customer_id){
            // insert new customer
            $ins = mysqli_prepare($conn, "insert into customers (customer_name, email, phone, address) values (?,?,?,?)");
            mysqli_stmt_bind_param($ins, "ssss", $customer_name, $email, $phone, $address);
            mysqli_stmt_execute($ins);
            $customer_id = mysqli_insert_id($conn);
        }

        // insert order
        $ord = mysqli_prepare($conn, "insert into orders (customer_id, total_amount, order_status) values (?,?,'pending')");
        mysqli_stmt_bind_param($ord, "id", $customer_id, $cart_total);
        mysqli_stmt_execute($ord);
        $order_id = mysqli_insert_id($conn);

        // insert order details
        foreach($_SESSION['cart'] as $ci){
            $fid      = intval($ci['id']);
            $qty      = intval($ci['qty']);
            $price    = floatval($ci['price']);
            $subtotal = $price * $qty;

            $det = mysqli_prepare($conn, "insert into order_details (order_id, food_id, quantity, price, subtotal) values (?,?,?,?,?)");
            mysqli_stmt_bind_param($det, "iiidd", $order_id, $fid, $qty, $price, $subtotal);
            mysqli_stmt_execute($det);
        }

        // clear cart
        $_SESSION['cart']      = [];
        $_SESSION['last_order'] = $order_id;

        header("Location: order_history.php?success=1&order_id=" . $order_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - FoodieHub</title>
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
    <h2 class="fw-bold mb-0"><i class="bi bi-bag-check me-2"></i>Place Your Order</h2>
    <p class="mb-0 opacity-75">Fill in your details to complete the order</p>
</div>

<div class="container py-4">
    <div class="row">

        <!-- order form -->
        <div class="col-md-7">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-person-fill me-2 text-warning"></i>Your Details
                    </h6>
                </div>
                <div class="card-body p-4">

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

                    <form method="POST" action="">

                        <div class="row">

                            <!-- name -->
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
                                <small class="text-muted">For order confirmation (optional)</small>
                            </div>

                            <!-- phone -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control"
                                       placeholder="e.g. 03001234567"
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                       required>
                                <small class="text-muted">10-15 digits</small>
                            </div>

                            <!-- address -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Delivery Address <span class="text-danger">*</span></label>
                                <input type="text" name="address" class="form-control"
                                       placeholder="e.g. House 12, Main Street, Lahore"
                                       value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                                       required>
                            </div>

                        </div>

                        <!-- order note -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Special Instructions</label>
                            <textarea name="note" class="form-control" rows="2"
                                      placeholder="Any special requests or notes for your order..."><?php echo isset($_POST['note']) ? htmlspecialchars($_POST['note']) : ''; ?></textarea>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-warning btn-lg w-100 fw-bold">
                            <i class="bi bi-bag-check me-2"></i>
                            Confirm Order — $<?php echo number_format($cart_total, 2); ?>
                        </button>

                    </form>
                </div>
            </div>

        </div>

        <!-- order summary -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-receipt me-2 text-warning"></i>Your Order
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($_SESSION['cart'] as $ci): ?>
                        <tr>
                            <td class="small fw-bold"><?php echo htmlspecialchars($ci['name']); ?></td>
                            <td class="small text-muted">x<?php echo $ci['qty']; ?></td>
                            <td class="small text-success fw-bold">
                                $<?php echo number_format($ci['price'] * $ci['qty'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="fw-bold">Total Amount</td>
                                <td class="fw-bold text-success fs-6">
                                    $<?php echo number_format($cart_total, 2); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <a href="cart.php" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-pencil me-1"></i>Edit Cart
                    </a>
                </div>
            </div>

            <!-- payment note -->
            <div class="alert alert-info mt-3 small">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Payment on Delivery.</strong> Our staff will contact you to confirm your order.
            </div>
        </div>

    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
