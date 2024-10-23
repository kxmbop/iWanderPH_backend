<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; 

$token = $_SESSION['token'] ?? '';

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        $booking_sql = "SELECT b.BookingID, b.BookingDate, b.BookingStatus, b.TotalAmount, b.BookingType,
                       (SELECT COUNT(*) FROM reviews r WHERE r.bookingID = b.BookingID AND r.TravelerID = ?) AS hasReview
                        FROM booking b 
                        WHERE b.TravelerID = ?";
        $stmt = $conn->prepare($booking_sql);
        $stmt->bind_param("ii", $travelerID, $travelerID);
        $stmt->execute();
        $booking_result = $stmt->get_result();

        $bookings = [];
        if ($booking_result->num_rows > 0) {
            while ($row = $booking_result->fetch_assoc()) {
                $bookings[] = $row;
            }
            $response["bookings"] = $bookings;
        } else {
            $response["bookings"] = [];
        }


        $response["message"] = "Bookings retrieved successfully.";
        $response["success"] = true;
    } catch (ExpiredException $e) {
        $response["success"] = false;
        $response["message"] = "Token expired: " . $e->getMessage();
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = "Invalid token: " . $e->getMessage(); 
    }
} else {
    $response["success"] = false;
    $response["message"] = "No token provided.";
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
