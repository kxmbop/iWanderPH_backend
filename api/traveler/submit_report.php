<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';

$key = "123456";  // Replace with your actual key
$data = json_decode(file_get_contents("php://input"), true);

$reviewID = $data['reviewID'];
$violationID = $data['violationID'];
$reportMessage = $data['reportMessage'];
$reportDate = date("Y-m-d H:i:s");
$status = "pending";
$token = $data['token'];

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $reportedBy = $decoded->TravelerID;

    // Insert report into the database
    $sql = "INSERT INTO reports (reviewID, violation, reportedBy, reportMessage, reportDate, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $reviewID, $violationID, $reportedBy, $reportMessage, $reportDate, $status);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Report submitted successfully"]);
    } else {
        echo json_encode(["message" => "Error submitting report"]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["message" => "Token is invalid or expired"]);
}

$conn->close();
?>
