<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$response = [];
$key = "123456"; // The same secret key used in your token generation

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        // Retrieve and decode the booking data
        $bookingData = json_decode($_POST['bookingData'], true);
        $bookingType = $bookingData['type'];
        $itemId = intval($bookingData['itemId']);
        $subtotal = floatval($bookingData['subtotal']);
        $payoutAmount = floatval($bookingData['payout']);

        // Handle file upload
        $paymentUpload = null;
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['file']['tmp_name'];
            $paymentUpload = file_get_contents($fileTmpName); // Save file as binary data
        }

        // Determine BookingType (room/transportation)
        $merchantID = null;
        if ($bookingType === 'room') {
            $stmt = $conn->prepare("SELECT MerchantID FROM rooms WHERE RoomID = ?");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $stmt->bind_result($merchantID);
            $stmt->fetch();
            $stmt->close();
        } elseif ($bookingType === 'transportation') {
            $stmt = $conn->prepare("SELECT MerchantID FROM transportations WHERE TransportationID = ?");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $stmt->bind_result($merchantID);
            $stmt->fetch();
            $stmt->close();
        }

        if (!$merchantID) {
            throw new Exception("Invalid RoomID or TransportationID provided.");
        }

        // Insert into booking table
        $vat = round($subtotal * 0.12, 2); // 12% VAT
        $totalAmount = round($subtotal + $vat, 2);

        $stmt = $conn->prepare("INSERT INTO booking (TravelerID, PaymentUpload, PaymentStatus, BookingStatus, Subtotal, VAT, PayoutAmount, TotalAmount, BookingType) VALUES (?, ?, 'pending', 'pending', ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddds", $travelerID, $paymentUpload, $subtotal, $vat, $payoutAmount, $totalAmount, $bookingType);
        $stmt->execute();
        $bookingID = $stmt->insert_id;
        $stmt->close();

        // Additional handling for room or transportation bookings
        if ($bookingType === 'room') {
            $checkIn = $bookingData['checkIn'];
            $checkOut = $bookingData['checkOut'];
            $specialRequest = $bookingData['specialRequest'] ?? '';
            
            // Insert into room_booking table
            $stmt = $conn->prepare("INSERT INTO room_booking (CheckInDate, CheckOutDate, SpecialRequest, RoomID, TravelerID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $checkIn, $checkOut, $specialRequest, $itemId, $travelerID);
            $stmt->execute();
            $roomBookingID = $stmt->insert_id; // Get the RoomBookingID
            $stmt->close();

            // Update booking table with RoomBookingID
            $stmt = $conn->prepare("UPDATE booking SET RoomBookingID = ? WHERE BookingID = ?");
            $stmt->bind_param("ii", $roomBookingID, $bookingID);
            $stmt->execute();
            $stmt->close();
        } elseif ($bookingType === 'transportation') {
            $pickupLocation = $bookingData['pickupLocation'];
            $dropOffLocation = $bookingData['dropOffLocation'];
            $pickupDateTime = $bookingData['pickupDateTime'];
            $dropOffDateTime = $bookingData['dropOffDateTime'];
            
            // Insert into transportation_booking table
            $stmt = $conn->prepare("INSERT INTO transportation_booking (PickupLocation, DropoffLocation, PickupDateTime, DropoffDateTime, TransportationID, TravelerID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $pickupLocation, $dropOffLocation, $pickupDateTime, $dropOffDateTime, $itemId, $travelerID);
            $stmt->execute();
            $transportationBookingID = $stmt->insert_id; // Get the TransportationBookingID
            $stmt->close();

            // Update booking table with TransportationBookingID
            $stmt = $conn->prepare("UPDATE booking SET TransportationBookingID = ? WHERE BookingID = ?");
            $stmt->bind_param("ii", $transportationBookingID, $bookingID);
            $stmt->execute();
            $stmt->close();
        }

        $response["success"] = true;
        $response["message"] = "Booking created successfully.";
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "No token provided.";
}
//hi
$conn->close();
echo json_encode($response);
?>
