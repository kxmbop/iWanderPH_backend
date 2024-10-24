<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Updated query to handle both room and transportation bookings
$sql = "
    SELECT 
        b.BookingID AS bookingId,
        b.BookingDate AS bookingDate,
        t.Username AS travelerName,
        m.BusinessName AS merchantName,
        b.Subtotal AS subtotal,       
        b.VAT AS vat,                
        b.PayoutAmount AS payout,           
        b.TotalAmount AS totalAmount, 
        b.BookingStatus AS bookingstatus, 
        b.PaymentStatus AS paymentstatus,
        b.BookingType AS bookingType,
        
        rb.CheckInDate AS roomCheckInDate,
        rb.CheckOutDate AS roomCheckOutDate,
        rb.SpecialRequest AS roomSpecialRequest,

        tb.PickupDateTime AS transportationPickupDateTime,
        tb.DropoffDateTime AS transportationDropoffDateTime,
        tb.PickupLocation AS transportationPickupLocation,
        tb.DropoffLocation AS transportationDropoffLocation
        
    FROM booking b
    LEFT JOIN traveler t ON b.TravelerID = t.TravelerID
    LEFT JOIN merchant m ON b.MerchantID = m.MerchantID
    LEFT JOIN room_booking rb ON b.RoomBookingID = rb.RoomBookingID
    LEFT JOIN transportation_booking tb ON b.TransportationBookingID = tb.TransportationBookingID
    ORDER BY b.BookingDate DESC;
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
