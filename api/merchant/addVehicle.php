<?php
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
    exit();
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(['success' => false, 'message' => 'Authorization token is required']);
    exit();
}

$token = str_replace('Bearer ', '', $headers['Authorization']);
$key = "123456";

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    if (!isset($decoded->TravelerID)) {
        echo json_encode(['success' => false, 'message' => 'TravelerID not found in token']);
        exit();
    }
    $travelerID = $decoded->TravelerID;

    $merchantQuery = "SELECT MerchantID FROM merchant WHERE TravelerID = ?";
    $merchantStmt = $conn->prepare($merchantQuery);
    $merchantStmt->bind_param("i", $travelerID);
    $merchantStmt->execute();
    $merchantResult = $merchantStmt->get_result();

    if ($merchantResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Merchant not found for this TravelerID']);
        exit();
    }

    $merchantID = $merchantResult->fetch_assoc()['MerchantID'];

    $vehicleName = $_POST['vehicleName'] ?? '';
    $model = $_POST['Model'] ?? '';
    $brand = $_POST['Brand'] ?? '';
    $capacity = (int) ($_POST['Capacity'] ?? 0);
    $rentalPrice = (float) ($_POST['RentalPrice'] ?? 0.0);

    $stmt = $conn->prepare("INSERT INTO transportations (VehicleName, Model, Brand, Capacity, RentalPrice, MerchantID) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssidi", $vehicleName, $model, $brand, $capacity, $rentalPrice, $merchantID);

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert into transportations table: " . $stmt->error);
    }

    $transportationID = $stmt->insert_id;
    $stmt->close();

    foreach ($_FILES['ImageFile']['tmp_name'] as $key => $tmpName) {
        if (is_uploaded_file($tmpName)) {
            $imageData = file_get_contents($tmpName);

            $stmt = $conn->prepare("INSERT INTO transportation_gallery (ImageFile, TransportationID) VALUES (?, ?)");
            $stmt->bind_param("bi", $imageData, $transportationID);
            $stmt->send_long_data(0, $imageData);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert image into transportation_gallery: " . $stmt->error);
            }
            $stmt->close(); // Close the statement after execution
        }
    }

    $response['success'] = true;
    $response['message'] = "Vehicle and images added successfully.";
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);