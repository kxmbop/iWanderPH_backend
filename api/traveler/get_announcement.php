<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';  // Include your database connection

$response = [];

try {
    // Prepare SQL query to fetch announcements visible to 'Traveler' only
    $query = "SELECT header, description, createdAt 
              FROM announcements 
              WHERE visibleTo = 'Traveler' 
              ORDER BY createdAt DESC";
    
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        $response['error'] = 'Failed to retrieve announcements';
        echo json_encode($response);
        exit;
    }

    $announcements = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $announcements[] = [
            'header' => $row['header'],
            'description' => $row['description'],
            'createdAt' => $row['createdAt']
        ];
    }

    // Send the response with the announcements
    $response['announcements'] = $announcements;
    echo json_encode($response);

} catch (Exception $e) {
    $response['error'] = 'An error occurred while processing the request';
    echo json_encode($response);
    exit;
}

// Close the database connection
mysqli_close($conn);
?>
