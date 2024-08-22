<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['username'], $data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($storedPassword);
        $stmt->fetch();

        if ($password === $storedPassword) {
            echo json_encode(['status' => 'success', 'message' => 'Login successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
?>
