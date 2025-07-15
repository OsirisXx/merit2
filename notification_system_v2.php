<?php

/**
 * ENHANCED NOTIFICATION SYSTEM V2
 * Designed to work exactly like mobile application
 */

class NotificationSystemV2 {
    
    private $firebaseProjectId;
    private $firebaseApiKey;
    private $baseUrl;
    
    // Process types matching mobile app exactly
    const PROCESS_ADOPTION = 'ADOPTION';
    const PROCESS_DONATION = 'DONATION';
    const PROCESS_MATCHING = 'MATCHING';
    const PROCESS_APPOINTMENT = 'APPOINTMENT';
    const PROCESS_CHAT = 'CHAT';
    const PROCESS_PROFILE = 'PROFILE';
    const PROCESS_SYSTEM = 'SYSTEM';
    
    // Notification types matching mobile app exactly
    const TYPE_PROCESS_INITIATED = 'PROCESS_INITIATED';
    const TYPE_PROCESS_APPROVED = 'PROCESS_APPROVED';
    const TYPE_PROCESS_REJECTED = 'PROCESS_REJECTED';
    const TYPE_PROCESS_COMPLETED = 'PROCESS_COMPLETED';
    const TYPE_STATUS_UPDATE = 'STATUS_UPDATE';
    const TYPE_DOCUMENT_UPLOADED = 'DOCUMENT_UPLOADED';
    const TYPE_DOCUMENT_APPROVED = 'DOCUMENT_APPROVED';
    const TYPE_DOCUMENT_REJECTED = 'DOCUMENT_REJECTED';
    const TYPE_ADMIN_REVIEW_REQUIRED = 'ADMIN_REVIEW_REQUIRED';
    const TYPE_REMINDER_GENTLE = 'REMINDER_GENTLE';
    const TYPE_REMINDER_URGENT = 'REMINDER_URGENT';
    
