<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

$bookingId = $data['bookingId'];
$payoutTransactionID = $data['payoutTransactionID'];

$stmt = $conn->prepare("UPDATE bookings SET PayoutStatus = 'completed', payoutGCashTransactionID = ? WHERE BookingID = ?");
$stmt->bind_param("si", $payoutTransactionID, $bookingId);
$result = $stmt->execute();

if ($result) {
  echo 'Payout processed successfully!';
} else {
  echo 'Error processing payout: ' . $conn->error;
}
?>