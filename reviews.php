<?php
session_start();
include 'config.php';
include 'cookie.php';

$destination_id = isset($_GET['destination_id']) && is_numeric($_GET['destination_id']) ? (int)$_GET['destination_id'] : null;

if (isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    if ($rating >= 1 && $rating <= 5) {
        $insert = $conn->prepare("INSERT INTO reviews (destination_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiis", $destination_id, $user_id, $rating, $comment);
        $insert->execute();
        header("Location: reviews.php?destination_id=$destination_id");
        exit;
    }
}

if (!$destination_id) {
    header('Location: landing.php');
    exit;
}

// Lấy thông tin địa điểm
$stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$destination = $stmt->get_result()->fetch_assoc();

if (!$destination) {
    header('Location: landing.php');
    exit;
}

// Lấy tất cả đánh giá
$review_stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.destination_id = ? 
    ORDER BY r.review_id DESC
");
$review_stmt->bind_param("i", $destination_id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();

// Tính thống kê đánh giá
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM reviews
    WHERE destination_id = ?
");
$stats_stmt->bind_param("i", $destination_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$avg_rating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;
$total_reviews = $stats['total_reviews'] ?? 0;

// Tính phần trăm cho bar chart
$percentages = [];
for ($i=5; $i>=1; $i--) {
    // Sửa lại đúng tên key trong database
    $keys = [5 => 'five_star', 4 => 'four_star', 3 => 'three_star', 2 => 'two_star', 1 => 'one_star'];
    $percentages[$i] = $total_reviews > 0 ? round(($stats[$keys[$i]] / $total_reviews) * 100) : 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh Giá Cho <?= htmlspecialchars($destination['name']) ?> – TravelDest</title>
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
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

        /* Header */
        header { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; }
        .logo { font-size: 1.8rem; font-weight: 700; color: var(--primary); }
        .nav-menu { display: flex; align-items: center; gap: 2rem; }
        .nav-menu a { text-decoration: none; color: var(--dark); font-weight: 500; transition: color 0.3s; }
        .nav-menu a:hover { color: var(--primary); }
        .btn { background: var(--primary); color: white; padding: 0.6rem 1.2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
        .btn:hover { background: #ff5252; transform: translateY(-2px); }
        .btn-logout { background: #dc3545; }
        .btn-logout:hover { background: #c82333; }

        /* Hero Section */
        .hero { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?= htmlspecialchars($destination['image_url'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=2070') ?>'); background-size: cover; background-position: center; color: white; padding: 6rem 0; text-align: center; border-radius: 16px; margin-bottom: 3rem; }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; }
        .hero p { font-size: 1.5rem; opacity: 0.9; }

        /* Stats Section */
        .stats { display: flex; justify-content: space-between; gap: 2rem; margin-bottom: 3rem; }
        .stats-overview { flex: 1; text-align: center; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stats-overview .avg-rating { font-size: 3.5rem; color: #ffc107; font-weight: bold; }
        .stats-overview .total { font-size: 1.2rem; color: var(--gray); }
        .stats-bars { flex: 2; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .bar-row { display: flex; align-items: center; margin-bottom: 1rem; }
        .bar-label { width: 60px; font-weight: 500; color: #ffc107; }
        .bar { flex: 1; background: #e9ecef; height: 10px; border-radius: 5px; position: relative; }
        .bar-fill { background: #ffc107; height: 100%; border-radius: 5px; transition: width 1s ease-in-out; }
        .bar-percent { width: 50px; text-align: right; font-size: 0.9rem; color: var(--gray); }

        /* Reviews List */
        .reviews-list { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 3rem; }
        .review-card { border-bottom: 1px solid var(--border); padding: 1.5rem 0; }
        .review-card:last-child { border-bottom: none; }
        .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .reviewer { font-weight: 600; color: var(--dark); }
        .rating { color: #ffc107; font-weight: bold; }
        .review-comment { color: #555; line-height: 1.7; }

        /* Form Section */
        .add-review { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .add-review select, .add-review textarea { width: 100%; padding: 1rem; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 1rem; font-size: 1rem; }
        .add-review textarea { min-height: 150px; resize: vertical; }
        .add-review button { background: var(--primary); color: white; padding: 1rem 2rem; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .add-review button:hover { background: #ff5252; }

        /* Back Links */
        .back-links { text-align: center; margin-top: 3rem; }
        .back-links a { color: var(--primary); text-decoration: none; font-weight: 500; margin: 0 1rem; }
        .back-links a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .stats { flex-direction: column; }
            .hero h1 { font-size: 2.2rem; }
            .hero p { font-size: 1.2rem; }
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
                <a href="index.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn btn-logout">Đăng Xuất</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
    <div class="hero">
        <h1>Đánh Giá Cho <?= htmlspecialchars($destination['name']) ?></h1>
        <p>Khám phá ý kiến từ du khách thực tế</p>
    </div>

    <div class="stats">
        <div class="stats-overview">
            <div class="avg-rating"><?= $avg_rating ?> ★</div>
            <div class="total">(<?= $total_reviews ?> đánh giá)</div>
        </div>
        <div class="stats-bars">
            <h3>Phân Bố Đánh Giá</h3>
            <?php for ($i=5; $i>=1; $i--): ?>
                <div class="bar-row">
                    <div class="bar-label"><?= $i ?>★</div>
                    <div class="bar"><div class="bar-fill" style="width: <?= $percentages[$i] ?>%;"></div></div>
                    <div class="bar-percent"><?= $percentages[$i] ?>%</div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="reviews-list">
        <h3>Danh Sách Đánh Giá</h3>
        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer"><?= htmlspecialchars($review['username']) ?></div>
                        <div class="rating">
                            <?php for($j=1; $j<=5; $j++): ?>
                                <?= $j <= $review['rating'] ? '★' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-comment"><?= nl2br(htmlspecialchars($review['comment'] ?? 'Không có bình luận')) ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:var(--gray);">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="add-review">
            <h3>Thêm Đánh Giá Của Bạn</h3>
            <form method="POST">
                <select name="rating" required>
                    <option value="">Chọn số sao</option>
                    <option value="5">5★ - Tuyệt vời</option>
                    <option value="4">4★ - Rất tốt</option>
                    <option value="3">3★ - Bình thường</option>
                    <option value="2">2★ - Tạm được</option>
                    <option value="1">1★ - Không tốt</option>
                </select>
                <textarea name="comment" placeholder="Chia sẻ trải nghiệm của bạn..." required></textarea>
                <button type="submit" name="submit_review">Gửi Đánh Giá</button>
            </form>
        </div>
    <?php else: ?>
        <p style="text-align:center;"><a href="login.php" class="btn">Đăng nhập để thêm đánh giá</a></p>
    <?php endif; ?>

    <div class="back-links">
        <a href="detail.php?id=<?= $destination_id ?>">← Quay về chi tiết địa điểm</a>
        <a href="destinations.php">Quay về danh sách địa điểm</a>
    </div>
</div>

</body>
</html><?php
session_start();
include 'config.php';

$destination_id = isset($_GET['destination_id']) && is_numeric($_GET['destination_id']) ? (int)$_GET['destination_id'] : null;

if (isset($_POST['submit_reply']) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {

    $reply_text = trim($_POST['reply_text']);
    $parent_id  = (int)$_POST['parent_id'];
    $user_id    = $_SESSION['user_id'];

    $insertReply = $conn->prepare("
        INSERT INTO reviews (destination_id, user_id, rating, comment, parent_id, is_admin_reply)
        VALUES (?, ?, NULL, ?, ?, 1)
    ");

    $insertReply->bind_param("iisi", $destination_id, $user_id, $reply_text, $parent_id);
    $insertReply->execute();

    header("Location: reviews.php?destination_id=" . $destination_id);
    exit;
}

if (isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    $insert = $conn->prepare("
        INSERT INTO reviews (destination_id, user_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    $insert->bind_param("iiis", $destination_id, $user_id, $rating, $comment);
    $insert->execute();

    // Reload page để thấy đánh giá mới
    header("Location: reviews.php?destination_id=" . $destination_id);
    exit;
}
if (!$destination_id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin khu du lịch
$stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$destination = $stmt->get_result()->fetch_assoc();

if (!$destination) {
    header('Location: index.php');
    exit;
}

// Lấy tất cả đánh giá
$review_stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.destination_id = ? AND parent_id IS NULL
");
$review_stmt->bind_param("i", $destination_id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();

// Tính thống kê đánh giá
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM reviews 
    WHERE destination_id = ?
");
$stats_stmt->bind_param("i", $destination_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Danh Gia - <?php echo htmlspecialchars($destination['name']); ?></title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: #333; }
        h3 { color: #333; }
        .back-link { padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 3px; display: inline-block; margin-bottom: 20px; }
        .back-link:hover { background: #5a6268; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-box { padding: 15px; background: #f5f5f5; border: 1px solid #ddd; }
        .stat-box h4 { margin-top: 0; }
        .reviews-list { margin-top: 30px; }
        .review-item { padding: 15px; background: #f9f9f9; border: 1px solid #ddd; margin-bottom: 15px; }
        .review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .review-user { font-weight: bold; }
        .review-date { color: #666; font-size: 12px; }
        .review-rating { color: #ffc107; font-weight: bold; margin: 5px 0; }
        .review-comment { color: #555; }
        .no-reviews { padding: 30px; text-align: center; color: #999; }
    </style>
</head>
<body>

<a href="index.php" class="back-link">Quay Lai</a>

<h2>Danh Gia: <?php echo htmlspecialchars($destination['name']); ?></h2>
<div class="stats">
    <div class="stat-box">
        <h4>Danh Gia Trung Binh</h4>
        <p><?php echo $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 'N/A'; ?>/5</p>
    </div>
    <div class="stat-box">
        <h4>Tong Danh Gia</h4>
        <p><?php echo $stats['total_reviews'] ?? 0; ?> binh luan</p>
    </div>
    <div class="stat-box">
        <h4>5 Sao</h4>
        <p><?php echo $stats['five_star'] ?? 0; ?> danh gia</p>
    </div>
    <div class="stat-box">
        <h4>4 Sao</h4>
        <p><?php echo $stats['four_star'] ?? 0; ?> danh gia</p>
    </div>
    <div class="stat-box">
        <h4>3 Sao</h4>
        <p><?php echo $stats['three_star'] ?? 0; ?> danh gia</p>
    </div>
</div>
<?php if (isset($_SESSION['user_id'])): ?>
    <h3>Viet Danh Gia Cua Ban</h3>
    <form method="POST">
        <label>Danh gia sao:</label><br>
        <select name="rating" required>
            <option value="5">5 - Tuyet voi</option>
            <option value="4">4 - Tot</option>
            <option value="3">3 - Binh thuong</option>
            <option value="2">2 - Kem</option>
            <option value="1">1 - Rat te</option>
        </select><br><br>

        <label>Binh luan:</label><br>
        <textarea name="comment" rows="4" cols="50" required></textarea><br><br>

        <button type="submit" name="submit_review">Gui Danh Gia</button>
    </form>
    <hr>
<?php else: ?>
    <p><a href="login.php">Dang nhap</a> de viet danh gia.</p>
<?php endif; ?>
<div class="reviews-list">
    <h3>Chi Tiet Danh Gia</h3>
    
    <?php if ($reviews && $reviews->num_rows > 0): ?>
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="review-item">
                <?php
                // Lấy danh sách reply của bình luận này
                $replyStmt = $conn->prepare("
                    SELECT r.*, u.username 
                    FROM reviews r 
                    JOIN users u ON r.user_id = u.user_id
                    WHERE parent_id = ?
                    ORDER BY r.review_id ASC
                ");
                $replyStmt->bind_param("i", $review['review_id']);
                $replyStmt->execute();
                $replies = $replyStmt->get_result();
                ?>
                <div class="review-header">
                    <span class="review-user"><?php echo htmlspecialchars($review['username']); ?></span>
                </div>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="review_delete.php?review_id=<?php echo $review['review_id']; ?>&destination_id=<?php echo $destination_id; ?>" 
                    style="color:red; font-size:12px;"
                    onclick="return confirm('Bạn có chắc muốn xóa bình luận này?');">
                    xóa
                    </a>
                <?php endif; ?>

                <div class="review-rating">
                    <?php echo str_repeat('*', $review['rating']); ?> <?php echo $review['rating']; ?>/5
                </div>
                <div class="review-comment">
                    <?php echo htmlspecialchars($review['comment'] ?? 'Khong co comment'); ?>
                </div>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <form method="POST" style="margin-left:40px; margin-top:10px;">
                        <input type="hidden" name="parent_id" value="<?php echo $review['review_id']; ?>">
                        <textarea name="reply_text" rows="2" cols="50" placeholder="Admin trả lời..."></textarea><br>
                        <button type="submit" name="submit_reply">Gửi trả lời</button>
                    </form>
                <?php endif; ?>
                <?php while ($reply = $replies->fetch_assoc()): ?>
                    <div style="margin-left:40px; padding:10px; background:#eef; border-left:3px solid #339; margin-top:10px;">
                        <b>Admin trả lời:</b><br>
                        <?php echo htmlspecialchars($reply['comment']); ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <br>
                                <a href="review_delete.php?review_id=<?php echo $reply['review_id']; ?>&destination_id=<?php echo $destination_id; ?>" 
                                    style="color:red; font-size:12px;"
                                    onclick="return confirm('Xóa reply này?');">
                                    Xóa
                                </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-reviews">
            <p>Chua co danh gia nao cho khu du lich nay.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>