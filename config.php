<?php
$servername = "sql100.infinityfree.com";
$username = "if0_40575788";
$password = "nhom12345678";
$dbname = "if0_40575788_destinations";
$port = 3306;

// Kết nối MySQL
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Set UTF8
$conn->set_charset("utf8mb4");

// Kiểm tra lỗi
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
