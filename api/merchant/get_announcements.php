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

    // Fetching announcements for the merchant
    // Modify the query to filter for announcements visible to merchants in general
    $stmt = $conn->prepare("SELECT announcementID, header, description, createdAt FROM announcements WHERE visibleTo IS NULL OR visibleTo = 'merchant' OR visibleTo = 'all'  ORDER BY createdAt DESC");

    $stmt->execute();
    $result = $stmt->get_result();

    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcement = [
            'announcementID' => $row['announcementID'],
            'header' => $row['header'],
            'description' => $row['description'],
            'createdAt' => $row['createdAt']
        ];

        $announcements[] = $announcement;
    }

    if (count($announcements) > 0) {
        echo json_encode(['success' => true, 'announcements' => $announcements]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No announcements found.']);
    }

    $stmt->close();
    $conn->close();

} catch (ExpiredException $e) {
    echo json_encode(['success' => false, 'message' => 'Token expired.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid token.']);
}
