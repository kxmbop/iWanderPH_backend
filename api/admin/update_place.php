<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve POST data
    $placeId = $_POST['place_id'];
    $placeName = $_POST['place_name'];
    $description = $_POST['description'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $fullAddress = $_POST['full_address'];
    $islandGroup = $_POST['island_group'];
    $best_time_to_visit = $_POST['best_time_to_visit'];
    $entrance_fee = $_POST['entrance_fee'];
    $activities = $_POST['activities'];
    $nearby_points_of_interest = $_POST['nearby_points_of_interest'];
    $map_embed_link = $_POST['map_embed_link'];
    $how_to_get_there = $_POST['how_to_get_there'];

    // Prepare the SQL query to update place information
    $sql = "UPDATE places SET 
                place_name = ?, 
                description = ?, 
                region = ?, 
                province = ?, 
                full_address = ?, 
                island_group = ?,
                best_time_to_visit =?,
                entrance_fee=?,
                activities=?,
                nearby_points_of_interest=?,
                map_embed_link=?,
                how_to_get_there=?

            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssi", $placeName, $description, $region, $province, $fullAddress, $islandGroup, $best_time_to_visit, $entrance_fee, $activities,$nearby_points_of_interest, $map_embed_link, $how_to_get_there, $placeId);

    if ($stmt->execute()) {
        // Handle main image file upload if present
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
            $mainImage = $_FILES['main_image']['tmp_name'];
            // Code to upload the image to the server and update database goes here...
        }

        // Handle additional images if present
        if (isset($_FILES['additional_images'])) {
            foreach ($_FILES['additional_images']['tmp_name'] as $index => $additionalImage) {
                // Code to upload the additional image and update database goes here...
            }
        }

        echo json_encode(['message' => 'Place updated successfully']);
    } else {
        echo json_encode(['message' => 'Error updating place']);
    }
}
?>
