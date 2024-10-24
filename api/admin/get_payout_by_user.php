<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$userId = intval($_GET['user_id']); 

$merchantQuery = "SELECT merchantID FROM merchant WHERE travelerID = ?";
$merchantStmt = $conn->prepare($merchantQuery);
$merchantStmt->bind_param("i", $userId);

if ($merchantStmt->execute()) {
    $merchantResult = $merchantStmt->get_result();
    
    if ($merchantResult->num_rows > 0) {
        $merchant = $merchantResult->fetch_assoc();
        $merchantID = $merchant['merchantID'];

        $bookingQuery = "SELECT b.BookingID, b.BookingDate, b.BookingStatus, b.BookingType, b.PayoutAmount, b.PayoutStatus, b.payoutTransactionID
                         FROM booking AS b 
                         WHERE b.merchantID = ?";
        $bookingStmt = $conn->prepare($bookingQuery);
        $bookingStmt->bind_param("i", $merchantID);

        if ($bookingStmt->execute()) {
            $bookingResult = $bookingStmt->get_result();
            $saleBookings = $bookingResult->fetch_all(MYSQLI_ASSOC); 
            echo json_encode(['success' => true, 'data' => $saleBookings]); 
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch bookings']);
        }

        $bookingStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Merchant not found']);
    }

    $merchantStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch merchant information']);
}

$conn->close();
?>
