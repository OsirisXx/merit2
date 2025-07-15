<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: signin.php');
    exit;
}

require_once 'super_simple_notifications.php';

$message = '';

if ($_POST && isset($_POST['send_test'])) {
    $notificationSystem = new SuperSimpleNotifications();
    
    $result = $notificationSystem->sendTestNotification($_SESSION['uid']);
    
    $message = $result ? 
        "✅ Test notification sent! Check the bell icon in the navbar." : 
        "❌ Failed to send test notification.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Test Notification</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .btn { background: #007bff; color: white; border: none; padding: 15px 25px; margin: 10px; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <h1>🔔 Send Test Notification</h1>
    
    <div class="result info">
        <strong>Current User:</strong> <?php echo $_SESSION['uid']; ?><br>
        <strong>Purpose:</strong> Test if the notification bell icon shows new notifications
    </div>
    
    <?php if ($message): ?>
        <div class="result <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <button type="submit" name="send_test" class="btn">📦 Send Test Notification</button>
    </form>
    
    <div class="result info">
        <h3>Instructions:</h3>
        <ol>
            <li>Click "Send Test Notification" above</li>
            <li>Look at the notification bell (🔔) in the navbar</li>
            <li>You should see a red badge with the number of unread notifications</li>
            <li>Click the bell to see the notification popup</li>
            <li>The notification should appear in the list</li>
        </ol>
        
        <h3>What this tests:</h3>
        <ul>
            <li>✓ Notification creation via PHP CRUD system</li>
            <li>✓ Storage in notification_logs collection</li>
            <li>✓ Navbar bell icon loading notifications</li>
            <li>✓ Unread count badge display</li>
            <li>✓ Notification popup functionality</li>
        </ul>
    </div>
    
    <p>
        <a href="notifications_simple.php" style="color: #007bff;">View All Notifications (Simple)</a> |
        <a href="test_crud_notifications.php" style="color: #007bff;">View CRUD Test Page</a>
    </p>
    
    <script>
        // Auto-refresh the page every 30 seconds to see if notifications appear
        console.log('Test notification page loaded');
        console.log('User ID:', '<?php echo $_SESSION['uid']; ?>');
        
        // Function to check if notification count changes
        setInterval(() => {
            const badge = document.getElementById('notif-badge');
            if (badge && badge.style.display !== 'none') {
                console.log('Notification badge visible with count:', badge.textContent);
            }
        }, 2000);
    </script>
</body>
</html> 