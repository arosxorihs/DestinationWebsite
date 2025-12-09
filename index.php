<?php
session_start();
include 'config.php';
include 'cookie.php';

// AUTO LOGIN VIA COOKIE
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("SELECT user_id, username, role FROM cookies 
                            JOIN users ON cookies.user_id = users.user_id
                            WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();

        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
    }
}

// LOGOUT
if (isset($_GET['logout'])) {

    // Xoá cookie
    setcookie("remember_token", "", time() - 3600, "/");

    // Xoá token trong DB
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("DELETE FROM cookies WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
    }

    session_destroy();
    header("Location: index.php");
    exit;
}


$message = "";

// LOGIN & REGISTER
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['action'])) {

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // REGISTER
        if ($_POST['action'] === "register") {

            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $message = "Username already exists!";
            } else {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $hash);
                $stmt->execute();

                $message = "Register successful! Please login.";
            }
        }

        // LOGIN
       if ($_POST['action'] === "login") {

            $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows == 1) {

                $user = $res->fetch_assoc();

                if (password_verify($password, $user["password"])) {

                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    if (isset($_POST['remember'])) {
                        $token = bin2hex(random_bytes(32));

                        // Lưu vào DB
                        $stmt = $conn->prepare("INSERT INTO cookies (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))");
                        $stmt->bind_param("is", $user['user_id'], $token);
                        $stmt->execute();

                        // Set cookie
                        setcookie("remember_token", $token, time() + 86400 * 30, "/", "", false, true);
                    }
                    header("Location: index.php");
                    exit;
                } else {
                    $message = "Wrong password!";
                }
            } else {
                $message = "User not found!";
            }
        }
    }
}

$logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// FILTER FOR ADMIN
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

// Get categories and provinces for filter (admin only)
if ($is_admin) {
    $categories = $conn->query("SELECT DISTINCT category FROM destinations WHERE category IS NOT NULL ORDER BY category");
    $provinces = $conn->query("SELECT DISTINCT province FROM destinations WHERE province IS NOT NULL ORDER BY province");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Travel Destinations</title>

    <style>
       body {
  background: #f5f7fa;
  font-family: "Poppins", sans-serif;
  margin: 20px;
  padding: 0;
  color: #333;
}

h2, h3 {
  color: #333;
  margin: 20px 0 15px 0;
}

.header {
  text-align: center;
  margin-bottom: 20px;
}
.header a {
  margin: 0 10px;
  padding: 8px 15px;
  text-decoration: none;
  color: white;
}
.add-btn {
  background: #28a745;
}
.add-btn:hover {
  background: #218838;
}
.logout-btn {
  background: #dc3545;
}
.logout-btn:hover {
  background: #c82333;
}

.filter-section {
  background: #f5f5f5;
  padding: 15px;
  margin: 20px 0;
}
.filter-form {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}
.filter-group {
  display: flex;
  flex-direction: column;
}
.filter-group label {
  font-weight: bold;
  margin-bottom: 3px;
}
.filter-group select {
  padding: 8px;
}
.filter-btn, .reset-btn {
  padding: 8px 15px;
  border: none;
  cursor: pointer;
  text-decoration: none;
  color: white;
}
.filter-btn {
  background: #007bff;
}
.filter-btn:hover {
  background: #0056b3;
}
.reset-btn {
  background: #6c757d;
}
.reset-btn:hover {
  background: #5a6268;
}

/* ===== STYLE CHO CARD / GRID DESTINATIONS ===== */
.places-list {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-top: 20px;
}

.place-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  width: calc(33.333% - 20px);
  display: flex;
  flex-direction: column;
}

.place-card-img img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.place-card-body {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.place-title {
  margin: 0 0 10px 0;
  font-size: 20px;
  color: #333;
}

.place-description {
  flex: 1;
  font-size: 14px;
  color: #555;
  margin-bottom: 15px;
  line-height: 1.4;
}

.place-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  padding: 8px 12px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
  color: #fff;
  text-align: center;
  flex: 1;
  transition: background 0.2s;
}

.view-btn { background: #007bff; }
.edit-btn { background: #28a745; }
.delete-btn { background: #dc3545; }

.view-btn:hover { background: #0056b3; }
.edit-btn:hover { background: #1e7e34; }
.delete-btn:hover { background: #a71d2a; }

@media (max-width: 900px) {
  .place-card { width: calc(50% - 20px); }
}
@media (max-width: 600px) {
  .place-card { width: 100%; }
}

/* ===== Style cho nút, form ... giữ lại nếu bạn có phần form / login / filter ===== */
input, select, button {
  padding: 8px;
  box-sizing: border-box;
}
a {
  text-decoration: none;
}

    </style>

.places-list { display: flex; flex-wrap: wrap; gap: 20px; }
.place-card { … }


</head>

<body>

<?php if (!$logged_in): ?>

    <div class="auth-form">
        <h3>Login / Register</h3>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <label><input type="checkbox" name="remember"> Remember me</label>
            
            <button type="submit" name="action" value="login">Login</button>
            <button type="submit" name="action" value="register">Register</button>
        </form>
    </div>

<?php else: ?>

    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
            <?php if ($is_admin) echo " <span style='color: #dc3545;'>(Admin)</span>"; ?>
        </h2>
        
        <?php if ($is_admin): ?>
            <a href="form.php" class="add-btn">+ Add Destination</a>
        <?php endif; ?>
        
        <a href="index.php?logout=1" class="logout-btn">Logout</a>
    </div>

    <?php if ($is_admin): ?>
        <div class="filter-section">
            <h3>Filter</h3>
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Category:</label>
                    <select name="category">
                        <option value="">-- All --</option>
                        <?php
                        while ($cat = $categories->fetch_assoc()) {
                            $selected = ($category === $cat['category']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($cat['category']) . '" ' . $selected . '>' . htmlspecialchars($cat['category']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Province:</label>
                    <select name="province">
                        <option value="">-- All --</option>
                        <?php
                        while ($prov = $provinces->fetch_assoc()) {
                            $selected = ($province === $prov['province']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($prov['province']) . '" ' . $selected . '>' . htmlspecialchars($prov['province']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="filter-btn">Filter</button>
                <a href="index.php" class="reset-btn">Reset</a>
            </form>
        </div>
    <?php endif; ?>

    <h3>Destination List</h3>

   <div class="places-list">
  <?php while ($row = $result->fetch_assoc()): 
       $img = !empty($row['image_url']) ? $row['image_url'] : 'default.jpg';
  ?>
    <div class="place-card">
      <div class="place-card-img">
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
      </div>
      <div class="place-card-body">
        <h2 class="place-title"><?= htmlspecialchars($row['name']) ?></h2>
        <p class="place-description"><?= nl2br(htmlspecialchars($row['description'] ?? '')) ?></p>
        <div class="place-actions">
          <a href="detail.php?id=<?= $row['destination_id'] ?>" class="action-btn view-btn">View</a>
          <a href="reviews.php?destination_id=<?= $row['destination_id'] ?>" class="action-btn view-btn">Reviews</a>
          <?php if ($is_admin): ?>
            <a href="form.php?id=<?= $row['destination_id'] ?>" class="action-btn edit-btn">Edit</a>
            <a href="delete_destination.php?id=<?= $row['destination_id'] ?>"
               class="action-btn delete-btn"
               onclick="return confirm('Delete this item?');">Delete</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php endif; ?>

</body>
</html>