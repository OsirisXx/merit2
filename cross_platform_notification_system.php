<?php
/**
 * Cross-Platform Notification System
 * Ensures notifications sent from web also reach mobile devices and vice versa
 */

class CrossPlatformNotificationSystem {
    private $firebaseProjectId;
    private $baseUrl;
    private $functions;
    
    public function __construct() {
        $config = json_decode(file_get_contents(__DIR__ . "/config.json"), true);
        $this->firebaseProjectId = $config["firebase"]["projectId"] ?? "ally-user";
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->firebaseProjectId}/databases/(default)/documents";
        $this->functions = "https://us-central1-{$this->firebaseProjectId}.cloudfunctions.net";
    }
    
    public function sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message, $data = []) {
        try {
            error_log(" Sending cross-platform notification to user: $userId");
            
            // 1. Store in web notification system
            $webSuccess = $this->storeWebNotification($userId, $processType, $notificationType, $title, $message, $data);
            
            // 2. Send to mobile via FCM
            $mobileSuccess = $this->sendMobileNotification($userId, $processType, $notificationType, $title, $message, $data);
            
            return $webSuccess || $mobileSuccess;
            
        } catch (Exception $e) {
            error_log(" Cross-platform notification error: " . $e->getMessage());
            return false;
        }
    }
    
    private function storeWebNotification($userId, $processType, $notificationType, $title, $message, $data) {
        // Store notification in web collections
        try {
            $notification = [
                "userId" => $userId,
                "processType" => $processType,
                "notificationType" => $notificationType,
                "title" => $title,
                "message" => $message,
                "data" => $data,
                "timestamp" => time() * 1000,
                "status" => "sent",
                "isRead" => false,
                "source" => "web"
            ];
            
            // Use existing notification system
            require_once "super_simple_notifications.php";
            $simpleSystem = new SuperSimpleNotifications();
            return $simpleSystem->sendNotification($userId, $notificationType, $title, $message, $data);
            
        } catch (Exception $e) {
            error_log("Web notification error: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendMobileNotification($userId, $processType, $notificationType, $title, $message, $data) {
        // Send to mobile via HTTP request to Cloud Function
        try {
            $payload = [
                "userId" => $userId,
                "title" => $title,
                "message" => $message,
                "processType" => $processType,
                "notificationType" => $notificationType,
                "data" => $data
            ];
            
            $url = "{$this->functions}/sendEnhancedNotification";
            $options = [
                "http" => [
                    "header" => "Content-Type: application/json\r\n",
                    "method" => "POST",
                    "content" => json_encode(["data" => $payload]),
                    "timeout" => 10
                ]
            ];
            
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            
            if ($result !== FALSE) {
                error_log(" Mobile notification sent via FCM");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Mobile notification error: " . $e->getMessage());
            return false;
        }
    }
 }
 
 /**
  * Send user-specific notification (excludes admins)
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
    $system = new CrossPlatformNotificationSystem();
    return $system->sendUserNotification($userId, $processType, $notificationType, $title, $message, $data);
}

function sendCrossPlatformAdminNotification($processType, $notificationType, $title, $message, $data = []) {
    $system = new CrossPlatformNotificationSystem();
    return $system->sendAdminNotification($processType, $notificationType, $title, $message, $data);
}

function sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message, $data = []) {
    $system = new CrossPlatformNotificationSystem();
    return $system->sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message, $data);
}

?>
