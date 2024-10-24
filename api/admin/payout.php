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

$stmt = $conn->prepare("SELECT merchantID FROM booking WHERE BookingID = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$stmt->bind_result($merchantId);
$stmt->fetch();
$stmt->close();

if (!$merchantId) {
    echo json_encode(['error' => 'No merchant found for this Booking ID.']);
    exit;
}

$stmt = $conn->prepare("UPDATE booking SET payoutStatus = 'completed', payoutTransactionID = ?, payoutReleaseDate = NOW() WHERE BookingID = ?");
$stmt->bind_param("si", $payoutTransactionID, $bookingId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $notificationMessage = "You have been paid out for Booking ID #{$bookingId}. Please check your GCash balance.";
        
        $notificationStmt = $conn->prepare("INSERT INTO notifications (bookingID, userID, notificationMessage, createdAt) VALUES (?, ?, ?, NOW())");
        $notificationStmt->bind_param("iis", $bookingId, $merchantId, $notificationMessage);

        if ($notificationStmt->execute()) {
            echo json_encode(['success' => 'Payout initiated successfully! Notification created.']);
        } else {
            echo json_encode(['error' => 'Payout initiated, but error creating notification: ' . $notificationStmt->error]);
        }

        $notificationStmt->close();
    } else {
        echo json_encode(['error' => 'No records updated. Check if the Booking ID is correct or already updated.']);
    }
} else {
    echo json_encode(['error' => 'Error initiating payout: ' . $stmt->error]);
}

$stmt->close();
?>
