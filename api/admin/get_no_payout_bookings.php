<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$sql = "
    SELECT 
        b.BookingID as bookingId,
        b.BookingDate as bookingDate,
        t.username as travelerName,
        m.BusinessName as merchantName,
        b.Subtotal as subtotal,       
        b.VAT as vat,                
        b.PayoutAmount as payout,           
        b.TotalAmount as totalAmount, 
        b.BookingStatus as bookingstatus, 
        b.Duration as duration,
        b.PaymentStatus as paymentstatus
    FROM bookings b
    JOIN traveler t ON b.TravelerID = t.TravelerID
    JOIN merchant m ON b.MerchantID = m.MerchantID
    WHERE b.bookingStatus = 'pending' && b.payoutStatus = 'pending' && b.paymentStatus != 'failed'
    ORDER BY b.BookingDate ASC;
";

$result = $conn->query($sql);

$bookings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

echo json_encode($bookings);
$conn->close();
?>
