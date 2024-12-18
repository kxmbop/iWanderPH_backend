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
    $sql = "SELECT header, description, visibleTo, createdAt FROM announcements ORDER BY createdAt DESC";
    $result = $conn->query($sql);

    $announcements = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
    echo json_encode($announcements);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $header = $_POST['header']; 
    $description = $_POST['description']; 
    $visibleto = $_POST['visibleto']; 

    error_log("Header: $header, Description: $description, Visible To: $visibleto");

    $sql = "INSERT INTO announcements (header, description, visibleTo, createdAt) VALUES ( ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $header, $description, $visibleto);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Announcement successfully posted!']);
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

    $sql = "UPDATE notifications SET header = ?, description = ?, visibleTo = ? WHERE notificationID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $header, $description, $visibleto, $notificationID);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Notification successfully updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
    }

    $stmt->close();
}
?>
