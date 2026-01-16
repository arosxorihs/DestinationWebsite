<?php
session_start();
include 'config.php';
include 'cookie.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
    exit;
}


if (isset($_GET['logout'])) {
    header("Location: logout.php");
    exit;
}

$is_admin = ($_SESSION['role'] ?? 'user') === 'admin';
$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $dest_id = $_POST['destination_id'] ? (int)$_POST['destination_id'] : null;

    $stmt = $conn->prepare("INSERT INTO blogs (title, content, user_id, destination_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $content, $user_id, $dest_id);
    $stmt->execute();
    header("Location: blogs.php"); 
    exit;
}


$dests = $conn->query("SELECT destination_id, name FROM destinations ORDER BY name");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Blog - TravelDest</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .header a { text-decoration: none; }
        .btn { padding: 10px 20px; background: #ff6b6b; color: white; border-radius: 50px; text-decoration: none; font-weight: bold; }
        .btn:hover { background: #ff5252; }
        .btn-logout { background: #dc3545; }
        .btn-view-blogs { background: #007bff; }
        h2 { color: #333; text-align: center; }
        form { margin-top: 20px; }
        input[type="text"], textarea, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; }
        textarea { height: 200px; }
        button { padding: 12px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>! Dashboard Blog</h2>
        <div>
            <a href="blogs.php" class="btn btn-view-blogs">Xem Blog Cộng Đồng</a>
            <a href="?logout=1" class="btn btn-logout">Đăng Xuất</a>
        </div>
    </div>

    <h3>Viết Blog Du Lịch Mới</h3>
    <form method="POST">
        <input type="text" name="title" placeholder="Tiêu đề bài viết" required>
        <textarea name="content" placeholder="Nội dung blog của bạn..." required></textarea>
        <select name="destination_id">
            <option value="">-- Không gắn địa điểm --</option>
            <?php while($d = $dests->fetch_assoc()): ?>
                <option value="<?= $d['destination_id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Đăng Blog</button>
    </form>
</div>

</body>
</html>