<?php
// ...existing code...
session_start();
include 'config.php';

// chỉ admin mới truy cập
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

$destination_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$destination = ['name'=>'', 'country'=>'', 'description'=>'', 'image_url'=>''];

// Nếu có id => lấy record (EDIT mode)
if ($destination_id) {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $destination = $res->fetch_assoc();
    } else {
        // nếu id không tồn tại, quay về list
        header('Location: index.php');
        exit;
    }
}

// Xử lý POST cho cả Add và Edit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? '');
    $country = trim($_POST["country"] ?? '');
    $description = trim($_POST["description"] ?? '');
    $image_url = trim($_POST["image_url"] ?? '');

    if ($destination_id) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE destinations SET name=?, country=?, description=?, image_url=? WHERE destination_id=?");
        $stmt->bind_param("ssssi", $name, $country, $description, $image_url, $destination_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO destinations (name, country, description, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $country, $description, $image_url);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit;
}
?>

<h2><?php echo $destination_id ? 'Edit Destination' : 'Add Destination'; ?></h2>

<form method="POST">
    Name:<br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($destination['name']); ?>" required><br><br>

    Country:<br>
    <input type="text" name="country" value="<?php echo htmlspecialchars($destination['country']); ?>" required><br><br>

    Description:<br>
    <textarea name="description"><?php echo htmlspecialchars($destination['description']); ?></textarea><br><br>

    Image URL:<br>
    <input type="text" name="image_url" value="<?php echo htmlspecialchars($destination['image_url']); ?>"><br><br>

    <button type="submit"><?php echo $destination_id ? 'Update' : 'Add'; ?></button>
</form>

<br>
<a href="index.php">← Back to list</a>