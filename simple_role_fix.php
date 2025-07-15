<?php
/**
 * Simple Role Fix - Set admin role directly in Firestore
 * This script fixes the admin role issue for notification filtering
 */

echo "<h1>🔧 Simple Admin Role Fix</h1>";

// Admin user ID from the JSON file
$adminUserId = "h8qq0E8avWO74cqS2Goy1wtENJh1";

echo "<p><strong>Admin User ID:</strong> $adminUserId</p>";

// The problem and solution
echo "<h2>📋 Issue Summary</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<p><strong>Problem:</strong> Admin notifications appear for all users because the admin user doesn't have <code>role: 'admin'</code> in Firestore.</p>";
echo "<p><strong>Mobile App Logic:</strong> Checks Firestore <code>users/{userId}</code> document for <code>role</code> field.</p>";
echo "<p><strong>Web System Logic:</strong> Uses local <code>admin_users.json</code> file.</p>";
echo "</div>";

echo "<h2>🛠️ Manual Fix Required</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px;'>";
echo "<p>Since Firebase Admin SDK has authentication limitations, you need to manually set the admin role:</p>";

echo "<h3>Option 1: Use Firebase Console</h3>";
echo "<ol>";
echo "<li>Go to <a href='https://console.firebase.google.com' target='_blank'>Firebase Console</a></li>";
echo "<li>Select your project: <strong>ally-user</strong></li>";
echo "<li>Go to <strong>Firestore Database</strong></li>";
echo "<li>Navigate to <code>users</code> collection</li>";
echo "<li>Find document with ID: <code>$adminUserId</code></li>";
echo "<li>Add/Edit field: <code>role</code> = <code>admin</code></li>";
echo "<li>Save the changes</li>";
echo "</ol>";

echo "<h3>Option 2: Use Mobile App Admin Feature</h3>";
echo "<ol>";
echo "<li>Log into the mobile app with the admin account</li>";
echo "<li>The app should create the user document automatically</li>";
echo "<li>Then manually set the role field as above</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔍 Verification Steps</h2>";
echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
echo "<ol>";
echo "<li><strong>Check Firestore:</strong> Verify the admin user has <code>role: 'admin'</code></li>";
echo "<li><strong>Test Admin Notifications:</strong> Send a test admin notification</li>";
echo "<li><strong>Test User Notifications:</strong> Verify regular users don't see admin notifications</li>";
echo "<li><strong>Mobile App:</strong> Check notification filtering works correctly</li>";
echo "</ol>";
echo "</div>";

// Test the notification system
require_once 'super_simple_notifications.php';

echo "<h2>🧪 Test Notification System</h2>";
$notifications = new SuperSimpleNotifications();

// Send a test admin notification
$result = $notifications->sendAdminNotification(
    'test',
    'Admin Role Fix Test',
    'This is a test notification after fixing admin roles. Should only appear for admin users.',
    [
        'test' => true,
        'fix_timestamp' => time(),
        'isAdminNotification' => true,
        'targetRole' => 'admin',
        'notificationSource' => 'admin_system'
    ]
);

if ($result) {
    echo "<p style='color: green;'>✅ Test admin notification sent successfully!</p>";
    echo "<p>Check the mobile app - this should only appear for the admin user.</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send test notification</p>";
}

echo "<h2>📱 Expected Behavior After Fix</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px;'>";
echo "<ul>";
echo "<li><strong>Admin Users:</strong> See admin notifications + general notifications</li>";
echo "<li><strong>Regular Users:</strong> See only user notifications + general notifications</li>";
echo "<li><strong>Admin Notifications:</strong> Process submissions, system alerts for admins</li>";
echo "<li><strong>User Notifications:</strong> Process approvals, personal updates</li>";
echo "</ul>";
echo "</div>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
p, li { margin: 8px 0; }
code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style> 