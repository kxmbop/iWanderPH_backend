<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../../db.php'; 

$response = [];

$userId = $_GET['userId'] ?? null;

if ($userId) {
    $userFound = false;

    $tables = [
        'admin' => [
            'idColumn' => 'adminId',
            'displayName' => 'Customer Support' 
        ],
        'merchant' => [
            'idColumn' => 'merchantId',
            'displayName' => 'BusinessName' 
        ],
        'traveler' => [
            'idColumn' => 'travelerId',
            'displayName' => 'username' 
        ]
    ];

    foreach ($tables as $table => $info) {
        $query = "SELECT * FROM $table WHERE {$info['idColumn']} = ?";
        $stmt = $conn->prepare($query);
        
        $stmt->bind_param("i", $userId); 

        $stmt->execute();
        $result = $stmt->get_result();
        $userDetails = $result->fetch_assoc();

        if ($userDetails) {
            if ($table === 'admins') {
                $userDetails['displayName'] = $info['displayName'];
            } elseif ($table === 'merchants') {
                $userDetails['displayName'] = $userDetails['BusinessName'];
            } elseif ($table === 'travelers') {
                $userDetails['displayName'] = $userDetails['username'];
            }

            $response['userDetails'] = $userDetails;
            $userFound = true; 
            break; 
        }
    }

    if (!$userFound) {
        $response['error'] = 'User not found in any table.';
    }
} else {
    $response['error'] = 'User ID not provided.';
}

echo json_encode($response);
?>
