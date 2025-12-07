<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "travel_db"; // đúng tên database bạn tạo

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$destination_id = $_GET["id"] ?? null;

// Lấy 1 destination
if ($destination_id) {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $destination = $result->fetch_assoc();

    if (!$destination) {
        die("Destination not found!");
    }
} else {
    die("No ID provided!");
}

// Cập nhật
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $country = $_POST["country"];
    $description = $_POST["description"];
    $image_url = $_POST["image_url"];

    $stmt = $conn->prepare("
        UPDATE destinations 
        SET name=?, country=?, description=?, image_url=?
        WHERE destination_id=?
    ");

    $stmt->bind_param("ssssi",
        $name,
        $country,
        $description,
        $image_url,
        $destination_id
    );

    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<h2>Edit Destination</h2>

<form method="POST">

    Name:<br>
    <input type="text" name="name" value="<?php echo $destination['name']; ?>"><br><br>

    Country:<br>
    <input type="text" name="country" value="<?php echo $destination['country']; ?>"><br><br>

    Description:<br>
    <textarea name="description"><?php echo $destination['description']; ?></textarea><br><br>

    Image URL:<br>
    <input type="text" name="image_url" value="<?php echo $destination['image_url']; ?>"><br><br>

    <button type="submit">Update</button>

</form>

<br>
<a href="index.php">← Back to list</a>
