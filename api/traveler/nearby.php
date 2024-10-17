<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Get the place_id from the request parameters
$place_id = isset($_GET['place_id']) ? intval($_GET['place_id']) : 0; 

if (!$place_id) {
    echo json_encode(['error' => 'Place ID is required']);
    exit;
}

// Prepare and execute the query to get nearby merchants along with the lowest room rate
$sql = "SELECT 
            m.MerchantID, 
            m.BusinessName, 
            m.Address, 
            MIN(r.RoomRate) AS LowestRoomRate
        FROM 
            nearby n
        JOIN 
            merchant m ON n.Merchant_id = m.MerchantID
        LEFT JOIN 
            rooms r ON m.MerchantID = r.MerchantID
        WHERE 
            n.Place_id = ? AND m.isApproved = 1
        GROUP BY 
            m.MerchantID";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $place_id);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Query execution failed: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$nearby_merchants = [];

// Fetch the results
while ($row = $result->fetch_assoc()) {
    $nearby_merchants[] = $row;
}

if (count($nearby_merchants) === 0) {
    echo json_encode(['merchants' => [], 'message' => 'No nearby merchants found.']);
} else {
    echo json_encode(['merchants' => $nearby_merchants]);
}

$stmt->close();
$conn->close();

// Log the response
error_log('API response: ' . json_encode(['merchants' => $nearby_merchants]));
exit;
?>
