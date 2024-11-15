<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include '../../db.php';

$response = [];
$query = "SELECT ViewID, ViewName FROM views";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'ViewID' => $row['ViewID'],
            'ViewName' => $row['ViewName']
        ];
    }
}

echo json_encode($response);
$conn->close();
?>
