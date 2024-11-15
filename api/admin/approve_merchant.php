<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['merchantID'])) {
    $merchantID = $data['merchantID'];

    $sql = "UPDATE merchant SET isApproved = 1 WHERE merchantID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $merchantID);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Merchant approved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to approve merchant']);
    }

    $stmt->close();
}
?>
