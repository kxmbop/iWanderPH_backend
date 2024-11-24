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

$sqlRefundedDetails = "SELECT 
                       DATE_FORMAT(b.bookingDate, '%m/%d/%Y') AS bookingDate,  
                       b.bookingID, 
                       m.businessName AS merchantName,  
                       b.bookingStatus, 
                       b.roomBookingID, 
                       b.paymentTransactionID, 
                       b.paymentStatus, 
                       b.totalAmount, 
                       b.payoutStatus, 
                       DATE_FORMAT(b.payoutReleaseDate, '%m/%d/%Y') AS paymentReleasedDate,  
                       b.refundReason
                    FROM booking b
                    JOIN merchant m ON b.merchantID = m.merchantID  
                    WHERE b.bookingStatus = 'Refunded'";


$totalResult = $conn->query($sqlTotal);
if ($totalResult === false) {
    echo json_encode(['error' => 'Error in total bookings query: ' . $conn->error]);
    exit();
}

$refundedResult = $conn->query($sqlRefunded);
if ($refundedResult === false) {
    echo json_encode(['error' => 'Error in refunded bookings query: ' . $conn->error]);
    exit();
}

$refundedDetailsResult = $conn->query($sqlRefundedDetails);
if ($refundedDetailsResult === false) {
    echo json_encode(['error' => 'Error in refunded details query: ' . $conn->error]);
    exit();
}

if ($totalResult->num_rows > 0 && $refundedResult->num_rows > 0 && $refundedDetailsResult->num_rows > 0) {
    // Fetch the results only once
    $totalRow = $totalResult->fetch_assoc();
    $refundedRow = $refundedResult->fetch_assoc();
    $refundedDetails = [];
    
    while ($row = $refundedDetailsResult->fetch_assoc()) {
        $refundedDetails[] = $row;
    }

    // If either the total or refunded result is empty, return an error
    if (!$totalRow || !$refundedRow) {
        echo json_encode(['error' => 'Failed to fetch required data']);
        exit();
    }

    $totalBookings = $totalRow['totalBookings'];
    $refundedBookings = $refundedRow['refundedBookings'];
    $totalRefundedAmount = $refundedRow['totalRefundedAmount'];

    $refundedPercentage = ($totalBookings > 0) ? ($refundedBookings / $totalBookings) * 100 : 0;

    if (empty($refundedDetails)) {
        echo json_encode(['error' => 'No refunded details found']);
        exit();
    }

    echo json_encode([
        'refundedPercentage' => round($refundedPercentage, 2),
        'totalRefundedAmount' => $totalRefundedAmount,
        'refundedDetails' => $refundedDetails
    ]);
} else {
    echo json_encode(['error' => 'No data available']);
}

$conn->close();
?>
