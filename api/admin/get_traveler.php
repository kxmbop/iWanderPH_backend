<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['merchantID'])) {
    $merchantID = $_GET['merchantID'];

    // Step 1: Get TravelerID and Government Information from the merchant table
    $sql = "SELECT TravelerID, BusinessTin, BarangayClearance, MayorPermit, BirForm, DotAuth FROM merchant WHERE merchantID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $merchantID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $merchant = $result->fetch_assoc();
        $travelerID = $merchant['TravelerID'];

        // Encode the government documents as base64
        $merchant['BarangayClearance'] = base64_encode($merchant['BarangayClearance']);
        $merchant['MayorPermit'] = base64_encode($merchant['MayorPermit']);
        $merchant['BirForm'] = base64_encode($merchant['BirForm']);
        $merchant['DotAuth'] = base64_encode($merchant['DotAuth']);

        // Step 2: Use TravelerID to get traveler details
        $sql = "SELECT FirstName, LastName, Email, Mobile, Address, ProfilePic, Bio FROM traveler WHERE TravelerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $travelerID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $traveler = $result->fetch_assoc();
            $traveler['ProfilePic'] = base64_encode($traveler['ProfilePic']); // Encode image for display

            // Merge traveler details and merchant information
            $traveler = array_merge($traveler, $merchant);

            echo json_encode($traveler);
        } else {
            echo json_encode(['message' => 'Traveler not found']);
        }
    } else {
        echo json_encode(['message' => 'Merchant not found']);
    }

    $stmt->close();
}
?>
