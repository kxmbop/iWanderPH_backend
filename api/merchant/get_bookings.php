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

// Fetch token and status from query parameters using $_GET
$token = $_GET['token'] ?? null;
$status = $_GET['status'] ?? null;

if (!$token || !$status) {
    echo json_encode(['error' => 'Token and status are required']);
    exit;
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;
    $role = $decoded->role;
    
    // echo "TravelerID: $travelerID, Role: $role\n";
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}

// echo 'Traveler ID: ' . $travelerID . ', Status: ' . $status . "\n";

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

// echo 'Merchant ID: ' . $merchantID . "\n";

$sql = "
SELECT 
    b.bookingID,
    b.bookingDate,
    b.bookingStatus,
    b.paymentTransactionID,
    b.paymentStatus,
    b.subtotal,
    b.VAT,
    b.payoutAmount,
    b.totalAmount,
    b.payoutTransactionID,
    b.payoutStatus,
    b.refundReason,
    b.refundStatus,
    b.refundTransactionID,
    rb.RoomBookingID,
    rb.CheckInDate,
    rb.CheckOutDate,
    rb.SpecialRequest,
    tb.TransportationBookingID,
    tb.PickupDateTime,
    tb.DropoffDateTime,
    tb.PickupLocation,
    tb.DropoffLocation,
    t.Username AS TravelerUsername,
    t.FirstName AS TravelerFirstName,
    t.LastName AS TravelerLastName,
    m.BusinessName AS MerchantBusinessName
FROM booking b
LEFT JOIN room_booking rb ON b.roomBookingID = rb.RoomBookingID
LEFT JOIN transportation_booking tb ON b.transportationBookingID = tb.TransportationBookingID
JOIN traveler t ON b.TravelerID = t.TravelerID
JOIN merchant m ON b.MerchantID = m.MerchantID
WHERE b.MerchantID = ? AND b.bookingStatus = ?
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'SQL preparation failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("is", $merchantID, $status);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo json_encode(['error' => 'Query execution failed: ' . $stmt->error]);
    exit;
}

$bookings = $result->fetch_all(MYSQLI_ASSOC);

if (empty($bookings)) {
    echo json_encode(['message' => 'No bookings found with the given status']);
} else {
    echo json_encode($bookings);
}

$conn->close();
?>
