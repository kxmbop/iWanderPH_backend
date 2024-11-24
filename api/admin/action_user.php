<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$response = ["success" => false];

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['reportID'], $data['status'], $data['actualViolation'], $data['action'], $data['investigationSummary'], $data['actionDate'])) {
        $reportID = $data['reportID'];
        $status = $data['status'];
        $actualViolation = $data['actualViolation'];
        $action = $data['action'];
        $investigationSummary = $data['investigationSummary'];
        $actionDate = $data['actionDate'];

 
        $query = "UPDATE reports 
                  SET status = ?, actualViolation = ?, action = ?, investigationSummary = ?, actionDate = ?
                  WHERE reportID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $status, $actualViolation, $action, $investigationSummary, $actionDate, $reportID);

        if ($stmt->execute()) {
            $response["success"] = true;
        } else {
            $response["error"] = "Failed to update the report.";
        }
        $stmt->close();
    } else {
        $response["error"] = "Missing required fields.";
    }
} catch (Exception $e) {
    $response["error"] = "Exception: " . $e->getMessage();
}

// Output JSON response
echo json_encode($response);
?>