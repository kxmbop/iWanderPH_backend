<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

if (isset($_FILES['profilePicture']) && isset($_POST['admin_id'])) {
    $adminId = $_POST['admin_id'];
    $file = $_FILES['profilePicture'];

    // Define the upload path and filename
    $uploadDir = 'uploads/profilePictures/';
    $filePath = $uploadDir . basename($file['name']);

    // Move the file to the upload directory
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // SQL query to update the profile picture path
        $sql = "UPDATE admin SET profilePicture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $filePath, $adminId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Profile picture updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update profile picture."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn->close();
?>
