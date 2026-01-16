<?php
session_start();
include 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: blogs.php");
    exit;
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: blogs.php");
    exit;
}

$blog_id = (int)$_GET['id'];


$stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);

if ($stmt->execute()) {
   
    header("Location: blogs.php?deleted=1");
} else {
 
    header("Location: blogs.php?error=1");
}
exit;
?>