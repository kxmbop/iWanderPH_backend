<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $place_name = $_POST['place_name'];
    $description = $_POST['description'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $full_address = $_POST['full_address'];
    $island_group = $_POST['island_group'];
    $main_image = isset($_FILES['main_image']) ? file_get_contents($_FILES['main_image']['tmp_name']) : null;

    $sql = "INSERT INTO places (place_name, description, region, province, full_address, island_group, main_image)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $place_name, $description, $region, $province, $full_address, $island_group, $main_image);
    if ($stmt->execute()) {
        $place_id = $conn->insert_id;

        if (isset($_FILES['additional_images'])) {
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                $image = file_get_contents($tmp_name);
                $sql_image = "INSERT INTO place_images (place_id, images) VALUES (?, ?)";
                $stmt_image = $conn->prepare($sql_image);
                $stmt_image->bind_param("is", $place_id, $image);
                $stmt_image->execute();
            }
        }

        echo json_encode(["message" => "Place added successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to add place"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
