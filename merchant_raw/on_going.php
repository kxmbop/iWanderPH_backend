<?php
include "db_conn.php";

// Initialize filter and sorting variables
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Build SQL query
$sql = "
    SELECT 
        b.BookingID, 
        b.CheckIn, 
        b.CheckOut, 
        t.Username AS TravelerUsername, 
        b.BookingStatus, 
        b.TotalAmount
    FROM 
        bookings b
    JOIN 
        traveler t ON b.TravelerID = t.TravelerID
    WHERE 
        b.BookingStatus IN ('Ready', 'Checked-In', 'Checked-Out')
";

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

// Display messages
$message = isset($_GET['message']) ? $_GET['message'] : '';
$message_text = '';

switch ($message) {
    case 'checked_in_success':
        $message_text = 'Booking checked in successfully.';
        break;
    case 'checked_out_success':
        $message_text = 'Booking checked out successfully.';
        break;
    case 'checked_in_error':
        $message_text = 'Error checking in booking.';
        break;
    case 'checked_out_error':
        $message_text = 'Error checking out booking.';
        break;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>On-Going</title>
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
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 14px;
        }

        .check-in-btn {
            background-color: #28a745;
        }

        .check-out-btn {
            background-color: #ffc107;
        }

        .check-in-btn:hover {
            background-color: #218838;
        }

        .check-out-btn:hover {
            background-color: #e0a800;
        }

        .alert {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            font-size: 16px;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-ready {
            background-color: #ffecb5; /* Light orange */
        }

        .status-checked-in {
            background-color: #d4edda; /* Light green */
        }

        .status-checked-out {
            background-color: #fff3cd; /* Light yellow */
        }

</style>
</head>
<body>

<!-- Display message -->
<?php if ($message_text): ?>
    <div class="alert <?php echo (strpos($message, 'error') !== false ? 'error' : ''); ?>">
        <?php echo htmlspecialchars($message_text); ?>
    </div>
<?php endif; ?>


<h2>On-going</h2>

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
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Traveler</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Format dates
                $checkIn = date('F j, Y g:i A', strtotime($row['CheckIn']));
                $checkOut = date('F j, Y g:i A', strtotime($row['CheckOut']));
                $totalAmount = '₱ ' . number_format($row['TotalAmount'], 2);
                $statusClass = '';

                // Set background color based on status
                switch ($row['BookingStatus']) {
                    case 'Ready':
                        $statusClass = 'status-ready';
                        break;
                    case 'Checked-In':
                        $statusClass = 'status-checked-in';
                        break;
                    case 'Checked-Out':
                        $statusClass = 'status-checked-out';
                        break;
                }

                echo "<tr>
                    <td>{$row['BookingID']}</td>
                    <td>{$checkIn}</td>
                    <td>{$checkOut}</td>
                    <td>{$row['TravelerUsername']}</td>
                    <td class='{$statusClass}'>{$row['BookingStatus']}</td>
                    <td>{$totalAmount}</td>
                    <td class='action-buttons'>
                        <form action='check_in.php' method='POST'>
                            <input type='hidden' name='booking_id' value='{$row['BookingID']}'>
                            <button type='submit' class='check-in-btn'>Check In</button>
                        </form>
                        <form action='check_out.php' method='POST'>
                            <input type='hidden' name='booking_id' value='{$row['BookingID']}'>
                            <button type='submit' class='check-out-btn'>Check Out</button>
                        </form>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No records found</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>