<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT header, description, visibleTo, created_at FROM notifications ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $notifications = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
    echo json_encode($notifications);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $header = $_POST['header']; 
    $description = $_POST['description']; 
    $visibleto = $_POST['visibleto']; 
    $dedicatedto = $_POST['dedicatedto']; 

    error_log("Header: $header, Description: $description, Visible To: $visibleto, Dedicated To: $dedicatedto");

    $sql = "INSERT INTO notifications (header, description, visibleTo, dedicatedTo, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $header, $description, $visibleto, $dedicatedto);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Notification successfully posted!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Something went wrong.']);
    }

    $stmt->close();
}
?>
