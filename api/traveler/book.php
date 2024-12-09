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
$key = "123456"; 

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;
        $travelerUsername = $decoded->Username;

        $bookingData = json_decode($_POST['bookingData'], true);
        $bookingType = $bookingData['type'];
        $itemId = intval($bookingData['itemId']);
        $subtotal = floatval($bookingData['subtotal']);
        $payoutAmount = floatval($bookingData['payout']);
        $paymentMethod = $bookingData['paymentMethod'];

        // Determine payment method: proofOfPayment or payOnSite
        $proofOfPayment = null;
        $payOnSite = false;
        
        if ($paymentMethod === 'gcash' && isset($_FILES['proofOfPayment']) && $_FILES['proofOfPayment']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['proofOfPayment']['tmp_name'];
            $proofOfPayment = file_get_contents($fileTmpName);
        } elseif ($paymentMethod === 'payOnSite') {
            $payOnSite = true;
        }

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

        $vat = round($subtotal * 0.12, 2); 
        $totalAmount = round($subtotal + $vat, 2);

        $stmt = $conn->prepare("INSERT INTO booking (TravelerID, proofOfPayment, payOnSite, PaymentStatus, BookingStatus, Subtotal, VAT, PayoutAmount, TotalAmount, BookingType, merchantID) VALUES (?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdddsi", $travelerID, $proofOfPayment, $payOnSite, $subtotal, $vat, $payoutAmount, $totalAmount, $bookingType, $merchantID);
        $stmt->execute();
        $bookingID = $stmt->insert_id;
        $stmt->close();

        if ($bookingType === 'room') {
            $checkIn = $bookingData['checkIn'];
            $checkOut = $bookingData['checkOut'];
            $specialRequest = $bookingData['specialRequest'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO room_booking (CheckInDate, CheckOutDate, SpecialRequest, RoomID, TravelerID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $checkIn, $checkOut, $specialRequest, $itemId, $travelerID);
            $stmt->execute();
            $roomBookingID = $stmt->insert_id; 
            $stmt->close();

            $stmt = $conn->prepare("UPDATE booking SET RoomBookingID = ? WHERE BookingID = ?");
            $stmt->bind_param("ii", $roomBookingID, $bookingID);
            $stmt->execute();
            $stmt->close();
        } elseif ($bookingType === 'transportation') {
            $pickupLocation = $bookingData['pickupLocation'];
            $dropOffLocation = $bookingData['dropOffLocation'];
            $pickupDateTime = $bookingData['pickupDateTime'];
            $dropOffDateTime = $bookingData['dropOffDateTime'];
            
            $stmt = $conn->prepare("INSERT INTO transportation_booking (PickupLocation, DropoffLocation, PickupDateTime, DropoffDateTime, TransportationID, TravelerID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $pickupLocation, $dropOffLocation, $pickupDateTime, $dropOffDateTime, $itemId, $travelerID);
            $stmt->execute();
            $transportationBookingID = $stmt->insert_id; 
            $stmt->close();

            $stmt = $conn->prepare("UPDATE booking SET TransportationBookingID = ? WHERE BookingID = ?");
            $stmt->bind_param("ii", $transportationBookingID, $bookingID);
            $stmt->execute();
            $stmt->close();
        }

        $notificationMessage = "Booking request sent by traveler '$travelerUsername' for Booking ID: $bookingID. Please review the booking details and confirm or reject the request at your earliest convenience.";
        $stmt = $conn->prepare("INSERT INTO notifications (bookingID, notificationMessage, userID, isRead) VALUES (?, ?, ?, '0')");
        $stmt->bind_param("isi", $bookingID, $notificationMessage, $merchantID);
        $stmt->execute();
        $stmt->close();

        $response["success"] = true;
        $response["message"] = "Booking created and notification sent successfully.";
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "No token provided.";
}

$conn->close();
echo json_encode($response);
?>
