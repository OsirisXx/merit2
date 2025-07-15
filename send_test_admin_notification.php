<?php
require_once 'super_simple_notifications.php';

$notifications = new SuperSimpleNotifications();

$result = $notifications->sendAdminNotification(
    'urgent_fix',
    'NOTIFICATION ROLE FIX REQUIRED',
    'This admin notification is currently visible to ALL users. To fix: Set role=admin in Firestore for user h8qq0E8avWO74cqS2Goy1wtENJh1',
    [
        'urgent' => true,
        'fix_required' => true,
        'isAdminNotification' => true,
        'targetRole' => 'admin',
        'notificationSource' => 'admin_system'
    ]
);

echo $result ? "✅ Admin notification sent successfully" : "❌ Failed to send admin notification";
echo "\n";
?> 