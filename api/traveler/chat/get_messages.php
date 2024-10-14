<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../../../db.php'; 

$chatSessionId = $_GET['chatSessionId'];

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