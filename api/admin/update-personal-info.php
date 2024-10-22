<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $age = (int)$_POST['age'];
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $admin_id = 13; // Example Admin ID, replace as necessary

    $sql = "UPDATE admin SET first_name='$first_name', last_name='$last_name', gender='$gender', age='$age', email='$email', phone_number='$phone_number', bio='$bio' WHERE AdminID=$admin_id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Personal information updated successfully."]);
    } else {
        echo json_encode(["error" => "Error updating record: " . $conn->error]);
    }

    $conn->close();
}
?>
