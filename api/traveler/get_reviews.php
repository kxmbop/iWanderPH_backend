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
    // Decode JWT token to get TravelerID
    $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;
    
    // Fetch traveler information
    $travelerQuery = "SELECT * FROM traveler WHERE TravelerID = '$travelerID'";
    $travelerResult = mysqli_query($conn, $travelerQuery);
    
    if (!$travelerResult || mysqli_num_rows($travelerResult) === 0) {
        $response['error'] = 'Traveler not found';
        echo json_encode($response);
        exit;
    }
    
    $traveler = mysqli_fetch_assoc($travelerResult);
    
    // Fetch BookingIDs for the traveler
    $bookingQuery = "SELECT BookingID FROM booking WHERE TravelerID = '$travelerID'";
    $bookingResult = mysqli_query($conn, $bookingQuery);

    if ($bookingResult === false || mysqli_num_rows($bookingResult) === 0) {
        $response['error'] = 'No bookings found for the traveler';
        echo json_encode($response);
        exit;
    }

    // Store the BookingIDs in an array
    $bookingIDs = [];
    while ($bookingRow = mysqli_fetch_assoc($bookingResult)) {
        $bookingIDs[] = $bookingRow['BookingID'];
    }

    // Prepare the reviews array
    $reviews = [];
    
    foreach ($bookingIDs as $bookingID) {
        // Fetch reviews based on BookingID
        $reviewQuery = "
        SELECT 
            r.ReviewID, 
            r.ReviewComment, 
            r.ReviewRating, 
            (SELECT COUNT(*) FROM review_likes WHERE ReviewID = r.ReviewID) AS likes, 
            (SELECT COUNT(*) FROM review_comments WHERE ReviewID = r.ReviewID) AS comments,
            EXISTS(SELECT 1 FROM review_likes WHERE ReviewID = r.ReviewID AND userID = '$travelerID') AS liked
        FROM reviews r
        WHERE r.BookingID = '$bookingID'
        ";




        $reviewResult = mysqli_query($conn, $reviewQuery);

        if ($reviewResult === false) {
            $response['error'] = 'Failed to retrieve reviews';
            echo json_encode($response);
            exit;
        }

        while ($reviewRow = mysqli_fetch_assoc($reviewResult)) {
            $review = [
                'reviewID' => $reviewRow['ReviewID'],
                'comment' => $reviewRow['ReviewComment'],
                'rating' => $reviewRow['ReviewRating'],
                'likes' => $reviewRow['likes'],
                'comments' => $reviewRow['comments'],
                'images' => []
            ];

            // Fetch images for each review
            $imageQuery = "SELECT Image FROM review_images WHERE ReviewID = " . $reviewRow['ReviewID'];
            $imageResult = mysqli_query($conn, $imageQuery);

            if ($imageResult === false) {
                $response['error'] = 'Failed to retrieve images';
                echo json_encode($response);
                exit;
            }

            while ($imageRow = mysqli_fetch_assoc($imageResult)) {
                $imageData = $imageRow['Image'];
                $base64Image = base64_encode($imageData);
                $review['images'][] = $base64Image;
            }

            $reviews[] = $review;
        }
    }

    // Combine traveler and reviews into a single array
    if ($traveler['ProfilePic']) {
        $traveler['ProfilePic'] = base64_encode($traveler['ProfilePic']);
    }

    $data = [
        'traveler' => $traveler,
        'reviews' => $reviews
    ];

    echo json_encode($data);

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
