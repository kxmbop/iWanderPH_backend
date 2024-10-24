<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$sql = "
    SELECT 
        t.TravelerID as userId,
        t.Username as username,
        CONCAT(t.FirstName, ' ', t.LastName) as fullName,
        t.Email as email,
        t.isMerchant as isMerchant
    FROM traveler t
";

$users = [];
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($traveler = $result->fetch_assoc()) {
        $userId = $traveler['userId'];
        
        $travelerGmvSql = "
            SELECT COALESCE(SUM(b.TotalAmount), 0) as travelerGmv
            FROM booking b
            WHERE b.TravelerID = $userId
        ";
        $travelerGmvResult = $conn->query($travelerGmvSql);
        $travelerGmvRow = $travelerGmvResult->fetch_assoc();
        $traveler['travelerGmv'] = $travelerGmvRow['travelerGmv'];
        
        if ($traveler['isMerchant']) {
            $merchantGmvSql = "
                SELECT COALESCE(SUM(b.PayoutAmount), 0) as merchantGmv
                FROM bookings b
                WHERE b.MerchantID = (SELECT MerchantID FROM merchant WHERE TravelerID = $userId)
            ";
            $merchantGmvResult = $conn->query($merchantGmvSql);
            $merchantGmvRow = $merchantGmvResult->fetch_assoc();
            $traveler['merchantGmv'] = $merchantGmvRow['merchantGmv'];
        } else {
            $traveler['merchantGmv'] = 0; 
        }

        $bookingCountSql = "
            SELECT COUNT(*) as numBookings
            FROM bookings b
            WHERE b.TravelerID = $userId
        ";
        $bookingCountResult = $conn->query($bookingCountSql);
        $bookingCountRow = $bookingCountResult->fetch_assoc();
        $traveler['numBookings'] = $bookingCountRow['numBookings'];


        $users[] = $traveler;
    }
}

echo json_encode($users);
$conn->close();
?>
