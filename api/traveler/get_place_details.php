<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$placeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$response = array();

// Fetch place details
$sql = "SELECT place_name, description, region, province, full_address, island_group, main_image FROM places WHERE id = $placeId";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $response['place'] = $result->fetch_assoc();
    $response['place']['main_image'] = base64_encode($response['place']['main_image']);
}

// Fetch additional images
// Fetch additional images
$sql_images = "SELECT images FROM place_images WHERE place_id = $placeId";
$result_images = $conn->query($sql_images);

$images = array();
if ($result_images->num_rows > 0) {
    while ($row = $result_images->fetch_assoc()) {
        // Assuming the images column stores binary data, encode them as base64
        $images[] = base64_encode($row['images']); 
    }
}

$response['images'] = $images;

echo json_encode($response);

$conn->close();
?>
