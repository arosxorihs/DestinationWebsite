<?php
include 'config.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) die("Destination not found.");
$row = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($row['name']); ?></title>
</head>
<body>
<h2><?php echo htmlspecialchars($row['name']); ?></h2>
<p><strong>Country:</strong> <?php echo htmlspecialchars($row['country']); ?></p>
<p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
<?php if ($row['image_url']): ?>
<p><img src="<?php echo htmlspecialchars($row['image_url']); ?>" style="max-width:400px;"></p>
<?php endif; ?>
<div><a href="index.php">← Back to list</a></div>
</body>
</html>
