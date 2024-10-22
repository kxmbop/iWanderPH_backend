<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include '../../../db.php';
include '../encryption.php';
$key = '123456';

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if (isset($_GET['chatSessionId'])) {
    $chatSessionId = intval($_GET['chatSessionId']);
    
    $query = "SELECT userOne, userTwo FROM chat_session WHERE ChatSessionID = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $chatSessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            $userOne = decrypt($session['userOne'], $key); 
            $userTwo = decrypt($session['userTwo'], $key); 

            $userOneInfo = explode(' - ', $userOne);
            $userTwoInfo = explode(' - ', $userTwo);

            if (count($userOneInfo) < 3 || count($userTwoInfo) < 3) {
                echo json_encode(['error' => 'Invalid user information']);
                exit();
            }

            $userIdOne = $userOneInfo[0];
            $userRoleOne = trim($userOneInfo[2]);  

            $userIdTwo = $userTwoInfo[0];
            $userRoleTwo = trim($userTwoInfo[2]);  

            if ($userRoleOne === 'admin' || $userRoleTwo === 'admin') {
                if ($userRoleOne === 'admin') {
                    $receiverId = $userIdTwo; 
                    $receiverRole = $userRoleTwo; 
                } else {
                    $receiverId = $userIdOne; 
                    $receiverRole = $userRoleOne; 
                }
            } else {
                $receiverId = $userIdOne; 
                $receiverRole = $userRoleOne; 
            }

            if ($receiverRole === 'traveler') { 
                $query = "SELECT 
                            TravelerID,
                            TravelerUUID,
                            FirstName,
                            LastName,
                            Email,
                            Mobile,
                            Address,
                            Bio,
                            Username,
                            isMerchant,
                            isDeactivated,
                            isSuspended,
                            isBanned 
                          FROM 
                            traveler 
                          WHERE 
                            TravelerID = ?";
            } else if ($receiverRole === 'merchant') { 
                $query = "SELECT 
                            MerchantID,
                            MerchantUUID,
                            businessName,
                            Email,
                            Contact,
                            Address,
                            isApproved,
                            BusinessType,
                            TravelerID 
                          FROM 
                            merchant 
                          WHERE 
                            MerchantID = ?";
            } else {
                echo json_encode(['error' => 'Invalid user role']);
                exit();
            }

            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("i", $receiverId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $receiverInfo = $result->fetch_assoc(); 

                    if ($receiverInfo) {
                        echo json_encode(['receiver' => $receiverInfo]); 
                    } else {
                        echo json_encode(['error' => 'Receiver not found']); 
                    }
                } else {
                    echo json_encode(['error' => 'Statement execution failed: ' . $stmt->error]); 
                }
            } else {
                echo json_encode(['error' => 'Failed to prepare statement']); // Handle preparation errors
            }
        } else {
            echo json_encode(['error' => 'Chat session not found']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare statement']);
    }
} else {
    echo json_encode(['error' => 'Chat Session ID is required']);
}

$conn->close();
?>
