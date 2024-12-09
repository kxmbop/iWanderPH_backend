<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../db.php';

$key = "123456"; 
$response = [];

// Fetch token and status from query parameters
$token = $_GET['token'] ?? null;
$status = $_GET['status'] ?? null;

if (!$token || !$status) {
    echo json_encode(['error' => 'Token and status are required']);
    exit;
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $travelerID = $decoded->TravelerID;
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}

// Fetch MerchantID for the given TravelerID
$merchantSql = "SELECT MerchantID FROM merchant WHERE TravelerID = ?";
$merchantStmt = $conn->prepare($merchantSql);
$merchantStmt->bind_param("i", $travelerID);
$merchantStmt->execute();
$merchantResult = $merchantStmt->get_result();
$merchantRow = $merchantResult->fetch_assoc();

if (!$merchantRow) {
    echo json_encode(['error' => 'No merchant found for the given traveler']);
    exit;
}

$merchantID = $merchantRow['MerchantID'];

// SQL Query to fetch all relevant booking, review, and review image information
$sql = "
SELECT 
    b.bookingID,
    b.bookingDate,
    b.bookingType,
    b.bookingStatus,
    b.paymentTransactionID,
    b.paymentStatus,
    b.payOnSite,
    b.subtotal,
    b.VAT,
    b.payoutAmount,
    b.totalAmount,
    b.payoutTransactionID,
    b.payoutStatus,
    b.refundReason,
    b.refundStatus,
    b.refundTransactionID,
    b.roomBookingID,
    b.transportationBookingID,
    COUNT(bul.bookingID) AS bulCount,
    
    -- Room Booking details
    rb.RoomBookingID,
    rb.CheckInDate,
    rb.CheckOutDate,
    rb.SpecialRequest,
    
    -- Room details
    r.RoomID,
    r.RoomName,
    r.RoomQuantity,
    r.RoomRate,
    r.GuestPerRoom,
    
    -- Room Inclusions
    GROUP_CONCAT(DISTINCT inc.InclusionName SEPARATOR ', ') AS RoomInclusions,
    
    -- Room Views
    GROUP_CONCAT(DISTINCT v.ViewName SEPARATOR ', ') AS RoomViews,
    
    -- Transportation Booking details
    tb.TransportationBookingID,
    tb.PickupDateTime,
    tb.DropoffDateTime,
    tb.PickupLocation,
    tb.DropoffLocation,
    
    -- Transportation details
    tr.TransportationID,
    tr.VehicleName,
    tr.Model,
    tr.Brand,
    tr.Capacity,
    tr.RentalPrice,
    
    -- Traveler info
    t.Username AS TravelerUsername,
    t.FirstName AS TravelerFirstName,
    t.LastName AS TravelerLastName,
    
    -- Merchant info
    m.BusinessName AS MerchantBusinessName,
    
    -- Reviews
    rev.reviewID,
    rev.reviewComment,
    rev.reviewRating,
    rev.privacy AS reviewPrivacy,
    rev.createdAt AS reviewCreatedAt,
    
    -- Review Images (Will convert to base64 in PHP)
    GROUP_CONCAT(DISTINCT ri_rev.image SEPARATOR ', ') AS reviewImages
FROM booking b
LEFT JOIN booking_update_log bul ON b.bookingID = bul.bookingID
LEFT JOIN room_booking rb ON b.roomBookingID = rb.RoomBookingID
LEFT JOIN rooms r ON rb.RoomID = r.RoomID
LEFT JOIN room_inclusions ri ON r.RoomID = ri.RoomID
LEFT JOIN inclusions inc ON ri.InclusionID = inc.InclusionID
LEFT JOIN room_view rv ON r.RoomID = rv.RoomID
LEFT JOIN views v ON rv.ViewID = v.ViewID

LEFT JOIN transportation_booking tb ON b.transportationBookingID = tb.TransportationBookingID
LEFT JOIN transportations tr ON tb.TransportationID = tr.TransportationID

JOIN traveler t ON b.TravelerID = t.TravelerID
JOIN merchant m ON b.MerchantID = m.MerchantID

LEFT JOIN reviews rev ON b.bookingID = rev.bookingID
LEFT JOIN review_images ri_rev ON rev.reviewID = ri_rev.reviewID

WHERE b.MerchantID = ? 
AND b.bookingStatus = ?
GROUP BY b.bookingID, rev.reviewID
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $merchantID, $status);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];

while ($row = $result->fetch_assoc()) {
    $booking = $row;
    $bookingID = $row['bookingID'];

    // Fetch images for each review
    $reviewID = $row['reviewID'];
    if ($reviewID) {
        $imageSql = "SELECT Image FROM review_images WHERE reviewID = ?";
        $imageStmt = $conn->prepare($imageSql);
        $imageStmt->bind_param("i", $reviewID);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();

        $reviewImages = [];
        while ($imageRow = $imageResult->fetch_assoc()) {
            $base64Image = base64_encode($imageRow['Image']);
            $reviewImages[] = $base64Image;
        }
        $booking['reviewImages'] = $reviewImages;
    } else {
        $booking['reviewImages'] = [];
    }

    $bookings[] = $booking;
}

if (empty($bookings)) {
    echo json_encode(['message' => 'No bookings found with the given status']);
} else {
    echo json_encode($bookings);
}

$conn->close();
?>
