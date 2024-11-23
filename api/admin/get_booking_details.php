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
        b.ProofOfPayment AS ProofOfPayment,
        b.PayoutStatus AS PayoutStatus,
        b.BookingStatus AS BookingStatus,
        b.paymentTransactionID AS paymentGCashTransactionID,
        b.Subtotal AS Subtotal,
        b.VAT AS VAT,
        b.bookingType AS BookingType,
        b.PayoutAmount AS PayoutAmount,
        b.MerchantID AS MerchantID,
        b.TravelerID AS TravelerID,
        b.RefundReason AS RefundReason,
        b.RefundStatus AS RefundStatus,
        b.RoomBookingID AS RoomBookingID,
        b.TransportationBookingID AS TransportationBookingID,
        t.Mobile AS travelerMobile,
        t.FirstName AS TravelerFirstName,
        t.LastName AS TravelerLastName,
        t.Username AS TravelerUsername,
        t.Email AS TravelerEmail,
        m.BusinessName AS MerchantBusinessName,
        m.Email AS MerchantEmail,
        m.Contact AS MerchantContact,
        m.BusinessType AS MerchantBusinessType
    FROM booking b
    JOIN traveler t ON b.TravelerID = t.TravelerID
    JOIN merchant m ON b.MerchantID = m.MerchantID
    WHERE b.BookingID = ?";
$stmt = $conn->prepare($bookingQuery);
$stmt->bind_param("i", $bookingID);
$stmt->execute();
$result = $stmt->get_result();
$bookingDetails = $result->fetch_assoc();

$bookingDetails['ProofOfPayment'] = base64_encode($bookingDetails['ProofOfPayment']);
$inclusions = [];
$viewDetails = null;
$imageFiles = [];

if (!is_null($bookingDetails['RoomBookingID'])) {
    $roomBookingQuery = "
        SELECT 
            rb.CheckInDate AS CheckIn,
            rb.CheckOutDate AS CheckOut,
            r.RoomName AS RoomName,
            r.RoomID AS RoomID,
            r.MerchantID AS MerchantID
        FROM room_booking rb
        JOIN rooms r ON rb.RoomID = r.RoomID
        WHERE rb.RoomBookingID = ?";
    $roomStmt = $conn->prepare($roomBookingQuery);
    $roomStmt->bind_param("i", $bookingDetails['RoomBookingID']);
    $roomStmt->execute();
    $roomResult = $roomStmt->get_result();
    $listingDetails = $roomResult->fetch_assoc();

    $inclusionQuery = "
        SELECT i.InclusionName AS InclusionName
        FROM room_inclusions ri
        JOIN inclusions i ON ri.InclusionID = i.InclusionID
        WHERE ri.RoomID = ?";
    $inclusionStmt = $conn->prepare($inclusionQuery);
    $inclusionStmt->bind_param("i", $listingDetails['RoomID']);
    $inclusionStmt->execute();
    $inclusionResult = $inclusionStmt->get_result();
    while ($inclusion = $inclusionResult->fetch_assoc()) {
        $inclusions[] = $inclusion['InclusionName'];
    }

    $viewQuery = "
        SELECT v.ViewName AS ViewName
        FROM room_view rv
        JOIN views v ON rv.ViewID = v.ViewID
        WHERE rv.RoomID = ?";
    $viewStmt = $conn->prepare($viewQuery);
    $viewStmt->bind_param("i", $listingDetails['RoomID']);
    $viewStmt->execute();
    $viewResult = $viewStmt->get_result();
    $viewDetails = [];
    while ($view = $viewResult->fetch_assoc()) {
        $viewDetails[] = $view['ViewName'];
    }

    $roomImageQuery = "
        SELECT ImageFile
        FROM room_gallery
        WHERE RoomID = ?";
    $roomImageStmt = $conn->prepare($roomImageQuery);
    $roomImageStmt->bind_param("i", $listingDetails['RoomID']);
    $roomImageStmt->execute();
    $roomImageResult = $roomImageStmt->get_result();
    while ($image = $roomImageResult->fetch_assoc()) {
        $imageFiles[] = base64_encode($image['ImageFile']);
    }
} else if (!is_null($bookingDetails['TransportationBookingID'])) {
    $transportBookingQuery = "
        SELECT 
            tb.PickupDateTime AS PickupDateTime,
            tb.DropoffDateTime AS DropoffDateTime,
            tb.PickupLocation AS PickupLocation,
            tb.DropoffLocation AS DropoffLocation,
            t.VehicleName AS VehicleName,
            t.TransportationID AS TransportationID
        FROM transportation_booking tb
        JOIN transportations t ON tb.TransportationID = t.TransportationID
        WHERE tb.TransportationBookingID = ?";
    $transportStmt = $conn->prepare($transportBookingQuery);
    $transportStmt->bind_param("i", $bookingDetails['TransportationBookingID']);
    $transportStmt->execute();
    $transportResult = $transportStmt->get_result();
    $listingDetails = $transportResult->fetch_assoc();

    $transportImageQuery = "
        SELECT ImageFile
        FROM transportation_gallery
        WHERE TransportationID = ?";
    $transportImageStmt = $conn->prepare($transportImageQuery);
    $transportImageStmt->bind_param("i", $listingDetails['TransportationID']);
    $transportImageStmt->execute();
    $transportImageResult = $transportImageStmt->get_result();
    while ($image = $transportImageResult->fetch_assoc()) {
        $imageFiles[] = base64_encode($image['ImageFile']);
    }
}

$response = [
    "bookingDetails" => $bookingDetails,
    "listingDetails" => $listingDetails,
    "inclusions" => $inclusions,
    "viewDetails" => $viewDetails,
    "imageFiles" => $imageFiles
];

echo json_encode($response);
?>
