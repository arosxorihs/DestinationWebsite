<?php
include 'config.php';
include 'cookie.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Không tìm thấy địa điểm này.");
}

$dest = $result->fetch_assoc();

// Đếm số review + trung bình sao
$stats = $conn->query("
    SELECT COUNT(*) as total, ROUND(AVG(rating),1) as avg_rating 
    FROM reviews WHERE destination_id = $id
")->fetch_assoc();
$total_reviews = $stats['total'] ?? 0;
$avg_rating = $stats['avg_rating'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dest['name']) ?> – TravelDest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <style>
        :root { --primary: #ff6b6b; --dark: #2c3e50; }
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; margin:0; color:#333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

        header { background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.1); padding: 1rem 0; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: 700; color: var(--primary; }
        .nav-menu a { margin: 0 1rem; text-decoration: none; color: var(--dark); font-weight: 500; }
        .nav-menu a:hover { color: var(--primary); }
        .btn { background: var(--primary); color: white; padding: 0.6rem 1.3rem; border-radius: 50px; text-decoration: none; font-weight: 600; }

        .hero {
            background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url('<?= htmlspecialchars($dest['image_url'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=2070') ?>');
            background-size: cover; background-position: center;
            color: white; padding: 120px 20px; text-align: center; border-radius: 20px; margin: 20px 0;
        }
        .hero h1 { font-size: 3.5rem; margin: 0; }
        .hero p { font-size: 1.4rem; margin: 10px 0 0; opacity: 0.9; }

        .content { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 2rem 0; }
        .info-item strong { color: var(--primary); }
        .rating-box { text-align: center; background: #fff8ef; padding: 20px; border-radius: 12px; }
        .rating-box .stars { font-size: 2.5rem; color: #ffc107; }
        .actions { text-align: center; margin: 3rem 0; }
        .actions a { margin: 0 15px; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-weight: 600; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-secondary { background: #6c757d; color: white; }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="container nav">
        <div class="logo">TravelDest</div>
        <div class="nav-menu">
            <a href="landing.php">Trang Chủ</a>
            <a href="destinations.php">Điểm Đến</a>
            <a href="all_reviews.php">Reviews</a>
            <a href="blogs.php">Blog</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn">Đăng Nhập</a>
            <?php else: ?>
                <span>Xin chào, <?=htmlspecialchars($_SESSION['username'])?></span>
                <a href="logout.php" class="btn" style="background:#dc3545">Đăng Xuất</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
    <div class="hero">
        <h1><?= htmlspecialchars($dest['name']) ?></h1>
        <p><?= htmlspecialchars($dest['province'] ? $dest['province'].', ' : '') ?><?= htmlspecialchars($dest['country']) ?></p>
    </div>

    <div class="content">
        <div class="info-grid">
            <div>
                <p><strong>Quốc gia:</strong> <?= htmlspecialchars($dest['country']) ?></p>
                <?php if ($dest['province']): ?>
                    <p><strong>Tỉnh/Thành:</strong> <?= htmlspecialchars($dest['province']) ?></p>
                <?php endif; ?>
                <?php if ($dest['category']): ?>
                    <p><strong>Danh mục:</strong> <?= htmlspecialchars($dest['category']) ?></p>
                <?php endif; ?>
            </div>
            <div class="rating-box">
                <div class="stars"><?= str_repeat('★', round($avg_rating)) ?><?= str_repeat('☆', 5-round($avg_rating)) ?></div>
                <div style="font-size:1.5rem; margin:10px 0;"><strong><?= $avg_rating ?: 'Chưa có' ?> / 5.0</strong></div>
                <div>(<?= $total_reviews ?> đánh giá)</div>
            </div>
        </div>

        <h2 style="color:var(--dark); margin:2rem 0 1rem;">Mô tả</h2>
        <p style="font-size:1.1rem; line-height:1.8; color:#555;">
            <?= nl2br(htmlspecialchars($dest['description'])) ?>
        </p>

        <div class="actions">
            <a href="reviews.php?destination_id=<?= $id ?>" class="btn-primary">Xem & Viết Đánh Giá</a>
            <a href="destinations.php" class="btn-secondary">Quay Lại Danh Sách</a>
        </div>
    </div>
</div>

</body>
</html>