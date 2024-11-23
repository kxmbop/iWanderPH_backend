<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get the place_id from the query parameters
    $place_id = isset($_GET['place_id']) ? intval($_GET['place_id']) : null;

    if (!$place_id) {
        http_response_code(400);
        echo json_encode(["message" => "Place ID is required"]);
        exit;
    }

    // Fetch the place name
    $place_sql = "SELECT place_name FROM places WHERE id = $place_id";
    $place_result = $conn->query($place_sql);

    if ($place_result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(["message" => "Place not found"]);
        exit;
    }

    $place_name = $place_result->fetch_assoc()['place_name'];

    // Fetch nearby merchants
    $sql = "
        SELECT 
            m.merchantID,
            m.businessName,
            m.email,
            m.contact,
            m.address,
            m.businessType,
            TO_BASE64(m.profilePicture) as profilePicture
        FROM nearby n
        JOIN merchant m ON n.Merchant_id = m.merchantID
        WHERE n.Place_id = $place_id
    ";
    $result = $conn->query($sql);

    $merchants = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $merchants[] = $row;
        }
    }

    // Construct the response
    $response = [
        "placeName" => $place_name,
        "merchants" => $merchants
    ];

    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
