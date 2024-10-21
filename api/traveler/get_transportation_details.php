<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../../db.php';

$transportId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$response = array();

if ($transportId > 0) {
    $sql = "SELECT VehicleName, Model, Brand, Capacity, RentalPrice, DriverName, DriverContactNo 
            FROM transportations WHERE TransportationID = $transportId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $transportation = $result->fetch_assoc();

        // Fetch gallery images
        $gallery_sql = "SELECT ImageFile FROM transportation_gallery WHERE TransportationID = $transportId";
        $gallery_result = $conn->query($gallery_sql);
        $gallery = array();
        while ($image = $gallery_result->fetch_assoc()) {
            $gallery[] = base64_encode($image['ImageFile']);
        }
        $transportation['gallery'] = $gallery;

        $response['transportation'] = $transportation;
    } else {
        $response['error'] = 'Transportation not found';
    }
} else {
    $response['error'] = 'Invalid Transportation ID';
}

echo json_encode($response);
?>
