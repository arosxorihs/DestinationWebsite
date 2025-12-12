<?php
session_start();
include 'config.php';
include 'cookie.php';

// Lấy tất cả review + thêm image_url để hiển thị ảnh nhỏ
$reviews = $conn->query("
    SELECT r.*, u.username, d.name as dest_name, d.image_url, d.province 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    JOIN destinations d ON r.destination_id = d.destination_id 
    ORDER BY d.name ASC, r.review_id DESC
")->fetch_all(MYSQLI_ASSOC);

$grouped_reviews = [];
foreach ($reviews as $r) {
    $grouped_reviews[$r['dest_name']][] = $r;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tất Cả Đánh Giá – TravelDest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --primary: #ff6b6b;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #e9ecef;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

        /* Header */
        header {
            background: rgba(255,255,255,0.98);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .nav-menu a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: color 0.3s;
        }
        .nav-menu a:hover { color: var(--primary); }
        .btn {
            background: var(--primary);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn:hover { background: #ff5252; transform: translateY(-2px); }
        .btn-logout { background: #dc3545; }
        .btn-logout:hover { background: #c82333; }

        /* Main content */
        main { margin-top: 100px; padding: 2rem 0; }
        h1 {
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 3rem;
            color: var(--dark);
            position: relative;
        }
        h1::after {
            content: '';
            width: 100px;
            height: 5px;
            background: var(--primary);
            display: block;
            margin: 15px auto;
            border-radius: 3px;
        }

        /* Destination Group */
        .destination-group {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
            transition: transform 0.3s;
        }
        .destination-group:hover { transform: translateY(-10px); }

        .dest-header {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), 
                        url('https://images.unsplash.com/photo-1497436072909-60f3600d79e3?q=80&w=2070');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .dest-header img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .dest-info h2 {
            font-size: 2rem;
            margin: 0;
        }
        .dest-info p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .reviews-container {
            padding: 2rem;
        }
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .review-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 5px solid var(--primary);
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .reviewer {
            font-weight: 600;
            color: var(--dark);
        }
        .rating {
            color: #ffc107;
            font-size: 1.3rem;
            font-weight: bold;
        }
        .review-comment {
            line-height: 1.7;
            color: #555;
        }

        .no-reviews {
            text-align: center;
            padding: 4rem;
            color: var(--gray);
            font-size: 1.3rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu { flex-direction: column; gap: 1rem; }
            .dest-header { flex-direction: column; text-align: center; }
            .dest-header img { width: 100px; height: 100px; }
            h1 { font-size: 2.2rem; }
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
            <a href="all_reviews.php"><strong>Reviews</strong></a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn">Đăng Nhập</a>
            <?php else: ?>
                <span>Xin chào, <?=htmlspecialchars($_SESSION['username'])?></span>
                <a href="logout.php" class="btn btn-logout">Đăng Xuất</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <h1>Tất Cả Đánh Giá Từ Du Khách</h1>

    <?php if (empty($grouped_reviews)): ?>
        <div class="no-reviews">
            Chưa có đánh giá nào. Hãy là người đầu tiên chia sẻ trải nghiệm của bạn!
        </div>
    <?php else: ?>
        <?php foreach ($grouped_reviews as $dest_name => $revs): 
            $first_review = $revs[0]; // Lấy review đầu để lấy ảnh + tỉnh
        ?>
            <div class="destination-group">
                <div class="dest-header" style="background-image: url('<?= htmlspecialchars($first_review['image_url'] ?: 'https://images.unsplash.com/photo-1497436072909-60f3600d79e3?q=80&w=2070') ?>');">
                    <img src="<?= htmlspecialchars($first_review['image_url'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=800') ?>" 
                         alt="<?= htmlspecialchars($dest_name) ?>">
                    <div class="dest-info">
                        <h2><?= htmlspecialchars($dest_name) ?></h2>
                        <p><?= htmlspecialchars($first_review['province'] ?: $first_review['country'] ?? 'Điểm đến tuyệt vời') ?></p>
                    </div>
                </div>

                <div class="reviews-container">
                    <div class="reviews-grid">
                        <?php foreach ($revs as $r): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="reviewer"><?= htmlspecialchars($r['username']) ?></div>
                                    <div class="rating">
                                        <?php for($i=1;$i<=5;$i++): ?>
                                            <?= $i <= $r['rating'] ? '★' : '☆' ?>
                                        <?php endfor; ?>
                                        (<?= $r['rating'] ?>)
                                    </div>
                                </div>
                                <div class="review-comment">
                                    <?= nl2br(htmlspecialchars($r['comment'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

</body>
</html><?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền xóa.");
}

$review_id       = $_GET['review_id'];
$destination_id  = $_GET['destination_id'];

// Xóa reply nếu có
$conn->query("DELETE FROM reviews WHERE parent_id = $review_id");

// Xóa bình luận cha
$conn->query("DELETE FROM reviews WHERE review_id = $review_id");

header("Location: reviews.php?destination_id=" . $destination_id);
exit;