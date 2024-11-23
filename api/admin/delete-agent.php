<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; // Database connection

parse_str(file_get_contents("php://input"), $_DELETE); // To handle DELETE request data
$adminID = $_DELETE['adminID'] ?? null;

if (!$adminID) {
    http_response_code(400);
    echo json_encode(["error" => "Admin ID not provided"]);
    exit;
}

// Ensure the admin being deleted is not a Super Admin
$sql = "SELECT adminUserType FROM admin WHERE adminID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['adminUserType'] === 'SuperAdmin') {
        http_response_code(403);
        echo json_encode(["error" => "Cannot delete Super Admin"]);
        exit;
    }
}

// Proceed with deletion for non-Super Admin
$sql = "DELETE FROM admin WHERE adminID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminID);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Admin deleted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to delete admin", "details" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
