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

    // Query to fetch user profile information
    $profileQuery = "SELECT TravelerID, FirstName, LastName, Username, ProfilePic, Bio FROM traveler WHERE TravelerID = ?";
    $stmt = $conn->prepare($profileQuery);
    $stmt->bind_param("i", $travelerID);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $profile = $result->fetch_assoc();
            // Encode profile picture to base64 if it exists
            if (!empty($profile['ProfilePic'])) {
                $profile['ProfilePic'] = 'data:image/jpeg;base64,' . base64_encode($profile['ProfilePic']);
            }
            $response['profile'] = $profile;
            $response['success'] = true;
        } else {
            $response['error'] = 'Profile not found.';
            $response['success'] = false;
        }
    } else {
        $response['error'] = 'Failed to execute query: ' . $stmt->error;
        $response['success'] = false;
    }
    $stmt->close();

    // Fetch journey count for completed bookings
    $journeyQuery = "SELECT COUNT(*) AS journey_count FROM booking WHERE TravelerID = ? AND BookingStatus = 'Completed'";
    $stmt2 = $conn->prepare($journeyQuery);
    $stmt2->bind_param("i", $travelerID);
    $stmt2->execute();
    $journeyResult = $stmt2->get_result();
    $journeyData = $journeyResult->fetch_assoc();
    $response["journeys"] = $journeyData["journey_count"];
    $stmt2->close();

    // Fetch details of completed bookings with associated merchant information
    $completedBookingsQuery = "
        SELECT m.businessName, m.email, m.contact, m.address, m.profilePicture
        FROM booking b
        JOIN merchant m ON b.merchantID = m.merchantID
        WHERE b.TravelerID = ? AND b.BookingStatus = 'Completed'
    ";
    $stmt3 = $conn->prepare($completedBookingsQuery);
    $stmt3->bind_param("i", $travelerID);
    $stmt3->execute();
    $bookingsResult = $stmt3->get_result();

    $completedBookings = [];
    while ($row = $bookingsResult->fetch_assoc()) {
        if (!empty($row['profilePicture'])) {
            $row['profilePicture'] = 'data:image/jpeg;base64,' . base64_encode($row['profilePicture']);
        }
        $completedBookings[] = $row;
    }
    $response['completedBookings'] = $completedBookings;

} else {
    $response['error'] = 'No travelerID provided.';
    $response['success'] = false;
}

echo json_encode($response);
$conn->close();
?>