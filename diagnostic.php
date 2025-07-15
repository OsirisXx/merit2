<?php
// Simple diagnostic script for deployment issues
echo "<h1>🔍 Deployment Diagnostic</h1>";

// Check current directory
echo "<h2>📁 Current Directory Info</h2>";
echo "<strong>Current Directory:</strong> " . getcwd() . "<br>";
echo "<strong>Script Location:</strong> " . __FILE__ . "<br>";
echo "<strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// List files in current directory
echo "<h2>📋 Files in Current Directory</h2>";
$files = scandir('.');
echo "<ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $type = is_dir($file) ? '[DIR]' : '[FILE]';
        echo "<li>$type $file</li>";
    }
}
echo "</ul>";

// Check for key files
echo "<h2>✅ Key File Check</h2>";
$keyFiles = ['Index.php', 'Signin.php', 'Dashboard.php', 'config.json', 'session_check.php'];
foreach ($keyFiles as $file) {
    $exists = file_exists($file);
    $icon = $exists ? '✅' : '❌';
    echo "$icon $file<br>";
}

// PHP version and settings
echo "<h2>🐘 PHP Information</h2>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Session Support:</strong> " . (function_exists('session_start') ? '✅ Yes' : '❌ No') . "<br>";
echo "<strong>JSON Support:</strong> " . (function_exists('json_encode') ? '✅ Yes' : '❌ No') . "<br>";
echo "<strong>cURL Support:</strong> " . (function_exists('curl_init') ? '✅ Yes' : '❌ No') . "<br>";

// Test basic PHP functionality
echo "<h2>🧪 PHP Test</h2>";
try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    echo "✅ Session started successfully<br>";
} catch (Exception $e) {
    echo "❌ Session error: " . $e->getMessage() . "<br>";
}

// Check if we can read config.json
if (file_exists('config.json')) {
    try {
        $config = json_decode(file_get_contents('config.json'), true);
        echo "✅ config.json readable<br>";
        echo "📡 Firebase Project: " . ($config['firebase']['projectId'] ?? 'Not set') . "<br>";
    } catch (Exception $e) {
        echo "❌ config.json error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ config.json not found<br>";
}

// Server information
echo "<h2>🖥️ Server Info</h2>";
echo "<strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "<strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "<br>";

// Check for Ally subdirectory
if (is_dir('Ally')) {
    echo "<h2>⚠️ Found Ally Subdirectory</h2>";
    echo "<p style='color: red;'><strong>ISSUE FOUND:</strong> Files are in Ally/ subdirectory!</p>";
    echo "<p><strong>Solution:</strong> Move all files from Ally/ to the root directory.</p>";
    
    echo "<h3>Files in Ally/ directory:</h3>";
    $allyFiles = scandir('Ally');
    echo "<ul>";
    foreach ($allyFiles as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}

echo "<h2>🎯 Recommendations</h2>";
if (!file_exists('Index.php')) {
    echo "<p style='color: red;'>❌ Index.php not found in root directory</p>";
}
if (!file_exists('Signin.php')) {
    echo "<p style='color: red;'>❌ Signin.php not found in root directory</p>";
}
if (is_dir('Ally')) {
    echo "<p style='color: orange;'>⚠️ Move files from Ally/ to root directory</p>";
}

echo "<hr>";
echo "<p>📧 Send this diagnostic info if you need further help!</p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
h1, h2 { color: #333; }
li { margin: 5px 0; }
</style> 