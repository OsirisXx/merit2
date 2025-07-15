<?php
/**
 * SIMPLE NOTIFICATION SYSTEM - MOBILE APP COPY
 * Direct copy of mobile app logic, no complexity
 */

class SimpleNotificationSystem {
    
    // Process types - exact copy from mobile app
    const PROCESS_ADOPTION = 'ADOPTION';
    const PROCESS_DONATION = 'DONATION';
    const PROCESS_MATCHING = 'MATCHING';
    const PROCESS_APPOINTMENT = 'APPOINTMENT';
    const PROCESS_CHAT = 'CHAT';
    const PROCESS_PROFILE = 'PROFILE';
    const PROCESS_SYSTEM = 'SYSTEM';
    
    // Notification types - exact copy from mobile app
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
    
    private $firestoreUrl = 'https://firestore.googleapis.com/v1/projects/ally-user/databases/(default)/documents/';
    
    /**
     * Send notification - exact copy of mobile app logic
     */
    public function sendNotification($userId, $processType, $notificationType, $title, $message, $data = []) {
        try {
            // Create notification object - same structure as mobile app
            $notification = [
                'userId' => $userId,
                'processType' => $processType,
                'notificationType' => $notificationType,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'timestamp' => time() * 1000, // Mobile app uses milliseconds
                'status' => 'sent',
                'isRead' => false,
                'icon' => $this->getNotificationIcon($processType),
                'id' => uniqid('notif_', true)
            ];
            
            // Store in Firebase - same collection as mobile app
            $success = $this->storeInFirebase($notification);
            
            if ($success) {
                error_log("✅ Simple notification sent: {$title}");
                return true;
            } else {
                error_log("❌ Failed to send notification: {$title}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("❌ Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Store notification in Firestore - same as mobile app
     */
    private function storeInFirebase($notification) {
        try {
            // Convert to Firestore format
            $firestoreDoc = [
                'fields' => []
            ];
            
            foreach ($notification as $key => $value) {
                if (is_string($value)) {
                    $firestoreDoc['fields'][$key] = ['stringValue' => $value];
                } elseif (is_int($value) || is_float($value)) {
                    $firestoreDoc['fields'][$key] = ['integerValue' => (string)$value];
                } elseif (is_bool($value)) {
                    $firestoreDoc['fields'][$key] = ['booleanValue' => $value];
                } elseif (is_array($value)) {
                    $firestoreDoc['fields'][$key] = ['stringValue' => json_encode($value)];
                }
            }
            
            $url = $this->firestoreUrl . 'notification_logs';
            $data = json_encode($firestoreDoc);
            
            // Try cURL first
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                return ($httpCode === 200 || $httpCode === 201) && $response !== false;
            }
            
            // Fallback to file_get_contents
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $data,
                    'timeout' => 10
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            return $response !== false;
            
        } catch (Exception $e) {
            error_log("Firestore storage error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notifications for user - same as mobile app
     */
    public function getNotifications($userId, $limit = 20) {
        try {
            $url = $this->firebaseUrl . 'notification_logs.json?orderBy="userId"&equalTo="' . $userId . '"&limitToLast=' . $limit;
            
            $response = false;
            
            // Try cURL first
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                curl_close($ch);
            }
            
            // Fallback to file_get_contents
            if ($response === false) {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ]);
                
                $response = file_get_contents($url, false, $context);
            }
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && is_array($data)) {
                    $notifications = [];
                    foreach ($data as $key => $notification) {
                        $notification['id'] = $key;
                        $notifications[] = $notification;
                    }
                    
                    // Sort by timestamp descending
                    usort($notifications, function($a, $b) {
                        return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
                    });
                    
                    return $notifications;
                }
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Get notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get notification icons - same as mobile app
     */
    private function getNotificationIcon($processType) {
        switch ($processType) {
            case self::PROCESS_ADOPTION:
                return '👶';
            case self::PROCESS_DONATION:
                return '💝';
            case self::PROCESS_MATCHING:
                return '🤝';
            case self::PROCESS_APPOINTMENT:
                return '📅';
            case self::PROCESS_CHAT:
                return '💬';
            case self::PROCESS_PROFILE:
                return '👤';
            case self::PROCESS_SYSTEM:
                return '🔔';
            default:
                return '📋';
        }
    }
    
    /**
     * Send donation notification - mobile app exact copy
     */
    public function sendDonationNotification($userId, $donationType, $status) {
        $titles = [
            'submitted' => "Donation Submitted",
            'approved' => "Donation Approved",
            'rejected' => "Donation Rejected",
            'completed' => "Donation Completed"
        ];
        
        $messages = [
            'submitted' => "Your {$donationType} donation has been submitted and is under review.",
            'approved' => "Your {$donationType} donation has been approved!",
            'rejected' => "Your {$donationType} donation needs review. Please check your submissions.",
            'completed' => "Your {$donationType} donation has been successfully completed!"
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
        
        return $this->sendNotification(
            $userId,
            self::PROCESS_DONATION,
            $notificationType,
            $titles[$status] ?? "Donation Update",
            $messages[$status] ?? "Your donation status has been updated.",
            ['donationType' => $donationType, 'status' => $status]
        );
    }
    
    /**
     * Send appointment notification - mobile app exact copy
     */
    public function sendAppointmentNotification($userId, $status, $appointmentDate = null) {
        $titles = [
            'scheduled' => "Appointment Scheduled",
            'confirmed' => "Appointment Confirmed",
            'cancelled' => "Appointment Cancelled",
            'completed' => "Appointment Completed",
            'reminder' => "Appointment Reminder"
        ];
        
        $messages = [
            'scheduled' => "Your appointment has been scheduled" . ($appointmentDate ? " for {$appointmentDate}" : "") . ".",
            'confirmed' => "Your appointment has been confirmed" . ($appointmentDate ? " for {$appointmentDate}" : "") . ".",
            'cancelled' => "Your appointment has been cancelled. Please reschedule if needed.",
            'completed' => "Your appointment has been completed. Thank you!",
            'reminder' => "You have an upcoming appointment" . ($appointmentDate ? " on {$appointmentDate}" : "") . "."
        ];
        
        switch($status) {
            case 'scheduled':
                $notificationType = self::TYPE_PROCESS_INITIATED;
                break;
            case 'confirmed':
                $notificationType = self::TYPE_PROCESS_APPROVED;
                break;
            case 'cancelled':
                $notificationType = self::TYPE_PROCESS_REJECTED;
                break;
            case 'completed':
                $notificationType = self::TYPE_PROCESS_COMPLETED;
                break;
            case 'reminder':
                $notificationType = self::TYPE_REMINDER_GENTLE;
                break;
            default:
                $notificationType = self::TYPE_STATUS_UPDATE;
                break;
        }
        
        return $this->sendNotification(
            $userId,
            self::PROCESS_APPOINTMENT,
            $notificationType,
            $titles[$status] ?? "Appointment Update",
            $messages[$status] ?? "Your appointment status has been updated.",
            ['status' => $status, 'appointmentDate' => $appointmentDate]
        );
    }
    
    /**
     * Send test notification - for testing only
     */
    public function sendTestNotification($userId) {
        return $this->sendNotification(
            $userId,
            self::PROCESS_SYSTEM,
            self::TYPE_STATUS_UPDATE,
            "Test Notification",
            "This is a test notification sent at " . date('Y-m-d H:i:s'),
            ['test' => true, 'timestamp' => time()]
        );
    }
}

// Handle POST requests for notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No action specified']);
        exit;
    }

    $notificationSystem = new SimpleNotificationSystem();

    switch ($input['action']) {
        case 'send_notification':
            $userId = $input['userId'] ?? '';
            $processType = $input['processType'] ?? 'SYSTEM';
            $notificationType = $input['notificationType'] ?? 'STATUS_UPDATE';
            $title = $input['title'] ?? 'Notification';
            $message = $input['message'] ?? '';
            $data = $input['data'] ?? [];

            $result = $notificationSystem->sendNotification($userId, $processType, $notificationType, $title, $message, $data);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            break;

        case 'get_notifications':
            $userId = $input['userId'] ?? '';
            $limit = $input['limit'] ?? 20;

            $notifications = $notificationSystem->getNotifications($userId, $limit);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }
    exit;
}
?> 