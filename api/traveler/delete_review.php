<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456";

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';

// Extract token from header
if (strpos($authorizationHeader, 'Bearer ') !== false) {
    $token = str_replace('Bearer ', '', $authorizationHeader);
} else {
    $response['error'] = 'Authorization header missing or malformed';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $reviewID = $_GET['reviewID'] ?? null;

    if (!$reviewID) {
        $response['error'] = 'Review ID is required';
        echo json_encode($response);
        exit;
    }

    try {
        // Decode the JWT token
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID ?? null;

        if (!$travelerID) {
            $response['error'] = 'Invalid token payload: TravelerID missing';
            echo json_encode($response);
            exit;
        }

        // Start a transaction to ensure data integrity
        $conn->begin_transaction();

        // Delete related review likes, comments, and images first
        $deleteLikes = $conn->prepare("DELETE FROM review_likes WHERE reviewID = ?");
        $deleteLikes->bind_param("i", $reviewID);
        $deleteLikes->execute();

        $deleteComments = $conn->prepare("DELETE FROM review_comments WHERE reviewID = ?");
        $deleteComments->bind_param("i", $reviewID);
        $deleteComments->execute();

        $deleteImages = $conn->prepare("DELETE FROM review_images WHERE reviewID = ?");
        $deleteImages->bind_param("i", $reviewID);
        $deleteImages->execute();

        // Now delete the review itself
        $deleteReview = $conn->prepare("DELETE FROM reviews WHERE reviewID = ? AND TravelerID = ?");
        $deleteReview->bind_param("ii", $reviewID, $travelerID);
        if ($deleteReview->execute()) {
            $conn->commit(); // Commit the transaction
            $response['success'] = true;
            $response['message'] = 'Review deleted successfully';
        } else {
            $conn->rollback(); // Rollback if delete failed
            $response['error'] = 'Failed to delete review';
        }

    } catch (ExpiredException $e) {
        $response['error'] = 'Token has expired';
    } catch (Exception $e) {
        $response['error'] = 'Invalid token';
    }
} else {
    $response['error'] = 'Invalid request method';
}

$conn->close();
echo json_encode($response);
