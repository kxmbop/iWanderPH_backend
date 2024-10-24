<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../../../db.php'; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->chatSessionId)) {
        $chatSessionId = $data->chatSessionId;

        mysqli_begin_transaction($conn);

        try {
            $deleteMessagesQuery = "DELETE FROM chat_messages WHERE chatSessionId = ?";
            $stmt = $conn->prepare($deleteMessagesQuery);
            $stmt->bind_param("s", $chatSessionId);
            $stmt->execute();

            $deleteSessionQuery = "DELETE FROM chat_session WHERE chatSessionId = ?";
            $stmt = $conn->prepare($deleteSessionQuery);
            $stmt->bind_param("s", $chatSessionId);
            $stmt->execute();

            mysqli_commit($conn);
            echo json_encode(["status" => "success", "message" => "Conversation deleted successfully."]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(["status" => "error", "message" => "Failed to delete conversation: " . $e->getMessage()]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Chat session ID not provided."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

?>