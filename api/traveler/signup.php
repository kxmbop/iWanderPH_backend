<?php

// Enable error reporting for debugging (optional, remove in production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
include 'encryption.php';

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

    // Insert traveler data (without TravelerUUID)
    $role_type = 'traveler';

    $sql = "INSERT INTO traveler (Mobile, FirstName, LastName, Address, ProfilePic, Bio, Email, Username, Password, isMerchant, isDeactivated, isSuspended, isBanned)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 0)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssssss", $gcashNumber, $firstName, $lastName, $address, $profilePic, $bio, $email, $username, $password);

        if ($stmt->execute()) {
            // Retrieve the last inserted traveler ID
            $travelerID = $stmt->insert_id;

            // Now, encrypt the TravelerUUID using the travelerID
            $encryptionKey = '123456';
            $text_to_encrypt = $travelerID . " - " . $username . " - " . $role_type;
            $travelerUUID = encrypt($text_to_encrypt, $encryptionKey);

            // Update TravelerUUID in the database
            $updateSql = "UPDATE traveler SET TravelerUUID = ? WHERE travelerID = ?";
            if ($updateStmt = $conn->prepare($updateSql)) {
                $updateStmt->bind_param("si", $travelerUUID, $travelerID);
                if ($updateStmt->execute()) {
                    echo json_encode(['success' => 'Signup successful.']);
                } else {
                    echo json_encode(['error' => 'Error: Could not update TravelerUUID.']);
                    error_log("Update TravelerUUID error: " . $conn->error);
                }
                $updateStmt->close();
            } else {
                echo json_encode(['error' => 'Error: Could not prepare the update query.']);
                error_log("Prepare update query error: " . $conn->error);
            }
        } else {
            echo json_encode(['error' => 'Error: Could not execute the insert query.']);
            error_log("Insert error: " . $conn->error);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error: Could not prepare the insert query.']);
        error_log("Prepare statement error: " . $conn->error);
    }

    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
