<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['adminID'])) {
    http_response_code(400);
    echo json_encode(["message" => "adminID is required"]);
    exit;
}

$adminID = $data['adminID'];

$query = "DELETE FROM admin WHERE adminID = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Query preparation failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $adminID);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "User deleted successfully"]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No user found with the given adminID"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to execute query: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
