<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage_customers.php");
    exit();
}

$id = intval($_GET['id']);

// check exists
$chk = mysqli_prepare($conn, "select id from customers where id = ?");
mysqli_stmt_bind_param($chk, "i", $id);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);

if(mysqli_stmt_num_rows($chk) == 0){
    header("Location: manage_customers.php");
    exit();
}

// delete customer
$stmt = mysqli_prepare($conn, "delete from customers where id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: manage_customers.php?msg=deleted");
exit();
?>
