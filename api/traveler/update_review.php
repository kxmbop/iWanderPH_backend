<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';

$response = [];
$key = "123456"; // Replace with your secret key

// Get token from headers
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Parse JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Debugging: Log raw input data to verify
    file_put_contents('php://stderr', print_r($input, TRUE));

    if (empty($token)) {
        $response['error'] = 'Unauthorized';
        echo json_encode($response);
        exit;
    }

    try {
        // Decode the JWT token
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        // Get data from request
        $reviewID = $input['reviewID'] ?? null;
        $reviewComment = $input['reviewComment'] ?? '';
        $privacy = $input['privacy'] ?? 'public'; // Default to 'public' if not set

        // Validate privacy value
        if (!in_array($privacy, ['public', 'private'])) {
            $response['error'] = 'Invalid privacy value. Allowed values are "public" or "private".';
            echo json_encode($response);
            exit;
        }

        // Debugging: Log values to verify they are correct
        file_put_contents('php://stderr', print_r("Decoded TravelerID: $travelerID, Received ReviewID: $reviewID, Comment: $reviewComment, Privacy: $privacy\n", TRUE));

        // Validate data
        if (!$reviewID || empty($reviewComment)) {
            $response['error'] = 'Invalid input data. Review ID and Comment are required.';
            echo json_encode($response);
            exit;
        }

        // Prepare the SQL statement to update both reviewComment and privacy
        $stmt = $conn->prepare("UPDATE reviews SET ReviewComment = ?, privacy = ? WHERE ReviewID = ? AND TravelerID = ?");
        $stmt->bind_param("ssii", $reviewComment, $privacy, $reviewID, $travelerID);

        // Execute the query
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Review updated successfully.';
        } else {
            $response['error'] = 'Failed to update review. ' . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();

    } catch (Exception $e) {
        $response['error'] = 'Unauthorized: ' . $e->getMessage();
    }

    // Return response
    echo json_encode($response);
}
?>
