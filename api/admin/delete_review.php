<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../../db.php';

$response = ["success" => false]; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    $reviewID = $inputData['reviewId'] ?? null;
    $travelerID = $inputData['travelerID'] ?? null;

    if (!$reviewID || !$travelerID) {
        $response['error'] = 'Review ID and Traveler ID are required';
        echo json_encode($response);
        exit;
    }

    try {
        $conn->begin_transaction();

        $deleteLikes = $conn->prepare("DELETE FROM review_likes WHERE reviewID = ?");
        $deleteLikes->bind_param("i", $reviewID);
        $deleteLikes->execute();

        $deleteComments = $conn->prepare("DELETE FROM review_comments WHERE reviewID = ?");
        $deleteComments->bind_param("i", $reviewID);
        $deleteComments->execute();

        $deleteImages = $conn->prepare("DELETE FROM review_images WHERE reviewID = ?");
        $deleteImages->bind_param("i", $reviewID);
        $deleteImages->execute();

        // Delete the review itself
        $deleteReview = $conn->prepare("DELETE FROM reviews WHERE reviewID = ? AND TravelerID = ?");
        $deleteReview->bind_param("ii", $reviewID, $travelerID);
        
        if ($deleteReview->execute()) {
            $conn->commit(); 
            $response['success'] = true;
            $response['message'] = 'Review deleted successfully';
        } else {
            $conn->rollback(); 
            $response['error'] = 'Failed to delete review';
        }
    } catch (Exception $e) {
        $conn->rollback(); 
        $response['error'] = 'Error during review deletion: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Invalid request method';
}

$conn->close();
echo json_encode($response);
