<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

$key = "123456"; 

$data = json_decode(file_get_contents("php://input"));
$currentPassword = $data->currentPassword;
$token = $data->token;

if (!$currentPassword || !$token) {
    echo json_encode([
        'success' => false,
        'message' => 'Token and current password are required.'
    ]);
    exit();
}

try {
   $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
   $travelerID = $decoded->TravelerID;
} catch (ExpiredException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Token expired.'
    ]);
    exit();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid token.'
    ]);
    exit();
}

$query = "SELECT Password FROM traveler WHERE TravelerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $travelerID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($storedPassword);
$stmt->fetch();

if ($stmt->num_rows > 0) {
    if ($currentPassword == $storedPassword) {
        echo json_encode([
            'success' => true,
            'message' => 'Current password is correct.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect current password.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'User not found.'
    ]);
}

$stmt->close();
$conn->close();
?>
