<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT merchantID, businessName, email, contact, address, businessType FROM merchant WHERE isApproved = 0";
    $result = $conn->query($sql);

    $merchants = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $merchants[] = $row;
        }
    }

    echo json_encode($merchants);
}
?>
