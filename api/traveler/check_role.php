<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

include '../../db.php'; 

$response = [];
$key = "123456"; 

$data = json_decode(file_get_contents("php://input"));

if (isset($data->token)) {
    $token = $data->token;

    if (!empty($token)) {
        try {
            $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
            $travelerID = $decoded->TravelerID; 
            $query = "SELECT isMerchant FROM traveler WHERE TravelerID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $travelerID);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($isMerchant);
                $stmt->fetch();
                if ($isMerchant == 1) {
                    $response['message'] = 'User is a Merchant.';
                    $response['isMerchant'] = true;
                } else {
                    $response['message'] = 'User is not a Merchant.';
                    $response['isMerchant'] = false; 
                }
            } else {
                $response['message'] = 'Traveler not found.';
                $response['error'] = true;
            }

        } catch (ExpiredException $e) {
            $response['message'] = 'Token has expired';
            $response['error'] = true;
        } catch (Exception $e) {
            $response['message'] = 'Invalid token';
            $response['error'] = true;
        }
    } else {
        $response['message'] = 'No token provided';
        $response['error'] = true;
    }
} else {
    $response['message'] = 'No token found in the request body';
    $response['error'] = true;
}

echo json_encode($response);
?>