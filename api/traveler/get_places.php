<?php
session_start(); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Retrieve filter parameters from the request
$island_group = isset($_GET['island_group']) ? $_GET['island_group'] : '';
$region = isset($_GET['region']) ? $_GET['region'] : '';
$province = isset($_GET['province']) ? $_GET['province'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the base SQL query
$sql = "SELECT id, place_name, main_image, region, province, full_address FROM places WHERE 1=1";
$params = [];
$types = "";

// Filter by island group if provided and not "All"
if (!empty($island_group) && $island_group != 'All') {
    $sql .= " AND island_group = ?";
    $params[] = $island_group;
    $types .= "s";
}

// Filter by region if provided
if (!empty($region)) {
    $sql .= " AND region = ?";
    $params[] = $region;
    $types .= "s";
}

// Filter by province if provided
if (!empty($province)) {
    $sql .= " AND province = ?";
    $params[] = $province;
    $types .= "s";
}

// Apply search filter if provided
if (!empty($search)) {
    $sql .= " AND (place_name LIKE ? OR region LIKE ? OR province LIKE ? OR full_address LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= "ssss";
}

$stmt = $conn->prepare($sql);

// Bind parameters dynamically based on the filters provided
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$places = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Encode the main_image as base64 for display
        $row['main_image'] = base64_encode($row['main_image']);
        $places[] = $row;
    }
}

// Return the JSON response
echo json_encode($places);

// Close the database connection
$stmt->close();
$conn->close();
?>