    public function __construct() {
        // Load Firebase config
        $config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
        $this->firebaseProjectId = $config['firebase']['projectId'] ?? 'ally-user';
        $this->firebaseApiKey = $config['firebase']['apiKey'] ?? '';
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->firebaseProjectId}/databases/(default)/documents";
    }
    
    /**
     * Send notification - MAIN ENTRY POINT
     */
    public function sendNotification($userId, $processType, $notificationType, $title, $message, $additionalData = []) {
        
        $notificationData = [
            // Core notification fields (mobile app format)
            'userId' => $userId,
            'processType' => strtoupper($processType),
            'notificationType' => strtoupper($notificationType),
            'title' => $title,
            'message' => $message,
            'timestamp' => $this->getCurrentTimestamp(),
            'isRead' => false,
            'readAt' => null,
            'createdAt' => $this->getCurrentTimestamp(),
            
            // Additional fields for compatibility
            'type' => strtolower($processType), // navbar.php expects this
            'category' => strtoupper($processType),
            'status' => 'sent',
            'priority' => isset($additionalData['priority']) ? $additionalData['priority'] : 'normal',
            'source' => isset($additionalData['source']) ? $additionalData['source'] : 'system',
            
            // Custom data
            'data' => $additionalData
        ];
        
        // Try multiple storage methods for reliability
        $success = false;
        
        // Method 1: Direct Firebase REST API (most reliable)
        if ($this->storeInNotificationLogs($notificationData)) {
            $success = true;
            error_log("✅ Notification stored in notification_logs via REST API");
        }
        
        // Method 2: Store in user-specific collection (mobile app compatibility)
        if ($this->storeInUserNotifications($userId, $notificationData)) {
            $success = true;
            error_log("✅ Notification stored in user notifications");
        }
        
        // Method 3: Store in notifications collection (backup)
        if ($this->storeInNotificationsCollection($notificationData)) {
            $success = true;
            error_log("✅ Notification stored in notifications collection");
        }
        
        return $success;
    }
    
    /**
     * Send to admin users
     */
    public function sendAdminNotification($processType, $notificationType, $title, $message, $additionalData = []) {
        $adminUsers = $this->getAdminUsers();
        $success = true;
        
        foreach ($adminUsers as $adminUserId) {
            $adminData = array_merge($additionalData, [
                'isAdminNotification' => true,
                'targetRole' => 'admin'
            ]);
            
            if (!$this->sendNotification($adminUserId, $processType, $notificationType, $title, $message, $adminData)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Store notification in notification_logs collection (main collection)
     */
    private function storeInNotificationLogs($data) {
        $url = $this->baseUrl . "/notification_logs";
        return $this->makeFirestoreRequest($url, 'POST', $data);
    }
    
    /**
     * Store notification in user-specific notifications subcollection
     */
    private function storeInUserNotifications($userId, $data) {
        $url = $this->baseUrl . "/users/{$userId}/notifications";
        return $this->makeFirestoreRequest($url, 'POST', $data);
    }
    
    /**
     * Store notification in notifications collection (backup)
     */
    private function storeInNotificationsCollection($data) {
        $url = $this->baseUrl . "/notifications";
        return $this->makeFirestoreRequest($url, 'POST', $data);
    }
    
    /**
     * Make Firestore REST API request
     */
    private function makeFirestoreRequest($url, $method, $data) {
        $firestoreData = [
            'fields' => $this->convertToFirestoreFields($data)
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => $method,
                'content' => json_encode($firestoreData),
                'timeout' => 30,
                'ignore_errors' => true
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            $error = error_get_last();
            error_log("Firebase request failed: " . (isset($error['message']) ? $error['message'] : 'Unknown error'));
            return false;
        }
        
        $response = json_decode($result, true);
        if (!$response) {
            error_log("Firebase response parsing failed");
            return false;
        }
        
        return true;
    }
    
    /**
     * Get notifications for user (for API endpoints)
     */
    public function getNotificationsForUser($userId, $limit = 20) {
        $url = $this->baseUrl . "/notification_logs";
        
        $context = stream_context_create([
            'http' => ['timeout' => 30, 'ignore_errors' => true]
        ]);
        
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            return [];
        }
        
        $response = json_decode($result, true);
        $notifications = [];
        
        if (isset($response['documents'])) {
            foreach ($response['documents'] as $doc) {
                $docData = $this->convertFromFirestoreFields(isset($doc['fields']) ? $doc['fields'] : []);
                
                // Filter by userId
                if (isset($docData['userId']) && $docData['userId'] == $userId) {
                    $docData['id'] = basename($doc['name']);
                    $notifications[] = $docData;
                }
            }
        }
        
        // Sort by timestamp descending
        usort($notifications, function($a, $b) {
            $aTime = isset($a['timestamp']) ? $a['timestamp'] : 0;
            $bTime = isset($b['timestamp']) ? $b['timestamp'] : 0;
            return $bTime - $aTime;
        });
        
        return array_slice($notifications, 0, $limit);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        $url = $this->baseUrl . "/notification_logs/" . $notificationId;
        
        $updates = [
            'isRead' => true,
            'readAt' => $this->getCurrentTimestamp()
        ];
        
        return $this->updateNotification($notificationId, $updates);
    }
    
    /**
     * Update notification
     */
    private function updateNotification($notificationId, $updates) {
        $url = $this->baseUrl . "/notification_logs/" . $notificationId;
        
        // Get existing document
        $existingDoc = @file_get_contents($url);
        if ($existingDoc === FALSE) {
            return false;
        }
        
        $existing = json_decode($existingDoc, true);
        if (!$existing || !isset($existing['fields'])) {
            return false;
        }
        
        // Merge updates
        $existingData = $this->convertFromFirestoreFields($existing['fields']);
        $updatedData = array_merge($existingData, $updates);
        
        $firestoreData = [
            'fields' => $this->convertToFirestoreFields($updatedData)
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'PATCH',
                'content' => json_encode($firestoreData),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        return $result !== FALSE;
    }
    
    /**
     * Get admin users
     */
    private function getAdminUsers() {
        // For now, return a default admin user ID
        // In production, this would query the users collection for role='admin'
        return ['admin_user_id']; // Replace with actual admin user IDs
    }
    
    /**
     * Get current timestamp in milliseconds (mobile app format)
     */
    private function getCurrentTimestamp() {
        return time() * 1000;
    }
    
    /**
     * Convert PHP array to Firestore fields format
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
            } elseif (is_null($value)) {
                $fields[$key] = ['nullValue' => null];
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        
        return $fields;
    }
    
    /**
     * Convert Firestore fields format to PHP array
     */
    private function convertFromFirestoreFields($fields) {
        $data = [];
        
        foreach ($fields as $key => $value) {
            if (isset($value['stringValue'])) {
                $data[$key] = $value['stringValue'];
            } elseif (isset($value['integerValue'])) {
                $data[$key] = (int)$value['integerValue'];
            } elseif (isset($value['doubleValue'])) {
                $data[$key] = (float)$value['doubleValue'];
            } elseif (isset($value['booleanValue'])) {
                $data[$key] = $value['booleanValue'];
            } elseif (isset($value['mapValue']['fields'])) {
                $data[$key] = $this->convertFromFirestoreFields($value['mapValue']['fields']);
            } elseif (isset($value['nullValue'])) {
                $data[$key] = null;
            } else {
                $data[$key] = null;
            }
        }
        
        return $data;
    }
    
    // Convenience methods for different notification types
    
    public function sendDonationNotification($userId, $donationType, $status, $additionalData = []) {
        $titles = [
            'submitted' => '📦 Donation Submitted',
            'approved' => '✅ Donation Approved',
            'rejected' => '❌ Donation Rejected',
            'completed' => '🎉 Donation Completed'
        ];
        
        $messages = [
            'submitted' => "Your $donationType donation has been submitted for review.",
            'approved' => "Great news! Your $donationType donation has been approved.",
            'rejected' => "Your $donationType donation requires attention. Please check the details.",
            'completed' => "Thank you! Your $donationType donation has been completed successfully."
        ];
        
        // Determine notification type based on status
        $notificationType = self::TYPE_STATUS_UPDATE;
        if ($status === 'submitted') {
            $notificationType = self::TYPE_PROCESS_INITIATED;
        } elseif ($status === 'approved') {
            $notificationType = self::TYPE_PROCESS_APPROVED;
        } elseif ($status === 'rejected') {
            $notificationType = self::TYPE_PROCESS_REJECTED;
        } elseif ($status === 'completed') {
            $notificationType = self::TYPE_PROCESS_COMPLETED;
        }
        
        return $this->sendNotification(
            $userId,
            self::PROCESS_DONATION,
            $notificationType,
            isset($titles[$status]) ? $titles[$status] : '📦 Donation Update',
            isset($messages[$status]) ? $messages[$status] : "Your $donationType donation status has been updated to $status.",
            array_merge($additionalData, ['donationType' => $donationType, 'status' => $status])
        );
    }
    
    public function sendAdoptionNotification($userId, $status, $stepNumber = null, $additionalData = []) {
        $titles = [
            'initiated' => '👶 Adoption Process Started',
            'step_completed' => '✅ Step Completed',
            'approved' => '🎉 Adoption Approved',
            'rejected' => '❌ Adoption Rejected',
            'completed' => '💕 Adoption Completed'
        ];
        
        $messages = [
            'initiated' => 'Your adoption process has been initiated. Please complete the required steps.',
            'step_completed' => $stepNumber ? "Step $stepNumber has been completed successfully." : 'A step in your adoption process has been completed.',
            'approved' => 'Congratulations! Your adoption has been approved.',
            'rejected' => 'Your adoption requires attention. Please check the details.',
            'completed' => 'Congratulations! Your adoption process has been completed successfully.'
        ];
        
        // Determine notification type based on status
        $notificationType = self::TYPE_STATUS_UPDATE;
        if ($status === 'initiated') {
            $notificationType = self::TYPE_PROCESS_INITIATED;
        } elseif ($status === 'step_completed') {
            $notificationType = self::TYPE_STATUS_UPDATE;
        } elseif ($status === 'approved') {
            $notificationType = self::TYPE_PROCESS_APPROVED;
        } elseif ($status === 'rejected') {
            $notificationType = self::TYPE_PROCESS_REJECTED;
        } elseif ($status === 'completed') {
            $notificationType = self::TYPE_PROCESS_COMPLETED;
        }
        
        return $this->sendNotification(
            $userId,
            self::PROCESS_ADOPTION,
            $notificationType,
            isset($titles[$status]) ? $titles[$status] : '👶 Adoption Update',
            isset($messages[$status]) ? $messages[$status] : "Your adoption status has been updated to $status.",
            array_merge($additionalData, ['status' => $status, 'stepNumber' => $stepNumber])
        );
    }
    
    public function sendAppointmentNotification($userId, $status, $appointmentDate = null, $additionalData = []) {
        $titles = [
            'scheduled' => '📅 Appointment Scheduled',
            'confirmed' => '✅ Appointment Confirmed',
            'cancelled' => '❌ Appointment Cancelled',
            'reminder' => '⏰ Appointment Reminder'
        ];
        
        $messages = [
            'scheduled' => $appointmentDate ? "Your appointment has been scheduled for $appointmentDate." : 'Your appointment has been scheduled.',
            'confirmed' => $appointmentDate ? "Your appointment for $appointmentDate has been confirmed." : 'Your appointment has been confirmed.',
            'cancelled' => $appointmentDate ? "Your appointment for $appointmentDate has been cancelled." : 'Your appointment has been cancelled.',
            'reminder' => $appointmentDate ? "Reminder: You have an appointment on $appointmentDate." : 'You have an upcoming appointment.'
        ];
        
        // Determine notification type based on status
        $notificationType = self::TYPE_STATUS_UPDATE;
        if ($status === 'scheduled') {
            $notificationType = self::TYPE_PROCESS_INITIATED;
        } elseif ($status === 'confirmed') {
            $notificationType = self::TYPE_PROCESS_APPROVED;
        } elseif ($status === 'cancelled') {
            $notificationType = self::TYPE_PROCESS_REJECTED;
        } elseif ($status === 'reminder') {
            $notificationType = self::TYPE_REMINDER_GENTLE;
        }
        
        return $this->sendNotification(
            $userId,
            self::PROCESS_APPOINTMENT,
            $notificationType,
            isset($titles[$status]) ? $titles[$status] : '📅 Appointment Update',
            isset($messages[$status]) ? $messages[$status] : "Your appointment status has been updated to $status.",
            array_merge($additionalData, ['status' => $status, 'appointmentDate' => $appointmentDate])
        );
    }
    
    public function sendTestNotification($userId, $additionalData = []) {
        return $this->sendNotification(
            $userId,
            self::PROCESS_SYSTEM,
            self::TYPE_STATUS_UPDATE,
            '🔔 Test Notification',
            'This is a test notification to verify the notification system is working properly.',
            array_merge($additionalData, ['source' => 'test', 'isTest' => true])
        );
    }
}

// Create global function for easy access
function getNotificationSystemV2() {
    return new NotificationSystemV2();
}

?> 