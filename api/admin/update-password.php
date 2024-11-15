<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

// Receive the new password from the frontend
$data = json_decode(file_get_contents("php://input"), true);
$adminID = $data['adminID']; // Admin ID should be passed from the frontend
$newPassword = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password

// SQL query to update the password
$sql = "UPDATE admin SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newPassword, $adminID);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update password."]);
}

$stmt->close();
$conn->close();
?>
