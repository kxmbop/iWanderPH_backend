<?php
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
    exit();
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(['success' => false, 'message' => 'Authorization token is required']);
    exit();
}

$token = str_replace('Bearer ', '', $headers['Authorization']);
$key = "123456";

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    if (!isset($decoded->TravelerID)) {
        echo json_encode(['success' => false, 'message' => 'TravelerID not found in token']);
        exit();
    }

    $travelerID = $decoded->TravelerID;

    $merchantQuery = "SELECT MerchantID FROM merchant WHERE TravelerID = ?";
    $merchantStmt = $conn->prepare($merchantQuery);
    $merchantStmt->bind_param("i", $travelerID);
    $merchantStmt->execute();
    $merchantResult = $merchantStmt->get_result();

    if ($merchantResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Merchant not found for this TravelerID']);
        exit();
    }

    $merchantID = $merchantResult->fetch_assoc()['MerchantID'];

    $RoomID = (int) $_POST['RoomID'];
    $RoomName = $_POST['RoomName'];
    $RoomQuantity = (int) $_POST['RoomQuantity'];
    $GuestPerRoom = (int) $_POST['GuestPerRoom'];
    $RoomRate = (float) $_POST['RoomRate'];
    $Views = json_decode($_POST['Views'], true);
    $Inclusions = json_decode($_POST['Inclusions'], true);

    // Update room details
    $roomUpdateQuery = "UPDATE rooms SET RoomName = ?, RoomQuantity = ?, RoomRate = ?, GuestPerRoom = ? WHERE RoomID = ? AND MerchantID = ?";
    $roomStmt = $conn->prepare($roomUpdateQuery);
    $roomStmt->bind_param("sidiis", $RoomName, $RoomQuantity, $RoomRate, $GuestPerRoom, $RoomID, $merchantID);

    if ($roomStmt->execute()) {
        // Update gallery images
        if (!empty($_FILES['ImageFile']['tmp_name'])) {
            $deleteGalleryQuery = "DELETE FROM room_gallery WHERE RoomID = ?";
            $deleteGalleryStmt = $conn->prepare($deleteGalleryQuery);
            $deleteGalleryStmt->bind_param("i", $RoomID);
            $deleteGalleryStmt->execute();

            foreach ($_FILES['ImageFile']['tmp_name'] as $key => $tmpName) {
                if (is_uploaded_file($tmpName)) {
                    $imageData = file_get_contents($tmpName);

                    $galleryQuery = "INSERT INTO room_gallery (ImageFile, RoomID) VALUES (?, ?)";
                    $galleryStmt = $conn->prepare($galleryQuery);
                    $galleryStmt->bind_param("si", $imageData, $RoomID);
                    $galleryStmt->execute();
                }
            }
        }

        // Update inclusions
        $deleteInclusionsQuery = "DELETE FROM room_inclusions WHERE RoomID = ?";
        $deleteInclusionsStmt = $conn->prepare($deleteInclusionsQuery);
        $deleteInclusionsStmt->bind_param("i", $RoomID);
        $deleteInclusionsStmt->execute();

        foreach ($Inclusions as $InclusionID) {
            $inclusionQuery = "INSERT INTO room_inclusions (InclusionID, RoomID) VALUES (?, ?)";
            $inclusionStmt = $conn->prepare($inclusionQuery);
            $inclusionStmt->bind_param("ii", $InclusionID, $RoomID);
            $inclusionStmt->execute();
        }

        // Update views
        $deleteViewsQuery = "DELETE FROM room_view WHERE RoomID = ?";
        $deleteViewsStmt = $conn->prepare($deleteViewsQuery);
        $deleteViewsStmt->bind_param("i", $RoomID);
        $deleteViewsStmt->execute();

        foreach ($Views as $ViewID) {
            $viewQuery = "INSERT INTO room_view (ViewID, RoomID) VALUES (?, ?)";
            $viewStmt = $conn->prepare($viewQuery);
            $viewStmt->bind_param("ii", $ViewID, $RoomID);
            $viewStmt->execute();
        }

        echo json_encode(['success' => true, 'message' => 'Room updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update room']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Token decoding failed: ' . $e->getMessage()]);
}
?>