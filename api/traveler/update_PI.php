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

$key = "123456";

try {
    $token = $_POST['token'];
    $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;
} catch (ExpiredException $e) {
    echo json_encode(["error" => "Token has expired."]);
    exit;
} catch (Exception $e) {
    echo json_encode(["error" => "Invalid token."]);
    exit;
}

$firstName = $_POST['FirstName'];
$lastName = $_POST['LastName'];
$address = $_POST['Address'];
$bio = $_POST['Bio'];
$profilePic = null;

// Handle file upload for BLOB storage
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == UPLOAD_ERR_OK) {
    $profilePic = file_get_contents($_FILES['profilePic']['tmp_name']);
} else {
    echo json_encode(['error' => 'Profile picture upload failed or no file uploaded.']);
    exit;
}

// Prepare and execute SQL statement
$sql = "UPDATE traveler SET FirstName=?, LastName=?, Address=?, Bio=?, ProfilePic=? WHERE TravelerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $firstName, $lastName, $address, $bio, $profilePic, $travelerID);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update profile"]);
}

$stmt->close();
$conn->close();
?>
