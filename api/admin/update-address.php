<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

// Receive data from the frontend (admin ID and updated address details)
$data = json_decode(file_get_contents("php://input"), true);
$adminID = $data['adminID']; // Assuming admin_id is passed
$address = $data['address'];
$cityState = $data['cityState'];
$postalCode = $data['postalCode'];

// SQL query to update address
$sql = "UPDATE admin SET address = ?, cityState = ?, postalCode = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $address, $cityState, $postalCode, $adminID);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Address updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update address."]);
}

$stmt->close();
$conn->close();