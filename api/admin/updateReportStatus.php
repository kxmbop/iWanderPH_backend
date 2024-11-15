<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Get the reportID and new status from POST data
$reportID = isset($_POST['reportID']) ? (int)$_POST['reportID'] : 0;
$newStatus = isset($_POST['status']) ? $_POST['status'] : null;


if (!isset($_POST['reportID']) || !isset($_POST['status'])) {
   die('Missing reportID or status');
}


// Prepare the SQL query to update the report status
$updateQuery = "
    UPDATE reports 
    SET status = ? 
    WHERE reportID = ?";
$stmt = $conn->prepare($updateQuery);

// Bind the parameters to the query
$stmt->bind_param("si", $newStatus, $reportID);

// Execute the query
if ($stmt->execute()) {
    $response = [
        "success" => true,
        "message" => "Report status updated successfully"
    ];
} else {
    $response = [
        "error" => "Failed to update report status"
    ];
}


$stmt->close();


echo json_encode($response);
?>
