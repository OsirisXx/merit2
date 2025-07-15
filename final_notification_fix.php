<?php
/**
 * Final Notification Fix
 * Direct approach to fix admin notification filtering
 */

echo "<h1>🎯 Final Notification Fix</h1>";

// Step 1: Identify the admin user
$adminUserId = "h8qq0E8avWO74cqS2Goy1wtENJh1";
echo "<p><strong>Admin User ID:</strong> $adminUserId</p>";

// Step 2: Create a local notification test
require_once 'super_simple_notifications.php';

echo "<h2>💡 The Issue</h2>";
echo "<div style='background: #ffebee; padding: 15px; border-left: 5px solid #f44336;'>";
echo "<p><strong>Problem:</strong> Admin notifications show up for all users in mobile app</p>";
echo "<p><strong>Root Cause:</strong> Admin user doesn't have <code>role: 'admin'</code> in Firestore</p>";
echo "<p><strong>Solution:</strong> Set admin role in Firestore + test notification filtering</p>";
echo "</div>";

echo "<h2>🔧 Manual Fix Steps</h2>";
echo "<div style='background: #e8f5e9; padding: 15px; border-left: 5px solid #4caf50;'>";
echo "<h3>Step 1: Fix Firestore Role</h3>";
echo "<ol>";
echo "<li>Open <a href='https://console.firebase.google.com/project/ally-user/firestore' target='_blank'>Firebase Console</a></li>";
echo "<li>Go to Firestore Database</li>";
echo "<li>Navigate to <strong>users</strong> collection</li>";
echo "<li>Find document: <code>$adminUserId</code></li>";
echo "<li>Add field: <code>role</code> = <code>admin</code> (string type)</li>";
echo "<li>Save changes</li>";
echo "</ol>";

echo "<h3>Step 2: Test the Fix</h3>";
echo "<ol>";
echo "<li>Send test notifications using the form below</li>";
echo "<li>Check mobile app notifications</li>";
echo "<li>Verify admin notifications only appear for admin user</li>";
echo "</ol>";
echo "</div>";

// Step 3: Create notification testing form
echo "<h2>🧪 Notification Testing</h2>";

// Handle form submission
if ($_POST['action'] ?? '') {
    $notifications = new SuperSimpleNotifications();
    
    switch ($_POST['action']) {
        case 'test_admin':
            $result = $notifications->sendAdminNotification(
                'test',
                'Admin Test Notification',
                'This should ONLY appear for admin users. If regular users see this, the role fix is needed.',
                ['test' => true, 'timestamp' => time()]
            );
            echo "<div style='background: #e3f2fd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo $result ? "✅ Admin notification sent" : "❌ Admin notification failed";
            echo "</div>";
            break;
            
        case 'test_user':
            $testUserId = $_POST['test_user_id'] ?? 'test_user_123';
            $result = $notifications->sendUserNotification(
                $testUserId,
                'user_test',
                'User Test Notification',
                'This should appear for regular users but NOT for admin users.',
                ['test' => true, 'timestamp' => time()]
            );
            echo "<div style='background: #f3e5f5; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo $result ? "✅ User notification sent to $testUserId" : "❌ User notification failed";
            echo "</div>";
            break;
    }
}

?>

<form method="POST" style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>Test Admin Notifications</h3>
    <p>This will send a notification that should ONLY appear for admin users:</p>
    <button type="submit" name="action" value="test_admin" style="background: #f44336; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        🚨 Send Admin Test Notification
    </button>
</form>

<form method="POST" style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>Test User Notifications</h3>
    <p>This will send a notification for regular users (should NOT appear for admin):</p>
    <label>Test User ID: <input type="text" name="test_user_id" value="test_user_123" style="padding: 5px; margin: 0 10px;"></label>
    <button type="submit" name="action" value="test_user" style="background: #2196f3; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        👤 Send User Test Notification
    </button>
</form>

<?php

echo "<h2>📱 Expected Results After Fix</h2>";
echo "<table style='width: 100%; border-collapse: collapse; border: 1px solid #ddd;'>";
echo "<tr style='background: #f2f2f2;'>";
echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>User Type</th>";
echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Admin Notifications</th>";
echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>User Notifications</th>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 12px;'><strong>Admin User</strong><br>($adminUserId)</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: green;'>✅ Should see</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: red;'>❌ Should NOT see</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='border: 1px solid #ddd; padding: 12px;'><strong>Regular Users</strong><br>(all other users)</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: red;'>❌ Should NOT see</td>";
echo "<td style='border: 1px solid #ddd; padding: 12px; color: green;'>✅ Should see</td>";
echo "</tr>";
echo "</table>";

echo "<h2>🎯 Action Items</h2>";
echo "<div style='background: #fff9c4; padding: 15px; border: 1px solid #f9c74f; border-radius: 5px;'>";
echo "<ol>";
echo "<li><strong>FIRST:</strong> Set admin role in Firebase Console (see Step 1 above)</li>";
echo "<li><strong>THEN:</strong> Use the test buttons above to verify the fix</li>";
echo "<li><strong>FINALLY:</strong> Test in the mobile app to confirm proper filtering</li>";
echo "</ol>";
echo "</div>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
h1, h2, h3 { color: #333; }
p, li { margin: 8px 0; line-height: 1.5; }
code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; color: #d63384; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
button:hover { opacity: 0.8; }
</style> 