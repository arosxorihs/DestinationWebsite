<?php
$servername = "sql100.infinityfree.com";
$username = "if0_40575788";
$password = "nhom12345678";
$dbname = "if0_40575788_destinations";
$port = 3306;

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset để tránh lỗi tiếng Việt
$conn->set_charset("utf8mb4");
?>