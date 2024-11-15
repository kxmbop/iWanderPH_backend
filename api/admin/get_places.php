<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT id, place_name, description, region, province, full_address, island_group, 
                   TO_BASE64(main_image) as main_image 
            FROM places";
    $result = $conn->query($sql);

    $places = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $place_id = $row['id'];

            // Fetch images for the current place
            $images_sql = "SELECT TO_BASE64(images) as images FROM place_images WHERE place_id = $place_id";
            $images_result = $conn->query($images_sql);

            $images = [];
            if ($images_result->num_rows > 0) {
                while ($image_row = $images_result->fetch_assoc()) {
                    $images[] = $image_row['images'];
                }
            }

            $row['images'] = $images; // Add images to the current place
            $places[] = $row;
        }
    }

    echo json_encode($places);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
