<?php
include "db_conn.php";

// Initialize filter and sorting variables
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';


// SQL query to fetch booking data with traveler and review information
$sql = "
SELECT 
    b.BookingID, b.CheckIn, b.CheckOut, b.BookingStatus, b.TotalAmount,
    t.Username,
    r.ReviewID, r.ReviewRating, r.ReviewMessage AS ReviewStatement, r.ReviewCreate AS ReviewDate
FROM bookings b
LEFT JOIN traveler t ON b.TravelerID = t.TravelerID
LEFT JOIN review r ON b.BookingID = r.BookingID
WHERE (b.BookingStatus = 'Checked-Out' OR b.BookingStatus = 'Completed')
";


// Apply filter for username if provided
if (!empty($filter)) {
    $filter = $conn->real_escape_string($filter);
    $sql .= " AND (b.BookingID LIKE '%$filter%' OR t.Username LIKE '%$filter%')";
}

/// Apply monthly sorting if provided
if (!empty($month) && !empty($year)) {
    $sql .= " AND YEAR(b.CheckOut) = $year AND MONTH(b.CheckOut) = $month";
}

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
    <title>Completed</title>
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

        .fa-star {
    color: #ddd;
}

.fa-star.shaded {
    color: #ffc107;
}

table {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    table th, table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
        font-size: 14px;
        color: #333;
    }

    table th {
        background-color: #007bff;
        color: #ffffff;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.03em;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    table td {
        font-size: 14px;
        color: #333;
    }

    table td:nth-child(6) {
        font-weight: bold;
    }

    table td i.fa-star {
        color: #ddd;
    }

    table td i.fa-star.shaded {
        color: #ffc107;
    }

    .completed-status {
            font-weight: bold;
            color: #28a745;
            background-color: #d4edda;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .pending-status {
            color: none;
            background-color: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
        }

        table, table th, table td {
            font-size: 12px;
        }
    }

/* Modal Styles */
.modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-header, .modal-body {
            margin-bottom: 20px;
        }

        .modal-body img {
            max-width: 100%;
            height: auto;
        }

        .btn-contact {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-contact:hover {
            background-color: #0056b3;
        }

</style>
</head>
<body>

<h2>Completed</h2>
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

<!-- Display booking table -->
<table>
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Traveler</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Review ID</th>
            <th>Rating</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
        <td><a href="javascript:void(0);" onclick="openModal('<?php echo $row['BookingID']; ?>')"><?php echo $row['BookingID']; ?></a></td>
            <td><?php echo date('M d, Y', strtotime($row['CheckIn'])); ?></td>
            <td><?php echo date('M d, Y', strtotime($row['CheckOut'])); ?></td>
            <td><?php echo $row['Username']; ?></td>
            <td class="<?php echo $row['ReviewID'] ? 'completed-status' : 'pending-status'; ?>">
                <?php echo $row['ReviewID'] ? 'Completed' : $row['BookingStatus']; ?>
            </td>
            <td>₱ <?php echo number_format($row['TotalAmount'], 2); ?></td>
            <td><?php echo $row['ReviewID'] ? $row['ReviewID'] : 'No Review'; ?></td>
            <td>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa fa-star <?php echo $i <= $row['ReviewRating'] ? 'shaded' : ''; ?>"></i>
                <?php endfor; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Modal Structure -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-header">
            <h2>Booking Details</h2>
        </div>
        <div class="modal-body">
            <p><strong>Booking ID:</strong> <span id="modalBookingID"></span></p>
            <p><strong>Check-In:</strong> <span id="modalCheckIn"></span></p>
            <p><strong>Traveler:</strong> <span id="modalUsername"></span></p>
            <p><strong>Booking Status:</strong> <span id="modalBookingStatus"></span></p>
            <p><strong>Review ID:</strong> <span id="modalReviewID"></span></p>
            <p><strong>Rating:</strong> <span id="modalReviewRating"></span></p>
            <p><strong>Review Statement:</strong> <span id="modalReviewStatement"></span></p>
            <p><strong>Review Date:</strong> <span id="modalReviewDate"></span></p>
            <button class="btn-contact">Contact Customer</button>
        </div>
    </div>
</div>

<script>
    function openModal(bookingID) {
        // Fetch booking details via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetchBooking_details.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                document.getElementById('modalBookingID').innerText = data.BookingID;
                document.getElementById('modalCheckIn').innerText = data.CheckIn;
                document.getElementById('modalUsername').innerText = data.Username;
                document.getElementById('modalBookingStatus').innerText = data.BookingStatus;
                document.getElementById('modalReviewID').innerText = data.ReviewID ? data.ReviewID : 'No Review';
                document.getElementById('modalReviewRating').innerText = data.ReviewRating;
                document.getElementById('modalReviewStatement').innerText = data.ReviewStatement || 'No review statement';
                document.getElementById('modalReviewDate').innerText = data.ReviewDate || 'No review date';
                
                document.getElementById('bookingModal').style.display = 'block';
            }
        };
        xhr.send('bookingID=' + encodeURIComponent(bookingID));
    }

    document.querySelector('.close').onclick = function() {
        document.getElementById('bookingModal').style.display = 'none';
    };

    window.onclick = function(event) {
        if (event.target == document.getElementById('bookingModal')) {
            document.getElementById('bookingModal').style.display = 'none';
        }
    };
</script>

</body>
</html>
