<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT name, username FROM admin";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $admins = [];
        while($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        echo json_encode($admins);
    } else {
        echo json_encode([]);
    }

    $conn->close();
}
?>
