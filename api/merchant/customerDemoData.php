<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

require '../../vendor/autoload.php'; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$key = "123456"; // Replace with a secure key
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

try {
    // Decode the token
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID ?? null;

    if (!$travelerID) {
        http_response_code(400);
        echo json_encode(['error' => 'TravelerID not found in the token']);
        exit;
    }

    // Get the merchantID using travelerID
    $stmt = $conn->prepare("SELECT merchantID FROM merchant WHERE travelerID = ?");
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Merchant not found for the given travelerID']);
        exit;
    }

    $row = $result->fetch_assoc();
    $merchantID = $row['merchantID'];

    // Get the month and year from the request body
    $input = json_decode(file_get_contents('php://input'), true);
    $month = $input['month'] ?? date('m'); // Default to current month if not provided
    $year = $input['year'] ?? date('Y');   // Default to current year if not provided

    // Query to get completed bookings for repeat and new customers
    $stmt = $conn->prepare("
        SELECT travelerID, COUNT(*) AS bookingCount
        FROM booking
        WHERE merchantID = ? AND bookingStatus = 'Completed' AND MONTH(bookingDate) = ? AND YEAR(bookingDate) = ?
        GROUP BY travelerID
    ");
    $stmt->bind_param("iss", $merchantID, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $repeatCustomers = 0;
    $newCustomers = 0;

    while ($row = $result->fetch_assoc()) {
        if ($row['bookingCount'] > 1) {
            $repeatCustomers++;
        } else {
            $newCustomers++;
        }
    }

    // Return the data as JSON
    echo json_encode([
        'repeatCustomers' => $repeatCustomers,
        'newCustomers' => $newCustomers
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}
