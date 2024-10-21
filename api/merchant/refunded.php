<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../../vendor/autoload.php'; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$key = "123456"; // Replace with a secure key
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID ?? null;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}

if (!$travelerID) {
    http_response_code(400);
    echo json_encode(['error' => 'TravelerID not found in token']);
    exit;
}

$sql = "SELECT 
        b.bookingID, b.bookingDate, b.paymentStatus, b.listingID, b.listingType,
        b.totalAmount, t.Username AS travelerUsername 
    FROM bookings b
    JOIN traveler t ON b.travelerID = t.travelerID
    WHERE b.bookingStatus = 'refunded'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

ob_clean();
if (empty($bookings)) {
    echo json_encode(['message' => 'No accepted bookings found']);
} else {
    echo json_encode($bookings);
}

$conn->close();
exit;
?>
