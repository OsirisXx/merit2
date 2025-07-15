<?php
require_once 'session_check.php';
require_once 'notification_crud.php';

// Initialize CRUD
$notificationCRUD = new NotificationCRUD();

// Get current user ID - we'll try multiple sources
$currentUserId = null;

// Try to get from session first (PHP session)
if (isset($_SESSION['user_id'])) {
    $currentUserId = $_SESSION['user_id'];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $notificationId = $_POST['notificationId'] ?? null;
    
    switch ($action) {
        case 'markAsRead':
            if ($notificationId) {
                $success = $notificationCRUD->updateNotification($notificationId, ['isRead' => true]);
                echo json_encode(['success' => $success]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No notification ID']);
            }
            break;
            
        case 'markAllAsRead':
            if ($currentUserId) {
                $success = $notificationCRUD->markAllAsRead($currentUserId);
                echo json_encode(['success' => $success]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No user ID']);
            }
            break;
            
        case 'delete':
            if ($notificationId) {
                $success = $notificationCRUD->deleteNotification($notificationId);
                echo json_encode(['success' => $success]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No notification ID']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }
    exit;
}

// Get notifications for display
$notifications = [];
$userNotificationCount = 0;
$unreadCount = 0;

if ($currentUserId) {
    $notifications = $notificationCRUD->getNotificationsForUser($currentUserId);
    $userNotificationCount = count($notifications);
    $unreadCount = $notificationCRUD->getNotificationCount($currentUserId, true);
}

function timeAgo($timestamp) {
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    
    return date('M j, Y', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Ally Foundation</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-title {
            color: #333;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .notification-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn, .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            background: #f0f0f0;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .filter-btn.active, .action-btn.primary {
            background: #7CB9E8;
            color: white;
        }
        
        .action-btn.danger {
            background: #dc3545;
            color: white;
        }
        
        .notification-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s ease;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background-color: #f8f9ff;
            border-left: 4px solid #7CB9E8;
        }
        
        .notification-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .icon-adoption { background: #e8f5e8; color: #4caf50; }
        .icon-donation { background: #fff3e0; color: #ff9800; }
        .icon-appointment { background: #e3f2fd; color: #2196f3; }
        .icon-matching { background: #fce4ec; color: #e91e63; }
        .icon-chat { background: #f3e5f5; color: #9c27b0; }
        .icon-system { background: #f5f5f5; color: #607d8b; }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        
        .notification-message {
            color: #666;
            margin: 0 0 8px 0;
            line-height: 1.4;
            font-size: 14px;
        }
        
        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 12px;
            color: #999;
            flex-wrap: wrap;
        }
        
        .notification-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }
        
        .notification-actions button {
            padding: 4px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .mark-read-btn {
            background: #7CB9E8;
            color: white;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">🔔 Notifications</h1>
            
            <div class="notification-stats">
                <div>
                    <strong>Total:</strong> <?php echo $userNotificationCount; ?> notifications
                    <?php if ($unreadCount > 0): ?>
                        | <strong style="color: #e91e63;">Unread:</strong> <?php echo $unreadCount; ?>
                    <?php endif; ?>
                </div>
                <?php if ($unreadCount > 0): ?>
                    <button onclick="markAllAsRead()" class="action-btn primary">Mark All as Read</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="notification-filters">
            <button class="filter-btn active" onclick="filterNotifications('all')">All</button>
            <button class="filter-btn" onclick="filterNotifications('unread')">Unread</button>
            <button class="filter-btn" onclick="filterNotifications('ADOPTION')">Adoption</button>
            <button class="filter-btn" onclick="filterNotifications('DONATION')">Donation</button>
            <button class="filter-btn" onclick="filterNotifications('APPOINTMENT')">Appointment</button>
            <button class="filter-btn" onclick="filterNotifications('MATCHING')">Matching</button>
            <button class="filter-btn" onclick="filterNotifications('SYSTEM')">System</button>
        </div>
        
        <div class="debug-info">
            <strong>Debug Info:</strong>
            User ID: <?php echo $currentUserId ?: 'Not found'; ?> |
            Session ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?> |
            Total Found: <?php echo $userNotificationCount; ?> |
            Using: PHP CRUD direct to notification_logs
        </div>
        
        <div class="notification-list" id="notificationList">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <div style="font-size: 48px; opacity: 0.5; margin-bottom: 20px;">🔔</div>
                    <h3>No notifications</h3>
                    <p>You're all caught up!</p>
                    <?php if (!$currentUserId): ?>
                        <p><em>Note: No user ID found. Please make sure you're logged in.</em></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo (!isset($notification['isRead']) || !$notification['isRead']) ? 'unread' : ''; ?>" 
                         data-id="<?php echo htmlspecialchars($notification['id']); ?>" 
                         data-type="<?php echo htmlspecialchars($notification['processType'] ?? 'SYSTEM'); ?>">
                        
                        <div class="notification-icon icon-<?php echo strtolower($notification['processType'] ?? 'system'); ?>">
                            <?php
                            $icons = [
                                'ADOPTION' => '👶',
                                'DONATION' => '📦', 
                                'APPOINTMENT' => '📅',
                                'MATCHING' => '💕',
                                'CHAT' => '💬',
                                'SYSTEM' => '🔔'
                            ];
                            echo $icons[$notification['processType'] ?? 'SYSTEM'] ?? '🔔';
                            ?>
                        </div>
                        
                        <div class="notification-content">
                            <h4 class="notification-title"><?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?></h4>
                            <p class="notification-message"><?php echo htmlspecialchars($notification['message'] ?? ''); ?></p>
                            
                            <div class="notification-meta">
                                <span class="notification-time">
                                    🕒 <?php 
                                    $timestamp = $notification['timestamp'] ?? 0;
                                    if ($timestamp > 0) {
                                        $time = (int)($timestamp / 1000); // Convert from milliseconds
                                        echo timeAgo($time);
                                    } else {
                                        echo 'Unknown time';
                                    }
                                    ?>
                                </span>
                                <span class="notification-type" style="background: #e0e0e0; color: #666; padding: 2px 8px; border-radius: 10px; font-size: 11px;">
                                    <?php echo htmlspecialchars($notification['processType'] ?? 'SYSTEM'); ?>
                                </span>
                            </div>
                            
                            <div class="notification-actions">
                                <?php if (!isset($notification['isRead']) || !$notification['isRead']): ?>
                                    <button class="mark-read-btn" onclick="markAsRead('<?php echo htmlspecialchars($notification['id']); ?>')">
                                        Mark as read
                                    </button>
                                <?php endif; ?>
                                <button class="delete-btn" onclick="deleteNotification('<?php echo htmlspecialchars($notification['id']); ?>')">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        
        function filterNotifications(filter) {
            currentFilter = filter;
            
            // Update button states
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter notifications
            const notifications = document.querySelectorAll('.notification-item');
            notifications.forEach(item => {
                const type = item.dataset.type;
                const isUnread = item.classList.contains('unread');
                
                let show = false;
                
                if (filter === 'all') {
                    show = true;
                } else if (filter === 'unread') {
                    show = isUnread;
                } else {
                    show = type === filter;
                }
                
                item.style.display = show ? 'flex' : 'none';
            });
        }
        
        async function markAsRead(notificationId) {
            try {
                const formData = new FormData();
                formData.append('action', 'markAsRead');
                formData.append('notificationId', notificationId);
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload(); // Simple refresh
                } else {
                    alert('Failed to mark as read: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        async function markAllAsRead() {
            try {
                const formData = new FormData();
                formData.append('action', 'markAllAsRead');
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload(); // Simple refresh
                } else {
                    alert('Failed to mark all as read: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        async function deleteNotification(notificationId) {
            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('notificationId', notificationId);
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload(); // Simple refresh
                } else {
                    alert('Failed to delete notification: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    </script>
</body>
</html> 