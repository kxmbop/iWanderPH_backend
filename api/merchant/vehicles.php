<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
require '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$input = json_decode(file_get_contents("php://input"));
$token = $input->token ?? ''; 
$key = "123456"; 

if (!$token) {
    echo json_encode(['error' => 'Token not provided']);
    exit();
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    
    $travelerID = $decoded->TravelerID;

    $sql = 'SELECT MerchantID FROM merchant WHERE travelerID = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $merchantResult = $stmt->get_result();

    if ($merchantResult->num_rows > 0) {
        $merchantRow = $merchantResult->fetch_assoc();
        $merchantID = $merchantRow['MerchantID'];
        
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

        while ($row = $result->fetch_assoc()) {
            if (!empty($row['ImageFile'])) {
                $base64Image = base64_encode($row['ImageFile']); 
                $row['ImageFile'] = $base64Image; 
            }   
            $transportations[] = $row;
        }

        if (count($transportations) > 0) {
            echo json_encode(['transportations' => $transportations]);
        } else {
            echo json_encode(['message' => 'No transportations found for this merchant']);
        }

    } else {
        echo json_encode(['error' => 'Merchant not found']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid token or token expired']);
}
?>
