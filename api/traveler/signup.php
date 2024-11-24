<?php

// Enable error reporting for debugging (optional, remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Include database connection and encryption functions
include '../../db.php';
include 'encryption.php';

// Check database connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gcashNumber = isset($_POST['gcashNumber']) ? $_POST['gcashNumber'] : null;
    $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : null;
    $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    $bio = isset($_POST['bio']) ? $_POST['bio'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : null;

    $profilePic = null;
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == UPLOAD_ERR_OK) {
        $profilePic = file_get_contents($_FILES['profilePic']['tmp_name']); 
    } else {
        echo json_encode(['error' => 'Profile picture upload failed or no file uploaded.']);
        exit;
    }

    if (!$password || !$confirmPassword || $password !== $confirmPassword) {
        echo json_encode(['error' => 'Passwords do not match or are empty.']);
        exit;
    }

    $encryptionKey = '123456'; 
    $travelerUUID = encrypt(uniqid(), $encryptionKey);

    // $role_type = 'traveler';
    // $encryptionKey = '123456'; 
    // $text_to_encrypt = $traveler_id . " - " . $username . " - " . $role_type; 
    // $travelerUUID = encrypt($text_to_encrypt, $encryptionKey);

    $sql = "INSERT INTO traveler (TravelerUUID, Mobile, FirstName, LastName, Address, ProfilePic, Bio, Email, Username, Password, isMerchant, isDeactivated, isSuspended, isBanned)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 0)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssssss", $travelerUUID, $gcashNumber, $firstName, $lastName, $address, $profilePic, $bio, $email, $username, $password);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Signup successful.']);
        } else {
            echo json_encode(['error' => 'Error: Could not execute the query.']);
            error_log("Database error: " . $conn->error); 
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error: Could not prepare the query.']);
        error_log("Prepare statement error: " . $conn->error); 
    }

    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}


?>