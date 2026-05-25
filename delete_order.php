<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage_orders.php");
    exit();
}

$id = intval($_GET['id']);

// check exists
$chk = mysqli_prepare($conn, "select id from orders where id = ?");
mysqli_stmt_bind_param($chk, "i", $id);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);

if(mysqli_stmt_num_rows($chk) == 0){
    header("Location: manage_orders.php");
    exit();
}

// delete order details first then order
$stmt1 = mysqli_prepare($conn, "delete from order_details where order_id = ?");
mysqli_stmt_bind_param($stmt1, "i", $id);
mysqli_stmt_execute($stmt1);

$stmt2 = mysqli_prepare($conn, "delete from orders where id = ?");
mysqli_stmt_bind_param($stmt2, "i", $id);
mysqli_stmt_execute($stmt2);

header("Location: manage_orders.php?msg=deleted");
exit();
?>
