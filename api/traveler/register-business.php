<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';
include 'encryption.php';
require '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$response = [];
$key = "123456";
$headers = getallheaders();
$authorizationHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authorizationHeader);

if (!empty($token)) {
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $travelerID = $decoded->TravelerID;

        // Collect and sanitize input data
        $businessName = $_POST['businessName'] ?? '';
        $email = $_POST['email'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $address = $_POST['address'] ?? '';
        $businessType = $_POST['businessType'] ?? '';
        $businessTin = $_POST['businessTin'] ?? '';

        $profilePicture = null;
        $barangayClearance = null;
        $mayorPermit = null;
        $birForm = null;
        $dotAuth = null;

        // Handle profile picture upload
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['profilePicture']['tmp_name'];
            $profilePicture = file_get_contents($fileTmpName);
        }

        // Handle government documents uploads
        if (isset($_FILES['BarangayClearance']) && $_FILES['BarangayClearance']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['BarangayClearance']['tmp_name'];
            $barangayClearance = file_get_contents($fileTmpName);
        }
        if (isset($_FILES['MayorPermit']) && $_FILES['MayorPermit']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['MayorPermit']['tmp_name'];
            $mayorPermit = file_get_contents($fileTmpName);
        }
        if (isset($_FILES['BirForm']) && $_FILES['BirForm']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['BirForm']['tmp_name'];
            $birForm = file_get_contents($fileTmpName);
        }
        if (isset($_FILES['DotAuth']) && $_FILES['DotAuth']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['DotAuth']['tmp_name'];
            $dotAuth = file_get_contents($fileTmpName);
        }

        // Insert merchant details into the database
        $stmt = $conn->prepare("INSERT INTO merchant (
            merchantUUID, businessName, email, contact, address, profilePicture, 
            businessType, BusinessTin, BarangayClearance, MayorPermit, BirForm, DotAuth, travelerID, isApproved
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

        $merchantUUID = encrypt(uniqid(), '123456');
        $stmt->bind_param(
            "ssssssssssssi", 
            $merchantUUID, $businessName, $email, $contact, $address, $profilePicture, 
            $businessType, $businessTin, $barangayClearance, $mayorPermit, $birForm, $dotAuth, $travelerID
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert merchant details: " . $conn->error);
        }

        $merchantID = $stmt->insert_id;
        $stmt->close();

        // Update traveler table to mark as merchant
        $updateStmt = $conn->prepare("UPDATE traveler SET isMerchant = 1 WHERE TravelerID = ?");
        $updateStmt->bind_param("i", $travelerID);
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update traveler's isMerchant status: " . $conn->error);
        }
        $updateStmt->close();

        // Handle room and transportation details
        $roomDetails = json_decode($_POST['roomDetails'], true);
        if ($roomDetails === null) {
            throw new Exception("Failed to parse room details: " . json_last_error_msg());
        }

        foreach ($roomDetails as $roomIndex => $room) {
            $stmt = $conn->prepare("INSERT INTO rooms (RoomName, RoomQuantity, RoomRate, GuestPerRoom, MerchantID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sidii", $room['name'], $room['quantity'], $room['rate'], $room['guests'], $merchantID);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert room: " . $stmt->error);
            }
            $roomID = $stmt->insert_id;
            $stmt->close();

            foreach ($_FILES as $key => $file) {
                if (preg_match("/^roomImage_{$roomIndex}_\d+$/", $key) && $file['error'] === UPLOAD_ERR_OK) {
                    $fileTmpName = $file['tmp_name'];
                    $imageFile = file_get_contents($fileTmpName);

                    $stmt = $conn->prepare("INSERT INTO room_gallery (ImageFile, RoomID) VALUES (?, ?)");
                    $stmt->bind_param("bi", $imageFile, $roomID);
                    $stmt->send_long_data(0, $imageFile);

                    if (!$stmt->execute()) {
                        throw new Exception("Failed to insert room image: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            foreach ($room['inclusions'] as $inclusion) {
                $inclusionID = (int)$inclusion['InclusionID'];
                $stmt = $conn->prepare("INSERT INTO room_inclusions (InclusionID, RoomID) VALUES (?, ?)");
                $stmt->bind_param("ii", $inclusionID, $roomID);
                $stmt->execute();
                $stmt->close();
            }

            foreach ($room['views'] as $view) {
                $viewID = (int)$view['ViewID'];
                $stmt = $conn->prepare("INSERT INTO room_view (ViewID, RoomID) VALUES (?, ?)");
                $stmt->bind_param("ii", $viewID, $roomID);
                $stmt->execute();
                $stmt->close();
            }
        }

        $transportationDetails = json_decode($_POST['transportationDetails'], true);
        foreach ($transportationDetails as $transportIndex => $transport) {
            $stmt = $conn->prepare("INSERT INTO transportations (VehicleName, Model, Brand, Capacity, RentalPrice, MerchantID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssidi", $transport['type'], $transport['model'], $transport['brand'], $transport['capacity'], $transport['rate'], $merchantID);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert transportation: " . $conn->error);
            }
            $transportationID = $stmt->insert_id;
            $stmt->close();

            foreach ($_FILES as $key => $file) {
                if (preg_match("/^transportImage_{$transportIndex}_\d+$/", $key) && $file['error'] === UPLOAD_ERR_OK) {
                    $fileTmpName = $file['tmp_name'];
                    $imageFile = file_get_contents($fileTmpName);

                    $stmt = $conn->prepare("INSERT INTO transportation_gallery (ImageFile, TransportationID) VALUES (?, ?)");
                    $stmt->bind_param("bi", $imageFile, $transportationID);
                    $stmt->send_long_data(0, $imageFile);

                    if (!$stmt->execute()) {
                        throw new Exception("Failed to insert transportation image: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }
        }

        $response["success"] = true;
        $response["message"] = "Business and details registered successfully.";
    } catch (Exception $e) {
        $response["success"] = false;
        $response["message"] = $e->getMessage();
    }
} else {
    $response["success"] = false;
    $response["message"] = "No token provided.";
}

$conn->close();
echo json_encode($response);
