<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$reportID = isset($_GET['reportID']) ? (int)$_GET['reportID'] : 0; // Optional filter by reportID

// Debugging the reportID
error_log("reportID: " . $reportID);

// Query to fetch reports along with associated review, review images, and violation details
$reportQuery = "
    SELECT 
        r.reportID AS reportID,
        r.reviewID AS reviewID,
        r.violation AS violationID,
        r.reportedBy AS reportedBy,
        r.reportMessage AS reportMessage,
        r.reportDate AS reportDate,
        r.status AS status,
        rev.bookingID AS bookingID,
        rev.travelerID AS travelerID,
        rev.reviewComment AS reviewComment,
        rev.reviewRating AS reviewRating,
        rev.privacy AS privacy,
        rev.createdAt AS reviewCreatedAt,
        ri.reviewImageID AS reviewImageID,
        ri.image AS reviewImage,
        v.violationTitle AS violationTitle,
        v.violationDescription AS violationDescription
    FROM reports r
    LEFT JOIN reviews rev ON r.reviewID = rev.reviewID
    LEFT JOIN review_images ri ON rev.reviewID = ri.reviewID
    LEFT JOIN violations v ON r.violation = v.violationID
    WHERE (? = 0 OR r.reportID = ?)";
$stmt = $conn->prepare($reportQuery);
$stmt->bind_param("ii", $reportID, $reportID);

$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    // Group images under each reviewID
    $reviewID = $row['reviewID'];
    if (!isset($reports[$reviewID])) {
        $reports[$reviewID] = [
            "reportID" => $row['reportID'],
            "reviewID" => $row['reviewID'],
            "violation" => [
                "violationID" => $row['violationID'],
                "violationTitle" => $row['violationTitle'],
                "violationDescription" => $row['violationDescription']
            ],
            "reportedBy" => [
                "travelerID" => $row['reportedBy']
            ],
            "reportMessage" => $row['reportMessage'],
            "reportDate" => $row['reportDate'],
            "status" => $row['status'],
            "reviewDetails" => [
                "bookingID" => $row['bookingID'],
                "travelerID" => $row['travelerID'],
                "reviewComment" => $row['reviewComment'],
                "reviewRating" => $row['reviewRating'],
                "privacy" => $row['privacy'],
                "createdAt" => $row['reviewCreatedAt'],
                "images" => []
            ]
        ];
    }

    if (!empty($row['reviewImageID']) && !empty($row['reviewImage'])) {
        // Convert image to Base64
        $imageData = base64_encode($row['reviewImage']);
        $reports[$reviewID]['reviewDetails']['images'][] = [
            "reviewImageID" => $row['reviewImageID'],
            "image" => $imageData
        ];
    }
}

if (empty($reports)) {
    echo json_encode(["error" => "No reports found"]);
    exit;
}

$response = [
    "reports" => array_values($reports) // Reset indices to ensure JSON array structure
];

echo json_encode($response);
?>
