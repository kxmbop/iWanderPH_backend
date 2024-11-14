<?php
// Enable CORS (if you're making requests from a different domain)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Database connection
$servername = "localhost"; // Database server
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "your_database_name"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// SQL query to get payment summary
$sql = "
     SELECT 
        m.bookingID,
        m.businessName,
        SUM(b.subtotal * 0.12) AS totalRevenue
    FROM booking b
    JOIN merchant m ON b.merchantID = m.merchantID
    WHERE b.paymentStatus = 'successful'
    GROUP BY m.merchantID
";

$result = $conn->query($sql);

$payments = [];

// Check if there are results
if ($result->num_rows > 0) {
    // Fetch all payments
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}

// Return data as JSON
echo json_encode($payments);

// Close the connection
$conn->close();
?>