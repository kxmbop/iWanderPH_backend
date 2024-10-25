<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$response = [];

if (isset($_GET['reviewID'])) {
    $reviewID = $_GET['reviewID'];

    $reviewQuery = "
        SELECT r.ReviewID, r.ReviewComment, r.ReviewRating, b.BookingID, t.TravelerID, 
               t.FirstName, t.username, t.LastName, t.ProfilePic, bs.BusinessName, 
               bs.address 
        FROM reviews r
        INNER JOIN booking b ON r.BookingID = b.BookingID
        INNER JOIN traveler t ON b.TravelerID = t.TravelerID
        INNER JOIN merchant bs ON b.merchantID = bs.merchantID
        WHERE r.ReviewID = ?
    ";
    
    $stmt = $conn->prepare($reviewQuery);
    $stmt->bind_param("i", $reviewID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reviewRow = $result->fetch_assoc();

        // Fetch review images
        $imagesQuery = "SELECT Image FROM review_images WHERE ReviewID = ?";
        $imageStmt = $conn->prepare($imagesQuery);
        $imageStmt->bind_param("i", $reviewID);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        
        $images = [];
        while ($imageRow = $imageResult->fetch_assoc()) {
            $images[] = base64_encode($imageRow['Image']);
        }

        $review = [
            'reviewID' => $reviewRow['ReviewID'],
            'rating' => $reviewRow['ReviewRating'],
            'comment' => $reviewRow['ReviewComment'],
            'business' => [
                'name' => $reviewRow['BusinessName'],
                'address' => $reviewRow['address']
            ],
            'traveler' => [
                'username' => $reviewRow['username'],
                'profilePicture' => $reviewRow['ProfilePic'] ? base64_encode($reviewRow['ProfilePic']) : null
            ],
            'images' => $images // Pass the images array to the response
        ];

        $response['review'] = $review;
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['message'] = 'Review not found';
    }
} else {
    $response['success'] = false;
    $response['message'] = 'No reviewID provided';
}

echo json_encode($response);
?>
