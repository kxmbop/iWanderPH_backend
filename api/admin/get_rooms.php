<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['merchantID'])) {
    $merchantID = $_GET['merchantID'];

    // Step 1: Get all rooms for the given MerchantID
    $sql = "SELECT RoomID, RoomName, RoomQuantity, RoomRate, GuestPerRoom FROM rooms WHERE MerchantID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $merchantID);
    $stmt->execute();
    $result = $stmt->get_result();

    $rooms = [];
    while ($room = $result->fetch_assoc()) {
        $roomID = $room['RoomID'];

        // Step 2: Get room images from room_gallery
        $images = [];
        $imageSql = "SELECT ImageFile FROM room_gallery WHERE RoomID = ?";
        $imageStmt = $conn->prepare($imageSql);
        $imageStmt->bind_param("i", $roomID);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        while ($imageRow = $imageResult->fetch_assoc()) {
            $images[] = base64_encode($imageRow['ImageFile']);
        }
        $room['images'] = $images;

        // Step 3: Get room views from room_view and views tables
        $views = [];
        $viewSql = "SELECT ViewName FROM views v JOIN room_view rv ON v.ViewID = rv.ViewID WHERE rv.RoomID = ?";
        $viewStmt = $conn->prepare($viewSql);
        $viewStmt->bind_param("i", $roomID);
        $viewStmt->execute();
        $viewResult = $viewStmt->get_result();
        while ($viewRow = $viewResult->fetch_assoc()) {
            $views[] = $viewRow['ViewName'];
        }
        $room['views'] = $views;

        // Step 4: Get room inclusions from room_inclusions and inclusions tables
        $inclusions = [];
        $inclusionSql = "SELECT InclusionName FROM inclusions i JOIN room_inclusions ri ON i.InclusionID = ri.InclusionID WHERE ri.RoomID = ?";
        $inclusionStmt = $conn->prepare($inclusionSql);
        $inclusionStmt->bind_param("i", $roomID);
        $inclusionStmt->execute();
        $inclusionResult = $inclusionStmt->get_result();
        while ($inclusionRow = $inclusionResult->fetch_assoc()) {
            $inclusions[] = $inclusionRow['InclusionName'];
        }
        $room['inclusions'] = $inclusions;

        // Add the room to the rooms array
        $rooms[] = $room;
    }

    echo json_encode($rooms);
    $stmt->close();
}
?>
