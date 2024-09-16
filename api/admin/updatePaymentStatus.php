<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

$data = json_decode(file_get_contents('php://input'), true);
$bookingId = $data['bookingId'];

if (!$bookingId) {
    echo json_encode(['error' => 'Booking ID is required']);
    exit;
}

$sql = "UPDATE bookings SET payoutStatus = 'completed' WHERE BookingID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bookingId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update payment status']);
}

$stmt->close();
$conn->close();
?>
