<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';

$response = [];
$key = "123456"; // JWT key

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        // Decode the JWT token to extract the user ID
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        if (isset($_GET['reviewID'])) {
            $reviewID = $_GET['reviewID'];

            // Check if the user has liked the review
            $likedByUser = false;
            $likeCheckQuery = "SELECT 1 FROM review_likes WHERE reviewID = ? AND userID = ?";
            $likeCheckStmt = $conn->prepare($likeCheckQuery);
            $likeCheckStmt->bind_param("ii", $reviewID, $travelerID);
            $likeCheckStmt->execute();
            $likeCheckResult = $likeCheckStmt->get_result();

            if ($likeCheckResult->num_rows > 0) {
                $likedByUser = true;
            }

            // Return the like status in the response
            $response['likedByUser'] = $likedByUser;
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['message'] = 'No reviewID provided';
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = "Token error: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'No token provided';
}

$conn->close();

echo json_encode($response);
?>
