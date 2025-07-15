<?php
// Simple debug script to test messaging system
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Messaging System</h2>";

// Test 1: Include the messaging helper
try {
    require_once 'messaging_helper.php';
    echo "✅ messaging_helper.php included successfully<br>";
} catch (Exception $e) {
    echo "❌ Error including messaging_helper.php: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Create messaging helper instance
try {
    $messaging = new MessagingHelper();
    echo "✅ MessagingHelper instance created successfully<br>";
} catch (Exception $e) {
    echo "❌ Error creating MessagingHelper: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Test admin ID retrieval
try {
    $adminId = $messaging->getRandomAdminId();
    echo "✅ Admin ID retrieved: $adminId<br>";
} catch (Exception $e) {
    echo "❌ Error getting admin ID: " . $e->getMessage() . "<br>";
}

// Test 4: Test chat creation
try {
    $chatId = $messaging->createOrGetChat('testuser123', 'h8qq0E8avWO74cqS2Goy1wtENJh1', 'adoption');
    echo "✅ Chat created/retrieved: $chatId<br>";
} catch (Exception $e) {
    echo "❌ Error creating chat: " . $e->getMessage() . "<br>";
}

// Test 5: Test system message
try {
    $messageId = $messaging->sendSystemMessage($chatId, 'Test message from debug script', 'adoption');
    echo "✅ System message sent: $messageId<br>";
} catch (Exception $e) {
    echo "❌ Error sending system message: " . $e->getMessage() . "<br>";
}

// Test 6: Test adoption notification
try {
    $messageId = $messaging->sendAdoptionNotification('testuser123', 1, 'Initial Application', 'completed');
    echo "✅ Adoption notification sent: $messageId<br>";
} catch (Exception $e) {
    echo "❌ Error sending adoption notification: " . $e->getMessage() . "<br>";
}

echo "<h3>Debug Complete</h3>";
echo "<p>Check the error logs for detailed information.</p>";
echo "<p><a href='chat_messages.php'>View Chat Messages</a></p>";
?> 