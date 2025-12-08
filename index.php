<?php
session_start();
include 'config.php';

//// LOGOUT
if (isset($_GET['logout'])) {
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

                // bảng users của bạn chỉ có username + password
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


$filter_activity_id = (int)($_GET['activity'] ?? 0); // Lay ID hoat dong tu GET
$activities = []; // Khai bao mang luu danh sach hoat dong

// Truy van danh sach hoat dong tu Database
$q_act = $conn->query("SELECT activity_id, name FROM activities ORDER BY name");
if ($q_act) {
    while ($r_act = $q_act->fetch_assoc()) $activities[] = $r_act;
}

$logged_in = isset($_SESSION['user_id']);




if ($logged_in) {
    // Xay dung truy van
    $sql = "SELECT d.destination_id, d.name, d.country, d.description
            FROM destinations d";

    if ($filter_activity_id > 0) {
        // Neu co bo loc, JOIN voi bang trung gian (destination_activities)
        $sql .= " INNER JOIN destination_activities da ON d.destination_id = da.destination_id 
                 WHERE da.activity_id = ?";
    }

    $sql .= " ORDER BY d.destination_id DESC";
    
    // Su dung prepared statement de an toan hon
    if ($filter_activity_id > 0) {
        $q = $conn->prepare($sql);
        $q->bind_param('i', $filter_activity_id);
        $q->execute();
        $result = $q->get_result();
        $q->close();
    } else {
        // Neu khong co bo loc, chay truy van don gian
        $result = $conn->query($sql);
    }
} else {
    // Neu chua dang nhap, gan $result la null de tranh loi trong vong lap HTML
    $result = null; 
}




?>
<!DOCTYPE html>
<html>
<head>
    <title>Travel Destinations</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        table { border-collapse: collapse; width: 80%; margin: auto; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background: #f2f2f2; }
        input, button { width: 100%; padding: 8px; margin-top: 5px; }
        form { width: 300px; margin: auto; }
    </style>
</head>

<body>

<?php if (!$logged_in): ?>

<h2 align="center">Login / Register</h2>

<?php if ($message) echo "<p style='color:red;' align='center'>$message</p>"; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <button type="submit" name="action" value="login">Login</button>
    <button type="submit" name="action" value="register">Register</button>
</form>

<?php else: ?>

<h2 align="center">Welcome, <?php echo $_SESSION['username']; ?>
    <?php if ($_SESSION['role'] === "admin") echo " (Admin)"; ?>
</h2>

<div align="center">
    <?php if ($_SESSION['role'] === "admin"): ?>
        <a href="form.php">➕ Add Destination</a> |
    <?php endif; ?>

    <a href="index.php?logout=1">Logout</a>
</div>




</div>
<div align="center" style="margin-top: 15px; margin-bottom: 20px;">
    <form method="GET" style="display: inline-block;">
        <label for="activity_filter">Loc theo Hoat dong:</label>
        <select name="activity" id="activity_filter" onchange="this.form.submit()">
            <option value="0">-- Tat ca Hoat dong --</option>
            <?php foreach ($activities as $act): ?>
                <option value="<?= (int)$act['activity_id'] ?>" 
                        <?= $filter_activity_id === (int)$act['activity_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($act['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($filter_activity_id > 0): ?>
            <a href="index.php" style="margin-left: 10px;">Xoa loc</a> 
        <?php endif; ?>
    </form>
</div>
<h3 align="center">Destination List</h3>




<h3 align="center">Destination List</h3>

<table>
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>

<?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars(substr($row['description'], 0, 60)); ?>...</td>

        <td>
            <a href="detail.php?id=<?php echo $row['destination_id']; ?>">View</a>

            <?php if ($_SESSION['role'] === "admin"): ?>
                 | <a href="form.php?id=<?php echo $row['destination_id']; ?>">Edit</a>
                 | <a href="delete_destination.php?id=<?php echo $row['destination_id']; ?>"
                      onclick="return confirm('Delete this item?');">Delete</a>
            <?php endif; ?>
        </td>
    </tr>
<?php endwhile; ?>

</table>

<?php endif; ?>

</body>
</html>
