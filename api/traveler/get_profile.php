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
$key = "123456";  // Replace with your actual key

$token = $_SESSION['token'] ?? '';

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;
        $role = $decoded->role;

        if ($role !== 'traveler') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
            exit;
        }

        // Fetch traveler profile information

        $profile_sql = "
        SELECT t.TravelerID, t.FirstName, t.LastName, t.Username, t.ProfilePic, t.Bio, t.isMerchant, m.isApproved 
        FROM traveler t 
        LEFT JOIN merchant m ON t.TravelerID = m.travelerID 
        WHERE t.TravelerID = ?
        ";

        $stmt = $conn->prepare($profile_sql);
        $stmt->bind_param("i", $travelerID);
        $stmt->execute();
        $profile_result = $stmt->get_result();

        if ($profile_result->num_rows == 1) {
            $profile_data = $profile_result->fetch_assoc();
            $profile_data['ProfilePic'] = base64_encode($profile_data['ProfilePic']);
            $response["profile"] = $profile_data;
        } else {
            $response["profile"] = null;
        }

        // Fetch journey count for completed bookings
        $journey_sql = "SELECT COUNT(*) AS journey_count FROM booking WHERE TravelerID = ? AND BookingStatus = 'Completed'";
        $stmt2 = $conn->prepare($journey_sql);
        $stmt2->bind_param("i", $travelerID);
        $stmt2->execute();
        $journey_result = $stmt2->get_result();
        $journey_data = $journey_result->fetch_assoc();
        $response["journeys"] = $journey_data["journey_count"];

        // Fetch details of completed bookings with associated merchant information
        $completedBookingsQuery = "
            SELECT m.businessName, m.email, m.contact, m.address, m.profilePicture
            FROM booking b
            JOIN merchant m ON b.merchantID = m.merchantID
            WHERE b.TravelerID = ? AND b.bookingStatus = 'Completed'
        ";
        $stmt3 = $conn->prepare($completedBookingsQuery);
        $stmt3->bind_param("i", $travelerID);
        $stmt3->execute();
        $completedBookingsResult = $stmt3->get_result();

        $completedBookings = [];
        while ($booking = $completedBookingsResult->fetch_assoc()) {
            $booking['profilePicture'] = base64_encode($booking['profilePicture']); // Encode profile picture
            $completedBookings[] = $booking;
        }
        $response["completedBookings"] = $completedBookings;

        $response["message"] = "Token is valid.";
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