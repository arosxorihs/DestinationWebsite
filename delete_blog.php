<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: blogs.php");
    exit;
}

// Kiểm tra ID blog
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: blogs.php");
    exit;
}

$blog_id = (int)$_GET['id'];

// Xóa blog
$stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);

if ($stmt->execute()) {
    // Xóa thành công - quay về trang blog
    header("Location: blogs.php?deleted=1");
} else {
    // Xóa thất bại
    header("Location: blogs.php?error=1");
}
exit;
?>