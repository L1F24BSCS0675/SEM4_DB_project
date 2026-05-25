<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

// get all customers for dropdown
$customers = mysqli_query($conn, "select * from customers order by customer_name");

// get all food items for dropdown
$foods = mysqli_query($conn, "select food_items.*, categories.category_name from food_items inner join categories on food_items.category_id = categories.id where availability = 1 order by food_name");

$errors = [];

if(isset($_POST['add_order'])){

    $customer_id  = trim($_POST['customer_id']);
    $order_status = trim($_POST['order_status']);
    $food_ids     = isset($_POST['food_id'])   ? $_POST['food_id']   : [];
    $quantities   = isset($_POST['quantity'])  ? $_POST['quantity']  : [];

    // validation
    if(empty($customer_id))      $errors[] = "Please select a customer.";
    if(empty($food_ids))         $errors[] = "Please add at least one food item.";

    if(empty($errors)){

        // calculate total
        $total = 0;
        foreach($food_ids as $i => $fid){
            $qty = intval($quantities[$i]);
            if($qty < 1) continue;
            $fp  = mysqli_query($conn, "select price from food_items where id = " . intval($fid));
            $fp  = mysqli_fetch_assoc($fp);
            $total += $fp['price'] * $qty;
        }

        // insert order
        $sql  = "insert into orders (customer_id, total_amount, order_status) values (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ids", $customer_id, $total, $order_status);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        // insert order details
        foreach($food_ids as $i => $fid){
            $fid = intval($fid);
            $qty = intval($quantities[$i]);
            if($qty < 1) continue;

            $fp  = mysqli_query($conn, "select price from food_items where id = $fid");
            $fp  = mysqli_fetch_assoc($fp);
            $price    = $fp['price'];
            $subtotal = $price * $qty;

            $ds   = mysqli_prepare($conn, "insert into order_details (order_id, food_id, quantity, price, subtotal) values (?,?,?,?,?)");
            mysqli_stmt_bind_param($ds, "iiidd", $order_id, $fid, $qty, $price, $subtotal);
            mysqli_stmt_execute($ds);
        }

        header("Location: manage_orders.php?msg=added");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Order - FoodieHub</title>
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
            <h4 class="fw-bold mb-0">Add New Order</h4>
            <small class="text-muted">Create a new order for a customer</small>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="">

                <div class="row">

                    <!-- customer -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Select Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                            <?php while($c = mysqli_fetch_assoc($customers)): ?>
                            <option value="<?php echo $c['id']; ?>"
                                <?php echo (isset($_POST['customer_id']) && $_POST['customer_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['customer_name']); ?> (<?php echo $c['phone']; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- order status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Order Status</label>
                        <select name="order_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                </div>

                <!-- food items section -->
                <h6 class="fw-bold mt-2 mb-3">
                    <i class="bi bi-egg-fried me-2 text-warning"></i>Add Food Items
                </h6>

                <div id="food_rows">
                    <!-- first row -->
                    <div class="row food-row mb-2" id="row_1">
                        <div class="col-md-7">
                            <select name="food_id[]" class="form-select food-select" onchange="updatePrice(this)">
                                <option value="">-- Select Food Item --</option>
                                <?php
                                mysqli_data_seek($foods, 0);
                                while($f = mysqli_fetch_assoc($foods)):
                                ?>
                                <option value="<?php echo $f['id']; ?>" data-price="<?php echo $f['price']; ?>">
                                    <?php echo htmlspecialchars($f['food_name']); ?> - $<?php echo number_format($f['price'],2); ?>
                                    (<?php echo $f['category_name']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="quantity[]" class="form-control qty-input"
                                   placeholder="Qty" min="1" value="1" onchange="calcTotal()">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- add more button -->
                <button type="button" class="btn btn-outline-warning btn-sm mb-3" onclick="addFoodRow()">
                    <i class="bi bi-plus-circle me-1"></i>Add Another Item
                </button>

                <!-- total display -->
                <div class="alert alert-light border mb-3">
                    <strong>Estimated Total: </strong>
                    <span id="total_display" class="fw-bold text-success fs-5">$0.00</span>
                </div>

                <!-- submit -->
                <button type="submit" name="add_order" class="btn btn-warning px-5 fw-bold">
                    <i class="bi bi-bag-check me-2"></i>Place Order
                </button>
                <a href="manage_orders.php" class="btn btn-outline-secondary ms-2">Cancel</a>

            </form>
        </div>
    </div>

</div>
</div>
</div>

<?php include('../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// food options html for cloning
var foodOptions = document.querySelector('.food-select').innerHTML;
var rowCount = 1;

function addFoodRow(){
    rowCount++;
    var div = document.createElement('div');
    div.className = 'row food-row mb-2';
    div.id = 'row_' + rowCount;
    div.innerHTML = `
        <div class="col-md-7">
            <select name="food_id[]" class="form-select food-select" onchange="updatePrice(this)">
                ${foodOptions}
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" name="quantity[]" class="form-control qty-input"
                   placeholder="Qty" min="1" value="1" onchange="calcTotal()">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>`;
    document.getElementById('food_rows').appendChild(div);
}

function removeRow(btn){
    var row = btn.closest('.food-row');
    row.remove();
    calcTotal();
}

function updatePrice(select){
    calcTotal();
}

function calcTotal(){
    var total = 0;
    var rows  = document.querySelectorAll('.food-row');
    rows.forEach(function(row){
        var sel = row.querySelector('.food-select');
        var qty = row.querySelector('.qty-input');
        if(sel.value){
            var opt   = sel.options[sel.selectedIndex];
            var price = parseFloat(opt.getAttribute('data-price')) || 0;
            var q     = parseInt(qty.value) || 1;
            total    += price * q;
        }
    });
    document.getElementById('total_display').textContent = '$' + total.toFixed(2);
}
</script>
</body>
</html>
