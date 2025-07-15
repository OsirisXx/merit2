<?php
/**
 * Firebase Admin SDK Notification System
 * Uses service account key for modern, secure FCM notifications
 */

class FirebaseAdminNotifications {
    private $serviceAccountPath;
    private $projectId;
    private $accessToken;
    private $tokenExpiry;
    
    public function __construct() {
        $this->serviceAccountPath = __DIR__ . '/functions/ally-user-firebase-adminsdk-fbsvc-4f2d3d1509.json';
        $this->projectId = 'ally-user';
        $this->accessToken = null;
        $this->tokenExpiry = 0;
    }
    
    /**
     * Get OAuth 2.0 access token for Firebase Admin SDK
     */
    private function getAccessToken() {
        // Check if token is still valid (with 5 minute buffer)
        if ($this->accessToken && time() < ($this->tokenExpiry - 300)) {
            return $this->accessToken;
        }
        
        if (!file_exists($this->serviceAccountPath)) {
            throw new Exception("Service account key file not found: {$this->serviceAccountPath}");
        }
        
        $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
        
        // Create JWT for Google OAuth
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $now = time();
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];
        
        $jwt = $this->createJWT($header, $payload, $serviceAccount['private_key']);
        
        // Exchange JWT for access token
        $response = $this->makeHttpRequest('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);
        
        if (!$response || !isset($response['access_token'])) {
            throw new Exception("Failed to get access token from Google OAuth");
        }
        
        $this->accessToken = $response['access_token'];
        $this->tokenExpiry = $now + ($response['expires_in'] ?? 3600);
        
        return $this->accessToken;
    }
    
    /**
     * Create JWT token for OAuth
     */
    private function createJWT($header, $payload, $privateKey) {
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = '';
        $success = openssl_sign(
            $headerEncoded . '.' . $payloadEncoded,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );
        
        if (!$success) {
            throw new Exception("Failed to sign JWT");
        }
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $this->base64UrlEncode($signature);
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Make HTTP request with cURL
     */
    private function makeHttpRequest($url, $data = null, $headers = []) {
        $ch = curl_init();
        
        $defaultHeaders = ['Content-Type: application/x-www-form-urlencoded'];
        $headers = array_merge($defaultHeaders, $headers);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: $error");
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = $decoded['error']['message'] ?? "HTTP $httpCode error";
            throw new Exception("Request failed: $errorMsg");
        }
        
        return $decoded;
    }
    
    /**
     * Send notification to specific FCM tokens
     */
    public function sendToTokens($tokens, $title, $message, $data = []) {
        if (empty($tokens)) {
            error_log("No FCM tokens provided");
            return false;
        }
        
        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }
        
