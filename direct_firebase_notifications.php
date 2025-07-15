<?php
/**
 * Direct Firebase Notification System
 * Sends notifications directly to Firestore and FCM without requiring Cloud Functions
 */

class DirectFirebaseNotifications {
    private $projectId;
    private $serverKey;
    private $firestoreUrl;
    
    public function __construct() {
        $config = json_decode(file_get_contents(__DIR__ . "/config.json"), true);
        $this->projectId = $config["firebase"]["projectId"] ?? "ally-user";
        $this->serverKey = $config["firebase"]["serverKey"] ?? ""; // FCM Server Key
        $this->firestoreUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }
    
    /**
     * Send notification to both web and mobile platforms
     */
    public function sendCrossPlatformNotification($userId, $title, $message, $data = []) {
        $results = [];
        
        // 1. Store in Firestore for web application
        $results['web'] = $this->storeWebNotification($userId, $title, $message, $data);
        
        // 2. Send FCM push notification for mobile
        $results['mobile'] = $this->sendMobileNotification($userId, $title, $message, $data);
        
        $success = $results['web'] || $results['mobile'];
        error_log("📱 Cross-platform notification sent - Web: " . ($results['web'] ? "✅" : "❌") . ", Mobile: " . ($results['mobile'] ? "✅" : "❌"));
        
        return $success;
    }
    
    /**
     * Send user notification (excludes admins)
     */
    public function sendUserNotification($userId, $title, $message, $data = []) {
        $userData = array_merge($data, [
            'isAdminNotification' => false,
            'targetRole' => 'user',
            'notificationSource' => 'user_system'
        ]);
        
        return $this->sendCrossPlatformNotification($userId, $title, $message, $userData);
    }
    
    /**
     * Send notification to all admins
     */
    public function sendAdminNotification($title, $message, $data = []) {
        $adminIds = $this->getAdminUserIds();
        if (empty($adminIds)) {
            error_log("⚠️ No admin users found for notification: {$title}");
            return false;
        }
        
        $success = true;
        $adminData = array_merge($data, [
            'isAdminNotification' => true,
            'targetRole' => 'admin',
            'notificationSource' => 'admin_system'
        ]);
        
        foreach ($adminIds as $adminId) {
            if (!$this->sendCrossPlatformNotification($adminId, $title, $message, $adminData)) {
                $success = false;
            }
        }
        
        error_log("📧 Admin notification sent to " . count($adminIds) . " admins: {$title}");
        return $success;
    }
    
    /**
     * Store notification in Firestore for web display
     */
    private function storeWebNotification($userId, $title, $message, $data) {
        try {
            $notification = [
                'fields' => [
                    'userId' => ['stringValue' => $userId],
                    'title' => ['stringValue' => $title],
                    'message' => ['stringValue' => $message],
                    'timestamp' => ['integerValue' => (string)(time() * 1000)],
                    'isRead' => ['booleanValue' => false],
                    'id' => ['stringValue' => uniqid('notif_', true)],
                    'source' => ['stringValue' => 'direct_firebase'],
                    'data' => ['mapValue' => ['fields' => $this->convertToFirestoreFields($data)]]
                ]
            ];
            
            // Store in multiple collections for redundancy
            $collections = [
                'notification_logs',
                'notifications',
                "users/{$userId}/notifications"
            ];
            
            $success = false;
            foreach ($collections as $collection) {
                if ($this->storeInFirestore($collection, $notification)) {
                    $success = true;
                }
            }
            
            // Also use existing notification system as fallback
            if (!$success) {
                require_once 'super_simple_notifications.php';
                $simpleSystem = new SuperSimpleNotifications();
                $success = $simpleSystem->sendNotification($userId, 'system', $title, $message, $data);
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("❌ Web notification storage error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send FCM push notification to mobile devices
     */
    private function sendMobileNotification($userId, $title, $message, $data) {
        try {
            // Get user's FCM tokens
            $tokens = $this->getUserFCMTokens($userId);
            if (empty($tokens)) {
                error_log("⚠️ No FCM tokens found for user: {$userId}");
                return false;
            }
            
            $payload = [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default'
                ],
                'data' => array_merge($data, [
                    'userId' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'timestamp' => (string)(time() * 1000),
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ])
            ];
            
            return $this->sendFCMRequest($payload);
            
        } catch (Exception $e) {
            error_log("❌ Mobile notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's FCM tokens from Firestore
     */
    private function getUserFCMTokens($userId) {
        try {
            $url = "{$this->firestoreUrl}/users/{$userId}";
            $response = @file_get_contents($url);
            
            if ($response === FALSE) {
                return [];
            }
            
            $userData = json_decode($response, true);
            $tokens = [];
            
            // Check various token field names
            $tokenFields = ['fcmToken', 'deviceToken', 'registrationToken', 'token'];
            
            foreach ($tokenFields as $field) {
                if (isset($userData['fields'][$field]['stringValue'])) {
                    $tokens[] = $userData['fields'][$field]['stringValue'];
                }
                if (isset($userData['fields'][$field]['arrayValue']['values'])) {
                    foreach ($userData['fields'][$field]['arrayValue']['values'] as $tokenValue) {
                        if (isset($tokenValue['stringValue'])) {
                            $tokens[] = $tokenValue['stringValue'];
                        }
                    }
                }
            }
            
            return array_unique($tokens);
            
        } catch (Exception $e) {
            error_log("Error getting FCM tokens: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send FCM request using curl
     */
    private function sendFCMRequest($payload) {
        if (empty($this->serverKey)) {
            error_log("❌ FCM Server Key not configured");
            return false;
        }
        
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['success']) && $result['success'] > 0) {
                error_log("✅ FCM notification sent successfully");
                return true;
            }
        }
        
        error_log("❌ FCM notification failed. HTTP Code: {$httpCode}, Response: {$response}");
        return false;
    }
    
    /**
     * Store document in Firestore collection
     */
    private function storeInFirestore($collection, $document) {
        try {
            $url = "{$this->firestoreUrl}/{$collection}";
            
            $options = [
                'http' => [
                    'header' => "Content-Type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($document),
                    'timeout' => 30
                ]
            ];
            
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            
            return $result !== FALSE;
            
        } catch (Exception $e) {
            error_log("Firestore storage error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert PHP array to Firestore field format
     */
    private function convertToFirestoreFields($data) {
        $fields = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $fields[$key] = ['stringValue' => $value];
            } elseif (is_int($value)) {
                $fields[$key] = ['integerValue' => (string)$value];
            } elseif (is_float($value)) {
                $fields[$key] = ['doubleValue' => $value];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_array($value)) {
                $fields[$key] = ['mapValue' => ['fields' => $this->convertToFirestoreFields($value)]];
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        
        return $fields;
    }
    
    /**
     * Get admin user IDs
     */
    private function getAdminUserIds() {
        try {
            $adminFile = __DIR__ . '/admin_users.json';
            if (!file_exists($adminFile)) {
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

function sendDirectFirebaseUserNotification($userId, $title, $message, $data = []) {
    $system = new DirectFirebaseNotifications();
    return $system->sendUserNotification($userId, $title, $message, $data);
}

function sendDirectFirebaseAdminNotification($title, $message, $data = []) {
    $system = new DirectFirebaseNotifications();
    return $system->sendAdminNotification($title, $message, $data);
}

function sendDirectFirebaseNotification($userId, $title, $message, $data = []) {
    $system = new DirectFirebaseNotifications();
    return $system->sendCrossPlatformNotification($userId, $title, $message, $data);
}

?> 