<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';
include '../encryption.php';  
$key = "123456";  

$chatSessionId = $_GET['chatSessionId'] ?? '';

$query = "SELECT senderId, message, timestamp 
          FROM chat_messages 
          WHERE chatSessionId = '$chatSessionId' 
          ORDER BY timestamp ASC";

$result = mysqli_query($conn, $query);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode(['success' => true, 'messages' => $messages]);

mysqli_close($conn);
?>
