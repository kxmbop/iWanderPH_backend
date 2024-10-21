<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$key = "123456"; 
$response = [];

// Read input from the POST request
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? null;
$status = $data['status'] ?? null;

// Check if token and status are provided
if (!$token || !$status) {
    echo json_encode(['error' => 'Token and status are required']);
    exit;
}

// Decode the JWT token
try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;
    $role = $decoded->role;
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}

// Fetch the merchant ID associated with the traveler
$merchantSql = "SELECT MerchantID FROM merchant WHERE TravelerID = ?";
$merchantStmt = $conn->prepare($merchantSql);
$merchantStmt->bind_param("i", $travelerID);
$merchantStmt->execute();
$merchantResult = $merchantStmt->get_result();
$merchantRow = $merchantResult->fetch_assoc();

if (!$merchantRow) {
    echo json_encode(['error' => 'No merchant found for the given traveler']);
    exit;
}

$merchantID = $merchantRow['MerchantID'];


// Fetch all bookings for the merchant with the specified status
$sql = "
SELECT 
    b.*, 
    t.Username, 
    m.BusinessName
FROM bookings b
JOIN merchant m ON b.MerchantID = m.MerchantID
JOIN traveler t ON b.TravelerID = t.TravelerID
WHERE m.MerchantID = ? AND b.BookingStatus = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $merchantID, $status);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

// Output the result in JSON format
if (empty($bookings)) {
    echo json_encode(['message' => 'No bookings found with the given status']);
} else {
    echo json_encode($bookings);
}

// Close the database connection
$conn->close();
?>
