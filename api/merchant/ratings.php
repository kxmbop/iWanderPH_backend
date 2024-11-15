<?php
session_start();

// Set CORS headers
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
    $month = $input['month'] ?? date('m');  // Default to current month if not provided
    $year = $input['year'] ?? date('Y');   // Default to current year if not provided

    // Format the first and last day of the month
    $start_date = "$year-$month-01";
    $end_date = date("Y-m-t", strtotime($start_date)); // Get the last day of the month

    // Get the average review rating grouped by date within the month
    $stmt = $conn->prepare("
        SELECT DATE(reviews.createdAt) AS review_date, AVG(reviews.reviewRating) AS averageRating
        FROM reviews
        JOIN booking ON reviews.bookingID = booking.bookingID
        WHERE booking.merchantID = ? AND reviews.createdAt BETWEEN ? AND ?
        GROUP BY review_date
        ORDER BY review_date DESC
    ");
    $stmt->bind_param("iss", $merchantID, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'review_date' => $row['review_date'],
            'averageRating' => $row['averageRating']
        ];
    }

    // Return the data
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}
?>
