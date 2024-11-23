<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Step 1: Retrieve the data from the request body
$data = json_decode(file_get_contents("php://input"));

// Step 2: Ensure required fields are provided
if (!isset($data->firstName) || !isset($data->lastName) || !isset($data->username) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    exit();
}

// Step 3: Hash the password before storing it
$password = password_hash($data->password, PASSWORD_BCRYPT);

// Step 4: Prepare the SQL query to insert the admin data
$query = "INSERT INTO admin (firstName, lastName, adminUserType, taxID, username, password) 
          VALUES (?, ?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = $conn->prepare($query);

if ($stmt) {
    // Step 5: Bind parameters and execute the query
    $firstName = $data->firstName;
    $lastName = $data->lastName;
    $adminUserType = 'SupportAgent';  // User type for support agents
    $taxID = isset($data->taxID) && !empty($data->taxID) ? $data->taxID : NULL;
    $username = $data->username;

    // Bind parameters to the query
    $stmt->bind_param("ssssss", $firstName, $lastName, $adminUserType, $taxID, $username, $password);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Admin account created successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to create admin account"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to prepare the query"]);
}

$stmt->close();
$conn->close();
?>
