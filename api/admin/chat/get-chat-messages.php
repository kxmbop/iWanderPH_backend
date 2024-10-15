<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';

// Get chatSessionId from the query parameters
$chatSessionId = $_GET['chatSessionId'] ?? '';

if ($chatSessionId) {
    $sql = "
        SELECT cm.chatMessageId, cm.senderId, cm.message, cm.timestamp, 
               IF(cs.chatType = 'merchant', m.merchantName, t.travelerName) as senderName
        FROM chat_messages cm
        JOIN chat_session cs ON cm.chatSessionId = cs.chatSessionId
        LEFT JOIN merchant m ON cm.senderId = m.merchantUUID AND cs.chatType = 'merchant'
        LEFT JOIN traveler t ON cm.senderId = t.travelerUUID AND cs.chatType = 'traveler'
        WHERE cm.chatSessionId = ?
        ORDER BY cm.timestamp ASC";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Bind the parameters and execute
        $stmt->bind_param('s', $chatSessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        echo json_encode($messages);
        
        $stmt->close();
    } else {
        echo json_encode(["error" => "Failed to prepare the SQL statement"]);
    }
} else {
    echo json_encode(["error" => "No chat session provided"]);
}

$conn->close();
?>
