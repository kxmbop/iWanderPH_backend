<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = $conn->real_escape_string($_POST['country']);
    $city_state = $conn->real_escape_string($_POST['city_state']);
    $postal_code = $conn->real_escape_string($_POST['postal_code']);
    $tax_id = $conn->real_escape_string($_POST['tax_id']);
    $admin_id = 13; // Example Admin ID, replace as necessary

    $sql = "UPDATE admin SET country='$country', city_state='$city_state', postal_code='$postal_code', tax_id='$tax_id' WHERE AdminID=$admin_id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Address updated successfully."]);
    } else {
        echo json_encode(["error" => "Error updating address: " . $conn->error]);
    }

    $conn->close();
}
?>
