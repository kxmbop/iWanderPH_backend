<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['bookingID']) || !isset($data['transactionID']) || !isset($data['paymentStatus'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$bookingID = $data['bookingID'];
$transactionID = $data['transactionID'];
$paymentStatus = $data['paymentStatus'];

// Log values for debugging
error_log("Booking ID: $bookingID");
error_log("Transaction ID: $transactionID");
error_log("Payment Status: $paymentStatus");

// Validate payment status against allowed values
$allowedStatuses = ['failed', 'pending', 'successful'];
if (!in_array($paymentStatus, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment status']);
    exit();
}

$query = "UPDATE booking SET paymentTransactionID = ?, paymentStatus = ? WHERE BookingID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $transactionID, $paymentStatus, $bookingID);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed', 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();

?>
