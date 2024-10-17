<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$roomId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = array();

if ($roomId === 0) {
    echo json_encode(array('error' => 'Invalid Room ID'));
    exit();
}

// Fetch room details
$sql_room = "SELECT RoomName, RoomRate, GuestPerRoom FROM rooms WHERE RoomID = $roomId";
$result_room = $conn->query($sql_room);

if ($result_room && $result_room->num_rows > 0) {
    $response['room'] = $result_room->fetch_assoc();

    // Fetch room gallery
    $sql_gallery = "SELECT ImageFile FROM room_gallery WHERE RoomID = $roomId";
    $result_gallery = $conn->query($sql_gallery);
    $gallery = array();
    if ($result_gallery && $result_gallery->num_rows > 0) {
        while ($image = $result_gallery->fetch_assoc()) {
            $gallery[] = base64_encode($image['ImageFile']);
        }
    }
    $response['room']['gallery'] = $gallery;

    // Fetch inclusions
    $sql_inclusions = "SELECT InclusionName FROM inclusions 
                       INNER JOIN room_inclusions ON inclusions.InclusionID = room_inclusions.InclusionID 
                       WHERE room_inclusions.RoomID = $roomId";
    $result_inclusions = $conn->query($sql_inclusions);
    $inclusions = array();
    if ($result_inclusions && $result_inclusions->num_rows > 0) {
        while ($inclusion = $result_inclusions->fetch_assoc()) {
            $inclusions[] = $inclusion['InclusionName'];
        }
    }
    $response['room']['inclusions'] = $inclusions;

    // Fetch views
    $sql_views = "SELECT ViewName FROM views 
                  INNER JOIN room_view ON views.ViewID = room_view.ViewID 
                  WHERE room_view.RoomID = $roomId";
    $result_views = $conn->query($sql_views);
    $views = array();
    if ($result_views && $result_views->num_rows > 0) {
        while ($view = $result_views->fetch_assoc()) {
            $views[] = $view['ViewName'];
        }
    }
    $response['room']['views'] = $views;
} else {
    $response['room'] = null;
}

echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//hi
$conn->close();
?>
