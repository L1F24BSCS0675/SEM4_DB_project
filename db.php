<?php
// database connection file
// Restaurant Management System

$host     = "localhost";
$dbname   = "restaurant_db";
$username = "root";
$password = "";

// connect to mysql
$conn = mysqli_connect($host, $username, $password, $dbname);

// check connection
if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

// set character set
mysqli_set_charset($conn, "utf8");
?>
