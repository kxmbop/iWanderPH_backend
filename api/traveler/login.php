<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true"); 
header("Content-Type: application/json");

session_start();
include '../../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = []; 
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
        if (!$stmt) {
            error_log("SQL Prepare Error: " . $conn->error);
            die("SQL Prepare Error");
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if ($password === $user["Password"]) {
                if ($user["isDeactivated"] || $user["isSuspended"] || $user["isBanned"]) {
                    $message = "Your account is not active.";
                } else {
                    $_SESSION["user_id"] = $user["TravelerID"];
                    $_SESSION["username"] = $user["Username"];

                    error_log(print_r($_SESSION, true)); 
                    $success = true;
                    $message = "Login successful.";
                }
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }

        $stmt->close();
    }
} else {
    $message = "Invalid request method.";
}

$conn->close(); 

echo json_encode(["success" => $success, "message" => $message]);
?>
