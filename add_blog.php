<?php
session_start();
include 'config.php';
include 'cookie.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title'] ?? '');
    $content        = trim($_POST['content'] ?? '');
    $destination_id = !empty($_POST['destination_id']) ? (int)$_POST['destination_id'] : null;

    if (empty($title) || empty($content)) {
        $message = "Tiêu đề và nội dung không được để trống!";
    } else {
       
        $stmt = $conn->prepare("
            INSERT INTO blogs 
                (title, content, user_id, destination_id) 
            VALUES 
                (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssii", $title, $content, $user_id, $destination_id);

        if ($stmt->execute()) {
            header("Location: blogs.php?success=1");
            exit;
        } else {
            $message = "Lỗi khi đăng bài: " . $stmt->error;
        }
    }
}


$dests = $conn->query("SELECT destination_id, name FROM destinations ORDER BY name");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viết Blog Du Lịch – TravelDest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <style>
        :root { --primary: #ff6b6b; --dark: #2c3e50; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); margin:0; padding:20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: var(--dark); margin-bottom: 2rem; font-size: 2.3rem; }
        .form-group { margin-bottom: 1.8rem; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        input, textarea, select {
            width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem;
        }
        textarea { min-height: 260px; resize: vertical; }
        button {
            background: var(--primary); color: white; padding: 14px 40px; border: none; border-radius: 50px;
            font-size: 1.1rem; font-weight: 600; cursor: pointer; display: block; margin: 30px auto 0;
        }
        button:hover { background: #ff5252; transform: translateY(-3px); }
        .back-link { text-align: center; margin-top: 2rem; }
        .back-link a { color: var(--primary); text-decoration: none; margin: 0 10px; font-weight: 500; }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <h2>Viết Blog Du Lịch Mới</h2>

    <?php if ($message): ?>
        <div class="error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Tiêu đề bài viết <span style="color:red;">*</span></label>
            <input type="text" name="title" placeholder="Ví dụ: Hành trình khám phá Đà Lạt 3 ngày 2 đêm" required>
        </div>

        <div class="form-group">
            <label>Nội dung bài viết <span style="color:red;">*</span></label>
            <textarea name="content" placeholder="Chia sẻ trải nghiệm, lịch trình, mẹo hay..." required></textarea>
        </div>

        <div class="form-group">
            <label>Gắn địa điểm (tùy chọn)</label>
            <select name="destination_id">
                <option value="">-- Không gắn địa điểm --</option>
                <?php while ($d = $dests->fetch_assoc()): ?>
                    <option value="<?= $d['destination_id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit">Đăng Bài Blog</button>
    </form>

    <div class="back-link">
        <a href="blogs.php">Xem tất cả blog</a> |
        <a href="landing.php">Quay lại Trang Chủ</a>
    </div>
</div>

</body>
</html>