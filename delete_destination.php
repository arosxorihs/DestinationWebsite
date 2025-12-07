<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("ID không tồn tại!");
}

$id = intval($_GET['id']);

// XÓA ĐÚNG CỘT: destination_id
$query = "DELETE FROM destinations WHERE destination_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php");
exit;
?>
