<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];
$key = "123456"; // Your secret key

// Get token from headers
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Parse JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    if (empty($token)) {
        $response['error'] = 'Unauthorized';
        echo json_encode($response);
        exit;
    }

    try {
        // Decode the JWT token
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

    
        // Get data from request
        $reviewID = $input['reviewID'] ?? null;
        $reviewComment = $input['reviewComment'] ?? '';
    
        // Debugging: Log values to verify they are correct
        file_put_contents('php://stderr', print_r("Decoded TravelerID: $travelerID, Received ReviewID: $reviewID, Comment: $reviewComment\n", TRUE));
    
        // Validate data
        if (!$reviewID || !$reviewComment) {
            $response['error'] = 'Missing data for updating review.';
            echo json_encode($response);
            exit;
        }
        // Update review in the database
        $sql = "UPDATE reviews SET ReviewComment = ? WHERE ReviewID = ? AND TravelerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $reviewComment, $reviewID, $travelerID);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) { // Check if any rows were affected
                $response['success'] = true;
                $response['message'] = 'Review updated successfully';
            } else {
                $response['success'] = false;
                $response['error'] = 'No rows affected. Check if reviewID and travelerID are correct.';
            }
        } else {
            $response['error'] = 'Failed to update review';
        }

    } catch (ExpiredException $e) {
        $response['error'] = 'Token has expired';
    } catch (Exception $e) {
        $response['error'] = 'Invalid token';
    }
} else {
    $response['error'] = 'Invalid request method';
}

$conn->close();
echo json_encode($response);
?>
