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

        $query = "SELECT cs.* 
                  FROM chat_session cs 
                  WHERE cs.userOne = ? OR cs.userTwo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $travelerUUID, $travelerUUID);
        $stmt->execute();
        $result = $stmt->get_result();

        $userDetailsQuery = "SELECT 
                                t.travelerId, t.travelerUUID, CONCAT(t.firstname, ' ', t.lastname) AS name, t.username,
                                m.merchantId, m.merchantUUID, m.businessName AS name, m.BusinessType
                            FROM 
                                traveler t 
                            LEFT JOIN 
                                merchant m ON t.travelerId = m.merchantId";
        $stmt = $conn->prepare($userDetailsQuery);
        $stmt->execute();
        $userDetailsResult = $stmt->get_result();

        $userDetails = [];
        while ($row = $userDetailsResult->fetch_assoc()) {
            if ($row['travelerId']) {
                $userDetails[$row['travelerId']] = [
                    'userid' => $row['travelerId'],
                    'userUUId' => $row['travelerUUID'],
                    'name' => $row['name'],
                    'username' => $row['username']
                ];
            } elseif ($row['merchantId']) {
                $userDetails[$row['merchantId']] = [
                    'userid' => $row['merchantId'],
                    'userUUId' => $row['merchantUUID'],
                    'name' => $row['name'],
                    'BusinessType' => $row['BusinessType']
                ];
            }
        }

        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $chatSessionId = $row['chatSessionId'];

            $userOneEncrypted = $row['userOne'];
            $userTwoEncrypted = $row['userTwo'];

            $userOneDecrypted = decrypt($userOneEncrypted, $key);
            $userTwoDecrypted = decrypt($userTwoEncrypted, $key);

            $userOneParts = explode(' - ', $userOneDecrypted);
            $userTwoParts = explode(' - ', $userTwoDecrypted);

            $userOneId = $userOneParts[0];
            $userTwoId = $userTwoParts[0];

            $userOneDetails = $userDetails[$userOneId];
            $userTwoDetails = $userDetails[$userTwoId];

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
        $response["message"] = "Invalid token : " . $e->getMessage();
        echo json_encode($response);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Authorization token is missing']);
    exit;
}
?>