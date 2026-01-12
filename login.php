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
        } else { // Mã hóa mật khẩu khi đăng ký
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            $stmt->execute();
            $message = "Đăng ký thành công! Hãy đăng nhập.";
        }
    }
    // Kiểm tra mật khẩu khi đăng nhập
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
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - TravelDest</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 500px; 
            margin: 50px auto; 
            padding: 20px;
            background: #ffffff; /* Nền trắng */
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
        }
        h3 {
            color: #333;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        input {
            width: 100%; 
            padding: 12px; 
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #ff6b6b;
        }
        button { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0;
            background: #ff6b6b; /* Đổi từ #28a745 sang màu đỏ */
            color: white; 
            border: none; 
            cursor: pointer;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }
        button:hover {
            background: #ff5252; /* Màu đỏ đậm hơn khi hover */
        }
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            background: #ffebee;
            color: #c62828;
            text-align: center;
        }
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        label {
            font-size: 14px;
            color: #555;
        }
        a {
            color: #ff6b6b;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Đăng Nhập</h2>
    <?php if ($message): ?>
        <p class="message <?= strpos($message, 'thành công') !== false ? 'success' : '' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="action" value="login">
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <label>
            <input type="checkbox" name="remember" style="width: auto; margin-right: 5px;"> 
            Ghi nhớ đăng nhập
        </label><br><br>
        <button type="submit">Đăng Nhập</button>
    </form>

    <h3>Hoặc Đăng Ký</h3>
    <form method="POST">
        <input type="hidden" name="action" value="register">
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng Ký</button>
    </form>

    <div class="back-link">
        <a href="landing.php">← Quay về trang chủ</a>
    </div>
</div>
</body>
</html>