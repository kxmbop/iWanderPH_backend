<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// SQL to get merchants and their lowest room rates
$sql = "SELECT m.MerchantID, m.BusinessName, m.Address, 
               (SELECT MIN(r.RoomRate) FROM rooms r WHERE r.MerchantID = m.MerchantID) AS lowestRoomRate 
        FROM merchant m 
        WHERE m.isApproved = 1"; // Assuming you only want approved merchants

$result = $conn->query($sql);

$merchants = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $merchants[] = $row;
    }
}

echo json_encode($merchants);

$conn->close();
//hello
?>
