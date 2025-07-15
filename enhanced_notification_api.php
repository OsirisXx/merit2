<?php
session_start();

/**
 * ENHANCED NOTIFICATION API V2 - FIXED FOR JSON SYSTEM
 * API endpoint for notification system operations - now works with notifications.json
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'User not authenticated'
    ]);
    exit;
}

$userId = $_SESSION['uid'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_notifications':
            $targetUserId = $_GET['userId'] ?? $userId;
            $limit = (int)($_GET['limit'] ?? 20);
            
            // Security check - users can only get their own notifications unless admin
            if ($targetUserId !== $userId && !isAdmin($userId)) {
                throw new Exception('Access denied');
            }
            
            $notifications = getNotificationsFromJSON($targetUserId, $limit);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications),
                'unreadCount' => count(array_filter($notifications, function($n) { 
                    return !($n['read'] ?? false) && !($n['isRead'] ?? false); 
                }))
            ]);
            break;
            
        case 'mark_as_read':
            $notificationId = $_POST['notificationId'] ?? '';
            
            if (empty($notificationId)) {
                throw new Exception('Notification ID required');
            }
            
            $success = markNotificationAsReadInJSON($notificationId);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notification marked as read' : 'Failed to mark as read'
            ]);
            break;
            
        case 'send_test_notification':
            $targetUserId = $_POST['userId'] ?? $userId;
            
            // Security check - users can only send test notifications to themselves unless admin
            if ($targetUserId !== $userId && !isAdmin($userId)) {
                throw new Exception('Access denied');
            }
            
            $success = sendTestNotificationToJSON($targetUserId);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Test notification sent successfully' : 'Failed to send test notification'
            ]);
            break;
            
        case 'send_donation_notification':
            $targetUserId = $_POST['userId'] ?? $userId;
            $donationType = $_POST['donationType'] ?? 'general';
            $status = $_POST['status'] ?? 'submitted';
            
            // Security check for admin actions
            if ($targetUserId !== $userId && !isAdmin($userId)) {
                throw new Exception('Access denied');
            }
            
            $success = $notificationSystem->sendDonationNotification($targetUserId, $donationType, $status, [
                'source' => 'api',
                'triggeredBy' => $userId
            ]);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Donation notification sent' : 'Failed to send donation notification'
            ]);
            break;
            
        case 'send_adoption_notification':
            $targetUserId = $_POST['userId'] ?? $userId;
            $status = $_POST['status'] ?? 'initiated';
            $stepNumber = $_POST['stepNumber'] ?? null;
            
            // Security check for admin actions
            if ($targetUserId !== $userId && !isAdmin($userId)) {
                throw new Exception('Access denied');
            }
            
            $success = $notificationSystem->sendAdoptionNotification($targetUserId, $status, $stepNumber, [
                'source' => 'api',
                'triggeredBy' => $userId
            ]);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Adoption notification sent' : 'Failed to send adoption notification'
            ]);
            break;
            
        case 'send_appointment_notification':
            $targetUserId = $_POST['userId'] ?? $userId;
            $status = $_POST['status'] ?? 'scheduled';
            $appointmentDate = $_POST['appointmentDate'] ?? null;
            
            // Security check for admin actions
            if ($targetUserId !== $userId && !isAdmin($userId)) {
                throw new Exception('Access denied');
            }
            
            $success = $notificationSystem->sendAppointmentNotification($targetUserId, $status, $appointmentDate, [
                'source' => 'api',
                'triggeredBy' => $userId
            ]);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Appointment notification sent' : 'Failed to send appointment notification'
            ]);
            break;
            
        case 'get_notification_count':
            $targetUserId = $_GET['userId'] ?? $userId;
            
            // Security check
            if ($targetUserId !== $userId && !isAdmin($userId)) {
                throw new Exception('Access denied');
            }
            
            $notifications = getNotificationsFromJSON($targetUserId, 50);
            $unreadCount = count(array_filter($notifications, function($n) { 
                return !($n['read'] ?? false) && !($n['isRead'] ?? false); 
            }));
            
            echo json_encode([
                'success' => true,
                'totalCount' => count($notifications),
                'unreadCount' => $unreadCount
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Get notifications from JSON file for a specific user
 */
function getNotificationsFromJSON($userId, $limit = 20) {
    $filename = 'notifications.json';
    
    if (!file_exists($filename)) {
        return [];
    }
    
    $content = file_get_contents($filename);
    if (!$content) {
        return [];
    }
    
    $allNotifications = json_decode($content, true);
    if (!$allNotifications) {
        return [];
    }
    
    // Filter notifications for this user
    $userNotifications = array_filter($allNotifications, function($notification) use ($userId) {
        return ($notification['userId'] ?? $notification['targetUserId'] ?? '') === $userId;
    });
    
    // Sort by timestamp (newest first)
    usort($userNotifications, function($a, $b) {
        $timestampA = $a['timestamp'] ?? 0;
        $timestampB = $b['timestamp'] ?? 0;
        return $timestampB - $timestampA;
    });
    
    // Limit results
    return array_slice($userNotifications, 0, $limit);
}

/**
 * Mark notification as read in JSON file
 */
function markNotificationAsReadInJSON($notificationId) {
    $filename = 'notifications.json';
    
    if (!file_exists($filename)) {
        return false;
    }
    
    $content = file_get_contents($filename);
    if (!$content) {
        return false;
    }
    
    $allNotifications = json_decode($content, true);
    if (!$allNotifications) {
        return false;
    }
    
    // Find and update the notification
    $updated = false;
    for ($i = 0; $i < count($allNotifications); $i++) {
        if (($allNotifications[$i]['id'] ?? '') === $notificationId) {
            $allNotifications[$i]['read'] = true;
            $allNotifications[$i]['isRead'] = true;
            $allNotifications[$i]['readAt'] = time() * 1000;
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        $result = file_put_contents($filename, json_encode($allNotifications, JSON_PRETTY_PRINT));
        return $result !== false;
    }
    
    return false;
}

/**
 * Send test notification to JSON file
 */
function sendTestNotificationToJSON($userId) {
    $notificationData = [
        'id' => 'test_' . time() . '_' . substr($userId, 0, 8),
        'userId' => $userId,
        'targetUserId' => $userId,
        'title' => 'Test Notification',
        'message' => 'This is a test notification sent at ' . date('Y-m-d H:i:s'),
        'type' => 'system',
        'read' => false,
        'isRead' => false,
        'timestamp' => time() * 1000,
        'source' => 'api_test'
    ];
    
    return addNotificationToJSON($notificationData);
}

/**
 * Add notification to JSON file
 */
function addNotificationToJSON($notificationData) {
    $filename = 'notifications.json';
    
    // Read existing notifications
    $notifications = [];
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        if ($content) {
            $notifications = json_decode($content, true) ?: [];
        }
    }
    
    // Add new notification
    $notifications[] = $notificationData;
    
    // Write back to file
    $result = file_put_contents($filename, json_encode($notifications, JSON_PRETTY_PRINT));
    
    return $result !== false;
}

/**
 * Check if user is admin
 */
function isAdmin($userId) {
    // Simple admin check - in production, query the users collection
    // For now, check session role
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?> 