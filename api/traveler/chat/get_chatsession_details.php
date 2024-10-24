<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../../db.php'; 
require '../../../vendor/autoload.php';
include '../encryption.php';

$chatSessionId = isset($_GET['chatsessionId']) ? intval($_GET['chatsessionId']) : null;

if ($chatSessionId === null) {
    echo json_encode(['success' => false, 'error' => 'Chat session ID is required.']);
    exit;
}

$query = "SELECT userOne, userTwo FROM chat_session WHERE chatSessionId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $chatSessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Decrypt the user IDs
    $userOneEncrypted = $row['userOne'];
    $userTwoEncrypted = $row['userTwo'];

    $key = "123456"; 
    $userOneDecrypted = decrypt($userOneEncrypted, $key);
    $userTwoDecrypted = decrypt($userTwoEncrypted, $key);

    $userOneParts = explode(' - ', $userOneDecrypted);
    $userTwoParts = explode(' - ', $userTwoDecrypted);

    $userOneId = $userOneParts[0];
    $userOneTableName = $userOneParts[2];
    $userTwoId = $userTwoParts[0];
    $userTwoTableName = $userTwoParts[2];

    $userOneDetails = [];
    $userTwoDetails = [];

    if ($userOneTableName == 'traveler') {
        $query = "SELECT travelerId AS userid, travelerUUID as userUUId, CONCAT(firstname, ' ', lastname) AS name, username FROM traveler WHERE travelerId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userOneId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userOneDetails = $result->fetch_assoc();

        $userOneDetails['userid'] = intval($userOneDetails['userid']);
    } elseif ($userOneTableName == 'merchant') {
        $query = "SELECT merchantId AS userid, merchantUUID as userUUId, businessName AS name FROM merchant WHERE merchantId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userOneId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userOneDetails = $result->fetch_assoc();

        $userOneDetails['userid'] = intval($userOneDetails['userid']);
    } elseif ($userOneTableName == 'admin') {
        $query = "SELECT adminId AS userid FROM admin WHERE adminId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userOneId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userOneDetails = $result->fetch_assoc();

        $userOneDetails['userid'] = intval($userOneDetails['userid']);
    }


    if ($userTwoTableName == 'traveler') {
        $query = "SELECT travelerId AS userid, travelerUUID as userUUId, CONCAT(firstname, ' ', lastname) AS name, username FROM traveler WHERE travelerId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userTwoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userTwoDetails = $result->fetch_assoc();

        $userTwoDetails['userid'] = intval($userTwoDetails['userid']);
    } elseif ($userTwoTableName == 'merchant') {
        $query = "SELECT merchantId AS userid, merchantUUID as userUUId, businessName AS name FROM merchant WHERE merchantId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userTwoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userTwoDetails = $result->fetch_assoc();

        $userTwoDetails['userid'] = intval($userTwoDetails['userid']);
    } elseif ($userTwoTableName == 'admin') {
        $query = "SELECT adminId AS userid FROM admin WHERE adminId = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $userTwoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userTwoDetails = $result->fetch_assoc();

        $userTwoDetails['userid'] = intval($userTwoDetails['userid']);
    }

    // Return the JSON response
    echo json_encode([
        'success' => true,
        'userOneDetails' => $userOneDetails,
        'userTwoDetails' => $userTwoDetails
    ]);
} else {
    echo json_encode(['error' => 'Chat session not found.']);
}

$stmt->close();
$conn->close();
?>
