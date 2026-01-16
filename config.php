<?php
$servername = "sql100.infinityfree.com";
$username = "if0_40575788";
$password = "nhom12345678";
$dbname = "if0_40575788_destinations";
$port = 3306;


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>