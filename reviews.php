<?php
session_start();
include 'config.php';

$destination_id = isset($_GET['destination_id']) && is_numeric($_GET['destination_id']) ? (int)$_GET['destination_id'] : null;

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
    WHERE r.destination_id = ? 
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

<div class="reviews-list">
    <h3>Chi Tiet Danh Gia</h3>
    
    <?php if ($reviews && $reviews->num_rows > 0): ?>
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="review-item">
                <div class="review-header">
                    <span class="review-user"><?php echo htmlspecialchars($review['username']); ?></span>
                </div>
                <div class="review-rating">
                    <?php echo str_repeat('*', $review['rating']); ?> <?php echo $review['rating']; ?>/5
                </div>
                <div class="review-comment">
                    <?php echo htmlspecialchars($review['comment'] ?? 'Khong co comment'); ?>
                </div>
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