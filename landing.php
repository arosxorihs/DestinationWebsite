<?php
session_start();
include 'config.php';
include 'cookie.php'; // Auto login nếu có remember token

// Lấy 8 điểm du lịch nổi bật
$stmt = $conn->query("SELECT destination_id, name, country, province, image_url, description 
                      FROM destinations 
                      ORDER BY destination_id DESC 
                      LIMIT 8");
$destinations = $stmt->fetch_all(MYSQLI_ASSOC);

// Lấy 5 review 5 sao làm testimonial
$reviews = $conn->query("SELECT r.comment, r.rating, u.username, d.name as place 
                         FROM reviews r 
                         JOIN users u ON r.user_id = u.user_id 
                         JOIN destinations d ON r.destination_id = d.destination_id 
                         WHERE r.rating = 5 
                         ORDER BY RAND() 
                         LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelDest – Khám Phá Điểm Đến Đẹp Nhất</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        :root { --primary: #ff6b6b; --dark: #2c3e50; --light: #f8f9fa; --gray: #6c757d; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Poppins', sans-serif; line-height: 1.6; color: #333; }
        img { max-width: 100%; display: block; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        header { position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;
                 background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
                 box-shadow: 0 2px 20px rgba(0,0,0,0.1); }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; }
        .logo { font-size: 1.8rem; font-weight: 700; color: var(--primary); }
        .nav-menu { display: flex; gap: 2rem; align-items: center; }
        .nav-menu a { text-decoration: none; color: var(--dark); font-weight: 500; }
        .nav-menu a:hover { color: var(--primary); }
        .btn { background: var(--primary); color: white; padding: 0.7rem 1.5rem; border-radius: 50px; text-decoration: none; font-weight: 600; }
        .btn:hover { background: #ff5252; }
        .btn-logout { background: #dc3545; }

        .hero { height: 100vh; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)),
                url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=2070') center/cover no-repeat;
                display: flex; align-items: center; color: white; text-align: center; }
        .hero h1 { font-size: 4rem; margin-bottom: 1rem; }
        .hero p { font-size: 1.3rem; max-width: 700px; margin: 0 auto 2rem; }

        section { padding: 5rem 0; }
        .section-title { text-align: center; font-size: 2.8rem; margin-bottom: 1rem; color: var(--dark); }
        .section-subtitle { text-align: center; color: var(--gray); margin-bottom: 4rem; }

        .dest-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .dest-card { border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .dest-card:hover { transform: translateY(-10px); }
        .dest-card img { height: 250px; object-fit: cover; }
        .dest-info { padding: 1.5rem; background: white; }
        .dest-info h3 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .dest-location { color: var(--primary); font-weight: 600; margin-bottom: 1rem; }

        .swiper { padding: 2rem 0; }
        .testimonial-card { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-align: center; }
        .stars { color: #ffc107; font-size: 1.5rem; margin: 1rem 0; }

        #contact form { display: grid; gap: 1rem; max-width: 600px; margin: 0 auto; }
        #contact input, #contact textarea { padding: 1rem; border: 1px solid #ddd; border-radius: 8px; }
        #contact button { background: var(--primary); color: white; border: none; padding: 1rem; cursor: pointer; border-radius: 8px; }

        footer { background: var(--dark); color: white; text-align: center; padding: 3rem 0; }

        @media (max-width: 768px) {
            .nav-menu { gap: 1rem; font-size: 0.9rem; }
            .hero h1 { font-size: 2.8rem; }
        }
    </style>
</head>
<body>

<header>
    <div class="nav">
        <div class="logo">TravelDest</div>
        <div class="nav-menu">
            <a href="landing.php"><strong>Trang Chủ</strong></a>
            <a href="destinations.php">Điểm Đến</a>
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

<section class="hero" id="home">
    <div class="container">
        <h1>Khám Phá Những Điểm Đến Tuyệt Đẹp</h1>
        <p>Hàng trăm địa điểm trong và ngoài nước – Review thật từ cộng đồng du lịch</p>
        <a href="destinations.php" class="btn" style="font-size:1.2rem;padding:1rem 2rem;">Khám Phá Tất Cả</a>
    </div>
</section>

<section id="destinations">
    <div class="container">
        <h2 class="section-title">Gợi Ý Điểm Đến</h2>
        <p class="section-subtitle">Những nơi bạn nên ghé thăm</p>
        <div class="dest-grid">
            <?php foreach($destinations as $d): ?>
            <div class="dest-card">
                <img src="<?= $d['image_url'] ?: 'https://images.unsplash.com/photo-1499856871958-5d4672888491?q=80&w=800' ?>" 
                     alt="<?=htmlspecialchars($d['name'])?>">
                <div class="dest-info">
                    <h3><?=htmlspecialchars($d['name'])?></h3>
                    <p class="dest-location"><i class="ri-map-pin-line"></i> <?=htmlspecialchars($d['province'] ?: $d['country'])?></p>
                    <p><?=htmlspecialchars(mb_substr($d['description'], 0, 100))?>...</p>
                    <a href="detail.php?id=<?=$d['destination_id']?>" class="btn" style="font-size:0.9rem;margin-top:1rem;display:inline-block;">
                        Xem Chi Tiết
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p style="text-align:center; margin-top:2rem;"><a href="destinations.php" class="btn">Xem Tất Cả Điểm Đến</a></p>
    </div>
</section>

<section id="reviews" style="background:#f8f9fa;">
    <div class="container">
        <h2 class="section-title">Du Khách Nói Gì?</h2>
        <p class="section-subtitle">Hơn 1.000+ đánh giá 5 sao</p>

        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php foreach($reviews as $r): ?>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="stars">★★★★★</div>
                        <p>"<?=htmlspecialchars($r['comment'])?>"</p>
                        <strong>— <?=htmlspecialchars($r['username'])?></strong><br>
                        <small>tại <?=htmlspecialchars($r['place'])?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        <p style="text-align:center; margin-top:2rem;"><a href="all_reviews.php" class="btn">Xem Tất Cả Reviews</a></p>
    </div>
</section>



<footer style="background: #2c3e50; color: #ecf0f1; padding: 60px 0 30px; margin-top: 100px;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px; text-align: left;">

            <!-- Cột 1: Logo + Giới thiệu -->
            <div style="text-align: center;">
                <h2 style="color: #ff6b6b; font-size: 2.2rem; margin-bottom: 15px;">TravelDest</h2>
                <p style="font-size: 1.1rem; line-height: 1.7; opacity: 0.9;">
                    Nền tảng chia sẻ trải nghiệm du lịch thực tế từ cộng đồng.<br>
                    Cùng khám phá thế giới theo cách của bạn!
                </p>
            </div>

            <!-- Cột 2: Liên kết nhanh -->
            <div>
                <h3 style="color: #ff6b6b; margin-bottom: 20px; font-size: 1.3rem;">Khám Phá</h3>
                <ul style="list-style: none; padding: 0; line-height: 2.2;">
                    <li><a href="destinations.php" style="color: #bdc3c7; text-decoration: none; transition: color 0.3s;">Điểm Đến Nổi Bật</a></li>
                    <li><a href="all_reviews.php" style="color: #bdc3c7; text-decoration: none; transition: color 0.3s;">Đánh Giá Du Khách</a></li>
                    <li><a href="blogs.php" style="color: #bdc3c7; text-decoration: none; transition: color 0.3s;">Blog Du Lịch</a></li>
                </ul>
            </div>

            <!-- Cột 3: Liên hệ -->
            <div>
                <h3 style="color: #ff6b6b; margin-bottom: 20px; font-size: 1.3rem;">Liên Hệ Chúng Tôi</h3>
                <p style="line-height: 2;">
                    <i class="ri-mail-line" style="color: #ff6b6b;"></i> Email: <a href="mailto:contact@traveldest.com" style="color: #3498db; text-decoration: none;">contact@traveldest.com</a><br>
                    <i class="ri-phone-line" style="color: #ff6b6b;"></i> Hotline: <strong>1900 1234</strong><br>
                    <i class="ri-map-pin-line" style="color: #ff6b6b;"></i> Địa chỉ: 123 Đường Láng, Hà Nội
                </p>

                <div style="margin-top: 25px;">
                    <a href="#" style="display: inline-block; margin-right: 12px; font-size: 1.6rem; color: #ecf0f1; transition: all 0.3s;"><i class="ri-facebook-circle-fill"></i></a>
                    <a href="#" style="display: inline-block; margin-right: 12px; font-size: 1.6rem; color: #ecf0f1; transition: all 0.3s;"><i class="ri-instagram-fill"></i></a>
                    <a href="#" style="display: inline-block; margin-right: 12px; font-size: 1.6rem; color: #ecf0f1; transition:hover { color: #e74c3c; } transition: all 0.3s;"><i class="ri-youtube-fill"></i></a>
                    <a href="#" style="display: inline-block; font-size: 1.6rem; color: #ecf0f1; transition: all 0.3s;"><i class="ri-tiktok-fill"></i></a>
                </div>
            </div>
        </div>

        <hr style="border: none; height: 1px; background: #444; margin: 40px 0;">

        <div style="text-align: center; font-size: 0.95rem; opacity: 0.8;">
            © 2025 <strong>TravelDest</strong> – Website Du Lịch Đẹp Nhất Việt Nam<br>
            Made with <span style="color: #e74c3c;">♥</span> by <a href="#" style="color: #3498db; text-decoration: none;">Bạn & Cộng Đồng</a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    new Swiper('.mySwiper', {
        loop: true,
        autoplay: { delay: 4000 },
        pagination: { el: '.swiper-pagination', clickable: true },
        slidesPerView: 1,
        spaceBetween: 20,
        breakpoints: { 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 }}
    });
</script>
</body>
</html>