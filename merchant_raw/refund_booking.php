<?php
include "db_conn.php";

// Get the BookingID from the URL
$bookingID = isset($_GET['id']) ? $_GET['id'] : '';

// Check if BookingID is provided
if ($bookingID) {
    // Sanitize the input
    $bookingID = $conn->real_escape_string($bookingID);

    // Update the booking status to "cancelled" or any other appropriate status
    $sql = "UPDATE bookings SET BookingStatus = 'refunded' WHERE BookingID = '$bookingID'";

    if ($conn->query($sql) === TRUE) {
        // Redirect to pending.php
        header("Location: pending.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "No BookingID provided.";
}

// Close the connection
$conn->close();
?>
