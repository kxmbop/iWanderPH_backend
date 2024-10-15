<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

include '../encryption.php';
include '../../../db.php'; 

$response = [];
$key = "123456"; 

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;
        $role = $decoded->role;

        if ($role !== 'traveler') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
            exit;
        }

        // Retrieve traveler UUID
        $getTravelerUUID = "SELECT travelerUUID FROM traveler WHERE travelerId = ?";
        $stmt = $conn->prepare($getTravelerUUID);
        $stmt->bind_param("s", $travelerID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $travelerUUID = $row['travelerUUID'];
        } else {
            echo json_encode(['success' => false, 'error' => 'Traveler UUID not found']);
            exit;
        }

        // Fetch chat sessions involving the traveler
        $query = "SELECT cs.* 
                  FROM chat_session cs 
                  WHERE cs.userOne = ? OR cs.userTwo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $travelerUUID, $travelerUUID);
        $stmt->execute();
        $result = $stmt->get_result();

        // Prepare to fetch details for travelers, merchants, and admins
        $userDetailsQuery = "
            SELECT t.travelerId AS userid, t.travelerUUID AS userUUID, CONCAT(t.firstname, ' ', t.lastname) AS name, t.username, NULL AS BusinessType
            FROM traveler t
            UNION ALL
            SELECT m.merchantId AS userid, m.merchantUUID AS userUUID, m.businessName AS username, m.businessName AS name, m.BusinessType
            FROM merchant m
            UNION ALL
            SELECT a.adminID AS userid, a.adminUUID AS userUUID, NULL AS name, 'Customer Support' AS username, NULL AS BusinessType
            FROM admin a";
        
        $stmt = $conn->prepare($userDetailsQuery);
        $stmt->execute();
        $userDetailsResult = $stmt->get_result();

        $userDetails = [];
        while ($row = $userDetailsResult->fetch_assoc()) {
            $userDetails[$row['userid']] = [
                'userid' => $row['userid'],
                'userUUID' => $row['userUUID'],
                'name' => $row['name'],
                'username' => $row['username'] ?? null,
                'BusinessType' => $row['BusinessType'] ?? null
            ];
        }

        // Construct chat session details with user information
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $chatSessionId = $row['chatSessionId'];

            $userOneEncrypted = $row['userOne'];
            $userTwoEncrypted = $row['userTwo'];

            // Decrypt UUIDs
            $userOneDecrypted = decrypt($userOneEncrypted, $key);
            $userTwoDecrypted = decrypt($userTwoEncrypted, $key);

            // Extract User IDs from decrypted UUIDs
            $userOneParts = explode(' - ', $userOneDecrypted);
            $userTwoParts = explode(' - ', $userTwoDecrypted);

            $userOneId = $userOneParts[0];
            $userTwoId = $userTwoParts[0];

            // Fetch details for both users
            $userOneDetails = $userDetails[$userOneId] ?? null;
            $userTwoDetails = $userDetails[$userTwoId] ?? null;

            $conversations[] = [
                'chatSessionId' => $chatSessionId,
                'userOneDetails' => $userOneDetails,
                'userTwoDetails' => $userTwoDetails
            ];
        }

        echo json_encode($conversations); 

        $stmt->close();
        $conn->close();

    } catch (ExpiredException $e) {
        $response["success"] = false;
        $response["message"] = "Token expired: " . $e->getMessage();
        echo json_encode($response);
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = "Invalid token: " . $e->getMessage();
        echo json_encode($response);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Authorization token is missing']);
    exit;
}
?>
