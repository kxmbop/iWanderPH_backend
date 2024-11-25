<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

try {
    // Query to fetch all transportation records, including vehicles without images
    $query = "SELECT t.TransportationID, t.VehicleName, t.Model, t.Brand, t.Capacity, t.RentalPrice,
                     tg.ImageFile AS TransportationImage
              FROM transportations t
              LEFT JOIN transportation_gallery tg ON t.TransportationID = tg.TransportationID";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $transportations = [];
    $vehicleImages = [];

    while ($row = $result->fetch_assoc()) {

        $currentVehicleID = $row['TransportationID'];
        $vehicleName = $row['VehicleName'];
        $vehicleModel = $row['Model'];
        $vehicleBrand = $row['Brand'];
        $vehicleCapacity = $row['Capacity'];
        $vehicleRentalPrice = $row['RentalPrice'];
        $imageData = $row['TransportationImage'];

        $vehicleImages = [];

        if (!empty($imageData)) {
            $base64Image = base64_encode($imageData);
            $vehicleImages[] = $base64Image;
        }

        $transportations[] = [
            'TransportationID' => $currentVehicleID,
            'VehicleName' => $vehicleName,
            'Model' => $vehicleModel,
            'Brand' => $vehicleBrand,
            'Capacity' => $vehicleCapacity,
            'RentalPrice' => $vehicleRentalPrice,
            'TransportationImages' => $vehicleImages 
        ];
    }

    if (!empty($transportations)) {
        echo json_encode(['success' => true, 'data' => $transportations]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No transportation found']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
