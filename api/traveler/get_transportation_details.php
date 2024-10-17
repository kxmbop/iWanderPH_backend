<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; // Ensure the path is correct

$transportId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = array();

if ($transportId === 0) {
    echo json_encode(array('error' => 'Invalid Transportation ID'));
    exit();
}

// Fetch transportation details
$sql_transport = "SELECT VehicleName, Model, Brand, Capacity, RentalPrice FROM transportations WHERE TransportationID = $transportId";
$result_transport = $conn->query($sql_transport);

if ($result_transport && $result_transport->num_rows > 0) {
    $response['transportation'] = $result_transport->fetch_assoc();
} else {
    $response['transportation'] = null;
}

// Fetch transportation gallery
$sql_gallery = "SELECT ImageFile FROM transportation_gallery WHERE TransportationID = $transportId";
$result_gallery = $conn->query($sql_gallery);
$gallery = array();
if ($result_gallery && $result_gallery->num_rows > 0) {
    while ($image = $result_gallery->fetch_assoc()) {
        $gallery[] = base64_encode($image['ImageFile']);
    }
}
$response['transportation']['gallery'] = $gallery;

// Output the JSON response
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
