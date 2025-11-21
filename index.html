<?php
session_start(); // only once here
include 'config.php';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if ($_POST['action'] === 'register') {
            // Check if username exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "Username already exists!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $hash);
                if ($stmt->execute()) {
                    $message = "Registration successful! You can login now.";
                } else {
                    $message = "Error: " . $conn->error;
                }
            }
        }

        if ($_POST['action'] === 'login') {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: index.php");
                    exit;
                } else {
                    $message = "Incorrect password!";
                }
            } else {
                $message = "User not found!";
            }
        }
    }
}
?>

<?php if (isset($_SESSION['user_id'])): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Destination List</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        table { border-collapse: collapse; width: 80%; margin: auto; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background: #f2f2f2; }
        a { text-decoration: none; color: blue; }
    </style>
</head>
<body>
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
<a href="form.php">➕ Add Destination</a> | 
<a href="index.php?logout=1">Logout</a>

<h3>Destination List</h3>
<table>
    <tr>
        <th>Name</th>
        <th>Country</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM destinations ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['country']); ?></td>
        <td><?php echo htmlspecialchars(substr($row['description'],0,50)); ?>...</td>
        <td>
            <a href="detail.php?id=<?php echo $row['destination_id']; ?>">View</a> |
            <a href="form.php?id=<?php echo $row['destination_id']; ?>">Edit</a> |
            <a href="delete_destination.php?id=<?php echo $row['destination_id']; ?>" onclick="return confirm('Delete this?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>

<?php else: ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login / Register</title>
</head>
<body>
<h2>Login / Register</h2>
<?php if ($message) echo "<p style='color:red;'>$message</p>"; ?>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit" name="action" value="login">Login</button>
    <button type="submit" name="action" value="register">Register</button>
</form>
</body>
</html>
<?php endif; ?>
<?php
include 'config.php';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$message = '';

// Handle login/register form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($_POST['action'] === 'register') {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
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
            $message = "Registration successful! You can log in now.";
        }
    } elseif ($_POST['action'] === 'login') {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit;
            } else {
                $message = "Incorrect password!";
            }
        } else {
            $message = "User not found!";
        }
    }
}

// Check if logged in
$logged_in = isset($_SESSION['username']);

// Fetch all destinations
$result = $conn->query("SELECT * FROM destinations ORDER BY created_at DESC");
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
        a { text-decoration: none; color: blue; }
        form { width: 300px; margin: auto; }
        input { width: 100%; padding: 8px; margin: 5px 0; }
        button { padding: 8px; width: 100%; }
    </style>
</head>
<body>

<?php if (!$logged_in): ?>
<h2 align="center">Login / Register</h2>
<?php if ($message) echo "<p style='color:red;' align='center'>$message</p>"; ?>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="action" value="login">Login</button>
    <button type="submit" name="action" value="register">Register</button>
</form>
<?php else: ?>
<h2 align="center">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
<div align="center"><a href="form.php">➕ Add Destination</a> | <a href="index.php?logout=1">Logout</a></div>

<h3 align="center">Destination List</h3>
<table>
    <tr>
        <th>Name</th>
        <th>Country</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['country']); ?></td>
        <td><?php echo htmlspecialchars(substr($row['description'],0,50)); ?>...</td>
        <td>
            <a href="detail.php?id=<?php echo $row['destination_id']; ?>">View</a> |
            <a href="form.php?id=<?php echo $row['destination_id']; ?>">Edit</a> |
            <a href="index.php?delete=<?php echo $row['destination_id']; ?>" onclick="return confirm('Delete this destination?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>

</body>
</html>
