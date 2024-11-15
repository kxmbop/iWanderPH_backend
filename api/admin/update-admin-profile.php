<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; // Make sure this file has the correct path

// Check if data is posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ensure the required fields are set
    if (isset($_POST['firstName']) && isset($_POST['lastName']) && isset($_POST['adminUserType']) && isset($_POST['taxID']) && isset($_POST['adminID'])) {

        // Process the profile picture if it exists, or skip if it's empty
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === 0) {
            $profilePicture = $_FILES['profilePicture'];
            $profilePictureName = $profilePicture['name'];
            $profilePictureTmpName = $profilePicture['tmp_name'];
            $profilePictureError = $profilePicture['error'];

            // Check for errors with the uploaded file
            if ($profilePictureError === 0) {
                // Process the profile picture as usual
                $profilePictureNewName = uniqid('', true) . '.' . pathinfo($profilePictureName, PATHINFO_EXTENSION);
                $profilePictureDestination = '../../uploads/profile_pictures/' . $profilePictureNewName;

                if (move_uploaded_file($profilePictureTmpName, $profilePictureDestination)) {
                    // File upload successful
                    // No need to do anything else, as $profilePictureNewName is already set
                } else {
                    // Handle upload error
                    $profilePictureNewName = null; // No new picture uploaded
                }
            } else {
                // No file uploaded or error occurred
                $profilePictureNewName = null;
            }
        } else {
            // No profile picture provided, leave it as null
            $profilePictureNewName = null;
        }

        // Prepare the SQL query based on whether profile picture is provided
        if ($profilePictureNewName) {
            $sql = "UPDATE admin SET firstName = ?, lastName = ?, adminUserType = ?, taxID = ?, profilePicture = ? WHERE adminID = ?";
        } else {
            $sql = "UPDATE admin SET firstName = ?, lastName = ?, adminUserType = ?, taxID = ? WHERE adminID = ?";
        }

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Check if the statement was prepared successfully
        if ($stmt === false) {
            echo json_encode(["error" => "Failed to prepare SQL query"]);
            exit;
        }

        // Bind parameters based on the SQL query
        if ($profilePictureNewName) {
            $stmt->bind_param("sssssi", $firstName, $lastName, $adminUserType, $taxID, $profilePictureNewName, $adminID);
        } else {
            $stmt->bind_param("ssssi", $firstName, $lastName, $adminUserType, $taxID, $adminID);
        }

        // Execute SQL query and check for success
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
        } else {
            echo json_encode(["error" => "Failed to execute SQL query"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Missing required fields"]);
    }

}

$conn->close();
?>
