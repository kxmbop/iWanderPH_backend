<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$currentPassword = $data['currentPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';
$token = $data['token'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($token)) {
    echo json_encode(["error" => "All fields are required."]);
    exit;
}

$key = "123456";  // Replace with your actual key
try {
    $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;
} catch (ExpiredException $e) {
    echo json_encode(["error" => "Token has expired."]);
    exit;
} catch (Exception $e) {
    echo json_encode(["error" => "Invalid token."]);
    exit;
}

// Fetch current password from database
$query = "SELECT Password FROM traveler WHERE TravelerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $travelerID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["error" => "User not found."]);
    exit;
}

if ($currentPassword !== $user['Password']) {
    echo json_encode(["error" => "Incorrect current password."]);
    exit;
}



// Update password in database
$updateQuery = "UPDATE traveler SET Password = ? WHERE TravelerID = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("si", $newPassword, $travelerID);

if ($updateStmt->execute()) {
    echo json_encode(["success" => true, "message" => "Password updated successfully"]);
} else {
    echo json_encode(["error" => "Failed to update password"]);
}

$updateStmt->close();
$conn->close();
?>
