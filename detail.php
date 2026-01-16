<?php
session_start();
require_once 'config.php';

$destination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


$stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$result = $stmt->get_result();
$destination = $result->fetch_assoc();

if (!$destination) {
    header("Location: index.php");
    exit();
}


$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE destination_id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$rating_result = $stmt->get_result()->fetch_assoc();
$avg_rating = $rating_result['avg_rating'] ? round($rating_result['avg_rating'], 1) : 0;
$total_reviews = $rating_result['total_reviews'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destination['name']); ?> - TravelDest</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .header {
            background-color: white;
            padding: 20px 50px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
       .logo { 
            font-size:1.8rem; 
            font-weight:700; 
            color:#ff6b6b; 
        }
        
        .nav { display:flex; justify-content:space-between; align-items:center; padding:1rem 5%; }

        .btn { background:#ff6b6b; color:white; padding:0.7rem 1.5rem; border-radius:50px; }
        
        .nav-menu { display:flex; gap:2rem; align-items:center; }
        .nav-menu a { text-decoration:none; color:#333; font-weight:500; }
        
        .content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .destination-header {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .destination-info h1 {
            font-size: 48px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .destination-meta {
            margin-bottom: 15px;
            font-size: 16px;
            color: #666;
        }
        
        .destination-meta strong {
            color: #333;
            font-weight: 600;
        }
        
        .rating-section {
            margin: 30px 0;
        }
        
        .stars {
            color: #fbbf24;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .rating-number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        
        .rating-count {
            color: #666;
            margin-top: 5px;
        }
        
        .destination-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .description-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .description-section h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .description-section p {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
        }
        
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 15px;
        }
        
        
    </style>
</head>
<body>

<header>

    <div class="nav">
        <div class="logo">TravelDest</div>
        <div class="nav-menu">
            <a href="landing.php">Trang Chủ</a>
            <a href="destinations.php"><strong>Điểm Đến</strong></a>
            <a href="all_reviews.php">Reviews</a>
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
<body>
    <div class="content">
        <div class="destination-header">
            <div class="destination-info">
                <h1><?php echo htmlspecialchars($destination['name']); ?></h1>
                
                <div class="destination-meta">
                    <?php echo htmlspecialchars($destination['province']); ?>, <?php echo htmlspecialchars($destination['country']); ?>
                </div>
                
                <div class="destination-meta">
                    <strong>Quốc gia:</strong> <?php echo htmlspecialchars($destination['country']); ?>
                </div>
                
                <div class="destination-meta">
                    <strong>Tỉnh/Thành:</strong> <?php echo htmlspecialchars($destination['province']); ?>
                </div>
                
                <div class="destination-meta">
                    <strong>Danh mục:</strong> <?php echo htmlspecialchars($destination['category']); ?>
                </div>
                
                <div class="rating-section">
                    <div class="stars">★★★★★</div>
                    <div class="rating-number"><?php echo $avg_rating; ?> / 5.0</div>
                    <div class="rating-count">(<?php echo $total_reviews; ?> đánh giá)</div>
                </div>
            </div>
            
            <div class="destination-image-container">
                <img src="<?php echo htmlspecialchars($destination['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($destination['name']); ?>" 
                     class="destination-image"
                     onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
            </div>
        </div>
        
        <div class="description-section">
            <h2>Mô tả</h2>
            <p><?php echo htmlspecialchars($destination['description']); ?></p>
            
            <div class="actions">
                <a href="reviews.php?destination_id=<?php echo $destination_id; ?>" class="btn btn-primary">Xem & Viết Đánh Giá</a>
                <a href="destinations.php" class="btn btn-secondary">Quay Lại Danh Sách</a>
            </div>
        </div>
    </div>
</body>
</html>