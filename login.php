<?php
session_start();
include 'config.php';
include 'cookie.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($_POST['action'] === "register") {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "Tên đăng nhập đã tồn tại!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            $stmt->execute();
            $message = "Đăng ký thành công! Hãy đăng nhập.";
        }
    }

    if ($_POST['action'] === "login") {
        $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';

                // Remember me
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    $stmt2 = $conn->prepare("INSERT INTO cookies (user_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt2->bind_param("iss", $user['user_id'], $token, $expires);
                    $stmt2->execute();
                    setcookie("remember_token", $token, time() + 86400*30, "/", "", false, true);
                }

                // Redirect dựa trên role
                if ($user['role'] === 'admin') {
                    header("Location: index.php"); // Admin → quản lý
                } else {
                    header("Location: landing.php"); // User → landing
                }
                exit;
            } else {
                $message = "Sai mật khẩu!";
            }
        } else {
            $message = "Không tìm thấy tài khoản!";
        }
    }
}
?>

<!DOCTYPE html>
<html><head><title>Đăng Nhập / Đăng Ký</title>
<style>
    body { font-family: Arial; max-width: 500px; margin: 50px auto; padding: 20px; }
    input, button { width: 100%; padding: 10px; margin: 10px 0; }
    button { background: #28a745; color: white; border: none; cursor: pointer; }
</style>
</head>
<body>
<h2>Đăng Nhập / Đăng Ký</h2>
<?php if ($message): ?><p style="color:red;"><?=htmlspecialchars($message)?></p><?php endif; ?>

<form method="POST">
    <input type="hidden" name="action" value="login">
    <input type="text" name="username" placeholder="Tên đăng nhập" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <label><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label><br><br>
    <button type="submit">Đăng Nhập</button>
</form>

<h3>Hoặc Đăng Ký</h3>
<form method="POST">
    <input type="hidden" name="action" value="register">
    <input type="text" name="username" placeholder="Tên đăng nhập" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <button type="submit">Đăng Ký</button>
</form>

<a href="landing.php">← Quay về trang chủ</a>
</body></html>