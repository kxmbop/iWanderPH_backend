<?php
// db_conn.php
include "db_conn.php";

// Check if a booking ID is passed in the URL
if (isset($_GET['id'])) {
    $bookingId = $_GET['id'];

    // Fetch the booking details from the database
    $sql = "
        SELECT 
            b.BookingID as bookingId,
            b.BookingDate as bookingDate,
            CONCAT(t.FirstName, ' ', t.LastName) as travelerFullName,
            t.Email as travelerEmail,
            t.Username as travelerUsername,
            t.TravelerID as travelerId,
            m.Email as merchantEmail,  
            m.BusinessName as merchantBusinessName,
            m.MerchantID as merchantId,
            b.Subtotal as subtotal,       
            b.VAT as vat,                
            b.PayoutAmount as payout,           
            b.TotalAmount as totalAmount, 
            b.BookingStatus as bookingstatus, 
            b.Duration as duration,
            b.PaymentStatus as paymentstatus,
            b.CheckIn as checkIn,
            b.CheckOut as checkOut,
            b.RefundAmount as refundAmount
        FROM bookings b
        JOIN traveler t ON b.TravelerID = t.TravelerID
        JOIN merchant m ON b.MerchantID = m.MerchantID
        WHERE b.BookingID = ?
    ";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a booking was found
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        echo "No booking found with that ID.";
        exit;
    }

    $stmt->close();
} else {
    echo "No booking ID provided.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Booking Details</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .sections-container {
            display: flex;
            justify-content: space-between;
            gap: 20px; /* Adds space between sections */
            margin-top: 20px;
        }

        .section {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            border: 2px solid darkslategray; /* Border color set to darkslategray */
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
        }

        td:first-child {
            font-weight: bold;
            width: 40%; /* Adjusts width for the label column */
        }

        td:last-child {
            text-align: left;
            width: 60%; /* Adjusts width for the value column */
        }


        

    </style>
</head>
<body>
    <div class="container">
        <h1>Booking Details</h1>
        <h2>#<?php echo $booking['bookingId']; ?></h2>
        <div class="sections-container">
            <div class="section booking-details">
                <table>
                    <tr>
                        <td><strong>Booking ID:</strong></td>
                        <td><?php echo $booking['bookingId']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Booking Date:</strong></td>
                        <td><?php echo date('Y-m-d', strtotime($booking['bookingDate'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Business Name:</strong></td>
                        <td><?php echo $booking['merchantBusinessName']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Booking Status:</strong></td>
                        <td><?php echo $booking['bookingstatus']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td>PHP <?php echo number_format($booking['subtotal'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>VAT:</strong></td>
                        <td>PHP <?php echo number_format($booking['vat'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Payout Amount:</strong></td>
                        <td>PHP <?php echo number_format($booking['payout'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Refund Amount:</strong></td>
                        <td>PHP <?php echo number_format($booking['refundAmount'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Amount:</strong></td>
                        <td>PHP <?php echo number_format($booking['totalAmount'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Payment Status:</strong></td>
                        <td><?php echo $booking['paymentstatus']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Duration:</strong></td>
                        <td><?php echo $booking['duration']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Check-In Date:</strong></td>
                        <td><?php echo $booking['checkIn']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Check-Out Date:</strong></td>
                        <td><?php echo $booking['checkOut']; ?></td>
                    </tr>
                </table>
            </div>

            <div class="section traveler-details">
                <h2>Traveler Details</h2>
                <table>
                    <tr>
                        <td><strong>Traveler ID:</strong></td>
                        <td><?php echo $booking['travelerId']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td><?php echo $booking['travelerUsername']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td><?php echo $booking['travelerFullName']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo $booking['travelerEmail']; ?></td>
                    </tr>
                </table>
            </div>

            <div class="section merchant-details">
                <h2>Merchant Details</h2>
                <table>
                    <tr>
                        <td><strong>Merchant ID:</strong></td>
                        <td><?php echo $booking['merchantId']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Business Name:</strong></td>
                        <td><?php echo $booking['merchantBusinessName']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email (as Username):</strong></td>
                        <td><?php echo $booking['merchantEmail']; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>


</body>

</html>
