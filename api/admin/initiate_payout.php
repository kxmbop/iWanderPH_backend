<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$bookingId = $_POST['bookingId'];

$stmt = $conn->prepare("UPDATE bookings SET PayoutStatus = 'initiated' WHERE BookingID = ?");
$stmt->bind_param("i", $bookingId);
$result = $stmt->execute();

if ($result) {
  echo 'Payout initiated successfully!';
} else {
  echo 'Error initiating payout: ' . $conn->error;
}
?>