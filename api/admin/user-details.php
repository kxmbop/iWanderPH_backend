<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if (isset($_GET['user_id'])) {
    $travelerId = intval($_GET['user_id']);
    
    $query = "SELECT * FROM traveler WHERE TravelerID = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $travelerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $traveler = $result->fetch_assoc();
            echo json_encode($traveler);
        } else {
            echo json_encode(['error' => 'Traveler not found']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare statement']);
    }
} else {
    echo json_encode(['error' => 'Traveler ID is required']);
}

$conn->close();
?>
