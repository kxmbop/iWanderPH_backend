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
$token = $data->token;
$firstName = $data->FirstName;
$lastName = $data->LastName;
$address = $data->Address;

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

$sql = "UPDATE traveler SET FirstName=?, LastName=?,  Address = ? WHERE TravelerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $firstName, $lastName, $address, $travelerID);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update profile"]);
}

$stmt->close();
$conn->close();
?>
