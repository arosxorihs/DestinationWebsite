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
        body { font-family: Arial; margin: 20px; }
        
        h2 { color: #333; margin: 20px 0; }
        h3 { color: #333; margin: 20px 0 15px 0; }
        
        .header { text-align: center; margin-bottom: 20px; }
        .header a { margin: 0 10px; padding: 8px 15px; text-decoration: none; color: white; }
        .add-btn { background: #28a745; }
        .add-btn:hover { background: #218838; }
        .logout-btn { background: #dc3545; }
        .logout-btn:hover { background: #c82333; }
        
        input, button { padding: 8px; margin-top: 5px; }
        form { width: 300px; margin: auto; }
        
        .auth-form { width: 300px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; }
        .auth-form h3 { text-align: center; }
        .auth-form input { width: 100%; box-sizing: border-box; }
        .auth-form button { width: 100%; margin: 5px 0; background: #007bff; color: white; border: none; cursor: pointer; }
        .auth-form button:hover { background: #0056b3; }
        
        .message { padding: 10px; margin: 10px 0; border-radius: 3px; text-align: center; }
        .message.error { background: #f8d7da; color: #721c24; }
        .message.success { background: #d4edda; color: #155724; }
        
        .filter-section { background: #f5f5f5; padding: 15px; margin: 20px 0; }
        .filter-section h3 { margin-top: 0; }
        .filter-form { display: flex; gap: 10px; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-weight: bold; margin-bottom: 3px; }
        .filter-group select { padding: 8px; }
        .filter-btn { padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
        .filter-btn:hover { background: #0056b3; }
        .reset-btn { padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; }
        .reset-btn:hover { background: #5a6268; }
        
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f2f2f2; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f9f9f9; }
        
        .action-links { white-space: nowrap; }
        .action-links a { margin: 2px; padding: 5px 8px; text-decoration: none; color: white; font-size: 12px; display: inline-block; }
        .action-links a.view { background: #17a2b8; }
        .action-links a.edit { background: #ffc107; color: black; }
        .action-links a.delete { background: #dc3545; }
        .action-links a:hover { opacity: 0.8; }
    </style>
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

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Country</th>
                <?php if ($is_admin): ?>
                    <th>Category</th>
                    <th>Province</th>
                <?php endif; ?>
                <th>Description</th>
                <th>Rating</th>
                <th>Actions</th>
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
                    $avg_rating = $review_data['avg_rating'] ? round($review_data['avg_rating'], 1) : 'N/A';
                    $review_count = $review_data['review_count'] ?? 0;
                    ?>
                    <tr>
                        <td><?php echo $row['destination_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['country']); ?></td>
                        <?php if ($is_admin): ?>
                            <td><?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['province'] ?? 'N/A'); ?></td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars(substr($row['description'], 0, 60)); ?>...</td>
                        <td>* <?php echo $avg_rating; ?> (<?php echo $review_count; ?>)</td>
                        <td class="action-links">
                            <a href="detail.php?id=<?php echo $row['destination_id']; ?>" class="view">View</a>
                            <a href="reviews.php?destination_id=<?php echo $row['destination_id']; ?>" class="view">Reviews</a>
                            <?php if ($is_admin): ?>
                                <a href="form.php?id=<?php echo $row['destination_id']; ?>" class="edit">Edit</a>
                                <a href="delete_destination.php?id=<?php echo $row['destination_id']; ?>" class="delete" onclick="return confirm('Delete this item?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $is_admin ? '8' : '6'; ?>" style="text-align: center;">No data available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>