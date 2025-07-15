<?php
/**
 * Debug Notification Roles
 * This script helps diagnose and fix notification role filtering issues
 */

require_once 'firebase_admin_notifications.php';
require_once 'super_simple_notifications.php';

echo "<h1>🔍 Notification Role Debugging</h1>";

// 1. Check admin_users.json
echo "<h2>1. Admin Users Configuration</h2>";
$adminFile = __DIR__ . '/admin_users.json';
if (file_exists($adminFile)) {
    $adminUsers = json_decode(file_get_contents($adminFile), true);
    echo "<p><strong>Admin users from local file:</strong></p>";
    echo "<pre>" . json_encode($adminUsers, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p style='color: red;'><strong>❌ admin_users.json not found!</strong></p>";
}

// 2. Test Firestore role checking
echo "<h2>2. Firestore Role Verification</h2>";
echo "<p>To fix the issue, admin users need to have role='admin' in Firestore users collection.</p>";

// 3. Show the actual problem
echo "<h2>3. 🚨 The Problem</h2>";
echo "<div style='background: #ffe6e6; padding: 15px; border-left: 4px solid #ff0000;'>";
echo "<p><strong>Issue:</strong> Admin notifications are showing up for regular users.</p>";
echo "<p><strong>Root Cause:</strong> Mismatch between admin user identification in web (local JSON file) vs mobile (Firestore role field).</p>";
echo "</div>";

// 4. Show the solution
echo "<h2>4. 🔧 The Solution</h2>";
echo "<div style='background: #e6ffe6; padding: 15px; border-left: 4px solid #00aa00;'>";
echo "<h3>Step 1: Fix User Role in Firestore</h3>";
echo "<p>The admin user <code>h8qq0E8avWO74cqS2Goy1wtENJh1</code> needs to have <code>role: 'admin'</code> in their Firestore user document.</p>";

echo "<h3>Step 2: Verify Notification Targeting</h3>";
echo "<p>Admin notifications must include these flags:</p>";
echo "<ul>";
echo "<li><code>isAdminNotification: true</code></li>";
echo "<li><code>targetRole: 'admin'</code></li>";
echo "<li><code>notificationSource: 'admin_system'</code></li>";
echo "</ul>";

echo "<h3>Step 3: Check Mobile App Filtering</h3>";
echo "<p>The mobile app's <code>MyFirebaseMessagingService.kt</code> filters notifications based on the user's role from Firestore.</p>";
echo "</div>";

// 5. Test notification data
$simpleNotifications = new SuperSimpleNotifications();
echo "<h2>5. Test Admin Notification</h2>";
$result = $simpleNotifications->sendAdminNotification(
    'debug',
    'Debug Admin Test',
    'This notification should only appear for admin users.',
    ['debug' => true]
);

echo $result ? "✅ Test notification sent" : "❌ Test notification failed";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
p { margin: 10px 0; }
code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
</style> 