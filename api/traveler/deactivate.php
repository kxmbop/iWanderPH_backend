<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

include '../../db.php'; // Ensure this file connects to your database

$response = [];
$key = "123456"; // Use a strong secret key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $token = isset($data['token']) ? $data['token'] : null;

    if ($token) {
        try {
            // Decode the token
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $travelerId = $decoded->TravelerID;

            // Prepare SQL statement to update isDeactivated status
            $stmt = $conn->prepare("UPDATE traveler SET isDeactivated = 1 WHERE TravelerID = ?");
            $stmt->bind_param("i", $travelerId);

            if ($stmt->execute()) {
                $response = [
                    "success" => true,
                    "message" => "Account deactivated successfully."
                ];
            } else {
                $response = [
                    "success" => false,
                    "message" => "Failed to deactivate account. Please try again."
                ];
            }

            $stmt->close();
        } catch (ExpiredException $e) {
            $response = [
                "success" => false,
                "message" => "Token has expired. Please log in again."
            ];
        } catch (Exception $e) {
            $response = [
                "success" => false,
                "message" => "Invalid token."
            ];
        }
    } else {
        $response = [
            "success" => false,
            "message" => "No token provided."
        ];
    }
} else {
    $response = [
        "success" => false,
        "message" => "Invalid request method."
    ];
}

// Send the response as JSON
echo json_encode($response);
$conn->close();
?>
