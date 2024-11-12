<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

$token = null;
$key = '123456'; 
$headers = apache_request_headers();

if (isset($headers['Authorization'])) {
    $token = str_replace("Bearer ", "", $headers['Authorization']);
}

if ($token) {
    try {
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID; 

        $updateQuery = "UPDATE traveler SET isDeactivated = 1 WHERE travelerID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $travelerID);

        if ($updateStmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Account deactivated successfully."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Error deactivating account. Please try again later."
            ]);
        }
    } catch (ExpiredException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Token has expired. Please log in again."
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Error decoding token: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Authorization token not provided."
    ]);
}
?>
