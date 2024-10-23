<?php
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = intval($_POST['booking_id']);

    // Prepare SQL query to update the booking status
    $sql = "UPDATE bookings SET BookingStatus = 'Checked-In' WHERE BookingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $booking_id);

    if ($stmt->execute()) {
        // Redirect back to on_going.php with a success message
        header("Location: on_going.php?message=checked_in_success");
        exit();
    } else {
        // Redirect back to on_going.php with an error message
        header("Location: on_going.php?message=checked_in_error");
        exit();
    }
}
?>
