<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

$sql = "
    SELECT 
        t.TravelerID as travelerId,
        t.Username as username,
        b.BookingID as bookingId,
        b.TotalAmount as totalAmount
    FROM bookings b
    JOIN traveler t ON b.TravelerID = t.TravelerID
";

$result = $conn->query($sql);

$travelerData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $travelerId = $row['travelerId'];
        $bookingId = $row['bookingId'];
        $totalAmount = $row['totalAmount'];

        // Initialize traveler data if not set
        if (!isset($travelerData[$travelerId])) {
            $travelerData[$travelerId] = [
                'username' => $row['username'],
                'totalAmount' => 0,
                'bookings' => []
            ];
        }

        // Accumulate total amount and list booking IDs
        $travelerData[$travelerId]['totalAmount'] += $totalAmount;
        $travelerData[$travelerId]['bookings'][] = $bookingId;
    }
}

// Prepare the final output
$output = [];
foreach ($travelerData as $travelerId => $data) {
    $output[] = [
        'travelerId' => $travelerId,
        'username' => $data['username'],
        'totalAmount' => $data['totalAmount'],
        'bookings' => $data['bookings']
    ];
}

echo json_encode($output);
$conn->close();
?>
