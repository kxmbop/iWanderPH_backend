<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

$bookingId = $data['bookingId'];
$refundAmount = $data['refundAmount'];
$refundTransactionID = $data['refundTransactionID'];
$refundReason = $data['refundReason'];
$refundReasonOther = $data['refundReasonOther'];

$stmt = $conn->prepare("UPDATE bookings SET RefundAmount = ?, refundGCashTransactionID = ?, RefundReason = ?, RefundReasonOther = ?, RefundStatus = 'processed' WHERE bookingID = ?");
$stmt->bind_param("sssss", $refundAmount, $refundTransactionID, $refundReason, $refundReasonOther, $bookingId);
$result = $stmt->execute();

if ($result) {
  echo 'Refund processed successfully!';
} else {
  echo 'Error processing refund: ' . $conn->error;
}
?>