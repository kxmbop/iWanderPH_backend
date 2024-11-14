<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "123456"; // Replace with a secure key
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID ?? null;

    if (!$travelerID) {
        http_response_code(400);
        echo json_encode(['error' => 'TravelerID not found in the token']);
        exit;
    }

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

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'month' => $row['month'],
            'year' => $row['year'],
            'bookingDate' => $row['bookingDate'],
            'totalAmount' => $row['totalAmount']
        ];
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}
?>
