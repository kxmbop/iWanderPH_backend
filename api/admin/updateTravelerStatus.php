<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $travelerID = $_POST['travelerID'];
    $isSuspended = $_POST['isSuspended'] ?? null;
    $isBanned = $_POST['isBanned'] ?? null;

    $sql = "UPDATE traveler SET ";
    if ($isSuspended !== null) {
        $sql .= "isSuspended = ?";
    } elseif ($isBanned !== null) {
        $sql .= "isBanned = ?";
    }
    $sql .= " WHERE travelerID = ?";

    $stmt = $conn->prepare($sql);
    if ($isSuspended !== null) {
        $stmt->bind_param('ii', $isSuspended, $travelerID);
    } elseif ($isBanned !== null) {
        $stmt->bind_param('ii', $isBanned, $travelerID);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
}
?>