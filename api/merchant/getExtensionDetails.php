<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Extract the bookingID from the decoded data
$bookingID = $data['bookingID'];

// Prepare and execute query
$query = "SELECT * FROM booking_update_log WHERE bookingID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bookingID);
$stmt->execute();
$result = $stmt->get_result();

// Check if any rows were returned
if ($result->num_rows > 0) {
    $extensions = [];
    while ($row = $result->fetch_assoc()) {
        $extensions[] = [
            'bookingID' => $row['bookingID'],
            'checkIn_pickUp' => $row['checkIn_pickUp'],
            'checkOut_dropOff' => $row['checkOut_dropOff'],
            'excessDays' => $row['excessDays'],
            'paymentStatus' => $row['paymentStatus'],
            'totalAmount' => $row['totalAmount']

        ];
    }
    echo json_encode(['data' => $extensions]);
} else {
    echo json_encode(['data' => []]);
}

$conn->close();
?>
