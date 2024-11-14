<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

// Step 1: Check if the connection is successful
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Step 2: Query to fetch support agents
$query = "
    SELECT 
        sa.id as agentId, 
        sa.firstName, 
        sa.lastName, 
        sa.bio, 
        sa.taxID, 
        sa.username,
        sa.password
    FROM support_agents sa
";

$result = $conn->query($query);

// Step 3: Check if query was successful
if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

// Step 4: Check if there are results
$supportAgents = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $supportAgents[] = $row;
    }
} else {
    // If no agents found, return an empty array
    $supportAgents = ["message" => "No support agents found"];
}

// Step 5: Return results
echo json_encode($supportAgents);

// Step 6: Close the connection
$conn->close();
?>