<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
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

        // Adjusted SQL query to retrieve the necessary data
        $sql = "SELECT adminID, firstName, lastName, email, phoneNumber, address, cityState, postCode, taxID, profilePicture, username, password, adminUserType
                FROM admin WHERE adminID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $adminID);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($adminID, $firstName, $lastName, $email, $phoneNumber, $address, $cityState, $postCode, $taxID, $profilePicture, $username, $password, $adminUserType);

        if ($stmt->fetch()) {
            // Prepare the response data
            $response['adminID'] = $adminID;
            $response['firstName'] = $firstName;
            $response['lastName'] = $lastName;
            $response['email'] = $email;
            $response['phoneNumber'] = $phoneNumber;
            $response['address'] = $address;
            $response['cityState'] = $cityState;
            $response['postCode'] = $postCode;
            $response['taxID'] = $taxID;
            $response['username'] = $username;
            $response['password'] = $password;
            $response['adminUserType'] = $adminUserType;

            // Handle the profile picture
            $response['profilePicture'] = null; // Default value

            if ($profilePicture) {
                // Construct the path to the profile picture
                $filePath = '../../uploads/profile_pictures/' . $profilePicture;
                
                // Check if the file exists and encode it as base64 if it does
                if (file_exists($filePath)) {
                    $response['profilePicture'] = base64_encode(file_get_contents($filePath));
                } else {
                    $response['profilePicture'] = 'File not found';
                }
            }
        } else {
            $response['error'] = 'Admin not found';
        }

        $stmt->close();
    } catch (ExpiredException $e) {
        $response['error'] = 'Token expired';
    } catch (Exception $e) {
        $response['error'] = 'Invalid token';
    }
} else {
    $response['error'] = 'Token not provided';
}

$conn->close();

// Return the response as JSON
echo json_encode($response);
?>
