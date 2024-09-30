<?php
include "db_conn.php";

if (isset($_POST['bookingID'])) {
    $bookingID = $conn->real_escape_string($_POST['bookingID']);

    $sql = "
    SELECT 
        b.BookingID, b.CheckIn, b.CheckOut, b.BookingStatus, b.TotalAmount,
        t.Username,
        r.ReviewID, r.ReviewRating, r.ReviewMessage AS ReviewStatement, r.ReviewCreate AS ReviewDate
    FROM bookings b
    LEFT JOIN traveler t ON b.TravelerID = t.TravelerID
    LEFT JOIN review r ON b.BookingID = r.BookingID
    WHERE b.BookingID = '$bookingID'
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode([]);
    }

    $conn->close();
}
?>
