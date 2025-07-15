<?php
/**
 * COMPREHENSIVE ADMIN NOTIFICATION TEST
 * Tests all admin notifications for appointments, donations, and matching
 */

require_once 'super_simple_notifications.php';

header('Content-Type: text/html; charset=UTF-8');

// Initialize notification system
$notifications = new SuperSimpleNotifications();

// Test data
$testUserId = 'test_user_123';
$testUserName = 'John Doe';
$testUserEmail = 'john.doe@example.com';

echo "<!DOCTYPE html>";
echo "<html><head><title>Admin Notification Test</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.test-section { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
.success { background-color: #d4edda; border-color: #c3e6cb; }
.error { background-color: #f8d7da; border-color: #f5c6cb; }
button { padding: 10px 15px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
button:hover { background: #0056b3; }
.result { margin: 10px 0; padding: 10px; border-radius: 3px; }
</style></head><body>";

echo "<h1>🔔 Admin Notification System Test</h1>";
echo "<p>Testing collection-based admin notifications for appointments, donations, and matching requests.</p>";

// Test 1: Appointment Notifications
echo "<div class='test-section'>";
echo "<h2>📅 1. Appointment Notifications Test</h2>";

if (isset($_POST['test_appointment'])) {
    echo "<h3>Testing Appointment Request...</h3>";
    
    $appointmentData = [
        'userName' => $testUserName,
        'userEmail' => $testUserEmail,
        'appointmentType' => 'Initial Consultation',
        'appointmentDate' => '2025-02-01 at 10:00 A.M.',
        'appointmentTime' => '10:00 A.M.'
    ];
    
    // Test user notification
    $userResult = $notifications->sendAppointmentNotification($testUserId, 'scheduled', $appointmentData);
    
    // Test admin notification
    $adminResult = $notifications->sendAppointmentRequestToAdmins(
        $testUserName,
        $testUserEmail,
        'Initial Consultation',
        '2025-02-01',
        '10:00 A.M.'
    );
    
    echo "<div class='result " . ($userResult ? 'success' : 'error') . "'>";
    echo "User Notification: " . ($userResult ? "✅ Sent successfully" : "❌ Failed");
    echo "</div>";
    
    echo "<div class='result " . ($adminResult ? 'success' : 'error') . "'>";
    echo "Admin Notification: " . ($adminResult ? "✅ Sent successfully" : "❌ Failed");
    echo "</div>";
    
    if ($userResult && $adminResult) {
        echo "<p><strong>✅ Appointment notifications working correctly!</strong></p>";
    } else {
        echo "<p><strong>❌ Appointment notifications failed!</strong></p>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='test_appointment'>Test Appointment Notifications</button>";
echo "</form>";
echo "</div>";

// Test 2: Donation Notifications
echo "<div class='test-section'>";
echo "<h2>💝 2. Donation Notifications Test</h2>";

if (isset($_POST['test_donation'])) {
    echo "<h3>Testing Donation Submission...</h3>";
    
    // Test user notification
    $userResult = $notifications->sendDonationNotification($testUserId, 'food', 'submitted');
    
    // Test admin notification
    $adminResult = $notifications->sendDonationSubmissionToAdmins(
        $testUserName,
        $testUserEmail,
        'food',
        null
    );
    
    echo "<div class='result " . ($userResult ? 'success' : 'error') . "'>";
    echo "User Notification: " . ($userResult ? "✅ Sent successfully" : "❌ Failed");
    echo "</div>";
    
    echo "<div class='result " . ($adminResult ? 'success' : 'error') . "'>";
    echo "Admin Notification: " . ($adminResult ? "✅ Sent successfully" : "❌ Failed");
    echo "</div>";
    
    if ($userResult && $adminResult) {
        echo "<p><strong>✅ Donation notifications working correctly!</strong></p>";
    } else {
        echo "<p><strong>❌ Donation notifications failed!</strong></p>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='test_donation'>Test Donation Notifications</button>";
echo "</form>";
echo "</div>";

// Test 3: Matching Notifications
echo "<div class='test-section'>";
echo "<h2>💕 3. Matching Notifications Test</h2>";

if (isset($_POST['test_matching'])) {
    echo "<h3>Testing Matching Request...</h3>";
    
    $preferences = [
        'genderPreference' => 'Male',
        'skinColorPreference' => 'Light',
        'characteristicsPreference' => 'Playful',
        'preferredAge' => '2-3 years',
        'preferredSize' => 'Medium'
    ];
    
    // Test user notification
    $userResult = $notifications->sendMatchingRequestNotification($testUserId);
    
    // Test admin notification
    $adminResult = $notifications->sendMatchingRequestToAdmins(
        $testUserName,
        $testUserEmail,
        $preferences
    );
    
    echo "<div class='result " . ($userResult ? 'success' : 'error') . "'>";
    echo "User Notification: " . ($userResult ? "✅ Sent successfully" : "❌ Failed");
    echo "</div>";
    
    echo "<div class='result " . ($adminResult ? 'success' : 'error') . "'>";
    echo "Admin Notification: " . ($adminResult ? "✅ Sent successfully" : "❌ Failed");
    echo "</div>";
    
    if ($userResult && $adminResult) {
        echo "<p><strong>✅ Matching notifications working correctly!</strong></p>";
    } else {
        echo "<p><strong>❌ Matching notifications failed!</strong></p>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='test_matching'>Test Matching Notifications</button>";
echo "</form>";
echo "</div>";

// Test 4: Admin User Registration
echo "<div class='test-section'>";
echo "<h2>👤 4. Admin User Registration Test</h2>";

if (isset($_POST['test_admin_registration'])) {
    echo "<h3>Testing Admin User Registration...</h3>";
    
    $adminUserId = 'admin_test_456';
    $result = $notifications->registerAdminUser($adminUserId);
    
    echo "<div class='result " . ($result ? 'success' : 'error') . "'>";
    echo "Admin Registration: " . ($result ? "✅ Registered successfully" : "❌ Failed");
    echo "</div>";
    
    // Check if admin users file exists
    if (file_exists('admin_users.json')) {
        $adminUsers = json_decode(file_get_contents('admin_users.json'), true);
        echo "<p>Current admin users: " . implode(', ', $adminUsers) . "</p>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='test_admin_registration'>Test Admin Registration</button>";
echo "</form>";
echo "</div>";

// Test 5: View Recent Notifications
echo "<div class='test-section'>";
echo "<h2>📋 5. Recent Notifications</h2>";

if (isset($_POST['view_notifications'])) {
    echo "<h3>Recent User Notifications:</h3>";
    $userNotifications = $notifications->getNotifications($testUserId, 5);
    
    if (empty($userNotifications)) {
        echo "<p>No user notifications found.</p>";
    } else {
        echo "<ul>";
        foreach ($userNotifications as $notif) {
            echo "<li><strong>{$notif['title']}</strong> - {$notif['message']} <em>(" . date('Y-m-d H:i:s', $notif['timestamp'] / 1000) . ")</em></li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>Recent Admin Notifications:</h3>";
    // Get admin user IDs
    if (file_exists('admin_users.json')) {
        $adminUsers = json_decode(file_get_contents('admin_users.json'), true);
        if (!empty($adminUsers)) {
            $firstAdminId = $adminUsers[0];
            $adminNotifications = $notifications->getNotifications($firstAdminId, 5);
            
            if (empty($adminNotifications)) {
                echo "<p>No admin notifications found.</p>";
            } else {
                echo "<ul>";
                foreach ($adminNotifications as $notif) {
                    echo "<li><strong>{$notif['title']}</strong> - {$notif['message']} <em>(" . date('Y-m-d H:i:s', $notif['timestamp'] / 1000) . ")</em></li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p>No admin users registered. Please test admin registration first.</p>";
        }
    } else {
        echo "<p>Admin users file not found. Please test admin registration first.</p>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='view_notifications'>View Recent Notifications</button>";
echo "</form>";
echo "</div>";

// Test All Button
echo "<div class='test-section'>";
echo "<h2>🚀 6. Test All Systems</h2>";

if (isset($_POST['test_all'])) {
    echo "<h3>Running comprehensive test...</h3>";
    
    // Register admin
    $notifications->registerAdminUser('admin_test_comprehensive');
    
    // Test appointment
    $appointmentData = [
        'userName' => $testUserName,
        'userEmail' => $testUserEmail,
        'appointmentType' => 'Follow-up',
        'appointmentDate' => '2025-02-15 at 2:00 P.M.',
        'appointmentTime' => '2:00 P.M.'
    ];
    $apptUser = $notifications->sendAppointmentNotification($testUserId, 'scheduled', $appointmentData);
    $apptAdmin = $notifications->sendAppointmentRequestToAdmins($testUserName, $testUserEmail, 'Follow-up', '2025-02-15', '2:00 P.M.');
    
    // Test donation
    $donUser = $notifications->sendDonationNotification($testUserId, 'clothes', 'submitted');
    $donAdmin = $notifications->sendDonationSubmissionToAdmins($testUserName, $testUserEmail, 'clothes', null);
    
    // Test matching
    $matchUser = $notifications->sendMatchingRequestNotification($testUserId);
    $matchAdmin = $notifications->sendMatchingRequestToAdmins($testUserName, $testUserEmail, ['test' => 'preferences']);
    
    $allPassed = $apptUser && $apptAdmin && $donUser && $donAdmin && $matchUser && $matchAdmin;
    
    echo "<div class='result " . ($allPassed ? 'success' : 'error') . "'>";
    echo "Comprehensive Test: " . ($allPassed ? "✅ All systems working!" : "❌ Some systems failed");
    echo "</div>";
    
    echo "<p>Results:</p>";
    echo "<ul>";
    echo "<li>Appointment User: " . ($apptUser ? "✅" : "❌") . "</li>";
    echo "<li>Appointment Admin: " . ($apptAdmin ? "✅" : "❌") . "</li>";
    echo "<li>Donation User: " . ($donUser ? "✅" : "❌") . "</li>";
    echo "<li>Donation Admin: " . ($donAdmin ? "✅" : "❌") . "</li>";
    echo "<li>Matching User: " . ($matchUser ? "✅" : "❌") . "</li>";
    echo "<li>Matching Admin: " . ($matchAdmin ? "✅" : "❌") . "</li>";
    echo "</ul>";
}

echo "<form method='post'>";
echo "<button type='submit' name='test_all'>🚀 Test All Systems</button>";
echo "</form>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>First run 'Test Admin Registration' to create admin users</li>";
echo "<li>Then test individual notification types</li>";
echo "<li>Use 'View Recent Notifications' to see if notifications were created</li>";
echo "<li>Run 'Test All Systems' for a comprehensive check</li>";
echo "</ol>";

echo "<p><strong>Note:</strong> This tests the backend notification system. To see actual admin notifications in the UI, log in as an admin user and check the notification bell.</p>";

echo "</body></html>";
?> 