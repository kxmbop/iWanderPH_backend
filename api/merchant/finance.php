<?php
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php'; 

if (!isset($_GET['token'])) {
    echo json_encode(['success' => false, 'message' => 'Token is required']);
    exit();
}

$token = $_GET['token'];
$key = "123456"; 

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));

    if (isset($decoded->TravelerID)) {
        $travelerID = $decoded->TravelerID;  
    } else {
        echo json_encode(['success' => false, 'message' => 'TravelerID not found in token.']);
        exit;
    }

    // Now use the TravelerID to fetch the Merchant ID
    $merchantQuery = "SELECT merchantID FROM merchant WHERE travelerID = ?";
    $merchantStmt = $conn->prepare($merchantQuery);
    $merchantStmt->bind_param("i", $travelerID);  // Use TravelerID

    if ($merchantStmt->execute()) {
        $merchantResult = $merchantStmt->get_result();
        
        if ($merchantResult->num_rows > 0) {
            $merchant = $merchantResult->fetch_assoc();
            $merchantID = $merchant['merchantID'];

            // Fetch bookings using the merchantID
            $bookingQuery = "SELECT b.BookingID, b.BookingDate, b.BookingStatus, b.BookingType, b.PayoutAmount, b.PayoutStatus, b.payoutTransactionID, b.payoutReleaseDate
                             FROM booking AS b 
                             WHERE b.merchantID = ?";
            $bookingStmt = $conn->prepare($bookingQuery);
            $bookingStmt->bind_param("i", $merchantID);

            if ($bookingStmt->execute()) {
                $bookingResult = $bookingStmt->get_result();
                $saleBookings = $bookingResult->fetch_all(MYSQLI_ASSOC); 
                echo json_encode(['success' => true, 'data' => $saleBookings]); 
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to fetch bookings']);
            }

            $bookingStmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Merchant not found']);
        }

        $merchantStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch merchant information']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Token invalid: ' . $e->getMessage()]);
}

$conn->close();
