<?php
// Database connection
$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$dbname = 'iwanderph_db'; 

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch images
$sql = "SELECT * FROM review_images";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Uploaded Images</title>
</head>
<body>
    <h2>Uploaded Images</h2>
    <div>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div>
                <h3><?php echo $row['reviewID']; ?></h3>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" width="200" alt="<?php echo $row['reviewID']; ?>">
            </div>
            <hr>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
