<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$response = [];

if (isset($_GET['reviewID'])) {
    $reviewID = $_GET['reviewID'];

    // Fetch all comments for the review
    $commentQuery = "
        SELECT rc.comment, t.username
        FROM review_comments rc
        INNER JOIN traveler t ON rc.userID = t.TravelerID
        WHERE rc.reviewID = ?
    ";
    $stmt = $conn->prepare($commentQuery);
    $stmt->bind_param("i", $reviewID);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'comment' => $row['comment'],
            'username' => $row['username']
        ];
    }

    $response['comments'] = $comments;
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['message'] = 'No reviewID provided.';
}

echo json_encode($response);
?>
