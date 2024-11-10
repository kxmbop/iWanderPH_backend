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
$key = "123456"; // Replace with your actual key

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

        // Fetch completed bookings with associated merchant information
        $completedBookingsQuery = "
            SELECT m.businessName, m.email, m.contact, m.address, m.profilePicture
            FROM booking b
            JOIN merchant m ON b.merchantID = m.merchantID
            WHERE b.TravelerID = ? AND b.bookingStatus = 'Completed'
        ";
        $stmt = $conn->prepare($completedBookingsQuery);
        $stmt->bind_param("i", $travelerID);
        $stmt->execute();
        $completedBookingsResult = $stmt->get_result();

        $completedBookings = [];
        while ($booking = $completedBookingsResult->fetch_assoc()) {
            $booking['profilePicture'] = base64_encode($booking['profilePicture']); // Encode profile picture for frontend display
            $completedBookings[] = $booking;
        }
        
        $response["completedBookings"] = $completedBookings;
        $response["success"] = true;
        $response["message"] = "Completed bookings retrieved successfully.";
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
