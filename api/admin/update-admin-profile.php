<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; // Ensure this file has the correct path

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit(0);  // End the preflight response early
}

// Check if data is posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the required fields are set
    if (isset($_POST['firstName']) && isset($_POST['lastName']) && isset($_POST['adminUserType']) && isset($_POST['taxID']) && isset($_POST['adminID'])) {

        // Assign posted data to variables
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $adminUserType = $_POST['adminUserType'];
        $taxID = $_POST['taxID'];
        $adminID = $_POST['adminID'];

        // Profile picture handling
        $profilePictureNewName = null;
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === 0) {
            $profilePicture = $_FILES['profilePicture'];
            $profilePictureName = $profilePicture['name'];
            $profilePictureTmpName = $profilePicture['tmp_name'];
            $profilePictureNewName = uniqid('', true) . '.' . pathinfo($profilePictureName, PATHINFO_EXTENSION);
            $profilePictureDestination = '../../uploads/profile_pictures/' . $profilePictureNewName;

            // Move the uploaded file
            if (!move_uploaded_file($profilePictureTmpName, $profilePictureDestination)) {
                $profilePictureNewName = null;
                error_log("Failed to upload profile picture.");
            }
        }

        // Prepare the SQL query based on whether profile picture is provided
        if ($profilePictureNewName) {
            $sql = "UPDATE admin SET firstName = ?, lastName = ?, adminUserType = ?, taxID = ?, profilePicture = ? WHERE adminID = ?";
            $params = [$firstName, $lastName, $adminUserType, $taxID, $profilePictureNewName, $adminID];
            $param_types = "sssssi";
        } else {
            $sql = "UPDATE admin SET firstName = ?, lastName = ?, adminUserType = ?, taxID = ? WHERE adminID = ?";
            $params = [$firstName, $lastName, $adminUserType, $taxID, $adminID];
            $param_types = "ssssi";
        }

        // Prepare the statement
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(["error" => "Failed to prepare SQL query", "details" => $conn->error]);
            exit;
        }

        // Bind parameters
        $stmt->bind_param($param_types, ...$params);

        // Execute SQL query and check for success
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
        } else {
            http_response_code(500);
            error_log("SQL Execution Error: " . $stmt->error);
            echo json_encode(["error" => "Failed to execute SQL query", "details" => $stmt->error]);
        }

        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
    }

} else {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?>
