<?php
/**
 * DIRECT NOTIFICATION HANDLER - GUARANTEED TO WORK
 * Simple, direct notification system for ProgTracking
 */

require_once 'session_check.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

$action = $data['action'] ?? null;
$userId = $data['userId'] ?? null;
$userName = $data['userName'] ?? 'User';

if (!$action || !$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing action or userId']);
    exit;
}

try {
    $success = false;
    
    if ($action === 'adoption_started') {
        // 1. Send notification to USER
        $userNotification = [
            'id' => 'notif_' . uniqid() . '.' . mt_rand(10000000, 99999999),
            'userId' => $userId,
            'title' => '🎉 Adoption Started',
            'message' => "Congratulations! Your adoption process has been started successfully. Begin with Step 1 when you're ready!",
            'type' => 'adoption',
            'status' => 'process_started',
            'timestamp' => time() * 1000,
            'isRead' => false,
            'processType' => 'adoption',
            'notificationType' => 'process_initiated',
            'icon' => '👶',
            'data' => [
                'status' => 'process_started',
                'action' => 'adoption_started'
            ]
        ];
        
        // Store user notification in Firebase AND JSON
        $userSuccess = sendToFirebase('notifications', $userNotification);
        addToNotificationsJson($userNotification);
        
        // 2. Send notification to ADMIN
        $adminUsers = ['h8qq0E8avWO74cqS2Goy1wtENJh1']; // Direct admin ID
        foreach ($adminUsers as $adminId) {
            $adminNotification = [
                'id' => 'notif_admin_' . uniqid() . '.' . mt_rand(10000000, 99999999),
                'userId' => $adminId,
                'title' => '👶 New Adoption Process Started',
                'message' => "$userName has started the adoption process. Please monitor their progress.",
                'type' => 'adoption',
                'status' => 'admin_alert',
                'timestamp' => time() * 1000,
                'isRead' => false,
                'processType' => 'adoption',
                'notificationType' => 'process_initiated',
                'isAdminNotification' => true,
                'targetUserId' => $userId,
                'targetUserName' => $userName,
                'icon' => '👶',
                'data' => [
                    'status' => 'admin_alert',
                    'action' => 'adoption_started',
                    'targetUserId' => $userId,
                    'targetUserName' => $userName
                ]
            ];
            
            sendToFirebase('notifications', $adminNotification);
            addToNotificationsJson($adminNotification);
        }
        
        $success = $userSuccess;
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Notifications sent successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send notifications']);
    }

} catch (Exception $e) {
    error_log("Direct notification handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
}

/**
 * Send notification directly to Firebase
 */
function sendToFirebase($collection, $data) {
    $projectId = 'ally-user';
    $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/$collection";
    
    $firestoreData = [
        'fields' => convertToFirestoreFields($data)
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($firestoreData),
            'timeout' => 30
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        error_log("Failed to send notification to Firebase: $collection");
        return false;
    }
    
    return true;
}

/**
 * Convert PHP array to Firestore fields format
 */
function convertToFirestoreFields($data) {
    $fields = [];
    
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $fields[$key] = ['stringValue' => $value];
        } elseif (is_int($value)) {
            $fields[$key] = ['integerValue' => (string)$value];
        } elseif (is_float($value)) {
            $fields[$key] = ['doubleValue' => $value];
        } elseif (is_bool($value)) {
            $fields[$key] = ['booleanValue' => $value];
        } elseif (is_array($value)) {
            $fields[$key] = ['mapValue' => ['fields' => convertToFirestoreFields($value)]];
        } else {
            $fields[$key] = ['stringValue' => (string)$value];
        }
    }
    
    return $fields;
}

/**
 * Add notification to notifications.json file (for navbar display)
 */
function addToNotificationsJson($notification) {
    try {
        $jsonFile = __DIR__ . '/notifications.json';
        
        // Load existing notifications
        $notifications = [];
        if (file_exists($jsonFile)) {
            $existingData = file_get_contents($jsonFile);
            if ($existingData) {
                $notifications = json_decode($existingData, true) ?: [];
            }
        }
        
        // Add the new notification
        $notifications[] = $notification;
        
        // Keep only the most recent 100 notifications
        if (count($notifications) > 100) {
            $notifications = array_slice($notifications, -100);
        }
        
        // Save back to file
        $jsonData = json_encode($notifications, JSON_PRETTY_PRINT);
        $result = file_put_contents($jsonFile, $jsonData, LOCK_EX);
        
        if ($result === false) {
            error_log("Failed to write to notifications.json");
            return false;
        }
        
        error_log("✅ Successfully added notification to JSON file");
        return true;
        
    } catch (Exception $e) {
        error_log("Error adding notification to JSON: " . $e->getMessage());
        return false;
    }
}
?> 