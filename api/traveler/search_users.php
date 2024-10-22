<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$response = [];
$key = "123456"; 

$token = $_SESSION['token'] ?? '';

$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!isset($token)) {
  $response['error'] = 'Unauthorized';
  echo json_encode($response);
  exit;
}

$query = "
    SELECT 
        merchant.merchantuuid AS uuid, 
        merchant.BusinessName AS username, 
        merchant.BusinessType 
    FROM merchant 
    UNION 
    SELECT 
        traveler.traveleruuId AS uuid, 
        traveler.username, 
        CONCAT(traveler.firstname, ' ', traveler.lastname) AS fullname 
    FROM traveler
";

$result = mysqli_query($conn, $query);

if (!$result) {
  $response['error'] = 'Failed to retrieve users';
  echo json_encode($response);
  exit;
}

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = [
      'uuid' => $row['uuid'],
      'username' => $row['username'],
      'fullname' => $row['fullname'] ?? '', 
      'BusinessType' => $row['BusinessType'] ?? ''
    ];
}

$response['users'] = $users;
echo json_encode($response);
?>
