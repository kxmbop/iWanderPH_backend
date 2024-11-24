<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';

$query = "
    SELECT 
        traveler.travelerId as id,
        traveler.traveleruuId AS uuid, 
        traveler.username, 
        CONCAT(traveler.firstname, ' ', traveler.lastname) AS fullname 
    FROM traveler
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);

$stmt->close();
$conn->close();
?>
