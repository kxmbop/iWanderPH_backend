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

        $sql = "SELECT COUNT(*) AS activeBookings FROM booking WHERE travelerID = ? AND bookingStatus NOT IN ('Completed', 'Checked-Out')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $travelerID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        echo json_encode(["success" => true, "activeBookings" => $row['activeBookings']]);
    } catch (ExpiredException $e) {
        echo json_encode(["success" => false, "message" => "Token has expired"]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No token provided"]);
}
?>
