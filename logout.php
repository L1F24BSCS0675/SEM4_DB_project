<?php
session_start();

// destroy session and redirect to login
session_unset();
session_destroy();

header("Location: ../auth/login.php");
exit();
?>
