<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
$data = json_decode(file_get_contents("php://input"), true);

$firstName = $data['firstName'];
$lastName = $data['lastName'];
$bio = $data['bio'];
$taxID = $data['taxID'];
$username = $data['username'];
$password = password_hash($data['password'], PASSWORD_BCRYPT);

$query = "INSERT INTO support_agents (firstName, lastName, bio, taxID, username, password) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

$response = [];
if ($stmt) {
    $stmt->bind_param("ssssss", $firstName, $lastName, $bio, $taxID, $username, $password);
    if ($stmt->execute()) {
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Execution error: ' . $stmt->error;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Prepare error: ' . $conn->error;
}

echo json_encode($response);
$conn->close();
?>