        try {
            $accessToken = $this->getAccessToken();
            
            $notification = [
                'title' => $title,
                'body' => $message
            ];
            
            $fcmData = [];
            foreach ($data as $key => $value) {
                $fcmData[$key] = (string)$value;
            }
            
            $results = [];
            
            // Send to each token individually for better error handling
            foreach ($tokens as $token) {
                try {
                    $payload = [
                        'message' => [
                            'token' => $token,
                            'notification' => $notification,
                            'data' => $fcmData,
                            'android' => [
                                'notification' => [
                                    'sound' => 'default',
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                                ]
                            ]
                        ]
                    ];
                    
                    $response = $this->makeHttpRequest(
                        "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                        json_encode($payload),
                        [
                            'Authorization: Bearer ' . $accessToken,
                            'Content-Type: application/json'
                        ]
                    );
                    
                    $results[] = ['token' => $token, 'success' => true, 'response' => $response];
                    error_log("✅ FCM notification sent successfully to token: " . substr($token, 0, 20) . "...");
                    
                } catch (Exception $e) {
                    $results[] = ['token' => $token, 'success' => false, 'error' => $e->getMessage()];
                    error_log("❌ FCM notification failed for token " . substr($token, 0, 20) . "...: " . $e->getMessage());
                }
            }
            
            $successCount = count(array_filter($results, function($r) { return $r['success']; }));
            error_log("📊 FCM batch complete: {$successCount}/" . count($tokens) . " notifications sent");
            
            return $successCount > 0;
            
        } catch (Exception $e) {
            error_log("❌ FCM notification system error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification to a user (gets their FCM tokens from Firestore)
     */
    public function sendToUser($userId, $title, $message, $data = []) {
        try {
            $tokens = $this->getUserFCMTokens($userId);
            
            if (empty($tokens)) {
                error_log("⚠️ No FCM tokens found for user: $userId");
                return false;
            }
            
            return $this->sendToTokens($tokens, $title, $message, $data);
            
        } catch (Exception $e) {
            error_log("❌ Error sending notification to user $userId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get FCM tokens for a user from Firestore
     */
    private function getUserFCMTokens($userId) {
        try {
            $accessToken = $this->getAccessToken();
            
            // Query Firestore for user's FCM tokens
            $firestoreUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/fcm_tokens/{$userId}";
            
            $response = $this->makeHttpRequest(
                $firestoreUrl,
                null,
                ['Authorization: Bearer ' . $accessToken]
            );
            
            if (!$response || !isset($response['fields'])) {
                // Try alternative collection structure
                $firestoreUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/users/{$userId}";
                $response = $this->makeHttpRequest(
                    $firestoreUrl,
                    null,
                    ['Authorization: Bearer ' . $accessToken]
                );
                
                if ($response && isset($response['fields']['fcmToken']['stringValue'])) {
                    return [$response['fields']['fcmToken']['stringValue']];
                }
                
                return [];
            }
            
            $tokens = [];
            
            // Extract tokens from Firestore format
            if (isset($response['fields']['tokens']['arrayValue']['values'])) {
                foreach ($response['fields']['tokens']['arrayValue']['values'] as $tokenData) {
                    if (isset($tokenData['stringValue'])) {
                        $tokens[] = $tokenData['stringValue'];
                    }
                }
            } else if (isset($response['fields']['token']['stringValue'])) {
                $tokens[] = $response['fields']['token']['stringValue'];
            }
            
            return array_filter($tokens); // Remove empty tokens
            
        } catch (Exception $e) {
            error_log("Error fetching FCM tokens for user $userId: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send notification to all admin users
     */
    public function sendToAdmins($title, $message, $data = []) {
        try {
            $adminIds = $this->getAdminUserIds();
            
            if (empty($adminIds)) {
                error_log("⚠️ No admin users found");
                return false;
            }
            
            $success = false;
            foreach ($adminIds as $adminId) {
                if ($this->sendToUser($adminId, $title, $message, $data)) {
                    $success = true;
                }
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("❌ Error sending admin notifications: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get admin user IDs
     */
    private function getAdminUserIds() {
        $adminFile = __DIR__ . '/admin_users.json';
        
        if (file_exists($adminFile)) {
            $content = file_get_contents($adminFile);
            $data = json_decode($content, true);
            return is_array($data) ? $data : [];
        }
        
        return [];
    }
    
    /**
     * Test the notification system
     */
    public function testNotification($testToken = null) {
        try {
            if ($testToken) {
                return $this->sendToTokens(
                    [$testToken],
                    'Test Notification',
                    'This is a test notification from the Firebase Admin SDK system.',
                    ['test' => 'true', 'timestamp' => (string)time()]
                );
            } else {
                // Test with a dummy token to verify authentication
                $accessToken = $this->getAccessToken();
                return !empty($accessToken);
            }
        } catch (Exception $e) {
            error_log("Test notification failed: " . $e->getMessage());
            return false;
        }
    }
}

// Removed duplicate global functions to prevent redeclaration errors
// These functions are already defined in firebase_notifications_bridge.php
?> 