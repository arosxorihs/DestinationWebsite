<?php
session_start();
include 'config.php';

// Xóa cookie remember
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    setcookie("remember_token", "", time() - 3600, "/");
    $stmt = $conn->prepare("DELETE FROM cookies WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
}

session_destroy();
header("Location: landing.php");
exit;
?>