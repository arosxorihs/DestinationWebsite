<?php
// cookie.php – Auto login bằng Remember me token
// Đặt file này ở thư mục gốc (cùng cấp với index.php, landing.php)

if (isset($_SESSION['user_id'])) {
    return; // Đã login rồi thì không cần check cookie nữa
}

if (!isset($_COOKIE['remember_token'])) {
    return; // Không có cookie thì thôi
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

    // Tự động đăng nhập
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    // Gia hạn cookie thêm 30 ngày
    setcookie("remember_token", $token, time() + 86400 * 30, "/", "", false, true);
}
?>