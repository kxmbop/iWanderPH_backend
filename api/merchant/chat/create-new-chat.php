<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';
require '../../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../encryption.php';
$key = "123456";

$data = json_decode(file_get_contents('php://input'), true);

$userUUID = $data['userId'] ?? '';
$merchantToken = $data['merchantId'] ?? '';

$_userUUID = decrypt($userUUID, $key);
$userUUIDParts = explode(' - ', $_userUUID);
$userID = $userUUIDParts[0];
$userRole = $userUUIDParts[2]; 

if (!empty($merchantToken)) {
    try {
        $decoded = JWT::decode($merchantToken, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        $query = "SELECT merchantUUID FROM merchant WHERE travelerID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $travelerID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $merchantUUID = $row['merchantUUID'];
        } else {
            echo json_encode(["error" => "Merchant UUID not found."]);
            exit;
        }

        if ($userUUID && $userRole) {
            if ($userRole === 'traveler') {
                $chatType = 'user&merchant';
            } else {
                echo json_encode(["error" => "Invalid user role"]);
                exit;
            }

            $sql = "INSERT INTO chat_session (chatType, userOne, userTwo, isActivated, createdAt, updatedAt)
                    VALUES (?, ?, ?, 1, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $chatType, $merchantUUID, $userUUID);

            if ($stmt->execute()) {
                $chatSessionId = $stmt->insert_id;
                echo json_encode(["success" => true, "chatSessionId" => $chatSessionId]);
            } else {
                echo json_encode(["success" => false, "error" => "Failed to create new chat session"]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Invalid input"]);
        }

    } catch (Exception $e) {
        echo json_encode(["error" => "Invalid or expired token: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["error" => "Authorization token missing"]);
    exit;
}

$conn->close();
?>
