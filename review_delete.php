<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền xóa.");
}

$review_id       = $_GET['review_id'];
$destination_id  = $_GET['destination_id'];

// Xóa reply nếu có
$conn->query("DELETE FROM reviews WHERE parent_id = $review_id");

// Xóa bình luận cha
$conn->query("DELETE FROM reviews WHERE review_id = $review_id");

header("Location: reviews.php?destination_id=" . $destination_id);
exit;