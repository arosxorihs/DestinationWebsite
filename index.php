<?php
session_start();
include 'config.php';
include 'cookie.php'; // Auto login bằng Remember me

// === Nếu chưa đăng nhập → quay về landing page ===
if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
    exit;
}

$is_admin = ($_SESSION['role'] ?? 'user') === 'admin';

// === Đăng xuất ===
if (isset($_GET['logout'])) {
    header("Location: logout.php");
    exit;
}

$message = "";

// ==================== XỬ LÝ FILTER (CHỈ ADMIN) ====================
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$province = isset($_GET['province']) ? trim($_GET['province']) : '';

$query = "SELECT * FROM destinations WHERE 1=1";
$params = [];
$types = '';

if ($is_admin) {
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
        $types .= 's';
    }
    if ($province) {
        $query .= " AND province = ?";
        $params[] = $province;
        $types .= 's';
    }
}

$query .= " ORDER BY destination_id DESC";
$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TravelDest</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .header a { text-decoration: none; }
        .btn { padding: 10px 20px; background: #ff6b6b; color: white; border-radius: 50px; text-decoration: none; font-weight: bold; }
        .btn:hover { background: #ff5252; }
        .btn-add { background: #28a745; }
        .btn-logout { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        tr:hover { background: #f1f1f1; }
        .action-links a { margin-right: 10px; text-decoration: none; }
        .view { color: #007bff; }
        .edit { color: #28a745; }
        .delete { color: #dc3545; }
        .filter-box { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
    </style>
</head>
<body>

<div class="container">

    <!-- Header + Menu -->
    <div class="header">
        <h2>Xin chào, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</h2>
        <div>
            <a href="landing.php" class="btn">Trang Chủ Công Khai</a>
            <?php if (!$is_admin): ?>
                <a href="add_blog.php" class="btn">Viết Blog</a>
                <a href="blogs.php" class="btn">Xem Blog</a>
            <?php endif; ?>
            <?php if ($is_admin): ?>
                <a href="form.php" class="btn btn-add">+ Thêm Địa Điểm</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-logout">Đăng Xuất</a>
        </div>
    </div>

    <h1>Quản Lý Điểm Du Lịch</h1>

    <!-- Bộ lọc chỉ dành cho Admin -->
    <?php if ($is_admin): ?>
    <div class="filter-box">
        <form method="GET">
            <select name="category" style="padding:8px; margin-right:10px;">
                <option value="">-- Tất cả danh mục --</option>
                <option value="Beach" <?= $category==='Beach'?'selected':'' ?>>Beach</option>
                <option value="Mountain" <?= $category==='Mountain'?'selected':'' ?>>Mountain</option>
                <option value="History" <?= $category==='History'?'selected':'' ?>>History</option>
                <option value="Culture" <?= $category==='Culture'?'selected':'' ?>>Culture</option>
                <option value="Forest" <?= $category==='Forest'?'selected':'' ?>>Forest</option>
                <option value="City" <?= $category==='City'?'selected':'' ?>>City</option>
            </select>

            <input type="text" name="province" value="<?=htmlspecialchars($province)?>" placeholder="Tỉnh/Thành phố" style="padding:8px; width:200px;">
            <button type="submit" style="padding:8px 15px;">Lọc</button>
            <a href="index.php" style="margin-left:10px;">Xóa lọc</a>
        </form>
    </div>
    <?php endif; ?>

    <!-- Bảng danh sách địa điểm -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Địa Điểm</th>
                <th>Quốc Gia</th>
                <?php if ($is_admin): ?>
                    <th>Danh Mục</th>
                    <th>Tỉnh/Thành</th>
                <?php endif; ?>
                <th>Mô Tả</th>
                <th>Đánh Giá</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $review_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE destination_id = ?");
                    $review_stmt->bind_param("i", $row['destination_id']);
                    $review_stmt->execute();
                    $review_result = $review_stmt->get_result();
                    $review_data = $review_result->fetch_assoc();
                    $avg_rating = $review_data['avg_rating'] ? round($review_data['avg_rating'], 1) : 'Chưa có';
                    $review_count = $review_data['review_count'] ?? 0;
                    ?>
                    <tr>
                        <td><?= $row['destination_id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['country']) ?></td>
                        <?php if ($is_admin): ?>
                            <td><?= htmlspecialchars($row['category'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['province'] ?? 'N/A') ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars(substr($row['description'], 0, 60)) ?>...</td>
                        <td>★ <?= $avg_rating ?> (<?= $review_count ?>)</td>
                        <td class="action-links">
                            <a href="detail.php?id=<?= $row['destination_id'] ?>" class="view">Xem</a>
                            <a href="reviews.php?destination_id=<?= $row['destination_id'] ?>" class="view">Reviews</a>
                            <?php if ($is_admin): ?>
                                <a href="form.php?id=<?= $row['destination_id'] ?>" class="edit">Sửa</a>
                                <a href="delete_destination.php?id=<?= $row['destination_id'] ?>" class="delete"
                                   onclick="return confirm('Xóa địa điểm này?');">Xóa</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center; padding:30px;">Không có dữ liệu</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>