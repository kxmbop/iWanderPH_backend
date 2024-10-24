<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

include '../../db.php'; 
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(['success' => false, 'message' => 'No token provided.']);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader); 

$key = "123456"; 

try {
    $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));

    error_log(print_r($decoded, true));  

    if (isset($decoded->TravelerID)) {
        $travelerID = $decoded->TravelerID;  
    } else {
        echo json_encode(['success' => false, 'message' => 'TravelerID not found in token.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT MerchantID FROM merchant WHERE TravelerID = ?");
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $stmt->bind_result($merchantID);
    $stmt->fetch();
    $stmt->close();

    if (!$merchantID) {
        echo json_encode(['success' => false, 'message' => 'No merchant found for this traveler ID.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT notificationMessage, bookingID, createdAt FROM notifications WHERE userID = ? ORDER BY createdAt DESC");
    $stmt->bind_param("i", $merchantID);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notification = [
            'notificationMessage' => $row['notificationMessage'],
            'createdAt' => $row['createdAt']
        ];

        if (isset($row['bookingID'])) {
            $bookingID = $row['bookingID'];
            $bookingStmt = $conn->prepare("SELECT bookingStatus FROM booking WHERE bookingID = ?");
            $bookingStmt->bind_param("i", $bookingID);
            $bookingStmt->execute();
            $bookingResult = $bookingStmt->get_result();

            if ($bookingRow = $bookingResult->fetch_assoc()) {
                $notification['bookingStatus'] = $bookingRow['bookingStatus'];
            } else {
                $notification['bookingStatus'] = 'Unknown'; 
            }
            $bookingStmt->close();
        } else {
            $notification['bookingStatus'] = 'N/A'; 
        }

        $notifications[] = $notification;
    }

    if (count($notifications) > 0) {
        echo json_encode(['success' => true, 'notifications' => $notifications]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No notifications found.']);
    }

    $stmt->close();
    $conn->close();

} catch (ExpiredException $e) {
    echo json_encode(['success' => false, 'message' => 'Token expired.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid token.']);
}
