<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$island_group = isset($_GET['island_group']) ? $_GET['island_group'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL query with filtering and search functionality
$sql = "SELECT id, place_name, main_image FROM places WHERE 1=1";  // Make sure to select 'id'

if (!empty($island_group) && $island_group != 'All') {
    $sql .= " AND island_group = '$island_group'";
}

if (!empty($search)) {
    $sql .= " AND place_name LIKE '%$search%'";
}

$result = $conn->query($sql);

$places = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Encode the main_image as base64 to display as an image
        $row['main_image'] = base64_encode($row['main_image']);
        $places[] = $row;
    }
}

echo json_encode($places);

$conn->close();
//hello
?>
