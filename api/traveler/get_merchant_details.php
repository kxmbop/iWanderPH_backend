<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../db.php';

$merchantId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = array();

if ($merchantId === 0) {
    echo json_encode(array('error' => 'Invalid merchant ID'));
    exit();
}

$sql_merchant = "SELECT merchantId, BusinessName, Email, Contact, Address, profilePicture FROM merchant WHERE MerchantID = $merchantId";
$result_merchant = $conn->query($sql_merchant);

if ($result_merchant && $result_merchant->num_rows > 0) {
    $merchantData = $result_merchant->fetch_assoc();
    
    if ($merchantData['profilePicture']) {
        $merchantData['profilePicture'] = base64_encode($merchantData['profilePicture']);
    }

    $response['merchant'] = $merchantData;
} else {
    $response['merchant'] = null; 
}

$sql_rooms = "
    SELECT 
        r.RoomID, 
        r.RoomName, 
        r.RoomRate, 
        r.RoomQuantity - 
        IFNULL((SELECT COUNT(*) 
                FROM room_booking rb
                JOIN booking b 
                  ON rb.RoomBookingID = b.roomBookingID
                WHERE rb.RoomID = r.RoomID 
                  AND b.bookingStatus NOT IN ('Completed', 'Cancelled', 'Refunded')), 0) AS AvailableQuantity,
        r.RoomQuantity,
        r.GuestPerRoom
    FROM rooms r
    WHERE r.MerchantID = $merchantId;
";

$result_rooms = $conn->query($sql_rooms);

$rooms = array();
if ($result_rooms && $result_rooms->num_rows > 0) {
    while ($room = $result_rooms->fetch_assoc()) {
        $roomId = $room['RoomID'];

        // Fetch gallery images
        $sql_gallery = "SELECT ImageFile FROM room_gallery WHERE RoomID = $roomId";
        $result_gallery = $conn->query($sql_gallery);
        $gallery = array();
        if ($result_gallery && $result_gallery->num_rows > 0) {
            while ($image = $result_gallery->fetch_assoc()) {
                $gallery[] = base64_encode($image['ImageFile']);
            }
        }
        $room['gallery'] = $gallery;

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
        $room['inclusions'] = $inclusions;

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
        $room['views'] = $views;

        $rooms[] = $room;
    }
}
$response['rooms'] = $rooms;

$sql_transportations = "SELECT TransportationID, VehicleName, Model, Brand, Capacity, RentalPrice FROM transportations WHERE MerchantID = $merchantId";
$result_transportations = $conn->query($sql_transportations);

$transportations = array();
if ($result_transportations && $result_transportations->num_rows > 0) {
    while ($transport = $result_transportations->fetch_assoc()) {
        $transportId = $transport['TransportationID'];

        $sql_gallery_transport = "SELECT ImageFile FROM transportation_gallery WHERE TransportationID = $transportId";
        $result_gallery_transport = $conn->query($sql_gallery_transport);
        $transport_gallery = array();
        if ($result_gallery_transport && $result_gallery_transport->num_rows > 0) {
            while ($image = $result_gallery_transport->fetch_assoc()) {
                $transport_gallery[] = base64_encode($image['ImageFile']);
            }
        }
        $transport['gallery'] = $transport_gallery;
        $transportations[] = $transport;
    }
}
$response['transportations'] = $transportations;

echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
