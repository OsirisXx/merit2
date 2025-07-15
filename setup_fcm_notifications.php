<?php
/**
 * FCM Cross-Platform Notification Setup
 * This script helps configure Firebase Cloud Messaging for cross-platform notifications
 */

require_once 'cross_platform_notifications.php';

class FCMNotificationSetup {
    private $config;
    private $configPath;
    
    public function __construct() {
        $this->configPath = __DIR__ . '/config.json';
        $this->loadConfig();
    }
    
    private function loadConfig() {
        if (file_exists($this->configPath)) {
            $this->config = json_decode(file_get_contents($this->configPath), true);
        } else {
            throw new Exception("Config file not found: " . $this->configPath);
        }
    }
    
    private function saveConfig() {
        return file_put_contents($this->configPath, json_encode($this->config, JSON_PRETTY_PRINT));
    }
    
    /**
     * Configure FCM server key
     */
    public function configureFCMServerKey($serverKey) {
        if (empty($serverKey) || $serverKey === 'YOUR_FCM_SERVER_KEY_HERE') {
            throw new Exception("Invalid FCM server key provided");
        }
        
        $this->config['firebase']['serverKey'] = $serverKey;
        $this->saveConfig();
        
        return "FCM server key configured successfully";
    }
    
    /**
     * Test FCM connectivity
     */
    public function testFCMConnection() {
        $serverKey = $this->config['firebase']['serverKey'] ?? null;
        
        if (!$serverKey || $serverKey === 'YOUR_FCM_SERVER_KEY_HERE') {
            return [
                'success' => false,
                'message' => 'FCM server key not configured. Please set it in config.json'
            ];
        }
        
        // Test with a dummy notification to check if server key is valid
        $testPayload = [
            'registration_ids' => ['dummy_token'],
            'notification' => [
                'title' => 'Test Notification',
                'body' => 'Testing FCM connection'
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: key=' . $serverKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($testPayload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return [
                'success' => true,
                'message' => 'FCM connection successful! Server key is valid.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'FCM connection failed. HTTP code: ' . $httpCode . '. Response: ' . $response
            ];
        }
    }
    
