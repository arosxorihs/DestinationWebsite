<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) {header("Location: landing.php");exit;}

$blogs = $conn->query("SELECT b.*, u.username, d.name as dest_name 
                       FROM blogs b 
                       JOIN users u ON b.user_id=u.user_id 
                       LEFT JOIN destinations d ON b.destination_id=d.destination_id 
                       ORDER BY b.created_at DESC");
?>

<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Blogs Du Lịch</title>
<style>body{font-family:Arial;margin:40px}.blog{border:1px solid #ddd;padding:20px;margin-bottom:20px;border-radius:8px}</style>
</head><body>
<h2>📖 Blog Du Lịch Từ Cộng Đồng</h2>
<?php while($b=$blogs->fetch_assoc()):?>
<div class="blog">
    <h3><?=htmlspecialchars($b['title'])?></h3>
    <p><small>✍️ <?=$b['username']?> | 📅 <?=$b['created_at']?>
        <?php if($b['dest_name']):?> | 📍 <?=$b['dest_name']?><?php endif;?></small></p>
    <div><?=nl2br(htmlspecialchars($b['content']))?></div>
</div>
<?php endwhile;?>


<!-- Thêm đoạn này vào cuối file blogs.php, ngay trước </body></html> -->
<div style="text-align:center; margin:30px 0;">
    <a href="landing.php" 
       style="display:inline-block; padding:12px 30px; background:#6c757d; color:white; 
              text-decoration:none; border-radius:50px; font-weight:600; font-size:16px;
              box-shadow:0 4px 15px rgba(0,0,0,0.2); transition:all 0.3s;">
        Quay Lại Trang Chủ
    </a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="add_blog.php" 
       style="display:inline-block; margin-left:15px; padding:12px 30px; background:#28a745; color:white; 
              text-decoration:none; border-radius:50px; font-weight:600; font-size:16px;
              box-shadow:0 4px 15px rgba(0,0,0,0.2); transition:all 0.3s;">
        Viết Blog Mới
    </a>
    <?php endif; ?>
</div>
</body></html>