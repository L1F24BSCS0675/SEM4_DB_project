<?php
session_start();
include('../config/db.php');

// get categories
$categories = mysqli_query($conn, "select * from categories where status = 1");

// get featured food items (latest 6)
$featured = mysqli_query($conn,
    "select food_items.*, categories.category_name
     from food_items
     inner join categories on food_items.category_id = categories.id
     where food_items.availability = 1
     order by food_items.id desc limit 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodieHub - Best Restaurant in Town</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<?php include('../includes/header.php'); ?>

<!-- HERO SECTION -->
<section class="hero-section d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6" data-aos="fade-right">
                <span class="badge bg-warning text-dark px-3 py-2 mb-3 fs-6">
                    🍽️ Welcome to FoodieHub
                </span>
                <h1 class="display-4 fw-bold text-white mb-3">
                    Delicious Food <br>
                    <span style="color:#f5a623;">Delivered Fast</span>
                </h1>
                <p class="text-white-50 fs-5 mb-4">
                    Explore our wide range of mouth-watering dishes. 
                    From juicy burgers to wood-fired pizzas — we have it all.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="menu.php" class="btn btn-warning btn-lg fw-bold px-4">
                        <i class="bi bi-menu-button-wide me-2"></i>View Menu
                    </a>
                    <a href="cart.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-cart3 me-2"></i>My Cart
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge bg-warning text-dark ms-1"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
            <div class="col-md-6 text-center d-none d-md-block">
                <div class="hero-food-circle">
                    <i class="bi bi-egg-fried" style="font-size:180px; color:rgba(255,255,255,0.15);"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES STRIP -->
<section class="py-4" style="background:#fff8f0;">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-md-3 col-6">
                <div class="feature-box p-3">
                    <i class="bi bi-lightning-charge-fill text-warning fs-2 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-0">Fast Delivery</h6>
                    <small class="text-muted">30 minutes or less</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-box p-3">
                    <i class="bi bi-star-fill text-warning fs-2 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-0">Top Quality</h6>
                    <small class="text-muted">Fresh ingredients daily</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-box p-3">
                    <i class="bi bi-shield-check-fill text-warning fs-2 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-0">100% Hygienic</h6>
                    <small class="text-muted">Safe and clean kitchen</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-box p-3">
                    <i class="bi bi-headset text-warning fs-2 mb-2 d-block"></i>
                    <h6 class="fw-bold mb-0">24/7 Support</h6>
                    <small class="text-muted">Always here to help</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES SECTION -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Our <span style="color:#e8813a;">Categories</span></h2>
            <p class="text-muted">Browse by your favourite food type</p>
        </div>
        <div class="row g-3 justify-content-center">
        <?php
        $cat_icons = [
            'Burgers'  => 'bi-circle-fill',
            'Pizza'    => 'bi-circle-fill',
            'Pasta'    => 'bi-circle-fill',
            'Drinks'   => 'bi-cup-straw',
            'Desserts' => 'bi-cake2',
            'Starters' => 'bi-circle-fill',
        ];
        $cat_colors = ['#ff6b35','#e8813a','#f5a623','#4fc3f7','#ce93d8','#a5d6a7'];
        $ci = 0;
        while($cat = mysqli_fetch_assoc($categories)):
            $color = $cat_colors[$ci % count($cat_colors)];
            $ci++;
        ?>
        <div class="col-md-2 col-4">
            <a href="menu.php?cat=<?php echo $cat['id']; ?>" class="text-decoration-none">
                <div class="category-card text-center p-3 rounded-3 shadow-sm"
                     style="background:<?php echo $color; ?>15; border:2px solid <?php echo $color; ?>30; transition:0.3s;"
                     onmouseover="this.style.transform='translateY(-5px)'"
                     onmouseout="this.style.transform='translateY(0)'">
                    <div class="cat-icon mb-2" style="width:55px;height:55px;background:<?php echo $color; ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                        <i class="bi bi-egg-fried text-white fs-4"></i>
                    </div>
                    <p class="fw-bold mb-0 small" style="color:<?php echo $color; ?>;">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </p>
                </div>
            </a>
        </div>
        <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- FEATURED FOOD SECTION -->
<section class="py-5" style="background:#f9f9f9;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Featured <span style="color:#e8813a;">Dishes</span></h2>
                <p class="text-muted mb-0">Our most popular items</p>
            </div>
            <a href="menu.php" class="btn btn-outline-warning fw-bold">
                View All Menu <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
        <?php while($item = mysqli_fetch_assoc($featured)): ?>
        <div class="col-md-4 col-sm-6">
            <div class="food-card card border-0 shadow-sm h-100"
                 style="border-radius:15px; overflow:hidden; transition:0.3s;"
                 onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.15)'"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''">

                <!-- food image -->
                <div style="position:relative; overflow:hidden; height:200px; background:#f5f5f5;">
                    <img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>"
                         onerror="this.src='../uploads/default_food.jpg'"
                         style="width:100%; height:100%; object-fit:cover;"
                         alt="<?php echo htmlspecialchars($item['food_name']); ?>">
                    <span class="badge position-absolute top-0 end-0 m-2"
                          style="background:#e8813a;">
                        <?php echo htmlspecialchars($item['category_name']); ?>
                    </span>
                </div>

                <div class="card-body p-3">
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['food_name']); ?></h6>
                    <p class="text-muted small mb-2">
                        <?php echo substr(htmlspecialchars($item['description']), 0, 60); ?>...
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5" style="color:#e8813a;">
                            $<?php echo number_format($item['price'], 2); ?>
                        </span>
                        <button class="btn btn-warning btn-sm fw-bold"
                                onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['food_name']); ?>', <?php echo $item['price']; ?>)">
                            <i class="bi bi-cart-plus me-1"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="py-5 text-white text-center" style="background:linear-gradient(135deg,#b5451b,#e8813a);">
    <div class="container">
        <h2 class="fw-bold mb-2">Hungry? Order Now!</h2>
        <p class="mb-4 opacity-75">Fresh food prepared with love, delivered to your table fast.</p>
        <a href="menu.php" class="btn btn-light btn-lg fw-bold px-5">
            <i class="bi bi-bag-check me-2"></i>Order Now
        </a>
    </div>
</section>

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
