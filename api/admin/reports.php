<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$reportID = isset($_GET['reportID']) ? (int)$_GET['reportID'] : 0; // Optional filter by reportID

// Debugging the reportID
error_log("reportID: " . $reportID);

$reportQuery = "
    SELECT 
        r.reportID AS reportID,
        r.reviewID AS reviewID,
        r.violation AS violation,
        r.reportedBy AS reportedBy,
        r.reportMessage AS reportMessage,
        r.reportDate AS reportDate,
        r.status AS status
    FROM reports r
    WHERE (? = 0 OR r.reportID = ?)";
$stmt = $conn->prepare($reportQuery);
$stmt->bind_param("ii", $reportID, $reportID);

$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = [
        "reportID" => $row['reportID'],
        "reviewID" => $row['reviewID'],
        "violation" => $row['violation'],
        "reportedBy" => [
            "travelerID" => $row['reportedBy']
        ],
        "reportMessage" => $row['reportMessage'],
        "reportDate" => $row['reportDate'],
        "status" => $row['status']
    ];
}

if (empty($reports)) {
    echo json_encode(["error" => "No reports found"]);
    exit;
}

$response = [
    "reports" => $reports
];

echo json_encode($response);
?>
