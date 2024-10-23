<?php
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT header, description, visibleTo, createdAt FROM notifications ORDER BY createdAt DESC";
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

    $sql = "INSERT INTO notifications (header, description, visibleTo, dedicatedTo, createdAt) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $header, $description, $visibleto, $dedicatedto);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Notification successfully posted!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Something went wrong.']);
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    
    $notificationID = $_PUT['notificationID'];
    $header = $_PUT['header']; 
    $description = $_PUT['description']; 
    $visibleto = $_PUT['visibleto']; 
    $dedicatedto = $_PUT['dedicatedto'];

    $sql = "UPDATE notifications SET header = ?, description = ?, visibleTo = ?, dedicatedTo = ? WHERE notificationID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $header, $description, $visibleto, $dedicatedto, $notificationID);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Notification successfully updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
    }

    $stmt->close();
}
?>
