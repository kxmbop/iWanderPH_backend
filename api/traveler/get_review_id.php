<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; // Your secret key

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        $reviewID = $_GET['reviewID'] ?? null;

        if ($reviewID) {
            $sql = "SELECT ReviewComment, ReviewRating FROM reviews WHERE ReviewID = ? AND TravelerID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $reviewID, $travelerID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $review = $result->fetch_assoc();
                $response = $review;
            } else {
                $response['error'] = 'Review not found';
            }
        } else {
            $response['error'] = 'Review ID is required';
        }

    } catch (ExpiredException $e) {
        $response['error'] = 'Token has expired';
    } catch (Exception $e) {
        $response['error'] = 'Invalid token';
    }
} else {
    $response['error'] = 'Unauthorized';
}

$conn->close();
echo json_encode($response);
?>
