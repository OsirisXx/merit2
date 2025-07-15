<?php
// Test Step 10 Congratulatory Notification
require_once 'super_simple_notifications.php';

echo "<h1>🎊 Testing Step 10 Congratulatory Notification 🎊</h1>";

// Create notification system instance
$notifications = new SuperSimpleNotifications();

// Test user ID (you can change this to test with different users)
$testUserId = 'EcWvBKf3zvQsgEE5Tl99eErnblD3';

echo "<h2>Testing Regular Step Completion (Step 8)</h2>";
$result1 = $notifications->sendAdoptionNotification($testUserId, 'step_completed', 8, [
    'userName' => 'Test User',
    'userEmail' => 'test@example.com'
]);
echo $result1 ? "✅ Regular step notification sent successfully<br>" : "❌ Failed to send regular step notification<br>";

echo "<h2>Testing Step 10 Completion (CONGRATULATORY)</h2>";
$result2 = $notifications->sendAdoptionNotification($testUserId, 'step_completed', 10, [
    'userName' => 'Test User',
    'userEmail' => 'test@example.com'
]);
echo $result2 ? "✅ Step 10 congratulatory notification sent successfully<br>" : "❌ Failed to send Step 10 notification<br>";

echo "<h2>Recent Notifications for User</h2>";
$recentNotifications = $notifications->getNotifications($testUserId, 5);

foreach ($recentNotifications as $notification) {
    $icon = $notification['icon'] ?? '📋';
    $title = $notification['title'] ?? 'No Title';
    $message = $notification['message'] ?? 'No Message';
    $timestamp = date('Y-m-d H:i:s', ($notification['timestamp'] ?? 0) / 1000);
    
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>{$icon} {$title}</strong><br>";
    echo "<em>{$timestamp}</em><br>";
    echo "<p>{$message}</p>";
    echo "</div>";
}

echo "<h2>Recent Admin Notifications</h2>";
$adminNotifications = $notifications->getNotifications('h8qq0E8avWO74cqS2Goy1wtENJh1', 5);

foreach ($adminNotifications as $notification) {
    $icon = $notification['icon'] ?? '📋';
    $title = $notification['title'] ?? 'No Title';
    $message = $notification['message'] ?? 'No Message';
    $timestamp = date('Y-m-d H:i:s', ($notification['timestamp'] ?? 0) / 1000);
    
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #f8f9fa;'>";
    echo "<strong>{$icon} {$title}</strong><br>";
    echo "<em>{$timestamp}</em><br>";
    echo "<p>{$message}</p>";
    echo "</div>";
}

echo "<p><a href='ProgTracking.php'>← Back to Progress Tracking</a></p>";
?> 