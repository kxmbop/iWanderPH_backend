<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Get the last 6 months from the current date
$labels = [];
for ($i = 5; $i >= 0; $i--) {
    $monthYear = date("F Y", strtotime("-$i month"));
    $labels[] = $monthYear;
}

// Initialize monthlyTrends with labels and empty data for each month
$monthlyTrends = ["labels" => $labels, "data" => []];
$monthlyData = array_fill_keys($labels, []); // Initialize data for each month

// Query for monthly booking trends per Merchant ID
$sql = "SELECT 
            DATE_FORMAT(bookingDate, '%M %Y') AS monthYear,
            merchantID,
            COUNT(*) AS totalBookings
        FROM booking
        WHERE bookingDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(bookingDate), MONTH(bookingDate), merchantID
        ORDER BY YEAR(bookingDate), MONTH(bookingDate), merchantID";

$result = $conn->query($sql);

// Check if query returned results
if ($result === false) {
    die(json_encode(["error" => "Database query failed"]));
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $monthYear = $row["monthYear"];
        $merchantID = $row["merchantID"];
        $totalBookings = (int)$row["totalBookings"];

        // Initialize the month and merchant ID if they don't exist in the data structure
        if (!isset($monthlyData[$monthYear])) {
            $monthlyData[$monthYear] = [];
        }
        if (!isset($monthlyData[$monthYear][$merchantID])) {
            $monthlyData[$monthYear][$merchantID] = 0;
        }

        // Increment total bookings for the merchant in that month-year
        $monthlyData[$monthYear][$merchantID] += $totalBookings;
    }

    // Prepare the data for the response
    foreach ($labels as $monthYear) {
        $dataEntry = [];
        foreach ($monthlyData[$monthYear] as $merchantID => $totalBookings) {
            $dataEntry[] = $totalBookings;
        }
        // Add an empty entry if there are no bookings for that month
        $monthlyTrends["data"][] = !empty($dataEntry) ? $dataEntry : [0];
    }
}

// Return the data as JSON
echo json_encode([
    "monthlyTrends" => $monthlyTrends,
]);

$conn->close();
?>
