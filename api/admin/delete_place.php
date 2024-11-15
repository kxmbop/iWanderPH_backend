<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"));
    $placeId = $data->id;

    // Delete place from `places` table
    $sql = "DELETE FROM places WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $placeId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Place deleted successfully"]);
    } else {
        echo json_encode(["message" => "Error deleting place"]);
    }
}
?>
