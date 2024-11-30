<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$placeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$response = array();

$sql = "SELECT place_name, description, region, province, full_address, island_group, main_image, map_embed_link, 
        best_time_to_visit, entrance_fee, activities, nearby_points_of_interest, how_to_get_there 
        FROM places WHERE id = $placeId";
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
//hello
$conn->close();
?>
