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
$newUsername = $data['username'] ?? '';
$token = $data['token'] ?? '';

if (empty($newUsername) || empty($token)) {
    echo json_encode(["error" => "Username and token are required."]);
    exit;
}

$key = "123456";
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

if (!preg_match('/^[a-zA-Z0-9_.]+$/', $newUsername)) {
    echo json_encode(["error" => "Invalid username. Only letters, numbers, underscores, and periods are allowed."]);
    exit;
}

$query = "SELECT COUNT(*) as count FROM traveler WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $newUsername);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo json_encode(["error" => "Username is already taken."]);
    exit;
}

$updateQuery = "UPDATE traveler SET Username = ? WHERE TravelerID = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("si", $newUsername, $travelerID);

if ($updateStmt->execute()) {
    echo json_encode(["success" => true, "message" => "Username updated successfully"]);
} else {
    echo json_encode(["error" => "Failed to update username"]);
}

$updateStmt->close();
$conn->close();
?>
