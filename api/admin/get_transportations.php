<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['merchantID'])) {
    $merchantID = $_GET['merchantID'];

    // Step 1: Get all transportations for the given MerchantID
    $sql = "SELECT TransportationID, VehicleName, Model, Brand, Capacity, RentalPrice FROM transportations WHERE MerchantID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $merchantID);
    $stmt->execute();
    $result = $stmt->get_result();

    $transportations = [];
    while ($transportation = $result->fetch_assoc()) {
        $transportationID = $transportation['TransportationID'];

        // Step 2: Get transportation images from transportation_gallery
        $images = [];
        $imageSql = "SELECT ImageFile FROM transportation_gallery WHERE TransportationID = ?";
        $imageStmt = $conn->prepare($imageSql);
        $imageStmt->bind_param("i", $transportationID);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        while ($imageRow = $imageResult->fetch_assoc()) {
            $images[] = base64_encode($imageRow['ImageFile']);
        }
        $transportation['images'] = $images;

        // Add the transportation to the transportations array
        $transportations[] = $transportation;
    }

    echo json_encode($transportations);
    $stmt->close();
}
?>
