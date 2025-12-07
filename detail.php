<?php
include 'config.php';

// Lấy id từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Truy vấn đúng tên cột
$stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Destination not found.");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($row['name']); ?></title>
</head>
<body>

<h2><?php echo htmlspecialchars($row['name']); ?></h2>

<p><strong>Country:</strong> 
    <?php echo htmlspecialchars($row['country']); ?>
</p>

<p><strong>Description:</strong><br>
    <?php echo nl2br(htmlspecialchars($row['description'])); ?>
</p>

<?php if (!empty($row['image_url'])): ?>
    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
         style="max-width:400px;">
<?php endif; ?>

<br><br>
<a href="index.php">← Back to list</a>

</body>
</html>
