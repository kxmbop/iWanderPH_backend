<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; // Database connection

// Query to fetch only Support Agents (non-SuperAdmin)
$sql = "SELECT adminID, firstName, lastName, email, phoneNumber, address, cityState, postCode, taxID, username, adminUserType
        FROM admin
        WHERE adminUserType = 'SupportAgent'"; // Updated to fetch only SupportAgents
$result = $conn->query($sql);

$supportAgents = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $supportAgents[] = $row;
    }
}

echo json_encode($supportAgents);

$conn->close();
?>