    /**
     * Send test notification to check full cross-platform functionality
     */
    public function sendTestCrossPlatformNotification($userId = null) {
        try {
            $crossPlatform = new CrossPlatformNotifications();
            
            // Use a test user ID if none provided
            $testUserId = $userId ?: 'test_user_' . time();
            
            $success = $crossPlatform->sendCrossPlatformNotification(
                $testUserId,
                'SYSTEM',
                'TEST',
                'Cross-Platform Test',
                'This is a test notification to verify cross-platform functionality is working.',
                ['testMode' => true, 'timestamp' => time()]
            );
            
            return [
                'success' => $success,
                'message' => $success ? 'Test notification sent successfully!' : 'Failed to send test notification'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending test notification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get current configuration status
     */
    public function getConfigurationStatus() {
        $serverKey = $this->config['firebase']['serverKey'] ?? null;
        $isConfigured = $serverKey && $serverKey !== 'YOUR_FCM_SERVER_KEY_HERE';
        
        return [
            'fcm_configured' => $isConfigured,
            'server_key_set' => !empty($serverKey),
            'server_key_placeholder' => $serverKey === 'YOUR_FCM_SERVER_KEY_HERE',
            'project_id' => $this->config['firebase']['projectId'] ?? 'not_set',
            'config_file_exists' => file_exists($this->configPath)
        ];
    }
    
    /**
     * Integrate with existing notification system
     */
    public function setupExistingSystemIntegration() {
        // Check if existing notification files need updates
        $files = [
            'simple_notification_system.php',
            'notification_service.php',
            'super_simple_notifications.php'
        ];
        
        $integrations = [];
        
        foreach ($files as $file) {
            if (file_exists(__DIR__ . '/' . $file)) {
                $integrations[] = $this->updateNotificationFile($file);
            }
        }
        
        return $integrations;
    }
    
    private function updateNotificationFile($filename) {
        $filepath = __DIR__ . '/' . $filename;
        $content = file_get_contents($filepath);
        
        // Check if cross-platform integration is already present
        if (strpos($content, 'CrossPlatformNotifications') !== false) {
            return [
                'file' => $filename,
                'status' => 'already_integrated',
                'message' => 'Cross-platform notifications already integrated'
            ];
        }
        
        // Add cross-platform integration
        $integration = "\n\n// Cross-platform notification integration\nrequire_once 'cross_platform_notifications.php';\n";
        
        // Add to the end of the file before closing PHP tag
        $updatedContent = str_replace('?>', $integration . '?>', $content);
        
        if (file_put_contents($filepath, $updatedContent)) {
            return [
                'file' => $filename,
                'status' => 'integrated',
                'message' => 'Cross-platform notifications integrated successfully'
            ];
        } else {
            return [
                'file' => $filename,
                'status' => 'failed',
                'message' => 'Failed to integrate cross-platform notifications'
            ];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $setup = new FCMNotificationSetup();
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'configure_fcm':
                $serverKey = $_POST['server_key'] ?? '';
                $result = $setup->configureFCMServerKey($serverKey);
                echo json_encode(['success' => true, 'message' => $result]);
                break;
                
            case 'test_connection':
                $result = $setup->testFCMConnection();
                echo json_encode($result);
                break;
                
            case 'send_test':
                $userId = $_POST['user_id'] ?? null;
                $result = $setup->sendTestCrossPlatformNotification($userId);
                echo json_encode($result);
                break;
                
            case 'get_status':
                $result = $setup->getConfigurationStatus();
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'setup_integration':
                $result = $setup->setupExistingSystemIntegration();
                echo json_encode(['success' => true, 'integrations' => $result]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// HTML interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCM Cross-Platform Notification Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .setup-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .setup-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        .setup-section h3 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .btn.success {
            background: #28a745;
        }
        .btn.danger {
            background: #dc3545;
        }
        .status {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .status-item {
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .status-item.configured {
            background: #d4edda;
            color: #155724;
        }
        .status-item.not-configured {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🔔 FCM Cross-Platform Notification Setup</h1>
        <p>Configure Firebase Cloud Messaging for cross-platform notifications between web and mobile applications.</p>
        
        <!-- Configuration Status -->
        <div class="setup-section">
            <h3>📊 Current Configuration Status</h3>
            <button class="btn" onclick="checkStatus()">Refresh Status</button>
            <div id="status-display"></div>
        </div>
        
        <!-- FCM Server Key Configuration -->
        <div class="setup-section">
            <h3>🔑 FCM Server Key Configuration</h3>
            <p>Enter your Firebase Cloud Messaging server key to enable push notifications to mobile devices.</p>
            
            <div class="form-group">
                <label for="server-key">FCM Server Key:</label>
                <input type="text" id="server-key" placeholder="Enter your FCM server key...">
            </div>
            
            <button class="btn" onclick="configureFCM()">Configure FCM Key</button>
            <button class="btn" onclick="testConnection()">Test Connection</button>
            
            <div id="fcm-result"></div>
        </div>
        
        <!-- Test Notifications -->
        <div class="setup-section">
            <h3>🧪 Test Cross-Platform Notifications</h3>
            <p>Send test notifications to verify the system is working correctly.</p>
            
            <div class="form-group">
                <label for="test-user-id">Test User ID (optional):</label>
                <input type="text" id="test-user-id" placeholder="Leave empty for auto-generated test user">
            </div>
            
            <button class="btn" onclick="sendTestNotification()">Send Test Notification</button>
            
            <div id="test-result"></div>
        </div>
        
        <!-- Integration Setup -->
        <div class="setup-section">
            <h3>🔗 Existing System Integration</h3>
            <p>Integrate cross-platform notifications with your existing notification system.</p>
            
            <button class="btn" onclick="setupIntegration()">Setup Integration</button>
            
            <div id="integration-result"></div>
        </div>
    </div>

    <script>
        function showResult(elementId, success, message) {
            const element = document.getElementById(elementId);
            element.innerHTML = `<div class="status ${success ? 'success' : 'error'}">${message}</div>`;
        }
        
        function checkStatus() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get_status'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const status = data.data;
                    document.getElementById('status-display').innerHTML = `
                        <div class="status-grid">
                            <div class="status-item ${status.fcm_configured ? 'configured' : 'not-configured'}">
                                <strong>FCM Configured</strong><br>
                                ${status.fcm_configured ? '✅ Yes' : '❌ No'}
                            </div>
                            <div class="status-item ${status.server_key_set ? 'configured' : 'not-configured'}">
                                <strong>Server Key Set</strong><br>
                                ${status.server_key_set ? '✅ Yes' : '❌ No'}
                            </div>
                            <div class="status-item ${status.config_file_exists ? 'configured' : 'not-configured'}">
                                <strong>Config File</strong><br>
                                ${status.config_file_exists ? '✅ Exists' : '❌ Missing'}
                            </div>
                            <div class="status-item configured">
                                <strong>Project ID</strong><br>
                                ${status.project_id}
                            </div>
                        </div>
                    `;
                } else {
                    showResult('status-display', false, data.message);
                }
            })
            .catch(error => {
                showResult('status-display', false, 'Error checking status: ' + error.message);
            });
        }
        
        function configureFCM() {
            const serverKey = document.getElementById('server-key').value;
            if (!serverKey) {
                showResult('fcm-result', false, 'Please enter a server key');
                return;
            }
            
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=configure_fcm&server_key=${encodeURIComponent(serverKey)}`
            })
            .then(response => response.json())
            .then(data => {
                showResult('fcm-result', data.success, data.message);
                if (data.success) {
                    document.getElementById('server-key').value = '';
                    checkStatus();
                }
            })
            .catch(error => {
                showResult('fcm-result', false, 'Error configuring FCM: ' + error.message);
            });
        }
        
        function testConnection() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=test_connection'
            })
            .then(response => response.json())
            .then(data => {
                showResult('fcm-result', data.success, data.message);
            })
            .catch(error => {
                showResult('fcm-result', false, 'Error testing connection: ' + error.message);
            });
        }
        
        function sendTestNotification() {
            const userId = document.getElementById('test-user-id').value;
            
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=send_test&user_id=${encodeURIComponent(userId)}`
            })
            .then(response => response.json())
            .then(data => {
                showResult('test-result', data.success, data.message);
            })
            .catch(error => {
                showResult('test-result', false, 'Error sending test: ' + error.message);
            });
        }
        
        function setupIntegration() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=setup_integration'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let resultHtml = '<div class="status success">Integration setup completed!</div>';
                    data.integrations.forEach(integration => {
                        resultHtml += `<div class="status info"><strong>${integration.file}:</strong> ${integration.message}</div>`;
                    });
                    document.getElementById('integration-result').innerHTML = resultHtml;
                } else {
                    showResult('integration-result', false, data.message);
                }
            })
            .catch(error => {
                showResult('integration-result', false, 'Error setting up integration: ' + error.message);
            });
        }
        
        // Load status on page load
        document.addEventListener('DOMContentLoaded', checkStatus);
    </script>
</body>
</html> 