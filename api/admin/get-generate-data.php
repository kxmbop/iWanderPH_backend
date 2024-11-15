<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';


$labels = [];
for ($i = 5; $i >= 0; $i--) {
    $monthYear = date("F Y", strtotime("-$i month"));
    $labels[] = $monthYear;
}

// Initialize monthlyTrends with labels and empty data for each month
$monthlyTrends = ["labels" => $labels, "data" => []];
$monthlyData = array_fill_keys($labels, 0); // Initialize total bookings for each month

// Query for monthly booking trends
$sql = "SELECT 
            DATE_FORMAT(bookingDate, '%M %Y') AS monthYear,
            COUNT(*) AS totalBookings
            FROM booking
            WHERE bookingDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY YEAR(bookingDate), MONTH(bookingDate)
            ORDER BY YEAR(bookingDate), MONTH(bookingDate)";

$result = $conn->query($sql);

// Check if query returned results
if ($result === false) {
    die(json_encode(["error" => "Database query failed"]));
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $monthYear = $row["monthYear"];
        $totalBookings = (int)$row["totalBookings"];

        // Add the total bookings for that month
        $monthlyData[$monthYear] = $totalBookings;
    }

    // Prepare the data for the response
    foreach ($labels as $monthYear) {
        $monthlyTrends["data"][] = $monthlyData[$monthYear];
    }
}


$sqlCustomerCount = "SELECT 
                        merchantID,
                        COUNT(*) AS bookingCount
                    FROM booking
                    GROUP BY merchantID";

$resultCustomers = $conn->query($sqlCustomerCount);

// Initialize counters for new and repeat customers
$newCustomerCount = 0;
$repeatCustomerCount = 0;

if ($resultCustomers && $resultCustomers->num_rows > 0) {
    while ($row = $resultCustomers->fetch_assoc()) {
        $bookingCount = (int)$row["bookingCount"];
        
        // If customer has only 1 booking, they are new, otherwise, they are repeat
        if ($bookingCount == 1) {
            $newCustomerCount++;
        } else {
            $repeatCustomerCount++;
        }
    }
}

// Query for revenue trends for the last 6 months
$sqlRevenueTrends = "SELECT 
        DATE_FORMAT(bookingDate, '%M %Y') AS monthYear,
        SUM(subtotal - payoutAmount) AS totalRevenue
    FROM booking
    WHERE bookingDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(bookingDate), MONTH(bookingDate)
    ORDER BY YEAR(bookingDate), MONTH(bookingDate)
";


$resultRevenue = $conn->query($sqlRevenueTrends);

// Initialize revenue data
$revenueData = array_fill_keys($labels, 0); // Initialize revenue data for each month

if ($resultRevenue && $resultRevenue->num_rows > 0) {
    while ($row = $resultRevenue->fetch_assoc()) {
        $monthYear = $row["monthYear"];
        $totalRevenue = (float)$row["totalRevenue"];

        // Add the revenue for that month
        $revenueData[$monthYear] = $totalRevenue;
    }
}

// Prepare the revenue data for the response
$revenueTrends = ["labels" => $labels, "data" => []];
foreach ($labels as $monthYear) {
    $revenueTrends["data"][] = $revenueData[$monthYear];
}

// Return the data as JSON
echo json_encode([
    "monthlyTrends" => $monthlyTrends,
    "customerDemographics" => [
        "new" => $newCustomerCount,
        "repeat" => $repeatCustomerCount
    ],
    "revenueTrends" => $revenueTrends  // Add the revenue data to the response
]);



$conn->close();
?>
