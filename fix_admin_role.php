<?php
/**
 * Fix Admin Role in Firestore
 * This script sets the proper admin role for the admin user in Firestore
 */

require_once 'firebase_admin_notifications.php';

echo "<h1>🔧 Fix Admin Role in Firestore</h1>";

// Get admin user ID from local file
$adminFile = __DIR__ . '/admin_users.json';
if (!file_exists($adminFile)) {
    echo "<p style='color: red;'>❌ admin_users.json not found!</p>";
    exit;
}

$adminUsers = json_decode(file_get_contents($adminFile), true);
if (empty($adminUsers)) {
    echo "<p style='color: red;'>❌ No admin users found in config!</p>";
    exit;
}

$adminUserId = $adminUsers[0]; // Get the first admin user
echo "<p><strong>Fixing admin role for user:</strong> $adminUserId</p>";

try {
    $firebase = new FirebaseAdminNotifications();
    
    // Get access token using reflection
    $reflection = new ReflectionClass($firebase);
    $method = $reflection->getMethod('getAccessToken');
    $method->setAccessible(true);
    $accessToken = $method->invoke($firebase);
    
    $makeHttpRequest = $reflection->getMethod('makeHttpRequest');
    $makeHttpRequest->setAccessible(true);
    
    // First, check current user document
    $firestoreUrl = "https://firestore.googleapis.com/v1/projects/ally-user/databases/(default)/documents/users/{$adminUserId}";
    
    $response = $makeHttpRequest->invoke(
        $firebase,
        $firestoreUrl,
        null,
        ['Authorization: Bearer ' . $accessToken]
    );
    
    if ($response && isset($response['fields'])) {
        $currentRole = $response['fields']['role']['stringValue'] ?? 'not set';
        $username = $response['fields']['username']['stringValue'] ?? 'Unknown';
        
        echo "<p><strong>Current user info:</strong></p>";
        echo "<ul>";
        echo "<li>Username: $username</li>";
        echo "<li>Current role: $currentRole</li>";
        echo "</ul>";
        
        if ($currentRole === 'admin') {
            echo "<p style='color: green;'>✅ User already has admin role! The issue might be elsewhere.</p>";
        } else {
            echo "<p><strong>Updating role to 'admin'...</strong></p>";
            
            // Update the user document to set role = 'admin'
            $updateData = [
                'fields' => [
                    'role' => [
                        'stringValue' => 'admin'
                    ]
                ]
            ];
            
            // PATCH request to update the document
            $updateResponse = $makeHttpRequest->invoke(
                $firebase,
                $firestoreUrl . '?updateMask.fieldPaths=role',
                json_encode($updateData),
                [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ]
            );
            
            if ($updateResponse) {
                echo "<p style='color: green;'>✅ Successfully updated user role to 'admin'!</p>";
                echo "<p><strong>The notification filtering should now work properly.</strong></p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update user role</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ User document not found in Firestore!</p>";
        echo "<p><strong>The user needs to sign in to the mobile app at least once to create their Firestore document.</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li>Test admin notifications in the mobile app</li>";
echo "<li>Verify that regular users don't receive admin notifications</li>";
echo "<li>Check that admin users receive admin notifications properly</li>";
echo "</ol>";

echo "<h2>🧪 Test the Fix</h2>";
echo "<p><a href='debug_notification_roles.php' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Run Diagnostic Test</a></p>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
p { margin: 10px 0; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style> 