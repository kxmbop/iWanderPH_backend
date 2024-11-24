<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Acheckllow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$roomId = $_GET['RoomID'];
$query = "SELECT InclusionName, Description FROM room_inclusions WHERE RoomID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $roomId);
$stmt->execute();
$result = $stmt->get_result();
$inclusions = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($inclusions);

?>