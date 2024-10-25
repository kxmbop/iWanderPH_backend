<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; // Your JWT secret key

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID; // Extract TravelerID from the decoded token

        // Retrieve reviewID from the POST request
        $data = json_decode(file_get_contents("php://input"), true);
        $reviewID = $data['reviewID'];

        // Check if the review is already liked by the traveler
        $check_like_sql = "SELECT * FROM review_likes WHERE reviewID = ? AND userID = ?";
        $check_stmt = $conn->prepare($check_like_sql);
        $check_stmt->bind_param("ii", $reviewID, $travelerID);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // If already liked, unlike the review
            $unlike_sql = "DELETE FROM review_likes WHERE reviewID = ? AND userID = ?";
            $unlike_stmt = $conn->prepare($unlike_sql);
            $unlike_stmt->bind_param("ii", $reviewID, $travelerID);

            if ($unlike_stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Review unliked successfully.";
            } else {
                $response["success"] = false;
                $response["message"] = "Failed to unlike review.";
            }

            $unlike_stmt->close();
        } else {
            // If not liked, like the review
            $like_sql = "INSERT INTO review_likes (reviewID, userID) VALUES (?, ?)";
            $like_stmt = $conn->prepare($like_sql);
            $like_stmt->bind_param("ii", $reviewID, $travelerID);

            if ($like_stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Review liked successfully.";
            } else {
                $response["success"] = false;
                $response["message"] = "Failed to like review.";
            }

            $like_stmt->close();
        }

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
