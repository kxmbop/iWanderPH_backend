<?php
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if (!isset($_GET['token'])) {
    echo json_encode(['success' => false, 'message' => 'Token is required']);
    exit();
}

$token = $_GET['token'];
$key = "123456"; // Secret key for decoding JWT

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    if (!isset($decoded->TravelerID)) {
        echo json_encode(['success' => false, 'message' => 'TravelerID not found in token.']);
        exit();
    }

    $travelerID = $decoded->TravelerID;
    $merchantQuery = "SELECT MerchantID FROM merchant WHERE TravelerID = ?";
    $merchantStmt = $conn->prepare($merchantQuery);
    $merchantStmt->bind_param("i", $travelerID);

    if ($merchantStmt->execute()) {
        $merchantResult = $merchantStmt->get_result();

        if ($merchantResult->num_rows > 0) {
            $merchant = $merchantResult->fetch_assoc();
            $merchantID = $merchant['MerchantID'];

            // GET: Fetch rooms
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $selectQuery = "SELECT RoomID, RoomName, RoomQuantity, GuestPerRoom, RoomRate FROM rooms WHERE MerchantID = ?";
                $selectStmt = $conn->prepare($selectQuery);
                $selectStmt->bind_param("i", $merchantID);
                $selectStmt->execute();
                $result = $selectStmt->get_result();

                $rooms = [];
                while ($row = $result->fetch_assoc()) {
                    $rooms[] = $row;
                }
                echo json_encode(['success' => true, 'data' => $rooms]);
                $selectStmt->close();
            }

            // POST: Add a room
            elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $data = json_decode(file_get_contents("php://input"), true);

                if (isset($data['RoomName'], $data['RoomQuantity'], $data['GuestPerRoom'], $data['RoomRate'])) {
                    $RoomName = $data['RoomName'];
                    $RoomQuantity = $data['RoomQuantity'];
                    $GuestPerRoom = $data['GuestPerRoom'];
                    $RoomRate = $data['RoomRate'];

                    $insertQuery = "INSERT INTO rooms (RoomName, RoomQuantity, GuestPerRoom, RoomRate, MerchantID) 
                                    VALUES (?, ?, ?, ?, ?)";
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bind_param("siidi", $RoomName, $RoomQuantity, $GuestPerRoom, $RoomRate, $merchantID);

                    if ($insertStmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Room added successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add room']);
                    }
                    $insertStmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Missing required room data']);
                }
            }

           // DELETE: Remove a room
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Read data from DELETE request body
    $data = json_decode(file_get_contents("php://input"), true);
    $roomID = isset($data['RoomID']) ? intval($data['RoomID']) : null;

    if ($roomID) {
        $deleteQuery = "DELETE FROM rooms WHERE RoomID = ? AND MerchantID = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("ii", $roomID, $merchantID);

        if ($deleteStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Room deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete room']);
        }
        $deleteStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid RoomID']);
    }
}

        } else {
            echo json_encode(['success' => false, 'message' => 'Merchant not found']);
        }
        $merchantStmt->close();
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
}
$conn->close();
