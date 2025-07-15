<?php
require_once 'session_check.php';
require_once 'notification_crud.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get current user ID from session
$currentUserId = $_SESSION['user_id'] ?? null;

if (!$currentUserId) {
    echo json_encode(['error' => 'No user ID found', 'success' => false]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_notifications';

$crud = new NotificationCRUD();

switch ($action) {
    case 'get_notifications':
        $notifications = $crud->getNotificationsForUser($currentUserId, 10);
        $unreadCount = $crud->getNotificationCount($currentUserId, true);
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'userId' => $currentUserId
        ]);
        break;
        
    case 'mark_as_read':
        $notificationId = $_POST['notificationId'] ?? null;
        if ($notificationId) {
            $success = $crud->updateNotification($notificationId, ['isRead' => true]);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No notification ID']);
        }
        break;
        
    case 'mark_all_as_read':
        $success = $crud->markAllAsRead($currentUserId);
        echo json_encode(['success' => $success]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        break;
}
?> 