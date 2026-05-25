<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

// get all categories for dropdown
$cats = mysqli_query($conn, "select * from categories where status = 1 order by category_name");

$errors = [];
$success = "";

// when form submitted
if(isset($_POST['add_food'])){

    $food_name    = trim($_POST['food_name']);
    $category_id  = trim($_POST['category_id']);
    $description  = trim($_POST['description']);
    $price        = trim($_POST['price']);
    $availability = isset($_POST['availability']) ? 1 : 0;

    // validation
    if(empty($food_name))   $errors[] = "Food name is required.";
    if(empty($category_id)) $errors[] = "Please select a category.";
    if(empty($price))       $errors[] = "Price is required.";
    if(!is_numeric($price) || $price < 0) $errors[] = "Price must be a valid number.";

    // handle image upload
    $image = "default_food.jpg";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $image = time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
        } else {
            $errors[] = "Only jpg, jpeg, png, gif, webp images allowed.";
        }
    }

    // if no errors insert into database
    if(empty($errors)){
        $sql  = "insert into food_items (category_id, food_name, description, price, image, availability)
                 values (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issdsi", $category_id, $food_name, $description, $price, $image, $availability);

        if(mysqli_stmt_execute($stmt)){
            header("Location: manage_food.php?msg=added");
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
    <title>Add Food Item - FoodieHub</title>
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
                <a href="manage_food.php" class="nav-link active-link"><i class="bi bi-egg-fried me-2"></i>Food Items</a>
            </li>
            <li class="nav-item mb-1">
                <a href="manage_categories.php" class="nav-link side-link"><i class="bi bi-grid me-2"></i>Categories</a>
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
            <h4 class="fw-bold mb-0">Add New Food Item</h4>
            <small class="text-muted">Fill the form to add a new food item to the menu</small>
        </div>
        <a href="manage_food.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <!-- error messages -->
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
            <form method="POST" action="" enctype="multipart/form-data">

                <div class="row">

                    <!-- food name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Food Name <span class="text-danger">*</span></label>
                        <input type="text" name="food_name" class="form-control"
                               placeholder="e.g. Chicken Burger"
                               value="<?php echo isset($_POST['food_name']) ? htmlspecialchars($_POST['food_name']) : ''; ?>"
                               required>
                    </div>

                    <!-- category -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php while($cat = mysqli_fetch_assoc($cats)): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- price -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Price ($) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control"
                               placeholder="e.g. 9.99" step="0.01" min="0"
                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                               required>
                    </div>

                    <!-- image -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Food Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*"
                               onchange="previewImage(this)">
                        <small class="text-muted">Accepted: jpg, jpeg, png, gif, webp</small>
                        <div class="mt-2">
                            <img id="imgPreview" src="#" alt="preview"
                                 style="display:none; width:80px; height:80px; object-fit:cover; border-radius:10px; border:2px solid #eee;">
                        </div>
                    </div>

                    <!-- description -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Describe the food item..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <!-- availability -->
                    <div class="col-md-12 mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="availability"
                                   id="availSwitch"
                                   <?php echo (!isset($_POST['add_food']) || isset($_POST['availability'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="availSwitch">
                                Available on Menu
                            </label>
                        </div>
                    </div>

                    <!-- submit button -->
                    <div class="col-md-12">
                        <button type="submit" name="add_food" class="btn btn-warning px-5 fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>Add Food Item
                        </button>
                        <a href="manage_food.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>
</div>
</div>

<?php include('../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImage(input){
    if(input.files && input.files[0]){
        var reader = new FileReader();
        reader.onload = function(e){
            var img = document.getElementById('imgPreview');
            img.src = e.target.result;
            img.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
