<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['proofOfPayment']) && isset($_POST['bookingID'])) {
        $bookingID = $_POST['bookingID'];
        $proofOfPayment = $_FILES['proofOfPayment'];

        if ($proofOfPayment['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'File upload error']);
            exit();
        }

        $imageData = file_get_contents($proofOfPayment['tmp_name']);
        $paymentStatus = 'received';
        $conn->begin_transaction();

        try {
            $updateQuery = "UPDATE booking SET proofOfPayment = ? WHERE bookingID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('bi', $imageData, $bookingID);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update booking table');
            }

            $updateLogQuery = "UPDATE booking_update_log SET paymentStatus = ? WHERE bookingID = ?";
            $stmtLog = $conn->prepare($updateLogQuery);
            $stmtLog->bind_param('si', $paymentStatus, $bookingID);

            if (!$stmtLog->execute()) {
                throw new Exception('Failed to update booking update log');
            }

            $conn->commit();
            echo json_encode(['message' => 'Proof of payment uploaded and payment status updated']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'No file or bookingID provided']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>
