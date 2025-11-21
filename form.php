<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$name = $country = $description = $image_url = '';

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $name = $row['name'];
        $country = $row['country'];
        $description = $row['description'];
        $image_url = $row['image_url'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $country = $_POST['country'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    if ($_POST['id'] > 0) {
        $stmt = $conn->prepare("UPDATE destinations SET name=?, country=?, description=?, image_url=? WHERE destination_id=?");
        $stmt->bind_param("ssssi", $name, $country, $description, $image_url, $_POST['id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO destinations (name, country, description, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $country, $description, $image_url);
        $stmt->execute();
    }

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $id>0 ? 'Edit' : 'Add'; ?> Destination</title>
</head>
<body>
<h2 align="center"><?php echo $id>0 ? 'Edit' : 'Add'; ?> Destination</h2>
<form method="POST" style="width:400px; margin:auto;">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br><br>
    <label>Country:</label><br>
    <input type="text" name="country" value="<?php echo htmlspecialchars($country); ?>" required><br><br>
    <label>Description:</label><br>
    <textarea name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea><br><br>
    <label>Image URL:</label><br>
    <input type="text" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>"><br><br>
    <button type="submit"><?php echo $id>0 ? 'Update' : 'Add'; ?></button>
</form>
<div align="center"><a href="index.php">← Back to list</a></div>
</body>
</html>
