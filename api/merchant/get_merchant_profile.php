<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$input = json_decode(file_get_contents("php://input"));
$token = $input->token ?? ''; 
$key = "123456";

if (!$token) {
    echo json_encode(['error' => 'Token not provided']);
    exit();
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;

    $sql = 'SELECT * FROM merchant WHERE travelerID = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $merchantProfile = $result->fetch_assoc();
        
        if (isset($merchantProfile['profilePicture']) && !empty($merchantProfile['profilePicture'])) {
            $merchantProfile['profilePicture'] = base64_encode($merchantProfile['profilePicture']);
        } else {
            $merchantProfile['profilePicture'] = null; 
        }
        
        echo json_encode(['profile' => $merchantProfile]);
    } else {
        echo json_encode(['error' => 'Merchant not found']);
    }

    $stmt->close();
} catch (ExpiredException $e) {
    echo json_encode(['error' => 'Token expired']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid token']);
}

$conn->close();
?>