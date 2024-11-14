<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include '../../db.php';

$response = [];
$query = "SELECT InclusionID, InclusionName FROM inclusions";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'InclusionID' => $row['InclusionID'],
            'InclusionName' => $row['InclusionName']
        ];
    }
}

echo json_encode($response);
$conn->close();
?>
