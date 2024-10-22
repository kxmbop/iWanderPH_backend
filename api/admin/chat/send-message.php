<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';
require '../../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'), true);

$chatSessionId = $data['chatSessionId'] ?? '';
$message = $data['message'] ?? '';
$adminToken = $data['adminToken'] ?? '';

$key = "123456"; 

try {
    $decoded = JWT::decode($adminToken, new Key($key, 'HS256'));
    $adminID = $decoded->adminID;

    $query = "SELECT adminUUID FROM admin WHERE adminID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $adminID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $adminUUID = $row['adminUUID'];
    } else {
        echo json_encode(["success" => false, "error" => "Admin UUID not found"]);
        exit;
    }

    $sql = "INSERT INTO chat_messages (chatSessionId, senderId, message, timestamp)
            VALUES (?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $chatSessionId, $adminUUID, $message);

    if ($stmt->execute()) {
        $updateSql = "UPDATE chat_session SET updatedAt = NOW() WHERE chatSessionId = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('s', $chatSessionId);
        
        if ($updateStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Message sent and session updated"]);
        } else {
            echo json_encode(["success" => false, "error" => "Message sent, but failed to update chat session"]);
        }

        $updateStmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Failed to send message"]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid or expired token']);
}

$conn->close();
?>
