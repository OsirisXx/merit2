<?php
/**
 * Store FCM Token
 * Simple endpoint for mobile apps to register their FCM tokens
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['userId']) || !isset($input['fcmToken'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing userId or fcmToken']);
    exit;
}

$userId = $input['userId'];
$fcmToken = $input['fcmToken'];

try {
    // Include mobile notifications helper
    require_once 'simple_mobile_notifications.php';
    
    $mobileNotifications = new SimpleMobileNotifications();
    $success = $mobileNotifications->storeFCMToken($userId, $fcmToken);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'FCM token stored successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to store FCM token']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?> 