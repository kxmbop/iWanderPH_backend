<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';

$response = [];
$key = "123456";  // Secret key

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($token) && isset($data['BookingID']) && isset($data['RefundReason'])) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        $bookingId = intval($data['BookingID']);
        $refundReason = $data['RefundReason'];

        // Update RefundReason in the booking table
        $sql = "UPDATE booking SET RefundReason = ? WHERE BookingID = ? AND TravelerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $refundReason, $bookingId, $travelerID);
        
        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Refund reason updated successfully.";
        } else {
            $response["success"] = false;
            $response["message"] = "Failed to update refund reason.";
        }

        $stmt->close();
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = "Invalid token or failed to process request: " . $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "Invalid token or missing parameters.";
}

$conn->close();
echo json_encode($response);
?>
