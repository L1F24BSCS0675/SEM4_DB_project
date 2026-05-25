<?php
session_start();
include('../config/db.php');

// get all categories
$categories = mysqli_query($conn, "select * from categories where status = 1");

// filter by category if selected
$cat_filter = "";
if(isset($_GET['cat']) && !empty($_GET['cat'])){
    $cat_filter = intval($_GET['cat']);
}

// search
$search = "";
if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = trim($_GET['search']);
}

// build query
if(!empty($cat_filter) && !empty($search)){
    $sql  = "select food_items.*, categories.category_name from food_items inner join categories on food_items.category_id = categories.id where food_items.availability = 1 and food_items.category_id = ? and food_items.food_name like ? order by food_items.id desc";
    $stmt = mysqli_prepare($conn, $sql);
    $like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "is", $cat_filter, $like);
    mysqli_stmt_execute($stmt);
    $foods = mysqli_stmt_get_result($stmt);
} elseif(!empty($cat_filter)){
    $sql  = "select food_items.*, categories.category_name from food_items inner join categories on food_items.category_id = categories.id where food_items.availability = 1 and food_items.category_id = ? order by food_items.id desc";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cat_filter);
    mysqli_stmt_execute($stmt);
    $foods = mysqli_stmt_get_result($stmt);
} elseif(!empty($search)){
    $sql  = "select food_items.*, categories.category_name from food_items inner join categories on food_items.category_id = categories.id where food_items.availability = 1 and food_items.food_name like ? order by food_items.id desc";
    $stmt = mysqli_prepare($conn, $sql);
    $like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $foods = mysqli_stmt_get_result($stmt);
} else {
    $foods = mysqli_query($conn, "select food_items.*, categories.category_name from food_items inner join categories on food_items.category_id = categories.id where food_items.availability = 1 order by food_items.id desc");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - FoodieHub</title>
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
    <h2 class="fw-bold mb-0"><i class="bi bi-menu-button-wide me-2"></i>Our Menu</h2>
    <p class="mb-0 opacity-75">Choose from our wide range of delicious dishes</p>
</div>

<div class="container py-4">
    <div class="row">

        <!-- left: category filter sidebar -->
        <div class="col-md-3 mb-4">

            <!-- search bar -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <form method="GET" action="">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search food..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <?php if(!empty($cat_filter)): ?>
                            <input type="hidden" name="cat" value="<?php echo $cat_filter; ?>">
                            <?php endif; ?>
                            <button class="btn btn-warning" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- category list -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-grid me-2 text-warning"></i>Categories
                </div>
                <div class="card-body p-2">
                    <a href="menu.php"
                       class="d-block p-2 rounded mb-1 text-decoration-none fw-bold <?php echo empty($cat_filter) ? 'text-white' : 'text-dark'; ?>"
                       style="background:<?php echo empty($cat_filter) ? '#e8813a' : 'transparent'; ?>">
                        <i class="bi bi-grid me-2"></i>All Items
                    </a>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <a href="menu.php?cat=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"
                       class="d-block p-2 rounded mb-1 text-decoration-none <?php echo $cat_filter == $cat['id'] ? 'fw-bold text-white' : 'text-dark'; ?>"
                       style="background:<?php echo $cat_filter == $cat['id'] ? '#e8813a' : 'transparent'; ?>;
                              transition:0.2s;"
                       onmouseover="if(<?php echo $cat_filter == $cat['id'] ? 'false' : 'true'; ?>) this.style.background='#fff3e0'"
                       onmouseout="if(<?php echo $cat_filter == $cat['id'] ? 'false' : 'true'; ?>) this.style.background='transparent'">
                        <i class="bi bi-egg-fried me-2"></i>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- cart summary -->
            <?php
            $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
            $cart_total = 0;
            if(isset($_SESSION['cart'])){
                foreach($_SESSION['cart'] as $ci){ $cart_total += $ci['price'] * $ci['qty']; }
            }
            ?>
            <div class="card border-0 shadow-sm mt-3"
                 style="background:linear-gradient(135deg,#b5451b,#e8813a); color:white;">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-cart3 me-2"></i>Your Cart</h6>
                    <p class="mb-1 small opacity-75"><?php echo $cart_count; ?> items</p>
                    <p class="fw-bold fs-5 mb-2">$<?php echo number_format($cart_total, 2); ?></p>
                    <a href="cart.php" class="btn btn-light btn-sm w-100 fw-bold">
                        View Cart <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- right: food items grid -->
        <div class="col-md-9">

            <?php if(!empty($search)): ?>
            <div class="alert alert-info py-2 mb-3">
                <i class="bi bi-search me-2"></i>
                Search results for: <strong><?php echo htmlspecialchars($search); ?></strong>
                <a href="menu.php" class="float-end text-decoration-none">Clear</a>
            </div>
            <?php endif; ?>

            <div class="row g-3">
            <?php if(mysqli_num_rows($foods) > 0): ?>
            <?php while($item = mysqli_fetch_assoc($foods)): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 food-menu-card"
                     style="border-radius:15px; overflow:hidden; transition:0.3s;"
                     onmouseover="this.style.transform='translateY(-5px)'"
                     onmouseout="this.style.transform='translateY(0)'">

                    <!-- image -->
                    <div style="height:170px; overflow:hidden; background:#f5f5f5; position:relative;">
                        <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>"
                             onerror="this.src='../uploads/default_food.jpg'"
                             style="width:100%; height:100%; object-fit:cover;"
                             alt="<?php echo htmlspecialchars($item['food_name']); ?>">
                        <span class="badge position-absolute bottom-0 start-0 m-2"
                              style="background:#e8813a;">
                            <?php echo htmlspecialchars($item['category_name']); ?>
                        </span>
                    </div>

                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['food_name']); ?></h6>
                        <p class="text-muted small mb-2" style="font-size:12px;">
                            <?php echo substr(htmlspecialchars($item['description']), 0, 55); ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold" style="color:#e8813a; font-size:18px;">
                                $<?php echo number_format($item['price'], 2); ?>
                            </span>
                            <button class="btn btn-warning btn-sm fw-bold"
                                    onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['food_name']); ?>', <?php echo $item['price']; ?>)">
                                <i class="bi bi-cart-plus"></i> Add
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5>No food items found</h5>
                    <a href="menu.php" class="btn btn-warning mt-2">Show All Items</a>
                </div>
            </div>
            <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- toast notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="cartToast" class="toast align-items-center text-white border-0"
         style="background:#198754;" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-cart-check me-2"></i>
                <span id="toastMsg">Item added to cart!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>
