<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Transportation ID is required']);
    exit;
}

$transportationId = intval($_GET['id']);
error_log("Transportation ID received: " . $transportationId);

try {
    // SQL query to fetch transportation details
    $query = "
        SELECT t.VehicleName, t.Model, t.Brand, t.Capacity, t.RentalPrice, t.DriverName, t.DriverContactNo, tg.ImageFile
        FROM transportations t
        LEFT JOIN transportation_gallery tg ON t.TransportationID = tg.TransportationID
        WHERE t.TransportationID = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transportationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $transportationDetails = $result->fetch_assoc();

    if (!$transportationDetails) {
        echo json_encode(['error' => 'Transportation not found']);
        exit;
    }

    // Encode image as Base64 if it exists
    if (!empty($transportationDetails['ImageFile'])) {
        $transportationDetails['ImageFile'] = 'data:image/jpeg;base64,' . base64_encode($transportationDetails['ImageFile']);
    }

    // Output the JSON result
    echo json_encode($transportationDetails);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
