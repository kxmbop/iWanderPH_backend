<?php
// db_conn.php
include "db_conn.php";

// Fetch the bookings from the database
$sql = "
    SELECT 
        b.BookingID as bookingId,
        b.BookingDate as bookingDate,
        CONCAT(t.FirstName, ' ', t.LastName) as travelerName,
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
";

$result = $conn->query($sql);

$bookings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>View Bookings</title>
</head>
<body>
<div class="hub-container">
    <span>View Bookings</span>
    <div class="filter-box">
        <div class="search-bar">
            <label>Search by:</label>
            <input type="number" placeholder="booking id" id="b_id">
            <input type="text" placeholder="traveler username" id="t_user">
            <input type="text" placeholder="merchant username" id="m_user">
            <input type="date">
        </div>
        <div class="filter-bar">
            <button id="export_bt">Export</button>
            <i class='bx bx-chevron-left' id="left_bt"></i>
            <i class='bx bx-chevron-right' id="right_bt"></i>
            <i class='bx bxs-info-circle' id="info_bt"></i>
        </div>       
    </div>
    <div class="table-box">
        <table class="table-header">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Booking ID</th>
                    <th>Traveler</th>
                    <th>Merchant</th>
                    <th>Payment Status</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
        <div class="table-body">
            <table>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($booking['bookingDate'])); ?></td>
                            <td><?php echo $booking['bookingId']; ?></td>
                            <td><?php echo $booking['travelerName']; ?></td>
                            <td><?php echo $booking['merchantName']; ?></td>
                            <td><?php echo $booking['paymentstatus']; ?></td>
                            <td><?php echo $booking['duration']; ?></td>
                            <td><?php echo 'PHP ' . number_format($booking['totalAmount'], 2); ?></td>
                            <td><?php echo $booking['bookingstatus']; ?></td>
                            <td><a href="booking_details.php?id=<?php echo $booking['bookingId']; ?>"><button>Open</button></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
