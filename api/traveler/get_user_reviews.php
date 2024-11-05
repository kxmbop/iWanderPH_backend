<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$response = [];

// Check if travelerID is provided
if (isset($_GET['travelerID'])) {
    $travelerID = $_GET['travelerID'];

    // Query to fetch reviews and relevant data
    $reviewsQuery = "
        SELECT r.ReviewID, r.ReviewComment, r.ReviewRating, r.CreatedAt, 
               (SELECT COUNT(*) FROM review_likes WHERE ReviewID = r.ReviewID) AS likes,
               (SELECT COUNT(*) FROM review_comments WHERE ReviewID = r.ReviewID) AS comments,
               m.BusinessName, m.Address
        FROM reviews r
        JOIN booking b ON r.BookingID = b.BookingID
        JOIN merchant m ON b.MerchantID = m.MerchantID
        WHERE b.TravelerID = ?
        ORDER BY r.CreatedAt DESC
    ";
    
    $stmt = $conn->prepare($reviewsQuery);
    $stmt->bind_param("i", $travelerID);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $reviews = [];

        while ($row = $result->fetch_assoc()) {
            $review = [
                'reviewID' => $row['ReviewID'],
                'comment' => $row['ReviewComment'],
                'rating' => $row['ReviewRating'],
                'likes' => $row['likes'],
                'comments' => $row['comments'],
                'createdAt' => $row['CreatedAt'],
                'business' => [
                    'name' => $row['BusinessName'],
                    'address' => $row['Address']
                ]
            ];

            // Fetch review images for each review
            $imagesQuery = "SELECT Image FROM review_images WHERE ReviewID = ?";
            $imageStmt = $conn->prepare($imagesQuery);
            $imageStmt->bind_param("i", $row['ReviewID']);
            $imageStmt->execute();
            $imagesResult = $imageStmt->get_result();
            
            $images = [];
            while ($imageRow = $imagesResult->fetch_assoc()) {
                $images[] = base64_encode($imageRow['Image']); // Encode each image in base64
            }
            $review['images'] = $images;

            $reviews[] = $review;
        }

        $response['reviews'] = $reviews;
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to execute query: ' . $stmt->error;
        $response['success'] = false;
    }

    $stmt->close();
} else {
    $response['error'] = 'No travelerID provided.';
    $response['success'] = false;
}

echo json_encode($response);
$conn->close();
?>
