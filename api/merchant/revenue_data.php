<?php
session_start();

// Set CORS headers for all requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle pre-flight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set the content type to JSON
header("Content-Type: application/json");

// Include database connection and JWT libraries
include '../../db.php';
require '../../vendor/autoload.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "123456"; // Secret key, replace with a more secure one in production
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';

// Log the authorization header for debugging purposes
error_log("Authorization Header: " . $authorizationHeader);

// Check if the Authorization header is missing
if (empty($authorizationHeader)) {
    http_response_code(400);
    echo json_encode(['error' => 'Authorization header is missing']);
    exit;
}

// Extract the token from the Authorization header
$token = str_replace('Bearer ', '', $authorizationHeader);

// Validate the token format (should have 3 segments)
if (substr_count($token, '.') !== 2) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid token format']);
    exit;
}

try {
    // Decode the JWT token
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    // Get the traveler ID from the decoded token
    $travelerID = $decoded->TravelerID ?? null;

    // If TravelerID is not found in the token, return an error
    if (!$travelerID) {
        http_response_code(400);
        echo json_encode(['error' => 'TravelerID not found in the token']);
        exit;
    }

    // Prepare the query to fetch the merchant ID associated with the traveler
    $stmt = $conn->prepare("SELECT merchantID FROM merchant WHERE travelerID = ?");
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $result = $stmt->get_result();

    // If no merchant is found for the traveler, return an error
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Merchant not found for the given travelerID']);
        exit;
    }

    // Fetch the merchant ID
    $row = $result->fetch_assoc();
    $merchantID = $row['merchantID'];

    // Prepare the query to get completed bookings for the merchant
    $stmt = $conn->prepare("
        SELECT MONTH(bookingDate) AS month, YEAR(bookingDate) AS year, bookingDate, SUM(totalAmount) AS totalAmount
        FROM booking
        WHERE merchantID = ? AND bookingStatus = 'Completed' AND payoutStatus = 'completed'
        GROUP BY YEAR(bookingDate), MONTH(bookingDate), bookingDate
        ORDER BY year, month
    ");
    $stmt->bind_param("i", $merchantID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare the data to return
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'month' => $row['month'],
            'year' => $row['year'],
            'bookingDate' => $row['bookingDate'],
            'totalAmount' => $row['totalAmount']
        ];
    }

    // Return the data as JSON
    echo json_encode($data);

} catch (Exception $e) {
    // Handle errors in JWT decoding
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}
?>
