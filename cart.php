<?php
session_start();
include('../config/db.php');

// handle add to cart from POST
if(isset($_POST['add_to_cart'])){
    $food_id   = intval($_POST['food_id']);
    $food_name = trim($_POST['food_name']);
    $price     = floatval($_POST['price']);
    $qty       = 1;

    if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // if already in cart increase qty
    $found = false;
    foreach($_SESSION['cart'] as &$ci){
        if($ci['id'] == $food_id){
            $ci['qty']++;
            $found = true;
            break;
        }
    }
    if(!$found){
        $_SESSION['cart'][] = [
            'id'    => $food_id,
            'name'  => $food_name,
            'price' => $price,
            'qty'   => $qty
        ];
    }
    header("Location: cart.php");
    exit();
}

// handle update qty
if(isset($_POST['update_cart'])){
    $food_id = intval($_POST['food_id']);
    $qty     = intval($_POST['qty']);
    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as &$ci){
            if($ci['id'] == $food_id){
                if($qty <= 0){
                    // remove item
                } else {
                    $ci['qty'] = $qty;
                }
                break;
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// handle remove item
if(isset($_GET['remove'])){
    $remove_id = intval($_GET['remove']);
    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $k => $ci){
            if($ci['id'] == $remove_id){
                unset($_SESSION['cart'][$k]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// handle clear cart
if(isset($_GET['clear'])){
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}

// calculate total
$cart_total = 0;
if(isset($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $ci){
        $cart_total += $ci['price'] * $ci['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - FoodieHub</title>
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
    <h2 class="fw-bold mb-0"><i class="bi bi-cart3 me-2"></i>My Cart</h2>
    <p class="mb-0 opacity-75">Review your items before placing order</p>
</div>

<div class="container py-4">

    <?php if(empty($_SESSION['cart'])): ?>
    <!-- empty cart -->
    <div class="text-center py-5">
        <i class="bi bi-cart-x" style="font-size:80px; color:#ddd;"></i>
        <h4 class="mt-3 text-muted">Your cart is empty</h4>
        <p class="text-muted">Add some delicious items from our menu!</p>
        <a href="menu.php" class="btn btn-warning btn-lg fw-bold px-5 mt-2">
            <i class="bi bi-menu-button-wide me-2"></i>Browse Menu
        </a>
    </div>

    <?php else: ?>
    <div class="row">

        <!-- cart items -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-cart3 me-2 text-warning"></i>
                        Cart Items (<?php echo count($_SESSION['cart']); ?>)
                    </h6>
                    <a href="cart.php?clear=1"
                       onclick="return confirm('Clear all items from cart?')"
                       class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Clear Cart
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($_SESSION['cart'] as $ci): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($ci['name']); ?></strong>
                            </td>
                            <td class="text-muted">$<?php echo number_format($ci['price'], 2); ?></td>
                            <td>
                                <form method="POST" action="" class="d-flex align-items-center gap-1">
                                    <input type="hidden" name="food_id" value="<?php echo $ci['id']; ?>">
                                    <input type="number" name="qty" value="<?php echo $ci['qty']; ?>"
                                           min="1" max="20" style="width:65px;"
                                           class="form-control form-control-sm text-center">
                                    <button type="submit" name="update_cart"
                                            class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="fw-bold text-success">
                                $<?php echo number_format($ci['price'] * $ci['qty'], 2); ?>
                            </td>
                            <td>
                                <a href="cart.php?remove=<?php echo $ci['id']; ?>"
                                   onclick="return confirm('Remove this item?')"
                                   class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <a href="menu.php" class="btn btn-outline-warning">
                <i class="bi bi-arrow-left me-1"></i>Continue Shopping
            </a>
        </div>

        <!-- order summary -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-receipt me-2 text-warning"></i>Order Summary
                    </h6>
                </div>
                <div class="card-body">
                    <?php foreach($_SESSION['cart'] as $ci): ?>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted"><?php echo htmlspecialchars($ci['name']); ?> x<?php echo $ci['qty']; ?></span>
                        <span>$<?php echo number_format($ci['price'] * $ci['qty'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span style="color:#e8813a;">$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                </div>
                <div class="card-footer bg-white pb-3">
                    <a href="place_order.php" class="btn btn-warning w-100 fw-bold btn-lg">
                        <i class="bi bi-bag-check me-2"></i>Place Order
                    </a>
                </div>
            </div>
        </div>

    </div>
    <?php endif; ?>

</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
