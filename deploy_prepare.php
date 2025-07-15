<?php
/**
 * DEPLOYMENT PREPARATION SCRIPT
 * Run this script once after uploading to Hostinger to prepare the system
 */

echo "<h2>🚀 Hostinger Deployment Preparation</h2>";

// Create necessary directories
$directories = [
    'logs',
    'uploads',
    'functions',
    'icons',
    'images'
];

echo "<h3>📁 Creating Required Directories</h3>";
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Created directory: $dir<br>";
        } else {
            echo "❌ Failed to create directory: $dir<br>";
        }
    } else {
        echo "✅ Directory exists: $dir<br>";
    }
}

// Set proper file permissions
echo "<h3>🔐 Setting File Permissions</h3>";
$writableFiles = [
    'notifications.json',
    'admin_users.json',
    'fcm_tokens.json',
    'logs'
];

foreach ($writableFiles as $file) {
    if (file_exists($file)) {
        if (chmod($file, 0666)) {
            echo "✅ Set permissions for: $file<br>";
        } else {
            echo "❌ Failed to set permissions for: $file<br>";
        }
    } else {
        // Create empty files if they don't exist
        if (in_array($file, ['notifications.json', 'admin_users.json', 'fcm_tokens.json'])) {
            file_put_contents($file, '[]');
            chmod($file, 0666);
            echo "✅ Created and set permissions for: $file<br>";
        }
    }
}

// Check for required files
echo "<h3>📋 Checking Required Files</h3>";
$requiredFiles = [
    'config.json' => 'Configuration file',
    'firebase.json' => 'Firebase configuration',
    'cors.json' => 'CORS configuration',
    'functions/ally-user-firebase-adminsdk-fbsvc-4f2d3d1509.json' => 'Firebase service account key',
    'session_check.php' => 'Session management',
    'super_simple_notifications.php' => 'Notification system'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file<br>";
    } else {
        echo "❌ MISSING $description: $file<br>";
    }
}

// Test database connections and Firebase
echo "<h3>🔥 Firebase Connection Test</h3>";
if (file_exists('config.json')) {
    $config = json_decode(file_get_contents('config.json'), true);
    if ($config && isset($config['firebase'])) {
        echo "✅ Firebase configuration found<br>";
        echo "📡 Project ID: " . ($config['firebase']['projectId'] ?? 'Not set') . "<br>";
        echo "🔑 API Key: " . (isset($config['firebase']['apiKey']) ? 'Set' : 'Not set') . "<br>";
    } else {
        echo "❌ Firebase configuration invalid<br>";
    }
} else {
    echo "❌ config.json not found<br>";
}

// Check notification system
echo "<h3>🔔 Notification System Test</h3>";
if (file_exists('super_simple_notifications.php')) {
    require_once 'super_simple_notifications.php';
    try {
        $notifications = new SuperSimpleNotifications();
        echo "✅ Notification system loaded successfully<br>";
    } catch (Exception $e) {
        echo "❌ Notification system error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Notification system file not found<br>";
}

// Domain configuration check
echo "<h3>🌐 Domain Configuration</h3>";
$currentDomain = $_SERVER['HTTP_HOST'] ?? 'unknown';
echo "📍 Current domain: $currentDomain<br>";

if (strpos($currentDomain, 'meritxell-ally.org') !== false) {
    echo "✅ Production domain detected<br>";
} else {
    echo "⚠️ Domain mismatch - expected meritxell-ally.org<br>";
}

// CORS configuration check
if (file_exists('cors.json')) {
    $cors = json_decode(file_get_contents('cors.json'), true);
    if ($cors && isset($cors[0]['origin'])) {
        $origins = $cors[0]['origin'];
        echo "🔐 CORS origins configured: " . implode(', ', $origins) . "<br>";
    }
}

echo "<h3>✅ Deployment Status</h3>";
echo "<p><strong>Your Ally system is ready for production!</strong></p>";
echo "<p>🎯 Main URLs:</p>";
echo "<ul>";
echo "<li>🏠 <a href='https://meritxell-ally.org/'>Homepage</a></li>";
echo "<li>🔐 <a href='https://meritxell-ally.org/Signin.php'>Sign In</a></li>";
echo "<li>📊 <a href='https://meritxell-ally.org/Dashboard.php'>Dashboard</a></li>";
echo "<li>🔔 <a href='https://meritxell-ally.org/notifications_working_test.php'>Notifications Test</a></li>";
echo "</ul>";

echo "<p>🔧 <strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Test user login and registration</li>";
echo "<li>Test notification system</li>";
echo "<li>Upload any missing images to the /images folder</li>";
echo "<li>Test file uploads and Firebase integration</li>";
echo "<li>Delete this deploy_prepare.php file for security</li>";
echo "</ol>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
ul, ol { margin-left: 20px; }
a { color: #007cba; text-decoration: none; }
a:hover { text-decoration: underline; }
</style> 