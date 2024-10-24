<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../db.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$response = [];
$key = "123456"; // Your JWT secret key

// Extract token from headers
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

// Get TravelerID from the token
$travelerID = null;
if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;
    } catch (Exception $e) {
        // Handle token errors
        $response['error'] = 'Invalid token: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }
}

try {
    // Fetch all reviews and check if the user has liked each review
    $reviewQuery = "
        SELECT r.ReviewID, r.ReviewComment, r.ReviewRating, b.BookingID, t.TravelerID, t.FirstName, t.username, t.LastName, t.ProfilePic,
            bs.BusinessName, bs.address,  
            (SELECT COUNT(*) FROM review_likes WHERE ReviewID = r.ReviewID) AS likes,
            (SELECT COUNT(*) FROM review_comments WHERE ReviewID = r.ReviewID) AS comments,
            (SELECT COUNT(*) FROM review_likes WHERE ReviewID = r.ReviewID AND userID = ?) AS likedByUser
        FROM reviews r
        INNER JOIN booking b ON r.BookingID = b.BookingID
        INNER JOIN traveler t ON b.TravelerID = t.TravelerID
        INNER JOIN merchant bs ON b.merchantID = bs.merchantID  
        ORDER BY RAND()
    ";
    
    $stmt = $conn->prepare($reviewQuery);
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $reviewResult = $stmt->get_result();

    if ($reviewResult === false) {
        $response['error'] = 'Failed to retrieve reviews: ' . mysqli_error($conn);
        echo json_encode($response);
        exit;
    }

    $reviews = [];
    
    while ($reviewRow = mysqli_fetch_assoc($reviewResult)) {
        $review = [
            'reviewID' => $reviewRow['ReviewID'],
            'comment' => $reviewRow['ReviewComment'],
            'rating' => $reviewRow['ReviewRating'],
            'likes' => $reviewRow['likes'],
            'comments' => $reviewRow['comments'],
            'likedByUser' => $reviewRow['likedByUser'] > 0, // True if the user has liked the review
            'traveler' => [
                'travelerID' => $reviewRow['TravelerID'],
                'username' => $reviewRow['username'],
                'firstName' => $reviewRow['FirstName'],
                'lastName' => $reviewRow['LastName'],
                'profilePic' => $reviewRow['ProfilePic'] ? base64_encode($reviewRow['ProfilePic']) : null
            ],
            'business' => [
                'name' => $reviewRow['BusinessName'],
                'address' => $reviewRow['address']
            ],
            'images' => []
        ];

        // Fetch review images
        $imageQuery = "SELECT Image FROM review_images WHERE ReviewID = " . $reviewRow['ReviewID'];
        $imageResult = mysqli_query($conn, $imageQuery);
        
        if ($imageResult === false) {
            $response['error'] = 'Failed to retrieve review images: ' . mysqli_error($conn);
            echo json_encode($response);
            exit;
        }

        while ($imageRow = mysqli_fetch_assoc($imageResult)) {
            $base64Image = base64_encode($imageRow['Image']);
            $review['images'][] = $base64Image;
        }

        $reviews[] = $review;
    }

    $data = [
        'reviews' => $reviews
    ];

    echo json_encode($data);

} catch (Exception $e) {
    $response['error'] = 'An error occurred while processing: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

mysqli_close($conn);
?>
