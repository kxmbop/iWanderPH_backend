<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; 

$token = $_SESSION['token'] ?? '';

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        $profile_sql = "SELECT FirstName, LastName, Username, ProfilePic, Bio FROM traveler WHERE TravelerID = ?";
        $stmt = $conn->prepare($profile_sql);
        $stmt->bind_param("i", $travelerID);
        $stmt->execute();
        $profile_result = $stmt->get_result();

        if ($profile_result->num_rows == 1) {
            $profile_data = $profile_result->fetch_assoc();
            $response["profile"] = $profile_data;
            $profile_data['ProfilePic'] = base64_encode($profile_data['ProfilePic']);
            $response["profile"] = $profile_data;
        } else {
            $response["profile"] = null;
        }

        $journey_sql = "SELECT COUNT(*) AS journey_count FROM bookings WHERE TravelerID = ?";
        $stmt2 = $conn->prepare($journey_sql);
        $stmt2->bind_param("i", $travelerID);
        $stmt2->execute();
        $journey_result = $stmt2->get_result();
        $journey_data = $journey_result->fetch_assoc();
        $response["journeys"] = $journey_data["journey_count"];


        $response["message"] = "Token is valid.";
        $response["success"] = true;
    } catch (ExpiredException $e) {
        $response["success"] = false;
        $response["message"] = "Token expired: " . $e->getMessage();
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = "Invalid token: " . $e->getMessage(); 
    }
} else {
    $response["success"] = false;
    $response["message"] = "No token provided.";
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>