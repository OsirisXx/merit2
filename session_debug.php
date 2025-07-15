<?php
session_start();

// Debug session information
echo "<!DOCTYPE html><html><head><title>Session Debug</title>";
echo "<style>body{font-family:Arial;padding:20px;} .info{background:#e3f2fd;padding:10px;margin:10px 0;border-radius:5px;}</style>";
echo "</head><body>";
echo "<h2>🔍 Session Debug Information</h2>";

echo "<div class='info'>";
echo "<h3>Session Status</h3>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '❌ Inactive') . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Session Data</h3>";
if (empty($_SESSION)) {
    echo "❌ No session data found - User is NOT logged in<br>";
    echo "<a href='Signin.php'>Go to Login Page</a>";
} else {
    echo "✅ Session data exists:<br>";
    foreach ($_SESSION as $key => $value) {
        echo "- {$key}: " . htmlspecialchars($value) . "<br>";
    }
    
    if (isset($_SESSION['uid'])) {
        echo "<br><strong>✅ User is logged in as: " . htmlspecialchars($_SESSION['uid']) . "</strong><br>";
        
        // Test notification loading
        try {
            require_once 'super_simple_notifications.php';
            $notificationSystem = new SuperSimpleNotifications();
            $notifications = $notificationSystem->getNotifications($_SESSION['uid']);
            
            echo "<br><h3>🔔 Notification Test</h3>";
            echo "Total notifications: " . count($notifications) . "<br>";
            
            $unreadCount = count(array_filter($notifications, function($n) { return !$n['isRead']; }));
            echo "Unread notifications: {$unreadCount}<br>";
            
            if (!empty($notifications)) {
                echo "<br><strong>Latest notifications:</strong><br>";
                foreach (array_slice($notifications, 0, 3) as $notif) {
                    $status = $notif['isRead'] ? '✅ Read' : '🆕 Unread';
                    echo "- {$status} {$notif['title']}: {$notif['message']}<br>";
                }
            }
            
        } catch (Exception $e) {
            echo "<br>❌ Notification error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "<br>❌ User ID not set in session<br>";
    }
}
echo "</div>";

echo "<div class='info'>";
echo "<h3>Actions</h3>";
echo "<a href='Dashboard.php'>Go to Dashboard</a> | ";
echo "<a href='test_navbar_notifications.html'>Test Notifications</a> | ";
if (isset($_SESSION['uid'])) {
    echo "<a href='logout.php'>Logout</a>";
} else {
    echo "<a href='Signin.php'>Login</a>";
}
echo "</div>";

echo "</body></html>";
?> 