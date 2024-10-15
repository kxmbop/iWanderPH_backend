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

    // Fetch the sender's UUID from the database
    $getSenderUUID = "SELECT travelerUUID FROM traveler WHERE travelerId = '$senderId'";
    $result = mysqli_query($conn, $getSenderUUID);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $senderUUID = $row['travelerUUID'];
    } else {
        echo json_encode(['success' => false, 'error' => 'Sender UUID not found']);
        exit;
    }

    // Insert the new message into the chat_messages table
    $query = "INSERT INTO chat_messages (chatSessionId, senderId, message, timestamp) 
              VALUES ('$chatSessionId', '$senderUUID', '$message', NOW())";

    if (mysqli_query($conn, $query)) {
        // Update the updatedAt column in the chat_session table
        $updateSql = "UPDATE chat_session SET updatedAt = NOW() WHERE chatSessionId = '$chatSessionId'";
        if (mysqli_query($conn, $updateSql)) {
            echo json_encode(['success' => true, 'message' => 'Message sent and session updated']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Message sent, but failed to update chat session']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
}

mysqli_close($conn);
?>
