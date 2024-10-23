<?php
include "db_conn.php";

// Handle status change request via AJAX
if (isset($_POST['updateStatus']) && isset($_POST['BookingID'])) {
    $bookingID = $conn->real_escape_string($_POST['BookingID']);
    $sqlUpdate = "UPDATE bookings SET BookingStatus = 'ready' WHERE BookingID = '$bookingID'";
    $conn->query($sqlUpdate);
    echo json_encode(['success' => true]);
    exit();
}

// Get filter and sort values
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';

// Build the SQL query
$sql = "SELECT b.BookingID, 
               DATE_FORMAT(b.BookingDate, '%M %d, %Y') AS formattedDate, 
               b.BookingStatus, 
               b.TotalAmount, 
               t.Username
        FROM bookings b
        JOIN traveler t ON b.TravelerID = t.TravelerID
        WHERE b.BookingStatus IN ('accepted')";

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
    <title>Accepted</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        function markReady(bookingID, button) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Disable the button and update the status text
                button.disabled = true;
                button.innerText = 'Ready';
                button.style.backgroundColor = '#6c757d'; // Change to grey
                var statusCell = button.closest('tr').querySelector('.status');
                statusCell.innerHTML = '<span class="status-ready">Ready</span>';

                // Remove the row from the table
                var row = button.closest('tr');
                row.parentNode.removeChild(row); // Remove the row after marking it as ready
            }
        }
    };
    xhr.send("updateStatus=true&BookingID=" + bookingID);
}
    </script>

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

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
        }

        .booking-table th, .booking-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .booking-table th {
            background-color: #007bff;
            color: white;
        }

        .booking-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .actions {
            text-align: center;
        }

        .btn-ready {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
        }

        .btn-ready:hover {
            background-color: #218838;
            box-shadow: 0 6px 12px rgba(33, 136, 56, 0.5);
        }

        .btn-ready:active {
            background-color: #1e7e34;
            box-shadow: 0 3px 6px rgba(30, 126, 52, 0.3);
            transform: translateY(2px);
        }

</style>
</head>

<h2>Accepted</h2>

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

<table class="booking-table">
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>Booking Date</th>
            <th>Traveler</th>
            <th>Booking Status</th>
            <th>Total Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['BookingID']); ?></td>
                    <td><?php echo htmlspecialchars($row['formattedDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['Username']); ?></td>
                    <td class="status"><?php echo $row['BookingStatus'] == 'ready' ? '<span class="status-ready">Ready</span>' : htmlspecialchars($row['BookingStatus']); ?></td>
                    <td><?php echo '₱ ' . number_format($row['TotalAmount'], 2); ?></td>
                    <td class="actions">
                        <?php if ($row['BookingStatus'] != 'ready'): ?>
                            <button class="btn-ready" onclick="markReady('<?php echo $row['BookingID']; ?>', this)">Ready</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No bookings found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>