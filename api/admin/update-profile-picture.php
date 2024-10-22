<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = 13; // Example Admin ID, replace as necessary

    // Handle file upload for profile picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/"; // Directory where images will be saved
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            echo json_encode(["error" => "File is not an image."]);
            $uploadOk = 0;
        }

        // Check file size (5MB max)
        if ($_FILES["profile_picture"]["size"] > 5000000) {
            echo json_encode(["error" => "Sorry, your file is too large."]);
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            echo json_encode(["error" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed."]);
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo json_encode(["error" => "Sorry, your file was not uploaded."]);
        } else {
            // If everything is ok, try to upload file
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Update the profile picture path in the database
                $sql_picture = "UPDATE admin SET profile_picture='$target_file' WHERE AdminID=$admin_id";

                if ($conn->query($sql_picture) === TRUE) {
                    echo json_encode(["message" => "Profile picture updated successfully."]);
                } else {
                    echo json_encode(["error" => "Error updating profile picture: " . $conn->error]);
                }
            } else {
                echo json_encode(["error" => "Sorry, there was an error uploading your file."]);
            }
        }
    } else {
        echo json_encode(["error" => "No file uploaded or there was an upload error."]);
    }

    $conn->close();
}
?>
