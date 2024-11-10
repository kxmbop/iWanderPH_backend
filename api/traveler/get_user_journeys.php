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

    // Query to fetch completed journeys (bookings) with merchant information
    $completedJourneysQuery = "
        SELECT m.BusinessName, m.Email, m.Contact, m.Address, m.ProfilePicture
        FROM booking b
        JOIN merchant m ON b.MerchantID = m.MerchantID
        WHERE b.TravelerID = ? AND b.BookingStatus = 'Completed'
    ";
    
    $stmt = $conn->prepare($completedJourneysQuery);
    $stmt->bind_param("i", $travelerID);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $completedJourneys = [];

        while ($journey = $result->fetch_assoc()) {
            // Encode profile picture if it exists
            if ($journey['ProfilePicture']) {
                $journey['ProfilePicture'] = base64_encode($journey['ProfilePicture']);
            }
            $completedJourneys[] = $journey;
        }

        $response['completedJourneys'] = $completedJourneys;
        $response['success'] = true;
        $response['message'] = "Completed journeys retrieved successfully.";
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
