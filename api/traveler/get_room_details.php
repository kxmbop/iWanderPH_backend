<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../../db.php';

$roomId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$response = array();

if ($roomId > 0) {
    $sql = "SELECT RoomName, RoomRate, GuestPerRoom FROM rooms WHERE RoomID = $roomId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();

        // Fetch gallery images
        $gallery_sql = "SELECT ImageFile FROM room_gallery WHERE RoomID = $roomId";
        $gallery_result = $conn->query($gallery_sql);
        $gallery = array();
        while ($image = $gallery_result->fetch_assoc()) {
            $gallery[] = base64_encode($image['ImageFile']);
        }
        $room['gallery'] = $gallery;

        // Fetch inclusions
        $inclusions_sql = "SELECT InclusionName FROM inclusions 
                           INNER JOIN room_inclusions ON inclusions.InclusionID = room_inclusions.InclusionID 
                           WHERE room_inclusions.RoomID = $roomId";
        $inclusions_result = $conn->query($inclusions_sql);
        $inclusions = array();
        while ($inclusion = $inclusions_result->fetch_assoc()) {
            $inclusions[] = $inclusion['InclusionName'];
        }
        $room['inclusions'] = $inclusions;

        // Fetch views
        $views_sql = "SELECT ViewName FROM views 
                      INNER JOIN room_view ON views.ViewID = room_view.ViewID 
                      WHERE room_view.RoomID = $roomId";
        $views_result = $conn->query($views_sql);
        $views = array();
        while ($view = $views_result->fetch_assoc()) {
            $views[] = $view['ViewName'];
        }
        $room['views'] = $views;

        $response['room'] = $room;
    } else {
        $response['error'] = 'Room not found';
    }
} else {
    $response['error'] = 'Invalid Room ID';
}

echo json_encode($response);
?>
