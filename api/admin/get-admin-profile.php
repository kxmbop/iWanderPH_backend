<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';


if (isset($_GET['AdminID'])) {
    $AdminID = $_GET['AdminID'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE AdminID = ?");
    $stmt->bind_param("i", $AdminID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo json_encode($admin);
    } else {
        echo json_encode(['message' => 'Admin not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['message' => 'Invalid request']);
}

$conn->close();