<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
include 'encryption.php'; 


$data = json_decode(file_get_contents("php://input"), true);

$adminID = $data['adminID'];
$firstName = $data['firstName'];
$lastName = $data['lastName'];
$email = $data['email'];
$phone = $data['phoneNumber'];
$userType = $data['userType'];
$username = $data['username'];
$password = $data['password'];

$encryptionKey = "123456";
$role = "admin";
$encryptedUUID = encrypt("$adminID - $username - $role", $encryptionKey);

$query = "UPDATE admin SET adminUUID = ?, firstName = ?, lastName = ?, email = ?, phoneNumber = ?, username = ?, password = ?, userType = ? WHERE adminID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssssssss", $encryptedUUID, $firstName, $lastName, $email, $phone, $username, $password, $userType, $adminID);

if ($stmt->execute()) {
    echo json_encode(["message" => "User updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update user"]);
}

$stmt->close();
$conn->close();

?>
