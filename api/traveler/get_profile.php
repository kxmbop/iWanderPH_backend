<?php
header("Access-Control-Allow-Origin: http://localhost:4200"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true"); 
header("Content-Type: application/json");

session_start();
include '../../db.php';

$response = [];

// Debugging: Log session data
error_log(print_r($_SESSION, true)); // Check what is stored in the session

if (isset($_SESSION["user_id"])) {
    $travelerID = $_SESSION["user_id"];

    $profile_sql = "SELECT FirstName, LastName, Username, ProfilePic, Bio FROM traveler WHERE TravelerID = ?";
    $stmt = $conn->prepare($profile_sql);
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $profile_result = $stmt->get_result();

    if ($profile_result->num_rows == 1) {
        $profile_data = $profile_result->fetch_assoc();
        $response["profile"] = $profile_data;
    } else {
        // Debugging: Log if no profile is found
        error_log("No profile found for TravelerID: " . $travelerID);
        $response["success"] = false;
        $response["message"] = "No profile found.";
    }

    $journey_sql = "SELECT COUNT(*) AS journey_count FROM bookings WHERE TravelerID = ?";
    $stmt2 = $conn->prepare($journey_sql);
    $stmt2->bind_param("i", $travelerID);
    $stmt2->execute();
    $journey_result = $stmt2->get_result();
    $journey_data = $journey_result->fetch_assoc();
    $response["journeys"] = $journey_data["journey_count"];

    $response["success"] = true;
} else {
    // Debugging: Log when user is not logged in
    error_log("User not logged in.");
    $response["success"] = false;
    $response["message"] = "Not logged in.";
}

$conn->close();
echo json_encode($response);
?>
