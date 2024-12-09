<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

$bookingID = $data['bookingID'];
$checkInPickUp = $data['checkInPickUp'];
$checkOutDropOff = $data['checkOutDropOff'];
$excessDays = ceil((strtotime($checkOutDropOff) - strtotime($checkInPickUp)) / 86400);
$paymentStatus = 'pending';

$query = "SELECT roomBookingID FROM booking WHERE bookingID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $bookingID);
$stmt->execute();
$result = $stmt->get_result();
$roomBookingID = $result->fetch_assoc()['roomBookingID'];

if ($roomBookingID) {
   $query = "SELECT RoomID FROM room_booking WHERE roomBookingID = ?";
   $stmt = $conn->prepare($query);
   $stmt->bind_param('i', $roomBookingID);
   $stmt->execute();
   $result = $stmt->get_result();
   $roomID = $result->fetch_assoc()['RoomID'];

   if ($roomID) {
      $query = "SELECT RoomRate FROM rooms WHERE RoomID = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param('i', $roomID);
      $stmt->execute();
      $result = $stmt->get_result();
      $roomRate = $result->fetch_assoc()['RoomRate'];

      if ($roomRate) {
            $totalAmount = $excessDays * $roomRate;

            $query = "INSERT INTO booking_update_log (bookingID, checkIn_pickUp, checkOut_dropOff, excessDays, paymentStatus, totalAmount)
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ississ', $bookingID, $checkInPickUp, $checkOutDropOff, $excessDays, $paymentStatus, $totalAmount);

            if ($stmt->execute()) {
               echo json_encode(['message' => 'Extension logged successfully.']);
            } else {
               echo json_encode(['error' => 'Failed to log extension.']);
            }
      } else {
            echo json_encode(['error' => 'RoomRate not found.']);
      }
   } else {
      echo json_encode(['error' => 'RoomID not found.']);
   }
} else {
   echo json_encode(['error' => 'roomBookingID not found.']);
}

?>
