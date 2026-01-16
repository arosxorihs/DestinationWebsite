<?php


if (isset($_SESSION['user_id'])) {
    return; 
}

if (!isset($_COOKIE['remember_token'])) {
    return; 
}

$token = $_COOKIE['remember_token'];

$stmt = $conn->prepare("
    SELECT u.user_id, u.username, u.role 
    FROM cookies c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.token = ? AND c.expires_at > NOW()
    LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];


    setcookie("remember_token", $token, time() + 86400 * 30, "/", "", false, true);
}
?>