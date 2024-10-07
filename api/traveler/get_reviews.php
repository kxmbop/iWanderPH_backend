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
    
    // Fetch traveler information
    $travelerQuery = "SELECT * FROM traveler WHERE TravelerID = '$travelerID'";
    $travelerResult = mysqli_query($conn, $travelerQuery);
    $traveler = mysqli_fetch_assoc($travelerResult);
    
    // Fetch reviews
    $reviewQuery = "SELECT r.reviewID, r.reviewComment, r.reviewRating, 
               (SELECT COUNT(*) FROM review_likes WHERE reviewID = r.reviewID) AS likes,
               (SELECT COUNT(*) FROM review_comments WHERE reviewID = r.reviewID) AS comments
               FROM reviews r
               WHERE r.TravelerID = '$travelerID'";

    $result = mysqli_query($conn, $reviewQuery);

    if ($result === false) {
        $response['error'] = 'Failed to retrieve reviews';
        echo json_encode($response);
        exit;
    }

    $reviews = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $review = array(
            'reviewID' => $row['reviewID'],
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
            $imageData = $imageRow['image'];
            $base64Image = base64_encode($imageData);
            $review['images'][] = $base64Image;
        }

        $reviews[] = $review;
    }

    // Combine traveler and reviews into a single array
    $traveler['ProfilePic'] = base64_encode($traveler['ProfilePic']);
    $data = array(
        'traveler' => $traveler,
        'reviews' => $reviews
    );

    ob_start();
    echo json_encode($data);
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;
} catch (ExpiredException $e) {
    $response['error'] = 'Token has expired';
    echo json_encode($response);
    exit;
} catch (Exception $e) {
    $response['error'] = 'Invalid token';
    echo json_encode($response);
    exit;
}

mysqli_close($conn);
?>