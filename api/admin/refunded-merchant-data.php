<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$sqlTotal = "SELECT COUNT(*) AS totalBookings FROM booking";
$sqlRefunded = "SELECT 
                  COUNT(*) AS refundedBookings, 
                  SUM(totalAmount) AS totalRefundedAmount 
                FROM booking 
                WHERE bookingStatus = 'Refunded'";

$totalResult = $conn->query($sqlTotal);
$refundedResult = $conn->query($sqlRefunded);

if ($totalResult->num_rows > 0 && $refundedResult->num_rows > 0) {
    $totalRow = $totalResult->fetch_assoc();
    $refundedRow = $refundedResult->fetch_assoc();

    $totalBookings = $totalRow['totalBookings'];
    $refundedBookings = $refundedRow['refundedBookings'];
    $totalRefundedAmount = $refundedRow['totalRefundedAmount'];

    $refundedPercentage = ($totalBookings > 0) ? ($refundedBookings / $totalBookings) * 100 : 0;

    echo json_encode([
        'refundedPercentage' => round($refundedPercentage, 2),
        'totalRefundedAmount' => $totalRefundedAmount
    ]);
} else {
    echo json_encode(['error' => 'No data available']);
}

$conn->close();
?>
