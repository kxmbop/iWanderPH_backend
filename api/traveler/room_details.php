<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; // Use your MySQLi connection

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

$roomId = intval($_GET['id']);

try {
    // Prepare the SQL query to fetch room details
    $query = "
        SELECT r.RoomName, r.RoomRate, r.GuestPerRoom, rg.ImageFile 
        FROM rooms r
        LEFT JOIN room_gallery rg ON r.RoomID = rg.RoomID
        WHERE r.RoomID = ?
    ";

    // Use a prepared statement with MySQLi
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $roomDetails = $result->fetch_assoc();

    if (!$roomDetails) {
        echo json_encode(['error' => 'Room not found']);
        exit;
    }

    // Fetch inclusions for the room
    $inclusionQuery = "
        SELECT i.InclusionName 
        FROM room_inclusions ri
        INNER JOIN inclusions i ON ri.InclusionID = i.InclusionID
        WHERE ri.RoomID = ?
    ";

    $inclusionStmt = $conn->prepare($inclusionQuery);
    $inclusionStmt->bind_param('i', $roomId);
    $inclusionStmt->execute();
    $inclusionResult = $inclusionStmt->get_result();
    $roomDetails['inclusions'] = $inclusionResult->fetch_all(MYSQLI_ASSOC);

    // Fetch room view details
    $viewQuery = "
        SELECT v.ViewName 
        FROM room_view rv
        INNER JOIN views v ON rv.ViewID = v.ViewID
        WHERE rv.RoomID = ?
    ";

    $viewStmt = $conn->prepare($viewQuery);
    $viewStmt->bind_param('i', $roomId);
    $viewStmt->execute();
    $viewResult = $viewStmt->get_result();
    $roomDetails['view'] = $viewResult->fetch_assoc();

    // Return room details as JSON
    echo json_encode($roomDetails);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
