<?php
session_start();

// if already logged in go to dashboard
if(isset($_SESSION['admin_id'])){
    header("Location: ../admin/dashboard.php");
    exit();
}

include('../config/db.php');

$error = "";

// when form is submitted
if(isset($_POST['login'])){

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // basic validation
    if(empty($email) || empty($password)){
        $error = "Please fill in all fields.";
    } else {

        // check user in database
        $sql    = "select * from users where email = ?";
        $stmt   = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) == 1){
            $user = mysqli_fetch_assoc($result);

            // verify password
            if(password_verify($password, $user['password'])){
                // set session
                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin_name'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];

                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                $error = "Wrong password. Please try again.";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - FoodieHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body {
            background: linear-gradient(135deg, #b5451b 0%, #e8813a 50%, #f5a623 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
        }
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #b5451b, #e8813a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .btn-login {
            background: linear-gradient(135deg, #b5451b, #e8813a);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #e8813a, #b5451b);
            color: white;
            transform: translateY(-2px);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #eee;
        }
        .form-control:focus {
            border-color: #e8813a;
            box-shadow: 0 0 0 0.2rem rgba(232,129,58,0.25);
        }
        .input-group-text {
            background: #fff3e0;
            border: 2px solid #eee;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #e8813a;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
    </style>
</head>
<body>

<div class="login-card">

    <!-- logo -->
    <div class="login-logo">
        <i class="bi bi-shop text-white fs-2"></i>
    </div>

    <h4 class="text-center fw-bold mb-1">FoodieHub</h4>
    <p class="text-center text-muted small mb-4">Admin Panel Login</p>

    <!-- error message -->
    <?php if(!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- login form -->
    <form method="POST" action="">

        <!-- email -->
        <div class="mb-3">
            <label class="form-label fw-500 small">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control"
                       placeholder="admin@gmail.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       required>
            </div>
        </div>

        <!-- password -->
        <div class="mb-4">
            <label class="form-label fw-500 small">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="passwordField"
                       class="form-control" placeholder="Enter password" required>
                <span class="input-group-text" style="border-left:none; border-radius:0 10px 10px 0; cursor:pointer; border:2px solid #eee; border-left:none;"
                      onclick="togglePassword()">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </span>
            </div>
        </div>

        <!-- submit button -->
        <button type="submit" name="login" class="btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Dashboard
        </button>

    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        <i class="bi bi-shield-lock me-1"></i>
        Secure Admin Access Only
    </p>

    <!-- default credentials hint -->
    <div class="alert alert-info mt-3 small p-2 text-center">
        <strong>Default:</strong> admin@gmail.com / Admin123
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(){
    var field = document.getElementById('passwordField');
    var icon  = document.getElementById('eyeIcon');
    if(field.type === 'password'){
        field.type = 'text';
        icon.classList.replace('bi-eye','bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash','bi-eye');
    }
}
</script>
</body>
</html>
