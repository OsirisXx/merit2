<?php
/**
 * Simple Mobile Notification Add-on
 * This adds mobile push notifications to your existing notification system
 * WITHOUT breaking the existing web-to-web functionality
 */

class SimpleMobileNotifications {
    private $fcmServerKey;
    
    public function __construct() {
        // Try to get FCM server key from config
        $configFile = __DIR__ . '/config.json';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            $this->fcmServerKey = $config['firebase']['serverKey'] ?? null;
        }
    }
    
    /**
     * Send FCM notification to mobile devices
     * This is called AFTER the web notification is sent successfully
     */
    public function sendToMobile($userId, $title, $message, $data = []) {
        if (!$this->fcmServerKey || $this->fcmServerKey === 'YOUR_FCM_SERVER_KEY_HERE') {
            // Silently fail if no FCM key configured - don't break web notifications
            return true;
        }
        
        try {
            // Get user's FCM token from a simple file (not complex Firestore)
            $token = $this->getUserFCMToken($userId);
            if (!$token) {
                // No mobile token, but that's OK - web notifications still work
                return true;
            }
            
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                    'sound' => 'default'
                ],
                'data' => $data
            ];
            
            $this->sendFCMRequest($payload);
            return true; // Always return true so web notifications aren't affected
            
        } catch (Exception $e) {
            // Log error but don't break web notifications
            error_log("Mobile notification failed: " . $e->getMessage());
            return true;
        }
    }
    
    /**
     * Get user's FCM token from simple file storage
     */
    private function getUserFCMToken($userId) {
        $tokenFile = __DIR__ . "/fcm_tokens.json";
        if (!file_exists($tokenFile)) {
            return null;
        }
        
        $tokens = json_decode(file_get_contents($tokenFile), true);
        return $tokens[$userId] ?? null;
    }
    
    /**
     * Store user's FCM token (called from mobile app or registration)
     */
    public function storeFCMToken($userId, $token) {
        $tokenFile = __DIR__ . "/fcm_tokens.json";
        $tokens = [];
        
        if (file_exists($tokenFile)) {
            $tokens = json_decode(file_get_contents($tokenFile), true) ?: [];
        }
        
        $tokens[$userId] = $token;
        file_put_contents($tokenFile, json_encode($tokens, JSON_PRETTY_PRINT));
        return true;
    }
    
    /**
     * Send FCM request
     */
    private function sendFCMRequest($payload) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = [
            'Authorization: key=' . $this->fcmServerKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            error_log("✅ Mobile notification sent successfully");
        } else {
            error_log("⚠️ Mobile notification failed: HTTP $httpCode");
        }
    }
}

// Global helper function that safely adds mobile notifications
function sendMobileNotificationSafely($userId, $title, $message, $data = []) {
    try {
        $mobileNotifications = new SimpleMobileNotifications();
        $mobileNotifications->sendToMobile($userId, $title, $message, $data);
    } catch (Exception $e) {
        // Silently handle any errors so web notifications keep working
        error_log("Mobile notification error: " . $e->getMessage());
    }
}

?> 