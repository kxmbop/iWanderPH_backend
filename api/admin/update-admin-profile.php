<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
include '../../db.php';
include 'encryption.php'; 

$input = json_decode(file_get_contents('php://input'), true);

$headers = apache_request_headers();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $key = "123456"; 
    $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));

    $adminID = $decoded->adminID; 

    $firstName = $input['firstName'] ?? null;
    $lastName = $input['lastName'] ?? null;
    $phoneNumber = $input['phoneNumber'] ?? null;
    $email = $input['email'] ?? null;
    $address = $input['address'] ?? null;
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;

    if (!$adminID || !$username || !$password) {
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }

    $roleType = "admin"; 
    $textToEncrypt = $adminID . " - " . $username . " - " . $roleType; 
    $adminUUID = encrypt($textToEncrypt, $key);

    $stmt = $conn->prepare("UPDATE admin SET adminUUID = ?, firstName = ?, lastName = ?, phoneNumber = ?, email = ?, address = ?, username = ?, password = ? WHERE adminID = ?");
    $stmt->bind_param("ssssssssi", $adminUUID, $firstName, $lastName, $phoneNumber, $email, $address, $username, $password, $adminID);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update profile']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid token or token expired']);
}
