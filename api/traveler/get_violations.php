<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

try {
    $sql = "SELECT violationID, violationTitle, violationDescription FROM violations";
    $result = $conn->query($sql);

    $violations = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $violations[] = $row;
        }
    }

    echo json_encode($violations);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
