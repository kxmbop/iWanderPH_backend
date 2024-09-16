<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

$bookingID = $_GET['bookingId'];

$bookingQuery = "
    SELECT 
        b.BookingID AS BookingID,
        b.PaymentStatus AS PaymentStatus,
        b.TotalAmount AS TotalAmount,
        b.payoutStatus AS PayoutStatus,
        b.BookingStatus AS BookingStatus,
        b.CheckIn AS CheckIn,
        b.CheckOut AS CheckOut,
        b.Subtotal AS Subtotal,
        b.VAT AS VAT,
        b.PayoutAmount AS PayoutAmount,
        b.Duration AS Duration,
        b.ListingID AS ListingID,
        b.ListingType AS ListingType,
        b.MerchantID AS MerchantID,
        b.TravelerID AS TravelerID,
        t.FirstName AS TravelerFirstName,
        t.LastName AS TravelerLastName,
        t.Username AS TravelerUsername,
        t.Mobile AS TravelerMobile,
        t.Email AS TravelerEmail,
        m.BusinessName AS MerchantBusinessName,
        m.Email AS MerchantEmail,
        m.Contact AS MerchantContact,
        m.BusinessType AS MerchantBusinessType
    FROM bookings b
    JOIN traveler t ON b.TravelerID = t.TravelerID
    JOIN merchant m ON b.MerchantID = m.MerchantID
    WHERE b.BookingID = ?";
$stmt = $conn->prepare($bookingQuery);
$stmt->bind_param("i", $bookingID);
$stmt->execute();
$result = $stmt->get_result();
$bookingDetails = $result->fetch_assoc();

// room or transportation based on listingType
if ($bookingDetails['ListingType'] == 'room') {
    $listingQuery = "
        SELECT r.RoomName AS RoomName, r.MerchantID AS MerchantID
        FROM rooms r
        WHERE r.RoomID = ?";
} else {
    $listingQuery = "
        SELECT t.VehicleName AS VehicleName
        FROM transportations t
        WHERE t.TransportationID = ?";
}
$listingStmt = $conn->prepare($listingQuery);
$listingStmt->bind_param("i", $bookingDetails['ListingID']);
$listingStmt->execute();
$listingResult = $listingStmt->get_result();
$listingDetails = $listingResult->fetch_assoc();

//room inclusions
$inclusionQuery = "
    SELECT i.InclusionName AS InclusionName
    FROM room_inclusions ri
    JOIN inclusions i ON ri.InclusionID = i.InclusionID
    WHERE ri.RoomID = ?";
$inclusionStmt = $conn->prepare($inclusionQuery);
$inclusionStmt->bind_param("i", $bookingDetails['ListingID']);
$inclusionStmt->execute();
$inclusionResult = $inclusionStmt->get_result();
$inclusions = [];
while ($inclusion = $inclusionResult->fetch_assoc()) {
    $inclusions[] = $inclusion['InclusionName'];
}

//room view
$viewQuery = "
    SELECT v.ViewName AS ViewName
    FROM room_view rv
    JOIN views v ON rv.ViewID = v.ViewID
    WHERE rv.RoomID = ?";
$viewStmt = $conn->prepare($viewQuery);
$viewStmt->bind_param("i", $bookingDetails['ListingID']);
$viewStmt->execute();
$viewResult = $viewStmt->get_result();
$viewDetails = $viewResult->fetch_assoc();

$response = [
    "bookingDetails" => $bookingDetails,
    "listingDetails" => $listingDetails,
    "inclusions" => $inclusions,
    "viewDetails" => $viewDetails
];

echo json_encode($response);
?>
