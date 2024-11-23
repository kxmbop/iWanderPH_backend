<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// SQL query to get the booking trends
$sql = "SELECT 
        DATE_FORMAT(b.bookingDate, '%M %Y') AS monthYear,
        b.bookingID, 
        m.businessName AS merchantName, 
        b.bookingStatus, 
        (b.subtotal - b.payoutAmount) AS totalRevenue
    FROM booking b
    JOIN merchant m ON b.merchantID = m.merchantID
    WHERE b.bookingDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    AND b.bookingStatus = 'completed'
    ORDER BY b.bookingDate ASC"; 

$result = $conn->query($sql);

$payments = [];
$labels = [];
$data = [];

// Check if there are results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = [
            'month' => $row['monthYear'],
            'bookingID' => $row['bookingID'],
            'merchantName' => $row['merchantName'],
            'bookingStatus' => $row['bookingStatus'],
            'totalRevenue' => (float)$row['totalRevenue']
        ];

        // Prepare labels and data for the chart
        $labels[] = $row['monthYear'];
        $data[] = (float)$row['totalRevenue'];
    }
}

// Log the output before sending it back
error_log(json_encode(['labels' => $labels, 'data' => $data]));

$response = [
    'payments' => [
        'details' => $payments,
        'labels' => array_unique($labels), // Ensure unique labels for charting
        'data' => $data
    ]
];

echo json_encode($response);

// Close the connection
$conn->close();
?>
