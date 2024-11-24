<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

try {
    // Fetch transportation for the provided MerchantID
    $query = "SELECT t.TransportationID, t.VehicleName, t.Model, t.Brand, t.Capacity, t.RentalPrice, 
                     tg.ImageFile AS TransportationImage
              FROM transportations t
              LEFT JOIN transportation_gallery tg ON t.TransportationID = tg.TransportationID";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $transportations = [];
    while ($row = $result->fetch_assoc()) {
        // Check if the image file exists
        if (!empty($row['TransportationImage'])) {
            $imagePath = '../../path/to/your/images/' . $row['TransportationImage']; // Update this path accordingly
            if (file_exists($imagePath)) {
                // Read the image file and encode it to base64
                $imageData = file_get_contents($imagePath);
                $base64Image = base64_encode($imageData);
                $row['TransportationImage'] = 'data:image/jpeg;base64,' . $base64Image; // Assuming the image is JPEG, adjust the MIME type if needed
            } else {
                $row['TransportationImage'] = null; // If the image file doesn't exist, set it to null
            }
        } else {
            $row['TransportationImage'] = null; // If no image file is present, set it to null
        }
        $transportations[] = $row;
    }

    // Return transportation data or an appropriate message
    if (!empty($transportations)) {
        echo json_encode(['success' => true, 'data' => $transportations]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No transportation found for the given MerchantID']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
