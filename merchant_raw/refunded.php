<?php
include "db_conn.php";

// Initialize filter and sorting variables
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Base query to select refunded bookings
$sql = "SELECT b.BookingID, b.BookingDate, t.Username, b.BookingStatus, b.TotalAmount, b.ReasonForRefund
        FROM bookings b
        INNER JOIN traveler t ON b.TravelerID = t.TravelerID
        WHERE b.BookingStatus = 'refunded'";

// Apply filter for username if provided
if (!empty($filter)) {
    $filter = $conn->real_escape_string($filter);
    $sql .= " AND (b.BookingID LIKE '%$filter%' OR t.Username LIKE '%$filter%')";
}

// Apply monthly sorting if provided
if (!empty($month) && !empty($year)) {
    $sql .= " AND YEAR(b.BookingDate) = $year AND MONTH(b.BookingDate) = $month";
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
    <title>Refunded</title>
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
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-refunded {
            color: red;
        }
</style>
</head>
<body>

<h2>Refunded</h2>
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

<!-- Display the results in a table -->
<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Booking Date</th>
                <th>Username</th>
                <th>Booking Status</th>
                <th>Total Amount</th>
                <th>Reason for Refund</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['BookingID']; ?></td>
                    <td><?php echo date("M d, Y", strtotime($row['BookingDate'])); ?></td>
                    <td><?php echo $row['Username']; ?></td>
                    <td class="status-refunded"><?php echo ucfirst($row['BookingStatus']); ?></td>
                    <td>₱<?php echo number_format($row['TotalAmount'], 2); ?></td>
                    <td><?php echo $row['ReasonForRefund']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No refunded bookings found.</p>
<?php endif; ?>


</body>
</html>

<?php
$conn->close();
?>