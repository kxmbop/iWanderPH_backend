<?php
session_start(); // Start a new session
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php'; 
use \Firebase\JWT\JWT;

include '../../db.php';

$success = false;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $username = $data->username ?? '';
    $password = $data->password ?? '';

    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password.";
    } else {
        $sql = "SELECT TravelerID, Username, Password, isDeactivated, isSuspended, isBanned FROM traveler WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if ($password === $user["Password"]) {
                if ($user["isDeactivated"] || $user["isSuspended"] || $user["isBanned"]) {
                    $message = "Your account is not active.";
                } else {
                    $key = "123456"; 
                    $payload = [
                        'iat' => time(),
                        'TravelerID' => $user["TravelerID"],
                        'Username' => $user["Username"]
                    ];
            
                    $algorithm = 'HS256';
                    $jwt = JWT::encode($payload, $key, $algorithm);
            
                    $_SESSION['token'] = $jwt; // Store JWT in session

                    $success = true;
                    $message = "Login successful.";
                    echo json_encode(["success" => $success, "message" => $message, "token" => $jwt]);
                    exit();
                }
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }

        $stmt->close();
    }
}

$conn->close();

echo json_encode(["success" => $success, "message" => $message]);
?>