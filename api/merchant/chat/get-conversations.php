<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';
include '../encryption.php';
require '../../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'), true);

$merchantToken = $data['merchantId'] ?? '';
$key = "123456";  

try {
    $decoded = JWT::decode($merchantToken, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;  
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

$query = "SELECT merchantUUID FROM merchant WHERE travelerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $travelerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $merchantUUID = $row['merchantUUID'];
} else {
    echo json_encode(["success" => false, "error" => "Merchant UUID not found"]);
    exit;
}

$sql = "
    SELECT cs.chatSessionId, cs.userOne, cs.userTwo, cs.updatedAt
    FROM chat_session cs
    WHERE cs.userOne = ? OR cs.userTwo = ?
    ORDER BY cs.updatedAt DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare the query']);
    exit;
}

$stmt->bind_param("ss", $merchantUUID, $merchantUUID);
$stmt->execute();
$result = $stmt->get_result();
$conversations = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chatSessionId = $row['chatSessionId'];
        $userOne = $row['userOne'];
        $userTwo = $row['userTwo'];
        $receiverUUID = ($userOne === $merchantUUID) ? $userTwo : $userOne;

        $_userUUID = decrypt($receiverUUID, $key); 
        $userParts = explode(' - ', $_userUUID);  
        $userId = $userParts[0];
        $userRole = $userParts[2]; 
        
        if ($userRole === 'traveler') {
            $userQuery = "SELECT username AS name FROM traveler WHERE travelerID = ?";
        } else{
            $receiverName = "Customer Support";
        }

        if (isset($userQuery)) {
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param('s', $userId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();

            if ($userResult->num_rows > 0) {
                $userRow = $userResult->fetch_assoc();
                $receiverName = $userRow['name'];
            } else {
                $receiverName = "Customer Support"; 
            }

            $userStmt->close();
        }

        // Add conversation details to the response
        $conversations[] = [
            'chatSessionId' => $chatSessionId,
            'receiverUUID' => $receiverUUID,
            'updatedAt' => $row['updatedAt'],
            'receiverName' => $receiverName
        ];
    }
}

echo json_encode($conversations);

$stmt->close();
$conn->close();
?>
