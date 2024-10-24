<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';
include '../encryption.php';  
$key = "123456";  

$chatSessionId = $_GET['chatSessionId'] ?? '';

if ($chatSessionId) {
    $sql = "
        SELECT cm.chatMessageId, cm.senderId, cm.message, cm.timestamp, 
               cs.chatType
        FROM chat_messages cm
        JOIN chat_session cs ON cm.chatSessionId = cs.chatSessionId
        WHERE cm.chatSessionId = ?
        ORDER BY cm.timestamp ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $chatSessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $senderId = $row['senderId'];
            $_senderId = decrypt($senderId, $key);  
            $senderParts = explode(' - ', $_senderId);
            $senderRole = $senderParts[2];  

            if ($senderRole === 'admin') {
                $senderName = 'Customer Support';  
            } elseif ($row['chatType'] === 'support&merchant') {
                $sqlMerchant = "SELECT businessName FROM merchant WHERE MerchantUUID = ?";
                $stmtMerchant = $conn->prepare($sqlMerchant);
                $stmtMerchant->bind_param('s', $senderId);
                $stmtMerchant->execute();
                $resultMerchant = $stmtMerchant->get_result();
                $merchant = $resultMerchant->fetch_assoc();
                $senderName = $merchant['businessName'] ?? 'Unknown Merchant';
            } elseif ($row['chatType'] === 'support&traveler') {
                
                $sqlTraveler = "SELECT userName FROM traveler WHERE TravelerUUID = ?";
                $stmtTraveler = $conn->prepare($sqlTraveler);
                $stmtTraveler->bind_param('s', $senderId);
                $stmtTraveler->execute();
                $resultTraveler = $stmtTraveler->get_result();
                $traveler = $resultTraveler->fetch_assoc();
                $senderName = $traveler['userName'] ?? 'Unknown Traveler';
            } else {
                $senderName = 'Unknown Sender';  
            }

            $messages[] = [
                'chatMessageId' => $row['chatMessageId'],
                'senderName' => $senderName,
                'message' => $row['message'],
                'timestamp' => $row['timestamp']
            ];
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
