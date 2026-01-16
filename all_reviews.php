<?php
session_start();
include 'config.php';
include 'cookie.php';


$reviews = $conn->query("
    SELECT r.*, u.username, d.name as dest_name, d.image_url, d.province, d.destination_id
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    JOIN destinations d ON r.destination_id = d.destination_id 
    WHERE r.parent_id IS NULL
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
            position: relative;
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
        .view-all-link {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .view-all-link:hover {
            background: var(--primary);
            transform: scale(1.05);
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
            margin-bottom: 1rem;
        }
        
        .reply-indicator {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .no-reviews {
            text-align: center;
            padding: 4rem;
            color: var(--gray);
            font-size: 1.3rem;
        }

        @media (max-width: 768px) {
            .nav-menu { flex-direction: column; gap: 1rem; }
            .dest-header { flex-direction: column; text-align: center; }
            .dest-header img { width: 100px; height: 100px; }
            .view-all-link { position: static; margin-top: 1rem; }
            h1 { font-size: 2.2rem; }
        }
    </style>
</head>
<body>

<header>
    <div class="nav">
        <div class="logo">TravelDest</div>
        <div class="nav-menu">
            <a href="landing.php">Trang Chủ</a>
            <a href="destinations.php">Điểm Đến</a>
            <a href="all_reviews.php"><strong>Reviews</strong></a>
            <a href="blogs.php">Blog</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn">Đăng Nhập</a>
            <?php else: ?>
                <?php $is_admin = ($_SESSION['role'] ?? 'user') === 'admin';?>
                <span>Xin chào, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <?php if ($is_admin): ?>
                    <a href="index.php" class="btn">Dashboard</a>
                <?php endif; ?>
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
            $first_review = $revs[0]; 
            
            $review_ids = array_column($revs, 'review_id');
            $reply_counts = [];
            if (!empty($review_ids)) {
                $ids_str = implode(',', $review_ids);
                $reply_query = $conn->query("
                    SELECT parent_id, COUNT(*) as reply_count 
                    FROM reviews 
                    WHERE parent_id IN ($ids_str)
                    GROUP BY parent_id
                ");
                while ($row = $reply_query->fetch_assoc()) {
                    $reply_counts[$row['parent_id']] = $row['reply_count'];
                }
            }
        ?>
            <div class="destination-group">
                <div class="dest-header" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('<?= htmlspecialchars($first_review['image_url'] ?: 'https://images.unsplash.com/photo-1497436072909-60f3600d79e3?q=80&w=2070') ?>');">
                    <img src="<?= htmlspecialchars($first_review['image_url'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=800') ?>" 
                         alt="<?= htmlspecialchars($dest_name) ?>">
                    <div class="dest-info">
                        <h2><?= htmlspecialchars($dest_name) ?></h2>
                        <p>📍 <?= htmlspecialchars($first_review['province'] ?: 'Điểm đến tuyệt vời') ?> • <?= count($revs) ?> đánh giá</p>
                    </div>
                    <a href="reviews.php?destination_id=<?= $first_review['destination_id'] ?>" class="view-all-link">
                        Xem tất cả & Viết review →
                    </a>
                </div>

                <div class="reviews-container">
                    <div class="reviews-grid">
                        <?php foreach ($revs as $r): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="reviewer">👤 <?= htmlspecialchars($r['username']) ?></div>
                                    <div class="rating">
                                        <?php for($i=1;$i<=5;$i++): ?>
                                            <?= $i <= $r['rating'] ? '★' : '☆' ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-comment">
                                    <?= nl2br(htmlspecialchars(mb_substr($r['comment'], 0, 150))) ?>
                                    <?= mb_strlen($r['comment']) > 150 ? '...' : '' ?>
                                </div>
                                <?php if (isset($reply_counts[$r['review_id']])): ?>
                                    <span class="reply-indicator">
                                        💬 <?= $reply_counts[$r['review_id']] ?> trả lời từ admin
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

</body>
</html>