<?php
session_start();
include 'config.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

$destination_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

// Khởi tạo dữ liệu mặc định
$destination = [
    'name' => '',
    'country' => '',
    'description' => '',
    'image_url' => '',
    'category' => '',
    'province' => ''
];

// Nếu đang sửa → lấy dữ liệu cũ
if ($destination_id) {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $destination = $res->fetch_assoc();
    } else {
        header('Location: index.php');
        exit;
    }
}

// Xử lý lưu (thêm hoặc sửa)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name        = trim($_POST["name"] ?? '');
    $country     = trim($_POST["country"] ?? '');
    $description = trim($_POST["description"] ?? '');
    $image_url   = trim($_POST["image_url"] ?? '');  // Có thể để trống
    $category    = trim($_POST["category"] ?? '');
    $province    = trim($_POST["province"] ?? '');

    if ($destination_id) {
        // CẬP NHẬT
        $stmt = $conn->prepare("UPDATE destinations SET name=?, country=?, description=?, image_url=?, category=?, province=? WHERE destination_id=?");
        $stmt->bind_param("ssssssi", $name, $country, $description, $image_url, $category, $province, $destination_id);
    } else {
        // THÊM MỚI
        $stmt = $conn->prepare("INSERT INTO destinations (name, country, description, image_url, category, province) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $country, $description, $image_url, $category, $province);
    }

    $stmt->execute();
    $stmt->close();
    header("Location: index.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $destination_id ? 'Sửa Điểm Đến' : 'Thêm Điểm Đến Mới' ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h2 { color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        input[type="text"], textarea, select {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px;
        }
        textarea { min-height: 120px; resize: vertical; }
        button {
            background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 6px;
            font-size: 16px; cursor: pointer;
        }
        button:hover { background: #218838; }
        .back-link {
            display: inline-block; margin-left: 15px; padding: 12px 25px; background: #6c757d;
            color: white; text-decoration: none; border-radius: 6px;
        }
        .back-link:hover { background: #5a6268; }
        .note { font-size: 14px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2><?= $destination_id ? 'Sửa Điểm Đến' : 'Thêm Điểm Đến Mới' ?></h2>

    <form method="POST">
        <div class="form-group">
            <label>Tên Địa Điểm *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($destination['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Quốc Gia *</label>
            <input type="text" name="country" value="<?= htmlspecialchars($destination['country']) ?>" required>
        </div>

        <div class="form-group">
            <label>Link Ảnh Minh Họa (Image URL)</label>
            <input type="text" name="image_url" value="<?= htmlspecialchars($destination['image_url']) ?>"
                   placeholder="https://example.com/photo.jpg (để trống sẽ dùng ảnh mặc định)">
            <div class="note">
                Gợi ý: Dùng ảnh từ <a href="https://unsplash.com" target="_blank">Unsplash</a>, 
                <a href="https://pexels.com" target="_blank">Pexels</a> hoặc <a href="https://imgbb.com" target="_blank">ImgBB</a>
            </div>
        </div>

        <div class="form-group">
            <label>Danh Mục *</label>
            <select name="category" required>
                <option value="">-- Chọn danh mục --</option>
                <option value="Beach"    <?= $destination['category']==='Beach' ? 'selected':'' ?>>Bãi Biển</option>
                <option value="Mountain" <?= $destination['category']==='Mountain' ? 'selected':'' ?>>Núi</option>
                <option value="History"  <?= $destination['category']==='History' ? 'selected':'' ?>>Lịch Sử</option>
                <option value="Culture"  <?= $destination['category']==='Culture' ? 'selected':'' ?>>Văn Hóa</option>
                <option value="Forest"   <?= $destination['category']==='Forest' ? 'selected':'' ?>>Rừng</option>
                <option value="City"     <?= $destination['category']==='City' ? 'selected':'' ?>>Thành Phố</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tỉnh/Thành Phố</label>
            <input type="text" name="province" value="<?= htmlspecialchars($destination['province']) ?>"
                   placeholder="Ví dụ: Đà Nẵng, Hà Nội, Quảng Ninh...">
        </div>

        <div class="form-group">
            <label>Mô Tả</label>
            <textarea name="description"><?= htmlspecialchars($destination['description']) ?></textarea>
        </div>

        <button type="submit"><?= $destination_id ? 'Cập Nhật' : 'Thêm Mới' ?></button>
        <a href="index.php" class="back-link">Quay Lại Dashboard</a>
    </form>
</div>

</body>
</html>