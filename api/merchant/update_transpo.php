<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Acheckllow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $transportationId = isset($data['transportationId']) ? intval($data['transportationId']) : null;

    if ($transportationId && isset($data['VehicleName'], $data['Model'], $data['Brand'], $data['Capacity'], $data['RentalPrice'])) {
        $VehicleName = $data['VehicleName'];
        $Model = $data['Model'];
        $Brand = $data['Brand'];
        $Capacity = $data['Capacity'];
        $RentalPrice = $data['RentalPrice'];

        // Update room details based on RoomID
        $updateQuery = "UPDATE transportations SET VehicleName = ?, Model = ?, Brand = ?, Capacity = ?, RentalPrice = ? WHERE RoomID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sssid", $VehicleName, $Model, $Brand, $Capacity, $RentalPrice, $transportationId);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Transportation updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update transportation']);
        }
        $updateStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data for transportation update']);
    }
}
$conn->close();
?>
