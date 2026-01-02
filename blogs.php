<?php
session_start();
include 'config.php';

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$blogs = $conn->query("SELECT b.*, u.username, d.name as dest_name 
                       FROM blogs b 
                       JOIN users u ON b.user_id=u.user_id 
                       LEFT JOIN destinations d ON b.destination_id=d.destination_id 
                       ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogs Du Lịch - TravelDest</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 40px 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h2 {
            text-align: center;
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 40px;
        }
        .blog {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }
        .blog:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        .blog h3 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        .blog-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .blog-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .blog-content {
            line-height: 1.8;
            color: #555;
            font-size: 15px;
        }
        .delete-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .actions {
            text-align: center;
            margin: 40px 0;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 14px 35px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5a6268;
            transform: translateY(-3px);
        }
        .btn-primary {
            background: #28a745;
        }
        .btn-primary:hover {
            background: #218838;
        }
        .no-blogs {
            text-align: center;
            padding: 80px 20px;
            color: #999;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📖 Blog Du Lịch Từ Cộng Đồng</h2>
    
    <?php if ($blogs->num_rows > 0): ?>
        <?php while($b = $blogs->fetch_assoc()): ?>
        <div class="blog">
            <?php if ($is_admin): ?>
                <button class="delete-btn" onclick="if(confirm('Bạn có chắc muốn xóa blog này?')) { window.location.href='delete_blog.php?id=<?=$b['blog_id']?>'; }">
                    🗑️ Xóa
                </button>
            <?php endif; ?>
            
            <h3><?= htmlspecialchars($b['title']) ?></h3>
            
            <div class="blog-meta">
                <span>✍️ <?= htmlspecialchars($b['username']) ?></span>
                <span>📅 <?= date('d/m/Y H:i', strtotime($b['created_at'])) ?></span>
                <?php if($b['dest_name']): ?>
                    <span>📍 <?= htmlspecialchars($b['dest_name']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="blog-content">
                <?= nl2br(htmlspecialchars($b['content'])) ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-blogs">
            Chưa có blog nào. Hãy là người đầu tiên chia sẻ trải nghiệm của bạn! ✨
        </div>
    <?php endif; ?>
    
    <div class="actions">
        <a href="landing.php" class="btn">Quay Lại Trang Chủ</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="add_blog.php" class="btn btn-primary">Viết Blog Mới</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>