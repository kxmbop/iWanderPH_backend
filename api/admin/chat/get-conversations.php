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

$adminToken = $data['adminId'] ?? '';
$key = "123456";  

try {
    $decoded = JWT::decode($adminToken, new Key($key, 'HS256'));
    $adminID = $decoded->adminID;
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

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

$sql = "
    SELECT cs.chatSessionId, cs.userTwo, cs.updatedAt
    FROM chat_session cs
    WHERE cs.userOne = ? OR cs.userTwo = ?
    ORDER BY cs.updatedAt DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare the query']);
    exit;
}

$stmt->bind_param("ss", $adminUUID, $adminUUID);
$stmt->execute();

$result = $stmt->get_result();
$conversations = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chatSessionId = $row['chatSessionId'];
        $userTwo = $row['userTwo'];

        if ($userTwo !== $adminUUID) {
            $_userUUID = decrypt($userTwo, $key); 
            $userParts = explode(' - ', $_userUUID);
            $userId = $userParts[0];
            $userRole = $userParts[2]; 

            if ($userRole === 'merchant') {
                $userQuery = "SELECT businessName AS name FROM merchant WHERE merchantID = ?";
            } elseif ($userRole === 'traveler') {
                $userQuery = "SELECT CONCAT(firstName, ' ', lastName) AS name FROM traveler WHERE travelerID = ?";
            } else {
                continue; 
            }

            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param('s', $userId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();

            if ($userResult->num_rows > 0) {
                $userRow = $userResult->fetch_assoc();
                $receiverName = $userRow['name'];
            } else {
                $receiverName = "Unknown User"; 
            }

            $userStmt->close();
        } else {
            $receiverName = "Admin"; 
        }

        $conversations[] = [
            'chatSessionId' => $chatSessionId,
            'userTwo' => $userTwo,
            'updatedAt' => $row['updatedAt'],
            'receiverName' => $receiverName
        ];
    }
}

echo json_encode($conversations);

$stmt->close();
$conn->close();
?>
