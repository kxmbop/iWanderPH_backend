<?php
include "db_conn.php";

// Get filter and sort values
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Build the SQL query
$sql = "SELECT b.BookingID, b.BookingDate, b.PaymentStatus, b.ListingID, b.BookingStatus, b.Duration, b.CheckIn, b.CheckOut, b.Subtotal, b.VAT, b.PayoutAmount, b.TotalAmount, b.RefundAmount, t.Username
FROM bookings b
JOIN traveler t ON b.TravelerID = t.TravelerID
WHERE b.BookingStatus = 'pending'";

// Apply filter for username if provided
if (!empty($filter)) {
    $filter = $conn->real_escape_string($filter);
    $sql .= " AND (b.BookingID LIKE '%$filter%' OR t.Username LIKE '%$filter%')";
}

// Apply monthly sorting if provided
if (!empty($month) && !empty($year)) {
    $sql .= " AND YEAR(b.BookingDate) = $year AND MONTH(b.BookingDate) = $month";
}

// Execute the query
$result = $conn->query($sql);

// Handle connection close
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");
        * {
            margin: 0;
            padding: 0;
            border: none;
            outline: none;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f8f9fa;
            margin: 5%;
            padding: 0;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .filter-form {
            margin: 20px auto;
            max-width: 1300px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form input[type="text"], 
        .filter-form input[type="number"], 
        .filter-form select,
        .filter-form input[type="submit"] {
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            flex: 1;
            margin: 5px 0;
        }

        .filter-form input[type="text"], 
        .filter-form input[type="number"] {
            min-width: 150px;
        }

        .filter-form select {
            min-width: 150px;
        }

        .filter-form input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .filter-form input[type="submit"]:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
            border-radius: 4px;
            text-align: center;
            padding: 4px;
        }

        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
            border-radius: 4px;
            text-align: center;
            padding: 4px;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            text-align: center;
            padding: 4px;
        }

        .actions a {
            text-decoration: none;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            display: inline-block;
        }

        .actions a.accept {
            background-color: #4CAF50;
        }

        .actions a.refund {
            background-color: #f44336;
        }

        .actions a:hover {
            opacity: 0.9;
        }

        .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.3);
    padding: 0;
    overflow: auto;
}

.modal-content {
    background-color: #ffffff;
    margin: 25% auto;
    padding: 20px;
    border-radius: 12px;
    width: 80%;  /* Make it wider */
    max-width: 1100px;
    position: relative;
    /* Landscape orientation */
    height: 40%;  /* Make it shorter */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    overflow-y: auto;
    font-family: Arial, sans-serif;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #ddd;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close {
    color: #888;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover,
.close:focus {
    color: #555;
    text-decoration: none;
}

.modal-body {
    padding: 0 10px;
    font-size: 16px;
    color: #444;
}

.modal-body p {
    margin-bottom: 12px;
    line-height: 1.6;
}

.modal-body img {
    max-width: 100%;
    border-radius: 8px;
    margin-top: 15px;
}

.modal-footer {
    border-top: 2px solid #ddd;
    padding-top: 15px;
    text-align: right;
}

.modal-footer button {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    background-color: #007bff;
    color: white;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.modal-footer button:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

.modal-footer button:focus {
    outline: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        height: 80%;
    }
}

        
    </style>
</head>
<body>

<h2>Pending</h2>
<br>
<form method="POST" action="" class="filter-form">
    <input type="text" name="filter" placeholder="Search by bookingID or username" value="<?php echo htmlspecialchars($filter); ?>">
    
    <select name="month">
        <option value="">Month</option>
        <option value="01" <?php echo ($month == '01' ? 'selected' : ''); ?>>January</option>
        <option value="02" <?php echo ($month == '02' ? 'selected' : ''); ?>>February</option>
        <option value="03" <?php echo ($month == '03' ? 'selected' : ''); ?>>March</option>
        <option value="04" <?php echo ($month == '04' ? 'selected' : ''); ?>>April</option>
        <option value="05" <?php echo ($month == '05' ? 'selected' : ''); ?>>May</option>
        <option value="06" <?php echo ($month == '06' ? 'selected' : ''); ?>>June</option>
        <option value="07" <?php echo ($month == '07' ? 'selected' : ''); ?>>July</option>
        <option value="08" <?php echo ($month == '08' ? 'selected' : ''); ?>>August</option>
        <option value="09" <?php echo ($month == '09' ? 'selected' : ''); ?>>September</option>
        <option value="10" <?php echo ($month == '10' ? 'selected' : ''); ?>>October</option>
        <option value="11" <?php echo ($month == '11' ? 'selected' : ''); ?>>November</option>
        <option value="12" <?php echo ($month == '12' ? 'selected' : ''); ?>>December</option>
    </select>

    <input type="number" name="year" placeholder="Year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($year); ?>">
    <input type="submit" value="Search">
</form>

<table>
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>Booking Date</th>
            <th>Traveler</th>
            <th>Payment Status</th>
            <th>Total Amount</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                $statusClass = '';
                switch ($row['PaymentStatus']) {
                    case 'successful':
                        $statusClass = 'status-success';
                        break;
                    case 'in-progress':
                        $statusClass = 'status-in-progress';
                        break;
                    case 'failed':
                        $statusClass = 'status-failed';
                        break;
                }
                // Format booking date
                $date = new DateTime($row['BookingDate']);
                $formattedDate = $date->format('F j, Y');
                
                echo '<tr>';
                echo '<td><a href="#" class="booking-id" data-id="' . $row['BookingID'] . '">' . $row['BookingID'] . '</a></td>';
                echo '<td>' . $formattedDate . '</td>';
                echo '<td>' . $row['Username'] . '</td>';
                echo '<td class="' . $statusClass . '">' . ucfirst($row['PaymentStatus']) . '</td>';
                echo '<td>₱ ' . number_format($row['TotalAmount'], 2) . '</td>';
                echo '<td class="actions">';
                echo '<a href="accept_booking.php?id=' . $row['BookingID'] . '" class="accept">Accept</a>';
                echo '<a href="refund_booking.php?id=' . $row['BookingID'] . '" class="refund">Refund</a>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6">0 results</td></tr>';
        }
        ?>
    </tbody>
</table>

<!-- Modal Structure -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Booking Details</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" id="modal-details">
            <!-- Booking details will be loaded here using AJAX -->
        </div>
    </div>
</div>

<script>
    // Get the modal and close button elements
    var modal = document.getElementById('bookingModal');
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on the "Booking ID", open the modal
    document.querySelectorAll('.booking-id').forEach(function(element) {
        element.addEventListener('click', function(event) {
            event.preventDefault();
            var bookingID = this.getAttribute('data-id');

            // Fetch booking details using AJAX
            fetch('fetch_booking_details.php?bookingID=' + bookingID)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modal-details').innerHTML = data;
                    modal.style.display = "block";
                });
        });
    });

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>


</body>
</html>