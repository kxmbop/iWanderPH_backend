<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get the raw POST data and decode it
$input = json_decode(file_get_contents("php://input"));
$token = $input->token ?? ''; 
$key = "123456"; // Secret key used to sign the JWT

// Check if the token is provided
if (!$token) {
    echo json_encode(['error' => 'Token not provided']);
    exit();
}

try {
    // Decode the JWT token
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    
    // Extract TravelerID from the decoded token
    $travelerID = $decoded->TravelerID;

    // Query to get the MerchantID using the TravelerID
    $sql = 'SELECT MerchantID FROM merchant WHERE travelerID = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $merchantResult = $stmt->get_result();

    // Check if the merchant exists
    if ($merchantResult->num_rows > 0) {
        // Fetch the MerchantID from the result
        $merchantRow = $merchantResult->fetch_assoc();
        $merchantID = $merchantRow['MerchantID'];
        
        // SQL query to fetch transportations for the specific MerchantID
        $sql = 'SELECT t.TransportationID, t.VehicleName, t.Model, t.Brand, t.Capacity, t.RentalPrice, 
                       tg.TranspotationImageID, tg.ImageFile
                FROM transportations t
                LEFT JOIN transportation_gallery tg ON t.TransportationID = tg.TransportationID
                WHERE t.MerchantID = ?';
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $merchantID);
        $stmt->execute();
        $result = $stmt->get_result();

        $transportations = [];

        // Fetch all transportations for the merchant
        while ($row = $result->fetch_assoc()) {
            $transportations[] = $row;
        }

        // Return the transportations if found
        if (count($transportations) > 0) {
            echo json_encode(['transportations' => $transportations]);
        } else {
            echo json_encode(['message' => 'No transportations found for this merchant']);
        }

    } else {
        echo json_encode(['error' => 'Merchant not found']);
    }

} catch (Exception $e) {
    // Handle any errors during decoding
    echo json_encode(['error' => 'Invalid token or token expired']);
}
?>
