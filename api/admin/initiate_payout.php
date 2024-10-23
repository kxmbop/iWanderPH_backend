<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

$bookingId = isset($data['bookingId']) ? $data['bookingId'] : null;
$payoutTransactionID = isset($data['payoutTransactionID']) ? $data['payoutTransactionID'] : null;

if ($bookingId === null) {
    echo json_encode(['error' => 'Booking ID is required.']);
    exit;
}

$stmt = $conn->prepare("UPDATE booking SET payoutStatus = 'completed', payoutTransactionID = ?, payoutReleaseDate = NOW() WHERE BookingID = ?");

$stmt->bind_param("si", $payoutTransactionID, $bookingId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => 'Payout initiated successfully!']);
    } else {
        echo json_encode(['error' => 'No records updated. Check if the Booking ID is correct or already updated.']);
    }
} else {
    echo json_encode(['error' => 'Error initiating payout: ' . $stmt->error]);
}

$stmt->close();
?>
