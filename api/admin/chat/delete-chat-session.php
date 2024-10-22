<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include '../../../db.php';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$chatSessionId = isset($_GET['chatSessionId']) ? intval($_GET['chatSessionId']) : null;

if ($chatSessionId) {
    $queryMessages = "DELETE FROM chat_messages WHERE chatSessionId = ?";
    $stmtMessages = $conn->prepare($queryMessages);
    
    if ($stmtMessages) {
        $stmtMessages->bind_param("i", $chatSessionId);
        $stmtMessages->execute();
        
        $querySession = "DELETE FROM chat_session WHERE chatSessionId = ?";
        $stmtSession = $conn->prepare($querySession);
        
        if ($stmtSession) {
            $stmtSession->bind_param("i", $chatSessionId);
            $stmtSession->execute();
            
            if ($stmtSession->affected_rows > 0) {
                echo json_encode(['message' => 'Chat session and messages deleted successfully.']);
            } else {
                echo json_encode(['error' => 'Chat session not found or already deleted.']);
            }
        } else {
            echo json_encode(['error' => 'Failed to prepare statement for deleting chat session.']);
        }
    } else {
        echo json_encode(['error' => 'Failed to prepare statement for deleting chat messages.']);
    }
} else {
    echo json_encode(['error' => 'Chat session ID is required.']);
}

$conn->close();
?>