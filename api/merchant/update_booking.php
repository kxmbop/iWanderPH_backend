<?php 
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
include '../../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$bookingID = $data['bookingID'];
$bookingStatus = $data['bookingStatus'];

$stmt = $conn->prepare("UPDATE booking SET bookingStatus = ? WHERE bookingID = ?");
$stmt->bind_param("si", $bookingStatus, $bookingID);

if ($stmt->execute()) {
    $travelerStmt = $conn->prepare("SELECT travelerID FROM booking WHERE bookingID = ?");
    $travelerStmt->bind_param("i", $bookingID);
    $travelerStmt->execute();
    $travelerStmt->bind_result($travelerID);
    $travelerStmt->fetch();
    $travelerStmt->close();

    switch ($bookingStatus) {
        case 'Accepted':
            $notificationMessage = "Booking #{$bookingID} has been accepted by the merchant. We're excited to have you!";
            break;
        case 'Ready':
            $notificationMessage = "Booking #{$bookingID} is ready! Get set for your amazing experience.";
            break;
        case 'Checked-in':
            $notificationMessage = "Great news! You have checked in for booking #{$bookingID}. Enjoy your stay!";
            break;
        case 'Checked-out':
            $notificationMessage = "Thank you for your visit! You have successfully checked out from booking #{$bookingID}. We hope to see you again!";
            break;
        case 'Completed':
            $notificationMessage = "Booking #{$bookingID} is now completed. We hope you had a great experience!";
            break;
        case 'Cancelled':
            $notificationMessage = "We're sorry to inform you that booking #{$bookingID} has been cancelled. If you have any questions, please reach out to support.";
            break;
        default:
            $notificationMessage = "Booking #{$bookingID} status has been updated.";
    }

    $notificationStmt = $conn->prepare("INSERT INTO notifications (bookingID, notificationMessage, userID, createdAt) VALUES (?, ?, ?, NOW())");
    $notificationStmt->bind_param("isi", $bookingID, $notificationMessage, $travelerID);
    $notificationStmt->execute();

    echo json_encode(['success' => true, 'message' => 'Booking updated and notification created.']);
} else {
    echo json_encode(['error' => 'Error updating booking: ' . $stmt->error]);
}

$stmt->close();
$notificationStmt->close();
$conn->close();
?>
