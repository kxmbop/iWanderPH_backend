<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';

$response = [];
$key = "123456"; // Your JWT secret key

if (!isset($_GET['searchTerm']) || !isset($_GET['token'])) {
    echo json_encode(['success' => false, 'message' => 'Search term and token are required']);
    exit();
}

$searchTerm = $_GET['searchTerm'];
$token = $_GET['token']; // You might want to validate the token here if needed

// Decode the JWT token to extract TravelerID
try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID; // Extract TravelerID from the token
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid token: ' . $e->getMessage()]);
    exit();
}

// Search for MerchantID or Business Name
$query = "SELECT m.merchantID, m.businessName
          FROM merchant m
          WHERE m.merchantID = ? OR m.businessName LIKE ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$merchant = $result->fetch_assoc();

if ($merchant) {
    // Fetch rooms and transportations for the found Merchant
    $merchantID = $merchant['merchantID'];
    
    // Fetch rooms
    $roomsQuery = "SELECT RoomID, RoomName, RoomQuantity, GuestPerRoom, RoomRate 
                   FROM rooms WHERE MerchantID = ?";
    $stmt = $conn->prepare($roomsQuery);
    $stmt->bind_param("i", $merchantID);
    $stmt->execute();
    $roomsResult = $stmt->get_result();

    $rooms = [];
    while ($room = $roomsResult->fetch_assoc()) {
        $rooms[] = $room;
    }

    // Fetch transportations
    $transportQuery = "SELECT t.TransportationID, t.VehicleName, t.Model, t.Brand, t.Capacity, t.RentalPrice 
                       FROM transportations t WHERE t.MerchantID = ?";
    $stmt = $conn->prepare($transportQuery);
    $stmt->bind_param("i", $merchantID);
    $stmt->execute();
    $transportsResult = $stmt->get_result();

    $transportations = [];
    while ($transport = $transportsResult->fetch_assoc()) {
        $transportations[] = $transport;
    }

    // Return results
    echo json_encode([
        'success' => true,
        'rooms' => $rooms,
        'transportations' => $transportations
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No Merchant found']);
}

$stmt->close();
$conn->close();
?>
