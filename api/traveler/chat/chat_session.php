<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../../../db.php'; 
include '../encryption.php';
require '../../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

$response = [];
$key = "123456";

$sender = $_GET['sender'] ?? null; 
$receiverUUID = $_GET['receiver'] ?? null; 

$encrypted_text = $receiverUUID;
$receiver = decrypt($encrypted_text, $key);
$receiverParts = explode(' - ', $receiver );
$receiverId = $receiverParts[0];
$receiverRole = $receiverParts[2];

$get_senderUUIDquery = "SELECT travelerUUID FROM traveler WHERE travelerId = ? LIMIT 1"; // Limit to 1 result
$get_senderUUIDstmt = $conn->prepare($get_senderUUIDquery);
$get_senderUUIDstmt->bind_param("i", $sender);
$get_senderUUIDstmt->execute();
$get_senderUUIDresult = $get_senderUUIDstmt->get_result();

if ($get_senderUUIDresult->num_rows > 0) {
    $row = $get_senderUUIDresult->fetch_assoc(); 
    $senderUUID = $row['travelerUUID']; 
} else {
    $response['error'] = 'No traveler found with the given travelerId.';
    echo json_encode($response);
    exit;
}

$senderRole = 'traveler';
$chatType = '';
if ($senderRole === 'traveler' && $receiverRole === 'traveler') {
    $chatType = 'user2user';
} elseif ($senderRole === 'traveler' && $receiverRole === 'merchant') {
    $chatType = 'user&merchant';
} elseif ($senderRole === 'traveler' && $receiverRole === 'admin') {
    $chatType = 'user&admin';
} elseif ($senderRole === 'merchant' && $receiverRole === 'traveler') {
    $chatType = 'user&merchant';
} elseif ($senderRole === 'admin' && $receiverRole === 'traveler') {
    $chatType = 'user&admin';
} else {
    $response['error'] = 'Invalid chat type.';
    echo json_encode($response);
    exit;
}


$checkSessionQuery = "SELECT * FROM chat_session WHERE (userOne = ? AND userTwo = ?) OR (userOne = ? AND userTwo = ?)";
$checkSessionStmt = $conn->prepare($checkSessionQuery);
$checkSessionStmt->bind_param('ssss', $senderUUID, $receiverUUID, $receiverUUID, $senderUUID);
$checkSessionStmt->execute();

$existingSession = $checkSessionStmt->get_result()->fetch_assoc();

if (!$existingSession) {
    $insertSessionQuery = "INSERT INTO chat_session (chatType, userOne, userTwo, isActivated, createdAt, updatedAt) VALUES (?, ?, ?, true, NOW(), NOW())";
    $insertSessionStmt = $conn->prepare($insertSessionQuery);
    $insertSessionStmt->bind_param('sss', $chatType, $senderUUID, $receiverUUID);
    $isActivated = 1; 
    $insertSessionStmt->execute();

    $response['message'] = 'Chat session created successfully.';
    $response['chatSessionId'] = $conn->insert_id;
    //PRINT THE CHAT_SESSION OF THE chatSessionId
} else {
    $response['message'] = 'Chat session already exists.';
    $response['chatSessionId'] = $existingSession['chatSessionId'];
    $response['chatSessionDetails'] = $existingSession;
}

echo json_encode($response);
?>