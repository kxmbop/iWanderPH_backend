<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';

$response = [];
$key = "123456"; // Your JWT secret key

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID; // Extract TravelerID from the token

        // Get comment data from the POST request
        $data = json_decode(file_get_contents("php://input"), true);
        $reviewID = $data['reviewID'];
        $comment = $data['comment'];

        // Insert the new comment
        $insertCommentQuery = "INSERT INTO review_comments (reviewID, userID, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertCommentQuery);
        $stmt->bind_param("iis", $reviewID, $travelerID, $comment);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Comment added successfully.';
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to add comment.';
        }

    } catch (Exception $e) {
        $response['error'] = 'Invalid token: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'No token provided.';
}

echo json_encode($response);
?>
