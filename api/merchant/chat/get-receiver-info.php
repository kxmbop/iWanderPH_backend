<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php'; 
require '../../../vendor/autoload.php';  
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = '123456';  

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if (isset($_GET['chatSessionId']) && isset($_GET['token'])) {
    $chatSessionId = intval($_GET['chatSessionId']);
    $token = $_GET['token'];

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;
    } catch (Exception $e) {
        echo json_encode(['error' => 'Invalid or expired token']);
        exit();
    }

    $query = "SELECT merchantUUID FROM merchant WHERE travelerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $merchant = $result->fetch_assoc();
        $merchantUUID = $merchant['merchantUUID'];
    } else {
        echo json_encode(['error' => 'Merchant UUID not found']);
        exit();
    }

    $query = "SELECT userOne, userTwo FROM chat_session WHERE chatSessionId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $chatSessionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $session = $result->fetch_assoc();
        $userOneUUID = $session['userOne'];
        $userTwoUUID = $session['userTwo'];

        $receiverUUID = null;
        $receiverRole = null;

        if ($merchantUUID === $userOneUUID) {
            $receiverUUID = $userTwoUUID;
        } elseif ($merchantUUID === $userTwoUUID) {
            $receiverUUID = $userOneUUID;
        }

        if (!$receiverUUID) {
            echo json_encode(['error' => 'Merchant is not part of the chat session']);
            exit();
        }

        // Check receiver role and fetch receiver info
        $query = "SELECT TravelerID, TravelerUUID, Username, FirstName, LastName FROM traveler WHERE TravelerUUID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $receiverUUID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $receiverInfo = $result->fetch_assoc();
            echo json_encode(['receiver' => $receiverInfo]);
        } else {
            echo json_encode(['error' => 'Receiver not found']);
        }

    } else {
        echo json_encode(['error' => 'Chat session not found']);
    }

    $stmt->close();

} else {
    echo json_encode(['error' => 'Chat Session ID and Token are required']);
}

$conn->close();
?>
