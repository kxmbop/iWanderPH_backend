<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$headers = getallheaders();

try {
    // Correctly retrieve RoomID from POST data
    $RoomID = isset($_POST['RoomID']) ? (int)$_POST['RoomID'] : null;

    // Check if RoomID is null or 0
    if ($RoomID === null || $RoomID === 0) {
        echo json_encode(['success' => false, 'message' => 'RoomID is required']);
        exit();
    }

    $roomCheckQuery = "SELECT * FROM rooms WHERE RoomID = ?";
    $roomCheckStmt = $conn->prepare($roomCheckQuery);
    $roomCheckStmt->bind_param("i", $RoomID);
    $roomCheckStmt->execute();
    $roomCheckResult = $roomCheckStmt->get_result();

    if ($roomCheckResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Room not found or does not belong to the merchant']);
        exit();
    }

    $deleteGalleryQuery = "DELETE FROM room_gallery WHERE RoomID = ?";
    $deleteGalleryStmt = $conn->prepare($deleteGalleryQuery);
    $deleteGalleryStmt->bind_param("i", $RoomID);
    $deleteGalleryStmt->execute();

    $deleteViewQuery = "DELETE FROM room_view WHERE RoomID = ?";
    $deleteViewStmt = $conn->prepare($deleteViewQuery);
    $deleteViewStmt->bind_param("i", $RoomID);
    $deleteViewStmt->execute();

    $deleteInclusionsQuery = "DELETE FROM room_inclusions WHERE RoomID = ?";
    $deleteInclusionsStmt = $conn->prepare($deleteInclusionsQuery);
    $deleteInclusionsStmt->bind_param("i", $RoomID);
    $deleteInclusionsStmt->execute();

    $deleteRoomQuery = "DELETE FROM rooms WHERE RoomID = ?";
    $deleteRoomStmt = $conn->prepare($deleteRoomQuery);
    $deleteRoomStmt->bind_param("i", $RoomID);
    
    if ($deleteRoomStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Room deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete room']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>