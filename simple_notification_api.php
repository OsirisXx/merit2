<?php
session_start();

/**
 * SIMPLE NOTIFICATION API - MOBILE APP COPY
 * Just handles basic notification operations
 */

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$currentUserId = $_SESSION['uid'];

try {
    require_once 'super_simple_notifications.php';
    $notificationSystem = new SuperSimpleNotifications();
    
    // Handle GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        $userId = $_GET['userId'] ?? $currentUserId;
        
        // Security check - users can only get their own notifications
        if ($userId !== $currentUserId) {
            throw new Exception('Access denied');
        }
        
        switch ($action) {
            case 'get':
                $notifications = $notificationSystem->getNotifications($userId);
                echo json_encode([
                    'success' => true,
                    'notifications' => $notifications,
                    'count' => count($notifications),
                    'unreadCount' => count(array_filter($notifications, function($n) { return !$n['isRead']; }))
                ]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    }
    
    // Handle POST requests
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'sendTest':
                $userId = $input['userId'] ?? $currentUserId;
                
                // Security check
                if ($userId !== $currentUserId) {
                    throw new Exception('Access denied');
                }
                
                $success = $notificationSystem->sendTestNotification($userId);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Test notification sent' : 'Failed to send test'
                ]);
                break;
                
            case 'markRead':
                $notificationId = $input['notificationId'] ?? '';
                $userId = $input['userId'] ?? $currentUserId;
                
                // Security check
                if ($userId !== $currentUserId) {
                    throw new Exception('Access denied');
                }
                
                if (empty($notificationId)) {
                    throw new Exception('Notification ID required');
                }
                
                $success = $notificationSystem->markAsRead($notificationId);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Marked as read' : 'Failed to mark as read'
                ]);
                break;
                
            case 'markAllRead':
                $userId = $input['userId'] ?? $currentUserId;
                
                // Security check
                if ($userId !== $currentUserId) {
                    throw new Exception('Access denied');
                }
                
                $success = $notificationSystem->markAllAsRead($userId);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'All notifications marked as read' : 'Failed to mark all as read'
                ]);
                break;
                
            case 'sendDonation':
                $userId = $input['userId'] ?? $currentUserId;
                $donationType = $input['donationType'] ?? 'general';
                $status = $input['status'] ?? 'submitted';
                
                // Security check
                if ($userId !== $currentUserId) {
                    throw new Exception('Access denied');
                }
                
                $success = $notificationSystem->sendDonationNotification($userId, $donationType, $status);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Donation notification sent' : 'Failed to send donation notification'
                ]);
                break;
                
            case 'sendAppointment':
                $userId = $input['userId'] ?? $currentUserId;
                $status = $input['status'] ?? 'scheduled';
                $appointmentDate = $input['appointmentDate'] ?? null;
                
                // Security check
                if ($userId !== $currentUserId) {
                    throw new Exception('Access denied');
                }
                
                $success = $notificationSystem->sendAppointmentNotification($userId, $status, $appointmentDate);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Appointment notification sent' : 'Failed to send appointment notification'
                ]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    }
    
    else {
        throw new Exception('Invalid request method');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 