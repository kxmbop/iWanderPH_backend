<?php
include "db_conn.php";

if (isset($_GET['bookingID'])) {
    $bookingID = $conn->real_escape_string($_GET['bookingID']);

    // Fetch booking details
    $sql = "SELECT b.BookingID, b.BookingDate, b.PaymentStatus, b.BookingStatus, b.Subtotal, b.VAT, b.TotalAmount, t.Username, b.CheckIn, b.CheckOut, b.Duration
            FROM bookings b
            JOIN traveler t ON b.TravelerID = t.TravelerID
            WHERE b.BookingID = '$bookingID'";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Format dates
        $bookingDate = new DateTime($row['BookingDate']);
        $formattedBookingDate = $bookingDate->format('F j, Y');
        
        $checkInDate = new DateTime($row['CheckIn']);
        $formattedCheckIn = $checkInDate->format('F j, Y');
        
        $checkOutDate = new DateTime($row['CheckOut']);
        $formattedCheckOut = $checkOutDate->format('F j, Y');
        
        // Calculate length of stay
        $checkIn = new DateTime($row['CheckIn']);
        $checkOut = new DateTime($row['CheckOut']);
        
        // Calculate length of stay with time consideration
        if ($checkOut <= $checkIn) {
         $lengthOfStay = "Invalid dates";
        } else {
        // Calculate the total difference between check-in and check-out
        $interval = $checkIn->diff($checkOut);
    
        // Total days between the check-in and check-out
        $days = $interval->days;

        // If check-out time is later than check-in time, count it as an extra night
        $checkInTime = $checkIn->format('H:i');
        $checkOutTime = $checkOut->format('H:i');
    
        // Define cut-off time to determine if it counts as another night, e.g., 12 PM (noon)
        $nightCutOff = "12:00";

        if ($checkOutTime > $nightCutOff) {
        $nights = $days; // If check-out is after cut-off, count full days as nights
        } else {
        $nights = $days - 1; // If check-out is before cut-off, subtract one night
        }

        // Calculate the length of stay based on the total days and nights
        $lengthOfStay = "$days days and $nights nights";
        }

        // Transaction Fee
        $transactionFee = 275.68;

        // Fetch room details
        $sql_room = "SELECT r.RoomName, r.RoomID, r.guestperroom, rd.opt_inclusions, rd.gallery
                     FROM bookings b
                     JOIN rooms r ON b.ListingID = r.RoomID
                     JOIN room_details rd ON r.RoomID = rd.RoomID
                     WHERE b.BookingID = '$bookingID'";
                     
        $result_room = $conn->query($sql_room);
        $roomDetails = $result_room->fetch_assoc();
    }
}
?>
        
    <div class="parent">
    <div class="div1"><strong>Booking ID:</strong> <?php echo $row['BookingID']; ?></div>
    <div class="div2"><strong>Booking Date:</strong> <?php echo $formattedBookingDate; ?></div>
    <div class="div3"><strong>Traveler:</strong> @ <?php echo $row['Username']; ?></div>
    <div class="div4"><strong>Booking Status:</strong> <?php echo ucfirst($row['BookingStatus']); ?></div>
    <div class="div5"><strong>Subtotal:</strong> ₱ <?php echo number_format($row['Subtotal'], 2); ?></div>
    <div class="div6"><strong>VAT:</strong> ₱ <?php echo number_format($row['VAT'], 2); ?></div>
    <div class="div7"><strong>Transaction Fee:</strong> ₱ <?php echo number_format($transactionFee, 2); ?></div>
    <div class="div8"><strong>Total Amount:</strong> ₱ <?php echo number_format($row['TotalAmount'], 2); ?></div>
    <div class="div9"><button class="btn accept">Accept</button></div>
    <div class="div10"><button class="btn refund">Refund</button></div>
    <div class="div11"><button class="btn contact" onclick="contactCustomer('<?php echo $row['Username']; ?>')">Contact Customer</button></div>
    <div class="div12"><strong>Room Name:</strong> <?php echo $roomDetails['RoomName']; ?></div>
    <div class="div13"><strong>Room ID:</strong> <?php echo $roomDetails['RoomID']; ?></div>
    <div class="div14"><strong>Guests per Room:</strong> <?php echo $roomDetails['guestperroom']; ?></div>
    <div class="div15"><strong>Length of Stay:</strong> <?php echo $lengthOfStay; ?></div>
    <div class="div16"><strong>Check-in:</strong> <?php echo $formattedCheckIn; ?></div>
    <div class="div17"><strong>Check-out:</strong> <?php echo $formattedCheckOut; ?></div>
    <div class="div18"><strong>Inclusions:</strong> <?php echo $roomDetails['opt_inclusions']; ?></div>
    <div class="div19">
        <strong>Room Picture:</strong>
        <img src="<?php echo $roomDetails['gallery']; ?>" alt="Room Picture" style="width:100%; max-width:300px;">
    </div>
</div>


<style>
.parent {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    grid-template-rows: repeat(7, auto);
    gap: 8px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    width: 100%;
    max-width: 1100px;
    margin: auto;
}

.div1, .div2, .div3, .div4, .div5, .div6, .div7, .div8, .div9, .div10, .div11, .div12, .div13, .div14, .div15, .div16, .div17, .div18, .div19 {
    padding: 10px;
    background-color: #f7f7f7;
    border-radius: 5px;
    font-size: 14px;
}

.div1 { grid-column: span 2 / span 2; }
.div2 { grid-column: span 2 / span 2; grid-column-start: 1; grid-row-start: 2; }
.div3 { grid-column: span 2 / span 2; grid-column-start: 1; grid-row-start: 3; }
.div4 { grid-column: span 2 / span 2; grid-column-start: 1; grid-row-start: 4; }
.div5 { grid-column: span 2 / span 2; grid-column-start: 3; grid-row-start: 1; }
.div6 { grid-column: span 2 / span 2; grid-column-start: 3; grid-row-start: 2; }
.div7 { grid-column: span 2 / span 2; grid-column-start: 3; grid-row-start: 3; }
.div8 { grid-column: span 2 / span 2; grid-column-start: 3; grid-row-start: 4; }
.div9 { grid-column-start: 5; grid-row-start: 3; }
.div10 { grid-column-start: 5; grid-row-start: 4; }
.div11 { grid-row: span 2 / span 2; grid-column-start: 5; grid-row-start: 1; }
.div12 { grid-column: span 2 / span 2; grid-row-start: 5; }
.div13 { grid-column: span 2 / span 2; grid-column-start: 1; grid-row-start: 6; }
.div14 { grid-row: span 2 / span 2; grid-column-start: 3; grid-row-start: 5; }
.div15 { grid-row: span 2 / span 2; grid-column-start: 4; grid-row-start: 5; }
.div16 { grid-column-start: 1; grid-row-start: 7; }
.div17 { grid-column-start: 2; grid-row-start: 7; }
.div18 { grid-column: span 2 / span 2; grid-column-start: 3; grid-row-start: 7; }
.div19 { grid-row: span 3 / span 3; grid-column-start: 5; grid-row-start: 5; }

.btn {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.accept {
    background-color: #4CAF50;
    color: white;
}

.refund {
    background-color: #f44336;
    color: white;
}

.contact {
    background-color: #007bff;
    color: white;
}

.accept:hover, .refund:hover, .contact:hover {
    opacity: 0.9;
}
</style>

<script>
function contactCustomer(username) {
    alert('Contacting customer: ' + username);
}
</script>
