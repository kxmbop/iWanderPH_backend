<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';

if (empty($username) || !preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {
    echo json_encode(["error" => "Invalid username format."]);
    exit;
}

$query = "SELECT COUNT(*) as count FROM traveler WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$isTaken = $row['count'] > 0;
echo json_encode(["isTaken" => $isTaken]);

$stmt->close();
$conn->close();
?>
