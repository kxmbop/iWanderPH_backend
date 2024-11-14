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

$data = json_decode(file_get_contents("php://input"));
$phone = $data->phone;
$token = $data->token; 

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

if (!preg_match("/^[0-9]{12}$/", $phone)) {
    echo json_encode(["status" => "error", "message" => "Invalid phone number"]);
    exit();
}

$sql = "UPDATE traveler SET Mobile = ? WHERE TravelerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $phone, $travelerID); 
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Phone number updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update phone number"]);
}

$stmt->close();
$conn->close();
?>
