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

            // Additional GET requests based on action type
            elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
                $roomID = isset($_GET['roomId']) ? intval($_GET['roomId']) : null;
                
                if ($_GET['action'] == 'getRoomView' && $roomID) {
                    $viewQuery = "SELECT ViewName FROM room_views WHERE RoomID = ?";
                    $viewStmt = $conn->prepare($viewQuery);
                    $viewStmt->bind_param("i", $roomID);
                    $viewStmt->execute();
                    $viewResult = $viewStmt->get_result();
                    $views = [];
                    while ($viewRow = $viewResult->fetch_assoc()) {
                        $views[] = $viewRow;
                    }

                    // Ensure the data is returned as an array
                    echo json_encode(['success' => true, 'data' => (empty($views) ? [] : $views)]);
                    $viewStmt->close();
                } elseif ($_GET['action'] == 'getRoomInclusions' && $roomID) {
                    $inclusionQuery = "SELECT InclusionName, Description FROM room_inclusions WHERE RoomID = ?";
                    $inclusionStmt = $conn->prepare($inclusionQuery);
                    $inclusionStmt->bind_param("i", $roomID);
                    $inclusionStmt->execute();
                    $inclusionResult = $inclusionStmt->get_result();
                    $inclusions = [];
                    while ($inclusionRow = $inclusionResult->fetch_assoc()) {
                        $inclusions[] = $inclusionRow;
                    }

                    // Ensure the data is returned as an array
                    echo json_encode(['success' => true, 'data' => (empty($inclusions) ? [] : $inclusions)]);
                    $inclusionStmt->close();
                } elseif ($_GET['action'] == 'getRoomGallery' && $roomID) {
                    $galleryQuery = "SELECT ImageFile FROM room_gallery WHERE RoomID = ?";
                    $galleryStmt = $conn->prepare($galleryQuery);
                    $galleryStmt->bind_param("i", $roomID);
                    $galleryStmt->execute();
                    $galleryResult = $galleryStmt->get_result();
                    $gallery = [];
                    while ($galleryRow = $galleryResult->fetch_assoc()) {
                        $gallery[] = $galleryRow;
                    }

                    // Ensure the data is returned as an array
                    echo json_encode(['success' => true, 'data' => (empty($gallery) ? [] : $gallery)]);
                    $galleryStmt->close();
                }
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
?>
