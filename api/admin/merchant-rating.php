<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Query to fetch merchant details (merchantID and businessName)
$sql = "SELECT m.merchantID, m.businessName
        FROM merchant m
        ORDER BY m.merchantID ASC";

// Query to fetch merchant ratings
$sqlRatings = "SELECT m.merchantID, AVG(r.reviewRating) AS reviewRating
               FROM merchant m
               LEFT JOIN booking b ON b.merchantID = m.merchantID
               LEFT JOIN reviews r ON r.bookingID = b.bookingID
               GROUP BY m.merchantID";

// Execute merchant details query
$merchantResult = $conn->query($sql);

// Execute ratings query
$ratingsResult = $conn->query($sqlRatings);

// Initialize an array to store merchant data
$merchantData = array();

// Fetch merchant details
if ($merchantResult->num_rows > 0) {
    while ($row = $merchantResult->fetch_assoc()) {
        $merchantData[$row['merchantID']] = array(
            'merchantID' => $row['merchantID'],
            'businessName' => $row['businessName']
        );
    }
}

// Fetch ratings and combine with merchant data
if ($ratingsResult->num_rows > 0) {
    while ($row = $ratingsResult->fetch_assoc()) {
        if (isset($merchantData[$row['merchantID']])) {
            // Add review rating to the existing merchant data
            $merchantData[$row['merchantID']]['reviewRating'] = number_format($row['reviewRating'], 2);
        }
    }
}

// Prepare response
echo json_encode(array('merchantRatings' => array_values($merchantData)));

// Close connection
$conn->close();
?>
