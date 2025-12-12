<?php
session_start();
include 'config.php';
include 'cookie.php';

// ================== LẤY THAM SỐ FILTER ==================
$category = trim($_GET['category'] ?? '');
$province = trim($_GET['province'] ?? '');
$search   = trim($_GET['search'] ?? '');

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// ================== ĐẾM TỔNG ==================
$count_query = "SELECT COUNT(*) FROM destinations WHERE 1=1";
$count_params = [];
$count_types  = '';

if ($category) { $count_query .= " AND category = ?"; $count_params[] = $category; $count_types .= 's'; }
if ($province) { $count_query .= " AND province LIKE ?"; $count_params[] = "%$province%"; $count_types .= 's'; }
if ($search)   { $count_query .= " AND (name LIKE ? OR description LIKE ?)"; $count_params[] = "%$search%"; $count_params[] = "%$search%"; $count_types .= 'ss'; }

$stmt_count = $conn->prepare($count_query);
if (!empty($count_params)) $stmt_count->bind_param($count_types, ...$count_params);
$stmt_count->execute();
$total = $stmt_count->get_result()->fetch_row()[0];
$pages = ceil($total / $limit);

// ================== LẤY DATA ==================
$query = "SELECT * FROM destinations WHERE 1=1";
$params = [];
$types  = '';

if ($category) { $query .= " AND category = ?"; $params[] = $category; $types .= 's'; }
if ($province) { $query .= " AND province LIKE ?"; $params[] = "%$province%"; $types .= 's'; }
if ($search)   { $query .= " AND (name LIKE ? OR description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }

$query .= " ORDER BY destination_id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tất Cả Điểm Đến – TravelDest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; margin:0; background:#f8f9fa; }
        header { position:fixed; top:0; left:0; width:100%; background:rgba(255,255,255,0.95); backdrop-filter:blur(10px); box-shadow:0 2px 20px rgba(0,0,0,0.1); z-index:1000; }
        .nav { display:flex; justify-content:space-between; align-items:center; padding:1rem 5%; }
        .logo { font-size:1.8rem; font-weight:700; color:#ff6b6b; }
        .nav-menu { display:flex; gap:2rem; align-items:center; }
        .nav-menu a { text-decoration:none; color:#333; font-weight:500; }
        .btn { background:#ff6b6b; color:white; padding:0.7rem 1.5rem; border-radius:50px; }
        .container { padding: 100px 5% 50px; }
        .filter-form { display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; justify-content:center; }
        .filter-form input, .filter-form select { padding:0.8rem; border:1px solid #ddd; border-radius:8px; width:200px; }
        .filter-form button { background:#ff6b6b; color:white; border:none; padding:0.8rem 1.5rem; border-radius:8px; cursor:pointer; }
        .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:2rem; }
        .card { background:white; border-radius:15px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1); transition:0.3s; }
        .card:hover { transform:translateY(-10px); }
        .card img { width:100%; height:220px; object-fit:cover; }
        .card-body { padding:1.5rem; }
        .card-title { font-size:1.4rem; margin:0 0 0.5rem; }
        .location { color:#ff6b6b; font-weight:600; }
        .pagination { text-align:center; margin:3rem 0; }
        .pagination a { padding:0.5rem 1rem; margin:0 5px; background:#fff; border:1px solid #ddd; border-radius:8px; text-decoration:none; }
        .pagination a.active { background:#ff6b6b; color:white; }
    </style>
</head>
<body>

<header>
    <div class="nav">
        <div class="logo">TravelDest</div>
        <div class="nav-menu">
            <a href="landing.php">Trang Chủ</a>
            <a href="destinations.php">Điểm Đến</a>
            <a href="all_reviews.php">Reviews</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn">Đăng Nhập</a>
            <?php else: ?>
                <span>Xin chào, <?=htmlspecialchars($_SESSION['username'])?></span>
                <a href="logout.php" class="btn" style="background:#dc3545;">Đăng Xuất</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
    <h1 style="text-align:center;">Tất Cả Điểm Đến (<?= $total ?>)</h1>

    <form method="GET" class="filter-form">
        <select name="category">
            <option value="">Tất cả danh mục</option>
            <option value="Beach" <?= $category==='Beach'?'selected':'' ?>>Bãi Biển</option>
            <option value="Mountain" <?= $category==='Mountain'?'selected':'' ?>>Núi</option>
            <option value="History" <?= $category==='History'?'selected':'' ?>>Lịch Sử</option>
            <option value="Culture" <?= $category==='Culture'?'selected':'' ?>>Văn Hóa</option>
            <option value="Forest" <?= $category==='Forest'?'selected':'' ?>>Rừng</option>
            <option value="City" <?= $category==='City'?'selected':'' ?>>Thành Phố</option>
        </select>
        <input type="text" name="province" placeholder="Tỉnh/Thành" value="<?=htmlspecialchars($province)?>">
        <input type="text" name="search" placeholder="Tìm tên..." value="<?=htmlspecialchars($search)?>">
        <button type="submit">Lọc</button>
    </form>

    <div class="grid">
        <?php while ($d = $result->fetch_assoc()): ?>
        <div class="card">
            <img src="<?= htmlspecialchars($d['image_url'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=800') ?>" alt="<?= htmlspecialchars($d['name']) ?>">
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($d['name']) ?></h3>
                <p class="location">📍 <?= htmlspecialchars($d['province'] ?: $d['country']) ?></p>
                <p><?= htmlspecialchars(mb_substr($d['description'],0,100)) ?>...</p>
                <a href="detail.php?id=<?= $d['destination_id'] ?>" class="btn">Xem Chi Tiết</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for($i=1; $i<=$pages; $i++): ?>
            <a href="?page=<?= $i ?>&category=<?=urlencode($category)?>&province=<?=urlencode($province)?>&search=<?=urlencode($search)?>" class="<?= $i==$page?'active':'' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>