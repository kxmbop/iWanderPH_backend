<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; // Your JWT secret key

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID; // Extract TravelerID from the decoded token

        // Retrieve review data from the request
        $bookingID = $_POST['bookingID'];
        $reviewComment = $_POST['reviewComment'];
        $reviewRating = $_POST['reviewRating'];
        $privacy = $_POST['privacy'];
        $images = $_FILES['reviewImages'] ?? [];

        // Insert review into the 'reviews' table
        $review_sql = "INSERT INTO reviews (bookingID, TravelerID, reviewComment, reviewRating, privacy, createdAt) 
                       VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($review_sql);
        $stmt->bind_param("iisis", $bookingID, $travelerID, $reviewComment, $reviewRating, $privacy);

        if ($stmt->execute()) {
            $reviewID = $stmt->insert_id; // Get the inserted reviewID

            // Handle image uploads if any
            if (!empty($images['name'][0])) {
                foreach ($images['tmp_name'] as $key => $tmp_name) {
                    $imageData = file_get_contents($tmp_name);
                    $image_sql = "INSERT INTO review_images (reviewID, image) VALUES (?, ?)";
                    $image_stmt = $conn->prepare($image_sql);
                    $image_stmt->bind_param("ib", $reviewID, $imageData);
                    $image_stmt->send_long_data(1, $imageData); // Send long binary data
                    $image_stmt->execute();
                }
            }

            $response["success"] = true;
            $response["message"] = "Review submitted successfully.";
        } else {
            $response["success"] = false;
            $response["message"] = "Failed to submit review.";
        }

        $stmt->close();
    } catch (ExpiredException $e) {
        $response["success"] = false;
        $response["message"] = "Token expired: " . $e->getMessage();
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = "Invalid token: " . $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "No token provided.";
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
