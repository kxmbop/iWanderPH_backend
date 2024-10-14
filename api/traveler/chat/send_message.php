<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../../../db.php'; 

$data = json_decode(file_get_contents("php://input"));

if (isset($data->chatSessionId) && isset($data->message)) {
    $chatSessionId = $data->chatSessionId;
    $message = $data->message;
    $senderId = $data->senderId; 

    if (empty($senderId)) {
        echo json_encode(['success' => false, 'error' => 'Sender ID is missing']);
        exit;
    }

    $getSenderUUID = "SELECT travelerUUID FROM traveler  WHERE travelerId = '$senderId'";
    $result = mysqli_query($conn, $getSenderUUID);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $senderUUID = $row['travelerUUID'];
    } else {
        echo json_encode(['success' => false, 'error' => 'Sender UUID not found']);
        exit;
    }
    
    $timestamp = date("Y-m-d H:i:s");

    $query = "INSERT INTO chat_messages (chatSessionId, senderId, message, timestamp) 
              VALUES ('$chatSessionId', '$senderUUID', '$message', '$timestamp')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
}

mysqli_close($conn);
?>
