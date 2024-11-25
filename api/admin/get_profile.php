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

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));
        $adminID = $decoded->adminID;
        $role = $decoded->role;

        if ($role !== 'admin') {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized access';
            echo json_encode($response);
            exit;
        }

        $sql = "SELECT AdminID, username, email, phoneNumber, address, userType, firstName, lastName, password FROM admin WHERE adminID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $adminID);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($adminID, $username, $email, $phoneNumber, $address, $userType, $firstName, $lastName, $password);
                $stmt->fetch();
               
                $profile = [
                    'id' => $adminID,
                    'username' => $username,
                    'email' => $email,
                    'phoneNumber' => $phoneNumber,
                    'address' => $address,
                    'userType' => $userType,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'password' => $password,
                ];
                
                $response = [
                    "success" => true,
                    "message" => "Profile retrieved successfully",
                    "profile" => $profile
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Profile not found'
                ];
            }

            $stmt->close();
        } else {
            $response = [
                "success" => false,
                "message" => "Database query failed"
            ];
        }

    } catch (ExpiredException $e) {
        $response = [
            "success" => false,
            "message" => "Token expired: " . $e->getMessage()
        ];
    } catch (Exception $e) {
        $response = [
            "success" => false,
            "message" => "Invalid token: " . $e->getMessage()
        ]; 
    }
} else {
    $response = [
        "success" => false,
        "message" => "No token provided."
    ];
}

echo json_encode($response);

$conn->close();
?>
