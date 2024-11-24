<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Acheckllow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$roomId = $_GET['RoomID'];
$query = "SELECT ImageURL FROM room_gallery WHERE RoomID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $roomId);
$stmt->execute();
$result = $stmt->get_result();
$gallery = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($gallery);

?>