<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placeId = $data['placeId'] ?? null;
    $merchantId = $data['merchantId'] ?? null;

    if (!$placeId || !$merchantId) {
        http_response_code(400);
        echo json_encode(["message" => "Place ID and Merchant ID are required"]);
        exit;
    }

    // Unassign merchant
    $sql = "DELETE FROM nearby WHERE Place_id = $placeId AND Merchant_id = $merchantId";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Merchant unassigned successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error unassigning merchant: " . $conn->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
