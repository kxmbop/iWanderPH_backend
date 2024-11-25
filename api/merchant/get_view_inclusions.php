<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../db.php';

try {
    // Fetch views
    $query_views = "SELECT ViewID, ViewName FROM views";
    $stmt_views = $conn->prepare($query_views);
    $stmt_views->execute();
    $result_views = $stmt_views->get_result();

    $views = [];
    while ($row = $result_views->fetch_assoc()) {
        $views[] = $row;
    }

    // Fetch inclusions
    $query_inclusions = "SELECT InclusionID, InclusionName, InclusionDescription FROM inclusions";
    $stmt_inclusions = $conn->prepare($query_inclusions);
    $stmt_inclusions->execute();
    $result_inclusions = $stmt_inclusions->get_result();

    $inclusions = [];
    while ($row = $result_inclusions->fetch_assoc()) {
        $inclusions[] = $row;
    }

    // Return both views and inclusions
    echo json_encode([
        'success' => true,
        'data' => [
            'views' => $views,
            'inclusions' => $inclusions
        ]
    ]);
    
    $stmt_views->close();
    $stmt_inclusions->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
