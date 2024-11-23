<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Create labels for the last 6 months (from the current month to the past 5 months)
$labels = [];
for ($i = 5; $i >= 0; $i--) {
    $monthYear = date("F Y", strtotime("-$i month"));
    $labels[] = $monthYear;
}

// Initialize arrays to hold the trends and data
$monthlyTrends = ["labels" => $labels, "data" => []];
$monthlyData = array_fill_keys($labels, 0); // Initialize total bookings for each month
$trends = [];

// Query to fetch monthly booking trends with additional information (e.g., merchant name, booking status)
$sql = "SELECT 
            DATE_FORMAT(b.bookingDate, '%M %Y') AS monthYear,
            b.bookingID,  
            b.merchantID,
            b.bookingStatus,  
            b.subtotal,
            b.payoutAmount,
            m.businessName AS merchantName
        FROM booking b
        JOIN merchant m ON b.merchantID = m.merchantID
        WHERE b.bookingDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        AND b.bookingStatus != 'refunded'  
        ORDER BY b.bookingDate";

$result = $conn->query($sql);

// Check if query returned results
if ($result === false) {
    die(json_encode(["error" => "Database query failed"]));
}

if ($result->num_rows > 0) {
    // Process the results
    while ($row = $result->fetch_assoc()) {
        $monthYear = $row["monthYear"];
        $bookingID = $row["bookingID"];
        $merchantName = $row["merchantName"];
        $bookingStatus = $row["bookingStatus"];
        $subtotal = (float)$row["subtotal"];
        $payoutAmount = (float)$row["payoutAmount"];
        
        // Calculate booking percentage only if the booking is 'completed'
        if ($bookingStatus === 'completed') {
            // Calculate booking percentage
            $bookingPercentage = (($subtotal - $payoutAmount) / 500 ) / 100; 

            // Add data to the trends array
            $trends[] = [
                "month" => $monthYear,
                "bookingID" => $bookingID,
                "merchantName" => $merchantName,
                "bookingStatus" => $bookingStatus,
                "bookingPercentage" => $bookingPercentage
            ];
        }

        // Update monthly data for the graph
        $monthlyData[$monthYear] = isset($monthlyData[$monthYear]) ? $monthlyData[$monthYear] + 1 : 1;
    }

    // Prepare the monthly trends data for the response (graph data)
    foreach ($labels as $monthYear) {
        $monthlyTrends["data"][] = $monthlyData[$monthYear];
    }
}

// Query for revenue trends for the last 6 months
$sqlRevenueTrends = "SELECT 
        DATE_FORMAT(bookingDate, '%M %Y') AS monthYear,
        SUM(subtotal - payoutAmount) AS totalRevenue
        FROM booking
        WHERE bookingDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(bookingDate), MONTH(bookingDate)
        ORDER BY YEAR(bookingDate), MONTH(bookingDate)";

$resultRevenue = $conn->query($sqlRevenueTrends);

// Initialize revenue data
$revenueData = array_fill_keys($labels, 0);

if ($resultRevenue && $resultRevenue->num_rows > 0) {
    while ($row = $resultRevenue->fetch_assoc()) {
        $monthYear = $row["monthYear"];
        $totalRevenue = (float)$row["totalRevenue"];
        $revenueData[$monthYear] = $totalRevenue;
    }
}

$revenueTrends = ["labels" => $labels, "data" => []];
foreach ($labels as $monthYear) {
    $revenueTrends["data"][] = $revenueData[$monthYear];
}

// Return the data as JSON
echo json_encode([
    "monthlyTrends" => $monthlyTrends,
    "revenueTrends" => $revenueTrends,
    "trends" => $trends
]);

$conn->close();
?>
