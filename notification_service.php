<?php

class NotificationService {
    
    // Process types matching mobile app
    const PROCESS_ADOPTION = 'ADOPTION';
    const PROCESS_DONATION = 'DONATION';
    const PROCESS_MATCHING = 'MATCHING';
    const PROCESS_APPOINTMENT = 'APPOINTMENT';
    const PROCESS_CHAT = 'CHAT';
    const PROCESS_PROFILE = 'PROFILE';
    const PROCESS_SYSTEM = 'SYSTEM';
    
    // Notification types matching mobile app
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
    const TYPE_DEADLINE_APPROACHING = 'DEADLINE_APPROACHING';
    const TYPE_DEADLINE_MISSED = 'DEADLINE_MISSED';
    const TYPE_SYSTEM_MAINTENANCE = 'SYSTEM_MAINTENANCE';
    const TYPE_ACCOUNT_SECURITY = 'ACCOUNT_SECURITY';
    
    private $firebaseProjectId;
    private $firebaseApiKey;
    
    public function __construct() {
        // Load Firebase config
        $config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
        $this->firebaseProjectId = $config['firebase']['projectId'] ?? 'ally-user';
        $this->firebaseApiKey = $config['firebase']['apiKey'] ?? 'AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk';
    }
    
    /**
     * Send process notification to user
     */
    public function sendProcessNotification($processType, $notificationType, $userId, $title, $message, $data = []) {
        $notificationData = [
            'userId' => $userId,
            'processType' => $processType,
            'notificationType' => $notificationType,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'timestamp' => time() * 1000, // Firestore timestamp in milliseconds
            'isRead' => false,
            'isAdminNotification' => false,
            // Add fields that mobile app might expect
            'type' => strtolower($processType), // mobile app may expect lowercase type
            'category' => strtoupper($processType), // category in uppercase
            'status' => 'sent',
            'priority' => 'normal'
        ];
        
        try {
            // Use CRUD class for more reliable notification storage
            require_once __DIR__ . '/notification_crud.php';
            $crud = new NotificationCRUD();
            $success = $crud->createNotification($notificationData);
            
            // Also try original method as backup
            if (!$success) {
                $this->sendToFirestore('notification_logs', $notificationData);
                $this->sendToFirestore('notifications', $notificationData);
                $this->sendToUserNotifications($userId, $notificationData);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send admin notification
     */
    public function sendAdminNotification($processType, $notificationType, $title, $message, $data = []) {
        $adminUsers = $this->getAdminUsers();
        $success = true;
        
        foreach ($adminUsers as $adminUserId) {
            $notificationData = [
                'userId' => $adminUserId,
                'processType' => $processType,
                'notificationType' => $notificationType,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'timestamp' => time() * 1000,
                'isRead' => false,
                'isAdminNotification' => true,
                // Add fields that mobile app might expect
                'type' => strtolower($processType),
                'category' => strtoupper($processType),
                'status' => 'sent',
                'priority' => 'normal'
            ];
            
            try {
                // Use CRUD class for more reliable notification storage
                require_once __DIR__ . '/notification_crud.php';
                $crud = new NotificationCRUD();
                $success = $crud->createNotification($notificationData);
                
                // Also try original method as backup
                if (!$success) {
                    $this->sendToFirestore('notification_logs', $notificationData);
                    $this->sendToFirestore('notifications', $notificationData);
                    $this->sendToUserNotifications($adminUserId, $notificationData);
                }
            } catch (Exception $e) {
                error_log("Failed to send admin notification to $adminUserId: " . $e->getMessage());
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Send security notification
     */
    public function sendSecurityNotification($userId, $securityEvent, $details, $actionRequired = false) {
        $title = '🔒 Security Alert';
        $urgency = $actionRequired ? 'urgent' : 'normal';
        
        $notificationType = $actionRequired ? 
            self::TYPE_ACCOUNT_SECURITY : 
            self::TYPE_STATUS_UPDATE;
        
        $data = [
            'securityEvent' => $securityEvent,
            'details' => $details,
            'actionRequired' => $actionRequired,
            'urgency' => $urgency
        ];
        
        return $this->sendProcessNotification(
            self::PROCESS_SYSTEM,
            $notificationType,
            $userId,
            $title,
            $details,
            $data
        );
    }
    
    /**
     * Send reminder notification
     */
    public function sendReminderNotification($processType, $userId, $title, $message, $urgencyLevel = 'normal', $data = []) {
        $notificationType = $urgencyLevel === 'urgent' ? 
            self::TYPE_REMINDER_URGENT : 
            self::TYPE_REMINDER_GENTLE;
        
        $data = array_merge($data, [
            'urgencyLevel' => $urgencyLevel,
            'isReminder' => true
        ]);
        
        return $this->sendProcessNotification(
            $processType,
            $notificationType,
            $userId,
            $title,
            $message,
            $data
        );
    }
    
    /**
     * Send donation notification
     */
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
        
        switch($status) {
            case 'submitted':
                $notificationType = self::TYPE_PROCESS_INITIATED;
                break;
            case 'approved':
                $notificationType = self::TYPE_PROCESS_APPROVED;
                break;
            case 'rejected':
                $notificationType = self::TYPE_PROCESS_REJECTED;
                break;
            case 'completed':
                $notificationType = self::TYPE_PROCESS_COMPLETED;
                break;
            default:
                $notificationType = self::TYPE_STATUS_UPDATE;
                break;
        }
        
        $data = array_merge($additionalData, [
            'donationType' => $donationType,
            'status' => $status
        ]);
        
        return $this->sendProcessNotification(
            self::PROCESS_DONATION,
            $notificationType,
            $userId,
            $titles[$status] ?? 'Donation Update',
            $messages[$status] ?? "Your donation status has been updated.",
            $data
        );
    }
    
    /**
     * Send adoption notification
     */
    public function sendAdoptionNotification($userId, $status, $stepNumber = null, $additionalData = []) {
        $titles = [
            'process_started' => '🎉 Adoption Started',
            'step_completed' => '✅ Step Completed',
            'process_completed' => '🎉 Adoption Completed',
            'document_uploaded' => '📄 Document Uploaded',
            'document_approved' => '✅ Document Approved',
            'document_rejected' => '❌ Document Needs Review'
        ];
        
        $stepText = $stepNumber ? " Step $stepNumber" : "";
        
        switch($status) {
            case 'process_started':
                $message = "🎊 Congratulations! Your adoption process has been started successfully. Begin with Step 1 when you're ready!";
                $notificationType = self::TYPE_PROCESS_INITIATED;
                break;
            case 'step_completed':
                $message = "Congratulations! You have completed$stepText of your adoption process.";
                $notificationType = self::TYPE_PROCESS_APPROVED;
                break;
            case 'process_completed':
                $message = "🎊 Congratulations! Your adoption process has been completed successfully!";
                $notificationType = self::TYPE_PROCESS_COMPLETED;
                break;
            case 'document_uploaded':
                $message = "Your document for$stepText has been uploaded and is under review.";
                $notificationType = self::TYPE_DOCUMENT_UPLOADED;
                break;
            case 'document_approved':
                $message = "Your document for$stepText has been approved.";
                $notificationType = self::TYPE_DOCUMENT_APPROVED;
                break;
            case 'document_rejected':
                $message = "Your document for$stepText needs to be reviewed and resubmitted.";
                $notificationType = self::TYPE_DOCUMENT_REJECTED;
                break;
            default:
                $message = "Your adoption process has been updated.";
                $notificationType = self::TYPE_STATUS_UPDATE;
                break;
        }
        
        $data = array_merge($additionalData, [
            'status' => $status,
            'stepNumber' => $stepNumber
        ]);
        
        return $this->sendProcessNotification(
            self::PROCESS_ADOPTION,
            $notificationType,
            $userId,
            $titles[$status] ?? 'Adoption Update',
            $message,
            $data
        );
    }
    
    /**
     * Send appointment notification
     */
    public function sendAppointmentNotification($userId, $status, $appointmentDate = null, $additionalData = []) {
        $titles = [
            'scheduled' => '📅 Appointment Scheduled',
            'confirmed' => '✅ Appointment Confirmed',
            'cancelled' => '❌ Appointment Cancelled',
            'reminder' => '⏰ Appointment Reminder',
            'completed' => '✅ Appointment Completed'
        ];
        
        $dateText = $appointmentDate ? " for $appointmentDate" : "";
        
        switch($status) {
            case 'scheduled':
                $message = "Your appointment has been scheduled$dateText.";
                $notificationType = self::TYPE_PROCESS_INITIATED;
                break;
            case 'confirmed':
                $message = "Your appointment$dateText has been confirmed.";
                $notificationType = self::TYPE_PROCESS_APPROVED;
                break;
            case 'cancelled':
                $message = "Your appointment$dateText has been cancelled.";
                $notificationType = self::TYPE_PROCESS_REJECTED;
                break;
            case 'reminder':
                $message = "Reminder: You have an appointment$dateText.";
                $notificationType = self::TYPE_REMINDER_GENTLE;
                break;
            case 'completed':
                $message = "Your appointment$dateText has been completed.";
                $notificationType = self::TYPE_PROCESS_APPROVED;
                break;
            default:
                $message = "Your appointment has been updated.";
                $notificationType = self::TYPE_STATUS_UPDATE;
                break;
        }
        
        $data = array_merge($additionalData, [
            'status' => $status,
            'appointmentDate' => $appointmentDate
        ]);
        
        return $this->sendProcessNotification(
            self::PROCESS_APPOINTMENT,
            $notificationType,
            $userId,
            $titles[$status] ?? 'Appointment Update',
            $message,
            $data
        );
    }
    
    /**
     * Send matching notification
     */
    public function sendMatchingNotification($userId, $status, $childName = null, $additionalData = []) {
        $titles = [
            'request_submitted' => '💕 Matching Submitted',
            'match_found' => '🎉 Match Found!',
            'match_accepted' => '✅ Match Accepted',
            'match_rejected' => '❌ Match Not Suitable',
            'process_completed' => '🎊 Matching Completed'
        ];
        
        $childText = $childName ? " with $childName" : "";
        
        switch($status) {
            case 'request_submitted':
                $message = "You have been automatically matched. Our team will review the details and contact you soon.";
                $notificationType = self::TYPE_PROCESS_INITIATED;
                break;
            case 'match_found':
                $message = "Great news! We found a potential match$childText.";
                $notificationType = self::TYPE_PROCESS_COMPLETED;
                break;
            case 'match_accepted':
                $message = "Your match$childText has been accepted. The next steps will be communicated soon.";
                $notificationType = self::TYPE_PROCESS_COMPLETED;
                break;
            case 'match_rejected':
                $message = "The match$childText was not suitable. We'll continue searching for you.";
                $notificationType = self::TYPE_PROCESS_REJECTED;
                break;
            case 'process_completed':
                $message = "Congratulations! Your matching process$childText has been completed.";
                $notificationType = self::TYPE_PROCESS_COMPLETED;
                break;
            default:
                $message = "Your matching process has been updated.";
                $notificationType = self::TYPE_STATUS_UPDATE;
                break;
        }
        
        $data = array_merge($additionalData, [
            'status' => $status,
            'childName' => $childName
        ]);
        
        return $this->sendProcessNotification(
            self::PROCESS_MATCHING,
            $notificationType,
            $userId,
            $titles[$status] ?? 'Matching Update',
            $message,
            $data
        );
    }
    
    /**
     * Send chat notification
     */
    public function sendChatNotification($userId, $senderName, $message, $chatUserId, $additionalData = []) {
        $title = "💬 New message from $senderName";
        
        $data = array_merge($additionalData, [
            'senderName' => $senderName,
            'chatUserId' => $chatUserId,
            'messagePreview' => substr($message, 0, 100)
        ]);
        
        return $this->sendProcessNotification(
            self::PROCESS_CHAT,
            self::TYPE_STATUS_UPDATE,
            $userId,
            $title,
            $message,
            $data
        );
    }
    
    /**
     * Get admin users from admin_users.json
     */
    private function getAdminUsers() {
        try {
            $adminUsersFile = __DIR__ . '/admin_users.json';
            if (file_exists($adminUsersFile)) {
                $adminUsers = json_decode(file_get_contents($adminUsersFile), true);
                if (is_array($adminUsers) && !empty($adminUsers)) {
                    return $adminUsers;
                }
            }
        } catch (Exception $e) {
            error_log("Failed to load admin users: " . $e->getMessage());
        }
        
        // Fallback to default admin user
        return ['h8qq0E8avWO74cqS2Goy1wtENJh1'];
    }
    
    /**
     * Send notification to Firestore using REST API
     */
    private function sendToFirestore($collection, $data) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->firebaseProjectId}/databases/(default)/documents/$collection";
        
        $postData = [
            'fields' => $this->convertToFirestoreFields($data)
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($postData),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            // Log the error but don't fail completely
            error_log("Failed to send notification to Firestore: $collection");
            return false;
        }
        
        return json_decode($result, true);
    }
    
    /**
     * Send notification to user's notifications subcollection
     */
    private function sendToUserNotifications($userId, $data) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->firebaseProjectId}/databases/(default)/documents/users/$userId/notifications";
        
        $postData = [
            'fields' => $this->convertToFirestoreFields($data)
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($postData),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        // Don't throw exception if this fails, as it's supplementary
        if ($result === FALSE) {
            error_log("Failed to send notification to user's collection: $userId");
        }
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
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        
        return $fields;
    }
}

// Helper function to create notification service instance
function getNotificationService() {
    return new NotificationService();
}

// Helper functions for common notifications
function sendDonationNotification($userId, $donationType, $status, $additionalData = []) {
    return getNotificationService()->sendDonationNotification($userId, $donationType, $status, $additionalData);
}

function sendAdoptionNotification($userId, $status, $stepNumber = null, $additionalData = []) {
    return getNotificationService()->sendAdoptionNotification($userId, $status, $stepNumber, $additionalData);
}

function sendAppointmentNotification($userId, $status, $appointmentDate = null, $additionalData = []) {
    return getNotificationService()->sendAppointmentNotification($userId, $status, $appointmentDate, $additionalData);
}

function sendMatchingNotification($userId, $status, $childName = null, $additionalData = []) {
    return getNotificationService()->sendMatchingNotification($userId, $status, $childName, $additionalData);
}

function sendChatNotification($userId, $senderName, $message, $chatUserId, $additionalData = []) {
    return getNotificationService()->sendChatNotification($userId, $senderName, $message, $chatUserId, $additionalData);
}

?> 