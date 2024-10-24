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

// echo json_encode(['received_token' => $token]); 

if (!$token) {
    echo json_encode(['error' => 'Token not provided']);
    exit();
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    // print_r($decoded); 

    $travelerID = $decoded->TravelerID;

    $sql = 'SELECT * FROM merchant WHERE travelerID = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $travelerID);
    $stmt->execute();
    $merchantResult = $stmt->get_result();

    if ($merchantResult->num_rows > 0) {
        $merchantProfile = $merchantResult->fetch_assoc();

        if (isset($merchantProfile['profilePicture']) && !empty($merchantProfile['profilePicture'])) {
            $merchantProfile['merchantPic'] = base64_encode($merchantProfile['profilePicture']);
        } else {
            $merchantProfile['merchantPic'] = null; 
        }

        $travelerSql = 'SELECT * FROM traveler WHERE travelerID = ?'; 
        $travelerStmt = $conn->prepare($travelerSql);
        $travelerStmt->bind_param("i", $travelerID);
        $travelerStmt->execute();
        $travelerResult = $travelerStmt->get_result();

        if ($travelerResult->num_rows > 0) {
            $travelerProfile = $travelerResult->fetch_assoc();

            if (isset($travelerProfile['ProfilePic']) && !empty($travelerProfile['ProfilePic'])) {
                $travelerProfile['travelerPic'] = base64_encode($travelerProfile['ProfilePic']);
            } else {
                $travelerProfile['travelerPic'] = null; 
            }

            echo json_encode([
                'profile' => [
                    'businessName' => $merchantProfile['businessName'],
                    'ownerName' => $travelerProfile['FirstName'] . ' ' . $travelerProfile['LastName'],
                    'merchantID' => $merchantProfile['merchantID'],
                    'businessType' => $merchantProfile['businessType'],
                    'email' => $merchantProfile['email'],
                    'contact' => $merchantProfile['contact'],
                    'address' => $merchantProfile['address'],
                    'travelerID' => $travelerProfile['TravelerID'],
                    'username' => $travelerProfile['Username'],
                    'personalEmail' => $travelerProfile['Email'],
                    'personalContact' => $travelerProfile['Mobile'],
                    'personalAddress' => $travelerProfile['Address'],
                    'bio' => $travelerProfile['Bio'],
                    'merchantPic' => $merchantProfile['merchantPic'],
                    'travelerPic' => $travelerProfile['travelerPic']
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Traveler not found']);
        }

        $travelerStmt->close();
    } else {
        echo json_encode(['error' => 'Merchant not found']);
    }

    $stmt->close();
} catch (ExpiredException $e) {
    echo json_encode(['error' => 'Token expired: ' . $e->getMessage()]);
} catch (SignatureInvalidException $e) {
    echo json_encode(['error' => 'Invalid token signature: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Token decoding error: ' . $e->getMessage()]);
}

$conn->close();
