<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../../db.php';
include 'encryption.php';

$headers = apache_request_headers();

// Decode JSON data
$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'];
$firstName = $data['firstName'];
$lastName = $data['lastName'];
$email = $data['email'];
$phone = $data['phoneNumber'];
$userType = $data['userType'];
$password = $data['password'];

$query = "INSERT INTO admin (firstName, lastName, email, phoneNumber, username, password, userType) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssssss", $firstName, $lastName, $email, $phone, $username, $password, $userType);

if ($stmt->execute()) {
    $adminID = $conn->insert_id;

    $encryptionKey = "123456";
    $role = "admin";
    $encryptedUUID = encrypt("$adminID - $username - $role", $encryptionKey);

    $updateQuery = "UPDATE admin SET adminUUID = ? WHERE adminID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $encryptedUUID, $adminID);

    if ($updateStmt->execute()) {
        echo json_encode(["message" => "User added and UUID updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update UUID"]);
    }

    $updateStmt->close();
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to add user"]);
}

$stmt->close();
$conn->close();
?>
