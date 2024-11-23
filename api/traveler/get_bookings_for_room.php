<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../../db.php';

$roomId = isset($_GET['roomId']) ? intval($_GET['roomId']) : 0;

$response = array();

if ($roomId > 0) {
    // Fetch all bookings for the room that are not completed
    $sql = "SELECT bookingStatus FROM booking WHERE roomBookingID = $roomId AND bookingStatus != 'Completed'";
    $result = $conn->query($sql);

    $bookings = array();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    $response['bookings'] = $bookings;
} else {
    $response['error'] = 'Invalid Room ID';
}

echo json_encode($response);
?>
