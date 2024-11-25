<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {

    $query = "SELECT RoomID, RoomName, RoomQuantity, GuestPerRoom, RoomRate 
              FROM rooms";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No rooms found']);
        exit();
    }


    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $roomID = $row['RoomID'];

        $inclusionsQuery = "SELECT i.InclusionName, i.InclusionDescription
                            FROM room_inclusions ri
                            INNER JOIN inclusions i ON ri.InclusionID = i.InclusionID
                            WHERE ri.RoomID = ?";
        $inclusionsStmt = $conn->prepare($inclusionsQuery);
        $inclusionsStmt->bind_param("i", $roomID);
        $inclusionsStmt->execute();
        $inclusionsResult = $inclusionsStmt->get_result();
        $inclusions = [];
        if ($inclusionsResult->num_rows > 0) {
            while ($inclusion = $inclusionsResult->fetch_assoc()) {
                $inclusions[] = $inclusion;
            }
        }

        $viewsQuery = "SELECT v.ViewName
                       FROM room_view rv
                       INNER JOIN views v ON rv.ViewID = v.ViewID
                       WHERE rv.RoomID = ?";
        $viewsStmt = $conn->prepare($viewsQuery);
        $viewsStmt->bind_param("i", $roomID);
        $viewsStmt->execute();
        $viewsResult = $viewsStmt->get_result();
        $views = [];
        while ($view = $viewsResult->fetch_assoc()) {
            $views[] = $view['ViewName'];
        }

        $galleryQuery = "SELECT ImageFile
            FROM room_gallery
            WHERE RoomID = ?";
            $galleryStmt = $conn->prepare($galleryQuery);
            $galleryStmt->bind_param("i", $roomID);
            $galleryStmt->execute();
            $galleryResult = $galleryStmt->get_result();
            $gallery = [];

            while ($image = $galleryResult->fetch_assoc()) {
            $imageData = $image['ImageFile'];
            $imageFile = base64_encode($imageData);

            $gallery[] = $imageFile; 
        }

        $row['Inclusions'] = $inclusions;
        $row['Views'] = $views;
        $row['Gallery'] = $gallery;

        $rooms[] = $row;
    }

    if (!empty($rooms)) {
        echo json_encode(['success' => true, 'data' => $rooms]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No rooms found']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
