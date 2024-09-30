<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; 

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

try {
    $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;

    $reviewQuery = "SELECT r.reviewID, r.reviewComment, r.reviewRating, u.Username, u.ProfilePic, 
               (SELECT COUNT(*) FROM review_likes WHERE reviewID = r.reviewID) AS likes,
               (SELECT COUNT(*) FROM review_comments WHERE reviewID = r.reviewID) AS comments
               FROM reviews r
               JOIN traveler u ON r.userID = u.travelerID
               WHERE r.userID = '$travelerID'";

    $reviews = array();
    $result = mysqli_query($conn, $reviewQuery);
    if ($result === false) {
        $response['error'] = 'Failed to retrieve reviews';
        echo json_encode($response);
        exit;
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $review = array(
            'reviewID' => $row['reviewID'],
            'username' => $row['Username'],
            'profilePic' => $row['ProfilePic'],
            'comment' => $row['reviewComment'],
            'rating' => $row['reviewRating'],
            'likes' => $row['likes'],
            'comments' => $row['comments'],
            'images' => array()
        );
        $imageQuery = "SELECT image FROM review_images WHERE reviewID = " . $row['reviewID'];
        $imageResult = mysqli_query($conn, $imageQuery);
        if ($imageResult === false) {
            $response['error'] = 'Failed to retrieve images';
            echo json_encode($response);
            exit;
        }
        while ($imageRow = mysqli_fetch_assoc($imageResult)) {
            $review['images'][] = array('image' => $imageRow['image']);
        }

        $reviews[] = $review;
    }
    echo json_encode($reviews);
} catch (ExpiredException $e) {
    $response['error'] = 'Token has expired';
    echo json_encode($response);
    exit;
} catch (Exception $e) {
    $response['error'] = 'Invalid token';
    echo json_encode($response);
    exit;
}

echo json_encode($reviews);
mysqli_close($conn);
?>