<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Acheckllow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $roomId = isset($data['RoomID']) ? intval($data['RoomID']) : null;

    if ($roomId && isset($data['RoomName'], $data['RoomQuantity'], $data['GuestPerRoom'], $data['RoomRate'])) {
        $RoomName = $data['RoomName'];
        $RoomQuantity = $data['RoomQuantity'];
        $GuestPerRoom = $data['GuestPerRoom'];
        $RoomRate = $data['RoomRate'];

        // Update room details based on RoomID
        $updateQuery = "UPDATE rooms SET RoomName = ?, RoomQuantity = ?, GuestPerRoom = ?, RoomRate = ? WHERE RoomID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("siidi", $RoomName, $RoomQuantity, $GuestPerRoom, $RoomRate, $roomId);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Room updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update room']);
        }
        $updateStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data for room update']);
    }
}
$conn->close();
?>
