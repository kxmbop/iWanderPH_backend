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

include '../../db.php'; // Adjust the path to your db.php file
require '../../vendor/autoload.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "123456"; // Replace with your secure key
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

try {
    // Decode the token
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    // Get the year from the request body
    $input = json_decode(file_get_contents('php://input'), true);
    $year = $input['year'] ?? date('Y'); // Default to the current year if not provided

    // Prepare the query to get completed bookings grouped by month
    $stmt = $conn->prepare("
        SELECT MONTH(bookingDate) AS month, COUNT(*) AS count
        FROM booking
        WHERE bookingStatus = 'Completed' AND YEAR(bookingDate) = ?
        GROUP BY MONTH(bookingDate)
        ORDER BY MONTH(bookingDate)
    ");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'month' => str_pad($row['month'], 2, '0', STR_PAD_LEFT),    
            'count' => $row['count']
        ];
    }

    // Return the data as JSON
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}
