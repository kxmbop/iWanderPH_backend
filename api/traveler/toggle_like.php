<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;

$response = [];
$key = "123456";

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

$input = json_decode(file_get_contents("php://input"), true);
$reviewID = $input['reviewID'] ?? null;

if (!empty($token) && !empty($reviewID)) {
    try {
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $userID = $decoded->TravelerID;

        // Check if the user already liked the review
        $checkLikeQuery = "SELECT * FROM review_likes WHERE reviewID = ? AND userID = ?";
        $stmt = $conn->prepare($checkLikeQuery);
        $stmt->bind_param("ii", $reviewID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If liked, remove the like
            $deleteLikeQuery = "DELETE FROM review_likes WHERE reviewID = ? AND userID = ?";
            $stmt = $conn->prepare($deleteLikeQuery);
            $stmt->bind_param("ii", $reviewID, $userID);
            $stmt->execute();

            $response["message"] = "Review unliked";
        } else {
            // If not liked, add a like
            $insertLikeQuery = "INSERT INTO review_likes (reviewID, userID) VALUES (?, ?)";
            $stmt = $conn->prepare($insertLikeQuery);
            $stmt->bind_param("ii", $reviewID, $userID);
            $stmt->execute();

            $response["message"] = "Review liked";
        }

        // Get the updated like count
        $likeCountQuery = "SELECT COUNT(*) AS likes FROM review_likes WHERE reviewID = ?";
        $stmt = $conn->prepare($likeCountQuery);
        $stmt->bind_param("i", $reviewID);
        $stmt->execute();
        $likeCountResult = $stmt->get_result();
        $likeCountRow = $likeCountResult->fetch_assoc();

        $response["likes"] = $likeCountRow["likes"]; // Updated like count
        $response["success"] = true;
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = "Invalid token: " . $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "Token or reviewID not provided";
}

echo json_encode($response);
$conn->close();
?>
