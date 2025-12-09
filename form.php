<?php
session_start();
include 'config.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

$destination_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$destination = ['name'=>'', 'country'=>'', 'description'=>'', 'category'=>'', 'province'=>'', 'image_url'=>''];

// If id exists => EDIT mode
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

// Handle POST for both Add and Edit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? '');
    $country = trim($_POST["country"] ?? '');
    $description = trim($_POST["description"] ?? '');
    $category = trim($_POST["category"] ?? '');
    $province = trim($_POST["province"] ?? '');
    $image_url = trim($_POST["image_url"] ?? '');

    if ($destination_id) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE destinations SET name=?, country=?, description=?, category=?, province=?, image_url=? WHERE destination_id=?");
        $stmt->bind_param("ssssssi", $name, $country, $description, $category, $province, $image_url, $destination_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO destinations (name, country, description, category, province, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $country, $description, $category, $province, $image_url);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $destination_id ? 'Edit Destination' : 'Add Destination'; ?></title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="url"], textarea, select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            box-sizing: border-box;
        }
        textarea { resize: vertical; min-height: 80px; }
        button { padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; margin-right: 10px; }
        button:hover { background: #218838; }
        .back-link { padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; display: inline-block; }
        .back-link:hover { background: #5a6268; }
        .image-preview { margin-top: 10px; max-width: 300px; }
        .image-preview img { max-width: 100%; border: 1px solid #ddd; }
    </style>
</head>
<body>

<h2><?php echo $destination_id ? 'Edit Destination' : 'Add Destination'; ?></h2>

<form method="POST">
    <div class="form-group">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($destination['name']); ?>" required>
    </div>

    <div class="form-group">
        <label>Country:</label>
        <input type="text" name="country" value="<?php echo htmlspecialchars($destination['country']); ?>" required>
    </div>

    <div class="form-group">
        <label>Category:</label>
        <select name="category" required>
            <option value="">-- Select Category --</option>
            <option value="Beach" <?php echo ($destination['category'] === 'Beach') ? 'selected' : ''; ?>>Beach</option>
            <option value="Mountain" <?php echo ($destination['category'] === 'Mountain') ? 'selected' : ''; ?>>Mountain</option>
            <option value="History" <?php echo ($destination['category'] === 'History') ? 'selected' : ''; ?>>History</option>
            <option value="Culture" <?php echo ($destination['category'] === 'Culture') ? 'selected' : ''; ?>>Culture</option>
            <option value="Forest" <?php echo ($destination['category'] === 'Forest') ? 'selected' : ''; ?>>Forest</option>
            <option value="City" <?php echo ($destination['category'] === 'City') ? 'selected' : ''; ?>>City</option>
        </select>
    </div>

    <div class="form-group">
        <label>Province:</label>
        <input type="text" name="province" value="<?php echo htmlspecialchars($destination['province']); ?>" placeholder="e.g. Hanoi, Da Nang..." required>
    </div>

    <div class="form-group">
        <label>Description:</label>
        <textarea name="description"><?php echo htmlspecialchars($destination['description']); ?></textarea>
    </div>

    <div class="form-group">
        <label>Image URL:</label>
        <input type="url" name="image_url" value="<?php echo htmlspecialchars($destination['image_url']); ?>" placeholder="https://example.com/image.jpg">
        
        <?php if (!empty($destination['image_url'])): ?>
            <div class="image-preview">
                <p>Current Image:</p>
                <img src="<?php echo htmlspecialchars($destination['image_url']); ?>" alt="Destination Image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22300%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22%3EImage Not Found%3C/text%3E%3C/svg%3E';">
            </div>
        <?php endif; ?>
    </div>

    <button type="submit"><?php echo $destination_id ? 'Update' : 'Add'; ?></button>
    <a href="index.php" class="back-link">Back</a>
</form>

</body>
</html>