<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; 

$token = $_SESSION['token'] ?? '';

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

$decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
$travelerID = $decoded->TravelerID;
$role = $decoded->role;

$merchantSql = "SELECT MerchantID FROM merchant WHERE TravelerID = '$travelerID'";
$merchantResult = $conn->query($merchantSql);

if ($merchantResult === false) {
    echo json_encode(['error' => 'Error in merchant query: ' . $conn->error]);
    exit;
}

$merchantRow = $merchantResult->fetch_assoc();
if (!$merchantRow) {
    echo json_encode(['error' => 'No merchant found for the given traveler']);
    exit;
}

$merchantID = $merchantRow['MerchantID'];

$sql = "SELECT b.BookingID, b.BookingDate, b.PaymentStatus, b.ListingID, b.ListingType, b.BookingStatus, b.Duration, b.CheckIn, b.CheckOut, b.Subtotal, b.VAT, b.PayoutAmount, b.TotalAmount, b.RefundAmount, t.Username
FROM bookings b
LEFT JOIN rooms r ON b.ListingID = r.RoomID AND b.ListingType = 'room'
LEFT JOIN transportations tr ON b.ListingID = tr.TransportationID AND b.ListingType = 'car'
JOIN merchant m ON r.MerchantID = m.MerchantID OR tr.MerchantID = m.MerchantID
JOIN traveler t ON b.TravelerID = t.TravelerID
WHERE m.MerchantID = '$merchantID' AND b.BookingStatus = 'pending'";

$result = $conn->query($sql);

if ($result === false) {
    echo json_encode(['error' => 'Error in bookings query: ' . $conn->error]);
    exit;
}

$bookings = array();

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

if (empty($bookings)) {
    echo json_encode(['message' => 'No pending bookings found']);
} else {
    echo json_encode($bookings);
}

header('Content-Type: application/json');

$conn->close();
?>