<?php
/**
 * Cross-Platform Notification System
 * Ensures notifications sent from web also reach mobile devices and vice versa
 * Handles role-based filtering for both platforms
 */

class CrossPlatformNotifications {
    private $firebaseProjectId;
    private $functions;
    
    public function __construct() {
        $config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
        $this->firebaseProjectId = $config['firebase']['projectId'] ?? 'ally-user';
        $this->functions = "https://us-central1-{$this->firebaseProjectId}.cloudfunctions.net";
    }
    
    /**
     * Send notification to both web and mobile platforms
     */
    public function sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message, $data = []) {
        try {
            error_log("📱 Sending cross-platform notification to user: $userId");
            
            // Add cross-platform metadata
            $enhancedData = array_merge($data, [
                'crossPlatform' => true,
                'timestamp' => time() * 1000,
                'messageType' => $this->getMessageType($processType, $notificationType, $message)
            ]);
            
            // 1. Send to web notification system
            $webSuccess = $this->sendWebNotification($userId, $processType, $notificationType, $title, $message, $enhancedData);
            
            // 2. Send to mobile via FCM
            $mobileSuccess = $this->sendMobileNotification($userId, $processType, $notificationType, $title, $message, $enhancedData);
            
            $success = $webSuccess || $mobileSuccess;
            error_log($success ? "✅ Cross-platform notification sent" : "❌ Failed to send cross-platform notification");
            
            return $success;
            
        } catch (Exception $e) {
            error_log("❌ Cross-platform notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send user-specific notification (filtered for regular users)
     */
    public function sendUserNotification($userId, $processType, $notificationType, $title, $message, $data = []) {
        $userData = array_merge($data, [
            'isAdminNotification' => false,
            'targetRole' => 'user',
            'notificationSource' => 'user_system'
        ]);
        
        return $this->sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message, $userData);
    }
    
    /**
     * Send admin notification to all admins
     */
    public function sendAdminNotification($processType, $notificationType, $title, $message, $data = []) {
        try {
            $adminUsers = $this->getAdminUsers();
            $success = true;
            
            $adminData = array_merge($data, [
                'isAdminNotification' => true,
                'targetRole' => 'admin',
                'notificationSource' => 'admin_system'
            ]);
            
            foreach ($adminUsers as $adminUserId) {
                if (!$this->sendCrossPlatformNotification($adminUserId, $processType, $notificationType, $title, $message, $adminData)) {
                    $success = false;
                }
            }
            
            error_log("📢 Admin notification sent to " . count($adminUsers) . " admins");
            return $success;
            
        } catch (Exception $e) {
            error_log("❌ Admin notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification to web system (for web UI display)
     */
    private function sendWebNotification($userId, $processType, $notificationType, $title, $message, $data) {
        try {
            // Use the existing web notification system
            require_once __DIR__ . '/super_simple_notifications.php';
            $webSystem = new SuperSimpleNotifications();
            
            $result = $webSystem->sendNotification($userId, $notificationType, $title, $message, $data);
            
            if ($result) {
                error_log("💻 Web notification sent successfully");
                return true;
            } else {
                error_log("❌ Failed to send web notification");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("❌ Web notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification to mobile via Firebase Cloud Functions
     */
    private function sendMobileNotification($userId, $processType, $notificationType, $title, $message, $data) {
        try {
            // Prepare payload for Cloud Function
            $payload = [
                'userId' => $userId,
                'title' => $title,
                'message' => $message,
                'processType' => $processType,
                'notificationType' => $notificationType,
                'data' => $data,
                'urgencyLevel' => $this->getUrgencyLevel($notificationType),
                'isReminder' => false,
                'isBatch' => false
            ];
            
            // Call Firebase Cloud Function
            $result = $this->callCloudFunction('sendEnhancedNotification', $payload);
            
            if ($result && isset($result['success']) && $result['success']) {
                error_log("📱 Mobile notification sent via FCM");
                return true;
            } else {
                error_log("❌ Failed to send mobile notification");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("❌ Mobile notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Call Firebase Cloud Function via HTTP
     */
    private function callCloudFunction($functionName, $data) {
        try {
            $url = "{$this->functions}/{$functionName}";
            
            $postData = json_encode(['data' => $data]);
            
            $options = [
                'http' => [
                    'header' => [
                        "Content-Type: application/json",
                        "Content-Length: " . strlen($postData)
                    ],
                    'method' => 'POST',
                    'content' => $postData,
                    'timeout' => 15
                ]
            ];
            
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            
            if ($result === FALSE) {
                error_log("Failed to call Cloud Function: $functionName");
                return false;
            }
            
            $response = json_decode($result, true);
            return $response;
            
        } catch (Exception $e) {
            error_log("Cloud Function call error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get message type for filtering
     */
    private function getMessageType($processType, $notificationType, $message) {
        $lowerMessage = strtolower($message);
        
        if (strpos($lowerMessage, 'submitted') !== false || strpos($lowerMessage, 'request') !== false) {
            return strtolower($processType) . '_submitted';
        }
        
        if (strpos($lowerMessage, 'approved') !== false) {
            return strtolower($processType) . '_approved';
        }
        
        if (strpos($lowerMessage, 'rejected') !== false) {
            return strtolower($processType) . '_rejected';
        }
        
        if (strpos($lowerMessage, 'completed') !== false) {
            return strtolower($processType) . '_completed';
        }
        
        return strtolower($processType);
    }
    
    /**
     * Get urgency level for notification
     */
    private function getUrgencyLevel($notificationType) {
        $urgentTypes = [
            'DEADLINE_MISSED',
            'REMINDER_URGENT', 
            'ACCOUNT_SECURITY',
            'PROCESS_REJECTED',
            'ADMIN_REVIEW_REQUIRED'
        ];
        
        return in_array($notificationType, $urgentTypes) ? 'urgent' : 'normal';
    }
    
    /**
     * Get admin users from admin_users.json file
     */
    private function getAdminUsers() {
        try {
            $adminFile = __DIR__ . '/admin_users.json';
            if (!file_exists($adminFile)) {
                error_log("Admin users file not found");
                return [];
            }
            
            $adminData = json_decode(file_get_contents($adminFile), true);
            if (isset($adminData['admin_users'])) {
                return array_keys($adminData['admin_users']);
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Error getting admin users: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Helper functions for easy integration
 */

function sendCrossPlatformUserNotification($userId, $processType, $notificationType, $title, $message, $data = []) {
    $system = new CrossPlatformNotifications();
    return $system->sendUserNotification($userId, $processType, $notificationType, $title, $message, $data);
}

function sendCrossPlatformAdminNotification($processType, $notificationType, $title, $message, $data = []) {
    $system = new CrossPlatformNotifications();
    return $system->sendAdminNotification($processType, $notificationType, $title, $message, $data);
}

function sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message, $data = []) {
    $system = new CrossPlatformNotifications();
    return $system->sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message, $data);
}

?> 