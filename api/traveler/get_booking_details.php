<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
include '../../db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$response = [];
$key = "123456";  // Secret key

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        $bookingId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $bookingType = isset($_GET['type']) ? $_GET['type'] : '';

        // Validate BookingID and BookingType
        if (!$bookingId || !$bookingType) {
            $response["success"] = false;
            $response["message"] = "Invalid BookingID or BookingType.";
            echo json_encode($response);
            exit();
        }

// Continue with booking details retrieval logic...


        // Fetch booking details
        $sql_booking = "SELECT * FROM booking WHERE BookingID = ? AND TravelerID = ?";
        $stmt_booking = $conn->prepare($sql_booking);
        $stmt_booking->bind_param("ii", $bookingId, $travelerID);
        $stmt_booking->execute();
        $result_booking = $stmt_booking->get_result();
        $booking = $result_booking->fetch_assoc();
        $stmt_booking->close();

        if ($booking) {
            // Encode the PaymentUpload if it's not empty
            if (!empty($booking['PaymentUpload'])) {
                $booking['PaymentUpload'] = base64_encode($booking['PaymentUpload']);
            }
            $details['booking'] = $booking;
        }


        if ($bookingType === 'room') {
            // Fetch room booking details
            $roomBookingID = $booking['RoomBookingID'];
            $sql_room_booking = "SELECT CheckInDate, CheckOutDate, SpecialRequest, RoomID FROM room_booking WHERE RoomBookingID = ?";
            $stmt_room_booking = $conn->prepare($sql_room_booking);
            $stmt_room_booking->bind_param("i", $roomBookingID);
            $stmt_room_booking->execute();
            $result_room_booking = $stmt_room_booking->get_result();
            $roomBooking = $result_room_booking->fetch_assoc();
            $stmt_room_booking->close();

            $details['room_booking'] = $roomBooking;

            // Fetch room details
            $roomID = $roomBooking['RoomID'];
            $sql_room = "SELECT RoomName, RoomRate, MerchantID FROM rooms WHERE RoomID = ?";
            $stmt_room = $conn->prepare($sql_room);
            $stmt_room->bind_param("i", $roomID);
            $stmt_room->execute();
            $result_room = $stmt_room->get_result();
            $room = $result_room->fetch_assoc();
            $stmt_room->close();

            $details['room'] = $room;

            // Fetch merchant details
            $merchantID = $room['MerchantID'];
            $sql_merchant = "SELECT BusinessName, Email, Contact, Address, merchant_img FROM merchant WHERE MerchantID = ?";
            $stmt_merchant = $conn->prepare($sql_merchant);
            $stmt_merchant->bind_param("i", $merchantID);
            $stmt_merchant->execute();
            $result_merchant = $stmt_merchant->get_result();
            $merchant = $result_merchant->fetch_assoc();
            $stmt_merchant->close();

            if ($merchant && $merchant['merchant_img']) {
                $merchant['merchant_img'] = base64_encode($merchant['merchant_img']); // Convert image to base64
            }

            $details['merchant'] = $merchant;

            // Fetch room gallery
            $sql_gallery = "SELECT ImageFile FROM room_gallery WHERE RoomID = ?";
            $stmt_gallery = $conn->prepare($sql_gallery);
            $stmt_gallery->bind_param("i", $roomID);
            $stmt_gallery->execute();
            $result_gallery = $stmt_gallery->get_result();
            $gallery = [];
            while ($image = $result_gallery->fetch_assoc()) {
                $gallery[] = base64_encode($image['ImageFile']);
            }
            $stmt_gallery->close();

            $details['room']['gallery'] = $gallery;

        } elseif ($bookingType === 'transportation') {
            // Fetch transportation booking details
            $transportationBookingID = $booking['TransportationBookingID'];
            $sql_transportation_booking = "SELECT PickupLocation, DropoffLocation, PickupDateTime, DropoffDateTime, TransportationID FROM transportation_booking WHERE TransportationBookingID = ?";
            $stmt_transportation_booking = $conn->prepare($sql_transportation_booking);
            $stmt_transportation_booking->bind_param("i", $transportationBookingID);
            $stmt_transportation_booking->execute();
            $result_transportation_booking = $stmt_transportation_booking->get_result();
            $transportationBooking = $result_transportation_booking->fetch_assoc();
            $stmt_transportation_booking->close();

            $details['transportation_booking'] = $transportationBooking;

            // Fetch transportation details
            $transportationID = $transportationBooking['TransportationID'];
            $sql_transportation = "SELECT VehicleName, Model, Brand, Capacity, DriverName, DriverContactNo, RentalPrice, MerchantID FROM transportations WHERE TransportationID = ?";
            $stmt_transportation = $conn->prepare($sql_transportation);
            $stmt_transportation->bind_param("i", $transportationID);
            $stmt_transportation->execute();
            $result_transportation = $stmt_transportation->get_result();
            $transportation = $result_transportation->fetch_assoc();
            $stmt_transportation->close();

            $details['transportation'] = $transportation;

            // Fetch merchant details
            $merchantID = $transportation['MerchantID'];
            $sql_merchant = "SELECT BusinessName, Email, Contact, Address, merchant_img FROM merchant WHERE MerchantID = ?";
            $stmt_merchant = $conn->prepare($sql_merchant);
            $stmt_merchant->bind_param("i", $merchantID);
            $stmt_merchant->execute();
            $result_merchant = $stmt_merchant->get_result();
            $merchant = $result_merchant->fetch_assoc();
            $stmt_merchant->close();

            if ($merchant && $merchant['merchant_img']) {
                $merchant['merchant_img'] = base64_encode($merchant['merchant_img']); // Convert image to base64
            }

            $details['merchant'] = $merchant;

            // Fetch transportation gallery
            $sql_gallery_transport = "SELECT ImageFile FROM transportation_gallery WHERE TransportationID = ?";
            $stmt_gallery_transport = $conn->prepare($sql_gallery_transport);
            $stmt_gallery_transport->bind_param("i", $transportationID);
            $stmt_gallery_transport->execute();
            $result_gallery_transport = $stmt_gallery_transport->get_result();
            $gallery_transport = [];
            while ($image = $result_gallery_transport->fetch_assoc()) {
                $gallery_transport[] = base64_encode($image['ImageFile']);
            }
            $stmt_gallery_transport->close();

            $details['transportation']['gallery'] = $gallery_transport;
        }

        $response["success"] = true;
        $response["details"] = $details;
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = "Invalid token or booking retrieval failed: " . $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "No token provided.";
}

$conn->close();
echo json_encode($response);
?>
