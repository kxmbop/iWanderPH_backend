<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Fetch data
$totalRevenueQuery = "SELECT SUM(amount) AS totalRevenue FROM bookings";
$totalUsersQuery = "SELECT COUNT(*) AS totalUsers FROM users";
$totalBusinessQuery = "SELECT COUNT(*) AS totalBusiness FROM businesses";
$performanceQuery = "SELECT ROUND((SUM(amount) / (SELECT SUM(amount) FROM bookings WHERE date < CURDATE())) * 100, 2) AS performancePercentage FROM bookings WHERE date = CURDATE()";
$newBusinessesQuery = "SELECT * FROM businesses ORDER BY date_registered DESC LIMIT 5";

$totalRevenue = $conn->query($totalRevenueQuery)->fetch_assoc()['totalRevenue'] ?? 0;
$totalUsers = $conn->query($totalUsersQuery)->fetch_assoc()['totalUsers'] ?? 0;
$totalBusiness = $conn->query($totalBusinessQuery)->fetch_assoc()['totalBusiness'] ?? 0;
$performancePercentage = $conn->query($performanceQuery)->fetch_assoc()['performancePercentage'] ?? 0;
$newBusinesses = $conn->query($newBusinessesQuery);

$newBusinessesList = [];
while ($row = $newBusinesses->fetch_assoc()) {
    $newBusinessesList[] = [
        "name" => $row['owner_name'],
        "date" => $row['date_registered'],
        "businessName" => $row['business_name'],
        "amount" => $row['initial_deposit']
    ];
}

// Return JSON response
echo json_encode([
    "totalRevenue" => $totalRevenue,
    "totalUsers" => $totalUsers,
    "totalBusiness" => $totalBusiness,
    "performancePercentage" => $performancePercentage,
    "newBusinesses" => $newBusinessesList
]);

$conn->close();
?>
