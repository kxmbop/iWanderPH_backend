<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';  // Ensure your Composer autoload is included
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';  // Your database connection

$key = "123456";  // Secret key for JWT validation

// Function to decode and verify JWT token
function validateToken($token, $key) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded;  // Return decoded data if token is valid
    } catch (Exception $e) {
        return null;  // Return null if token is invalid or expired
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the token from POST data
    $token = $_POST['token'] ?? '';

    if (!$token) {
        echo json_encode(['error' => 'Token not provided']);
        exit();
    }

    // Validate the token
    $decoded = validateToken($token, $key);
    
    if ($decoded === null) {
        echo json_encode(['error' => 'Invalid or expired token']);
        exit();
    }

    // Get admin details from the POST request
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $adminUserType = $_POST['adminUserType'] ?? '';
    $idNo = $_POST['taxID'] ?? '';

    // Check if the profile picture is uploaded
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $profilePicture = $_FILES['profilePicture'];
        
        // Validate the uploaded file (you can extend this validation)
        $allowedTypes = ['image/jpeg', 'image/png'];
        if (!in_array($profilePicture['type'], $allowedTypes)) {
            echo json_encode(['error' => 'Invalid file type. Only JPEG and PNG are allowed.']);
            exit();
        }

        // Move the uploaded file to a directory on your server (e.g., /uploads/admin/).
        $uploadDir = '../../uploads/admin/';
        $uploadFilePath = $uploadDir . basename($profilePicture['name']);

        if (move_uploaded_file($profilePicture['tmp_name'], $uploadFilePath)) {
            // Prepare the query to update the admin profile in the database
            $adminID = $decoded->adminID;  // Assuming adminID is part of the token payload

            // Update the admin details along with the profile picture in the database
            $sql = "UPDATE admin SET firstName = ?, lastName = ?, adminUserType = ?, taxID = ?, profilePicture = ? WHERE adminID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $firstName, $lastName, $adminUserType, $taxID, $uploadFilePath, $adminID);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Profile updated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to update profile']);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['error' => 'Failed to upload the file']);
        }
    } else {
        echo json_encode(['error' => 'No profile picture uploaded']);
    }

    $conn->close();
} else {
    // GET request to retrieve profile picture
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get the token from GET data
        $token = $_GET['token'] ?? '';

        if (!$token) {
            echo json_encode(['error' => 'Token not provided']);
            exit();
        }

        // Validate the token
        $decoded = validateToken($token, $key);
        
        if ($decoded === null) {
            echo json_encode(['error' => 'Invalid or expired token']);
            exit();
        }

        // Retrieve the admin's profile picture from the database
        $adminID = $decoded->adminID;  // Assuming adminID is part of the token payload
        $sql = "SELECT profilePicture FROM admin WHERE adminID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $adminID);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($profilePicture);

        if ($stmt->fetch()) {
            if ($profilePicture && file_exists($profilePicture)) {
                // If the file exists, return the file as base64-encoded Blob
                $imageData = base64_encode(file_get_contents($profilePicture));
                echo json_encode(['profilePicture' => $imageData]);
            } else {
                echo json_encode(['error' => 'Profile picture not found']);
            }
        } else {
            echo json_encode(['error' => 'Admin not found']);
        }

        $stmt->close();
        $conn->close();
    }
}
?>
