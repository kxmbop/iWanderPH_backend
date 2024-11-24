<?php
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow these headers
header("Access-Control-Allow-Credentials: true");
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

    // Check if the merchant is already assigned to the place
    $checkSql = "SELECT * FROM nearby WHERE Place_id = $placeId AND Merchant_id = $merchantId";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Merchant is already assigned to this place"]);
        exit;
    }

    // Assign merchant
    $sql = "INSERT INTO nearby (Place_id, Merchant_id) VALUES ($placeId, $merchantId)";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Merchant assigned successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error assigning merchant: " . $conn->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
