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
    // Decode the JWT token to get the TravelerID
    $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;
    
    // Fetch traveler information
    $travelerQuery = "SELECT * FROM traveler WHERE TravelerID = '$travelerID'";
    $travelerResult = mysqli_query($conn, $travelerQuery);
    $traveler = mysqli_fetch_assoc($travelerResult);
    
    // Fetch BookingID for the traveler
    $bookingQuery = "SELECT BookingID FROM bookings WHERE TravelerID = '$travelerID'";
    $bookingResult = mysqli_query($conn, $bookingQuery);

    if ($bookingResult === false || mysqli_num_rows($bookingResult) === 0) {
        $response['error'] = 'No bookings found for the traveler';
        echo json_encode($response);
        exit;
    }

    $bookingRow = mysqli_fetch_assoc($bookingResult);
    $bookingID = $bookingRow['BookingID'];

    // Fetch reviews and username based on the BookingID
    $reviewQuery = "
        SELECT r.reviewID, r.reviewComment, r.reviewRating, 
               t.username,  -- Fetch username from traveler table
               (SELECT COUNT(*) FROM review_likes WHERE reviewID = r.reviewID) AS likes,
               (SELECT COUNT(*) FROM review_comments WHERE reviewID = r.reviewID) AS comments
        FROM reviews r
        JOIN bookings b ON r.BookingID = b.BookingID
        JOIN traveler t ON b.TravelerID = t.TravelerID  -- Join traveler to get username
        WHERE r.BookingID = '$bookingID'
    ";

    $result = mysqli_query($conn, $reviewQuery);

    if ($result === false) {
        $response['error'] = 'Failed to retrieve reviews';
        echo json_encode($response);
        exit;
    }

    function getRatingIcons($rating) {
        // You no longer need this function to generate HTML stars
        return $rating; // Just return the numeric rating
    }
    $reviews = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $review = array(
            'reviewID' => $row['reviewID'],
            'comment' => $row['reviewComment'],
            'rating' => getRatingIcons($row['reviewRating']), // Change this line
            'username' => $row['username'],  // Include the username from the traveler table
            'likes' => $row['likes'],
            'comments' => $row['comments'],
            'images' => array()
        );

        // Fetch images for each review
        $imageQuery = "SELECT image FROM review_images WHERE reviewID = " . $row['reviewID'];
        $imageResult = mysqli_query($conn, $imageQuery);

        if ($imageResult === false) {
            $response['error'] = 'Failed to retrieve images';
            echo json_encode($response);
            exit;
        }

        while ($imageRow = mysqli_fetch_assoc($imageResult)) {
            $imageData = $imageRow['image']; // Assuming 'image' is the BLOB column in your database

            if (!empty($imageData)) {
                $base64Image = base64_encode($imageData);
                $review['images'][] = 'data:image/jpeg;base64,' . $base64Image; 
            } else {
                $review['images'][] = 'path/to/placeholder_image.jpg'; 
            }
        }
        
        $reviews[] = $review; // Add review to the reviews array
    }

    // Base64 encode the traveler's profile picture
    $traveler['ProfilePic'] = base64_encode($traveler['ProfilePic']);
    
    // Combine traveler and reviews into a single array
    $data = array(
        'traveler' => $traveler,
        'reviews' => $reviews
    );

    // Send the response back to the client
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

// Close the database connection
mysqli_close($conn);
?>
