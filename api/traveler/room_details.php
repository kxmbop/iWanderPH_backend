<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

$roomId = intval($_GET['id']);
error_log("Room ID received: " . $roomId);

try {
    // Fetch room details along with the first image from the gallery
    $query = "
        SELECT r.RoomName, r.RoomRate, r.GuestPerRoom, rg.ImageFile 
        FROM rooms r
        LEFT JOIN room_gallery rg ON r.RoomID = rg.RoomID
        WHERE r.RoomID = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error); // Log any errors
        echo json_encode(['error' => 'Query preparation failed']);
        exit;
    }

    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $roomDetails = $result->fetch_assoc();

    if (!$roomDetails) {
        echo json_encode(['error' => 'Room not found']);
        exit;
    }

    // Encode the ImageFile blob to Base64 if it exists
    if (!empty($roomDetails['ImageFile'])) {
        $roomDetails['ImageFile'] = base64_encode($roomDetails['ImageFile']);
    }

    // Fetch room inclusions
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
    $roomInclusions = $inclusionResult->fetch_all(MYSQLI_ASSOC);

    // Fetch room views
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
    $roomViews = $viewResult->fetch_all(MYSQLI_ASSOC);

    // Combine all data
    $roomDetails['inclusions'] = $roomInclusions;
    $roomDetails['view'] = $roomViews;

    // Output the final result as JSON
    echo json_encode($roomDetails);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage()); // Log more details on the exception
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
