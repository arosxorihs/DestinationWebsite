<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "travel_db";  // ⬅ đúng tên database bạn tạo

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset để tránh lỗi tiếng Việt
$conn->set_charset("utf8mb4");
?>
