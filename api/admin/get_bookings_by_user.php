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

$query = "SELECT b.BookingID, b.BookingDate, m.businessName, b.BookingStatus, b.BookingType, b.TotalAmount 
          FROM booking AS b 
          JOIN merchant AS m ON b.merchantID = m.merchantID 
          WHERE b.travelerID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId); 

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC); 
    echo json_encode(['success' => true, 'data' => $bookings]); 
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch bookings']);
}

$stmt->close();
$conn->close();
?>
