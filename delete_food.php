<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}
include('../config/db.php');

// get id from url
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage_food.php");
    exit();
}

$id = intval($_GET['id']);

// check if food exists
$check = mysqli_prepare($conn, "select id from food_items where id = ?");
mysqli_stmt_bind_param($check, "i", $id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if(mysqli_stmt_num_rows($check) == 0){
    header("Location: manage_food.php");
    exit();
}

// delete the food item
$stmt = mysqli_prepare($conn, "delete from food_items where id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: manage_food.php?msg=deleted");
exit();
?>
