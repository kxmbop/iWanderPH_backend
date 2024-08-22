<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['name'], $data['username'], $data['password'])) {
    $name = $data['name'];
    $username = $data['username'];
    $password = $data['password'];

    $stmt = $conn->prepare("INSERT INTO admin (name, username, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $username, $password);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Sign up successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
?>
