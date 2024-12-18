<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php';

$response = [];

$success = null;  // Define default value for $success
$message = null;  // Define default value for $message

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['username'], $data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $stmt = $conn->prepare("SELECT adminID, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($adminID, $storedPassword);
        $stmt->fetch();

        if ($password === $storedPassword) {
            $key = "123456"; 
            $payload = [
                'iat' => time(), 
                'role' => 'admin',
                'adminID' => $adminID,
                'username' => $username
            ];
            $algorithm = 'HS256';
            $jwt = JWT::encode($payload, $key, $algorithm);
            
            $_SESSION['token'] = $jwt;

            $success = true;
            $message = "Login successful.";
            echo json_encode(["success" => $success, "message" => $message, "token" => $jwt]);
            exit();

        } else {
            $success = false;
            $message = 'Invalid username or password';
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
    } else {
        $success = false;
        $message = 'Invalid username or password';
        echo json_encode(['status' => 'error', 'message' => $message]);
    }

} else {
    $success = false;
    $message = 'Invalid input';
    echo json_encode(['status' => 'error', 'message' => $message]);
}

$conn->close();

// Ensure $success and $message are included in all responses
echo json_encode(["success" => $success, "message" => $message]);
?>
