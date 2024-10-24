<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "123456";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';

    file_put_contents('php://stderr', print_r($_POST, true)); 
    file_put_contents('php://stderr', print_r($_FILES, true)); 

    if (!$token) {
        echo json_encode(['error' => 'Token not provided']);
        exit();
    }

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        // Retrieve other data from POST
        $businessType = $_POST['businessType'] ?? '';
        $email = $_POST['email'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $address = $_POST['address'] ?? '';

        // Handle file upload if applicable
        if (isset($_FILES['merchantPic']) && $_FILES['merchantPic']['error'] == UPLOAD_ERR_OK) {
            $imgData = file_get_contents($_FILES['merchantPic']['tmp_name']);

            $sql = 'UPDATE merchant SET businessType = ?, email = ?, contact = ?, address = ?, profilePicture = ? WHERE travelerID = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $businessType, $email, $contact, $address, $imgData, $travelerID);
        } else {
            $sql = 'UPDATE merchant SET businessType = ?, email = ?, contact = ?, address = ? WHERE travelerID = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $businessType, $email, $contact, $address, $travelerID);
        }

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => 'Profile updated successfully']);
        } else {
            echo json_encode(['error' => 'No changes made or Merchant not found']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>
