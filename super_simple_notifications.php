<?php
/**
 * SUPER SIMPLE NOTIFICATION SYSTEM
 * Uses local files - just works, no complex setup needed
 */

// Handle API requests for navbar (MOBILE APP LOGIC)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $request = json_decode($input, true);
    
    if ($request && isset($request['action'])) {
        $notifications = new SuperSimpleNotifications();
        
        if ($request['action'] === 'get_notifications') {
            $userId = $request['userId'] ?? null;
            $limit = $request['limit'] ?? 20;
            
            if ($userId) {
                $userNotifications = $notifications->getNotifications($userId, $limit);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'notifications' => $userNotifications,
                    'count' => count($userNotifications)
                ]);
                exit;
            }
        }
        
        // MOBILE APP LOGIC: Handle appointment notifications exactly like mobile app
        if ($request['action'] === 'send_appointment_notification') {
            $userId = $request['userId'] ?? null;
            $status = $request['status'] ?? null;
            $appointmentData = $request['appointmentData'] ?? [];
            
            if ($userId && $status) {
                $success = $notifications->sendAppointmentNotification($userId, $status, $appointmentData);
                
                // COLLECTION-BASED ADMIN NOTIFICATIONS: Send to admins for various appointment actions
                if ($status === 'scheduled' || $status === 'pending') {
                    $userName = $appointmentData['userName'] ?? 'User';
                    $userEmail = $appointmentData['userEmail'] ?? '';
                    $appointmentType = $appointmentData['appointmentType'] ?? 'appointment';
                    $appointmentDate = $appointmentData['appointmentDate'] ?? 'scheduled date';
                    $appointmentTime = $appointmentData['appointmentTime'] ?? '';
                    
                    $displayTime = $appointmentTime ? " at {$appointmentTime}" : "";
                    $notifications->sendAppointmentRequestToAdmins(
                        $userName, 
                        $userEmail, 
                        $appointmentType, 
                        $appointmentDate, 
                        $appointmentTime
                    );
                    error_log("📧 COLLECTION-BASED: Admin appointment notification sent for {$appointmentType} by {$userName}");
                } elseif ($status === 'cancelled') {
                    // Send admin notification when user cancels appointment
                    $userName = $appointmentData['userName'] ?? $appointmentData['username'] ?? 'User';
                    $userEmail = $appointmentData['userEmail'] ?? '';
                    $appointmentType = $appointmentData['appointmentType'] ?? 'appointment';
                    $appointmentDate = $appointmentData['appointmentDate'] ?? 'scheduled date';
                    
                    $title = "❌ Appointment Cancelled";
                    $message = "{$userName} ({$userEmail}) has cancelled their {$appointmentType} appointment scheduled for {$appointmentDate}.";
                    
                    $notifications->sendAdminNotification('appointment', $title, $message, [
                        'userName' => $userName,
                        'userEmail' => $userEmail,
                        'appointmentType' => $appointmentType,
                        'appointmentDate' => $appointmentDate,
                        'status' => 'cancelled',
                        'actionRequired' => false
                    ]);
                    error_log("📧 COLLECTION-BASED: Admin cancellation notification sent for {$appointmentType} by {$userName}");
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Appointment notification sent' : 'Failed to send notification'
                ]);
                exit;
            }
        }
        
        // MOBILE APP LOGIC: Handle adoption notifications exactly like mobile app
        if ($request['action'] === 'send_adoption_notification') {
            $userId = $request['userId'] ?? null;
            $status = $request['status'] ?? null;
            $stepNumber = $request['stepNumber'] ?? 0;
            $data = $request['data'] ?? [];
            
            if ($userId && $status) {
                $success = $notifications->sendAdoptionNotification($userId, $status, $stepNumber, $data);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Adoption notification sent' : 'Failed to send notification'
                ]);
                exit;
            }
        }
        
        // COLLECTION-BASED: Handle donation notifications
        if ($request['action'] === 'send_donation_notification') {
            $userId = $request['userId'] ?? null;
            $donationType = $request['donationType'] ?? 'general';
            $status = $request['status'] ?? 'submitted';
            $userName = $request['userName'] ?? 'User';
            $userEmail = $request['userEmail'] ?? '';
            $amount = $request['amount'] ?? null;
            $donationId = $request['donationId'] ?? null;
            
            if ($userId) {
                $success = $notifications->sendDonationNotification($userId, $donationType, $status, $donationId);
                
                // COLLECTION-BASED ADMIN NOTIFICATIONS: Send to admins when users submit donations
                if ($status === 'submitted') {
                    $notifications->sendDonationSubmissionToAdmins($userName, $userEmail, $donationType, $amount);
                    error_log("📧 COLLECTION-BASED: Admin donation notification sent for {$donationType} by {$userName}");
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Donation notification sent' : 'Failed to send notification'
                ]);
                exit;
            }
        }
        
        // COLLECTION-BASED: Handle matching notifications
        if ($request['action'] === 'send_matching_notification') {
            $userId = $request['userId'] ?? null;
            $status = $request['status'] ?? 'request_submitted';
            $userName = $request['userName'] ?? 'User';
            $userEmail = $request['userEmail'] ?? '';
            $preferences = $request['preferences'] ?? [];
            
            if ($userId) {
                $success = $notifications->sendMatchingRequestNotification($userId);
                
                // COLLECTION-BASED ADMIN NOTIFICATIONS: Send to admins when users submit matching requests
                if ($status === 'request_submitted') {
                    $notifications->sendMatchingRequestToAdmins($userName, $userEmail, $preferences);
                    error_log("📧 COLLECTION-BASED: Admin matching notification sent by {$userName}");
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Matching notification sent' : 'Failed to send notification'
                ]);
                exit;
            }
        }

        // COLLECTION-BASED: Handle mark all as read
        if ($request['action'] === 'mark_all_as_read') {
            $userId = $request['userId'] ?? null;
            
            if ($userId) {
                $success = $notifications->markAllAsRead($userId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'All notifications marked as read' : 'Failed to mark notifications as read'
                ]);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
}

class SuperSimpleNotifications {
    
    private $dataFile = 'notifications.json';
    
    /**
     * Send notification - save to local file
     */
    public function sendNotification($userId, $type, $title, $message, $data = []) {
        try {
            // Ensure user notifications are marked as such
            if (!isset($data['isAdminNotification'])) {
                $data['isUserNotification'] = true;
                $data['notificationSource'] = 'user_system';
            }
            
            $notification = [
                'id' => uniqid('notif_', true),
                'userId' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'timestamp' => time() * 1000, // Mobile app uses milliseconds
                'isRead' => false,
                'icon' => $this->getIcon($type)
            ];
            
            $notifications = $this->loadNotifications();
            $notifications[] = $notification;
            
            // Keep only last 100 notifications
            if (count($notifications) > 100) {
                $notifications = array_slice($notifications, -100);
            }
            
            $success = $this->saveNotifications($notifications);
            
            if ($success) {
                error_log("✅ Notification sent: {$title}");
                
                // OPTIONAL: Try to send to Firebase collections (won't break if it fails)
                if (file_exists(__DIR__ . '/firebase_notifications_bridge.php')) {
                    try {
                        include_once __DIR__ . '/firebase_notifications_bridge.php';
                        sendFirebaseNotification($userId, $type, $title, $message, $data);
                    } catch (Exception $e) {
                        // Silently handle Firebase errors so file-based system keeps working
                        error_log("Firebase notification failed: " . $e->getMessage());
                    }
                }
                
                // Send to Firebase collections using simple REST API
                if (file_exists(__DIR__ . '/firebase_notifications_bridge.php')) {
                    try {
                        include_once __DIR__ . '/firebase_notifications_bridge.php';
                        $bridge = new FirebaseNotificationsBridge();
                        $bridge->sendNotification($userId, $type, $title, $message, $data);
                    } catch (Exception $e) {
                        error_log("Firebase notification failed: " . $e->getMessage());
                    }
                }
                
                return true;
            } else {
                error_log("❌ Failed to save notification: {$title}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("❌ Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send user-specific notification (never goes to admins)
     */
    public function sendUserNotification($userId, $type, $title, $message, $data = []) {
        // Explicitly mark as user notification
        $userData = array_merge($data, [
            'isUserNotification' => true,
            'notificationSource' => 'user_system',
            'targetRole' => 'user'
        ]);
        
        return $this->sendNotification($userId, $type, $title, $message, $userData);
    }
    
    /**
     * Get notifications for user
     */
    public function getNotifications($userId, $limit = 20) {
        try {
            $allNotifications = $this->loadNotifications();
            
            // Check if this user is an admin
            $isAdminUser = in_array($userId, $this->getAdminUserIds());
            
            // Filter by user ID and notification type
            $userNotifications = array_filter($allNotifications, function($n) use ($userId, $isAdminUser) {
                // Always show notifications specifically sent to this user
                if ($n['userId'] === $userId) {
                    // If user is admin, only show admin-specific notifications
                    if ($isAdminUser) {
                        // Only show notifications marked as admin notifications
                        return isset($n['data']['isAdminNotification']) && $n['data']['isAdminNotification'] === true;
                    }
                    // For regular users, show all their notifications except admin ones
                    return !isset($n['data']['isAdminNotification']) || $n['data']['isAdminNotification'] !== true;
                }
                return false;
            });
            
            // Sort by timestamp descending
            usort($userNotifications, function($a, $b) {
                return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
            });
            
            // Limit results
            return array_slice($userNotifications, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Get notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        try {
            $notifications = $this->loadNotifications();
            
            foreach ($notifications as &$notification) {
                if ($notification['id'] === $notificationId) {
                    $notification['isRead'] = true;
                    $notification['readAt'] = time() * 1000;
                    break;
                }
            }
            
            return $this->saveNotifications($notifications);
            
        } catch (Exception $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        try {
            $notifications = $this->loadNotifications();
            $updated = false;
            
            foreach ($notifications as &$notification) {
                if ($notification['userId'] === $userId && !$notification['isRead']) {
                    $notification['isRead'] = true;
                    $notification['readAt'] = time() * 1000;
                    $updated = true;
                }
            }
            
            if ($updated) {
                return $this->saveNotifications($notifications);
            }
            
            return true; // No notifications to update, consider success
            
        } catch (Exception $e) {
            error_log("Mark all as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Load notifications from file
     */
    private function loadNotifications() {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        
        $content = file_get_contents($this->dataFile);
        if ($content === false) {
            return [];
        }
        
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Save notifications to file
     */
    private function saveNotifications($notifications) {
        $content = json_encode($notifications, JSON_PRETTY_PRINT);
        return file_put_contents($this->dataFile, $content, LOCK_EX) !== false;
    }
    
    /**
     * Get notification icons
     */
    private function getIcon($type) {
        switch ($type) {
            case 'donation':
                return '💝';
            case 'appointment':
                return '📅';
            case 'adoption':
                return '👶';
            case 'matching':
                return '🤝';
            case 'chat':
                return '💬';
            case 'system':
                return '🔔';
            default:
                return '📋';
        }
    }
    
    /**
     * Send donation notification
     */
    public function sendDonationNotification($userId, $donationType, $status, $donationId = null) {
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
        
        return $this->sendUserNotification(
            $userId,
            'donation',
            $titles[$status] ?? "Donation Update",
            $messages[$status] ?? "Your donation status has been updated.",
            ['donationType' => $donationType, 'status' => $status, 'donationId' => $donationId]
        );
    }
    
    /**
     * Send appointment notification - MOBILE APP LOGIC COMPATIBLE
     */
    public function sendAppointmentNotification($userId, $status, $appointmentData = []) {
        // Handle both old format (appointmentDate as string) and new format (appointmentData as array)
        if (is_string($appointmentData)) {
            $appointmentData = ['appointmentDate' => $appointmentData];
        }
        
        // MOBILE APP LOGIC: Map status to proper notification format (website sends "accepted", mobile expects "confirmed")
        $statusMapping = [
            'accepted' => 'confirmed',  // Convert website's "accepted" to mobile app's "confirmed"
            'confirmed' => 'confirmed',
            'cancelled' => 'cancelled',
            'pending' => 'pending',
            'scheduled' => 'confirmed',
            'completed' => 'completed',
            'rescheduled' => 'rescheduled'
        ];
        
        $mappedStatus = $statusMapping[$status] ?? $status;
        
        // MOBILE APP LOGIC: Create notification titles exactly like NotificationOrchestrator
        $titles = [
            'pending' => "📅 Appointment Request Submitted",
            'confirmed' => "✅ Appointment Confirmed",
            'cancelled' => "❌ Appointment Cancelled", 
            'completed' => "✔️ Appointment Completed",
            'rescheduled' => "🔄 Appointment Rescheduled"
        ];
        
        $appointmentDate = $appointmentData['appointmentDate'] ?? 'your scheduled date';
        $appointmentTime = $appointmentData['appointmentTime'] ?? '';
        $appointmentType = $appointmentData['appointmentType'] ?? 'appointment';
        
        // Extract time from date if it contains "at"
        if (strpos($appointmentDate, ' at ') !== false) {
            $parts = explode(' at ', $appointmentDate);
            $appointmentDate = $parts[0];
            $appointmentTime = ' at ' . $parts[1];
        } else {
            $appointmentTime = $appointmentTime ? " at {$appointmentTime}" : "";
        }
        
        // MOBILE APP LOGIC: Create messages exactly like NotificationOrchestrator
        $messages = [
            'pending' => "Your {$appointmentType} appointment request for {$appointmentDate}{$appointmentTime} has been submitted and is pending review.",
            'confirmed' => "Your {$appointmentType} appointment has been confirmed for {$appointmentDate}{$appointmentTime}.",
            'cancelled' => "Your {$appointmentType} appointment scheduled for {$appointmentDate}{$appointmentTime} has been cancelled.",
            'completed' => "Your {$appointmentType} appointment on {$appointmentDate}{$appointmentTime} has been completed.",
            'rescheduled' => "Your {$appointmentType} appointment has been rescheduled to {$appointmentDate}{$appointmentTime}."
        ];
        
        // MOBILE APP LOGIC: Create notification data structure exactly like NotificationOrchestrator
        $notificationData = array_merge($appointmentData, [
            'status' => $mappedStatus,
            'processType' => 'APPOINTMENT',
            'notificationType' => 'STATUS_UPDATE',
            'actionType' => 'appointment',
            'appointmentType' => $appointmentType,
            'date' => trim($appointmentDate),
            'time' => trim($appointmentTime, ' at'),
            'appointmentCode' => $appointmentData['appointmentCode'] ?? '',
            'appointmentId' => $appointmentData['appointmentId'] ?? ''
        ]);
        
        return $this->sendUserNotification(
            $userId,
            'appointment',
            $titles[$mappedStatus] ?? "📅 Appointment Update",
            $messages[$mappedStatus] ?? "Your appointment status has been updated.",
            $notificationData
        );
    }
    
    /**
     * Send test notification
     */
    public function sendTestNotification($userId) {
        return $this->sendUserNotification(
            $userId,
            'system',
            'Test Notification',
            'This is a test notification to verify the system is working correctly.',
            ['test' => true, 'timestamp' => time()]
        );
    }
    
    /**
     * Send matching request notification
     */
    public function sendMatchingRequestNotification($userId) {
        return $this->sendUserNotification(
            $userId,
            'matching',
            'Matching Submitted',
            "You have been automatically matched. Our team will review the details and contact you soon.",
            ['status' => 'submitted']
        );
    }
    
    /**
     * Send match found notification
     */
    public function sendMatchFoundNotification($userId, $childName) {
        return $this->sendUserNotification(
            $userId,
            'matching',
            'Match Found!',
            "We found a potential match for you: {$childName}. Please review the details.",
            ['status' => 'match_found', 'childName' => $childName]
        );
    }
    
    /**
     * Send match accepted notification
     */
    public function sendMatchAcceptedNotification($userId, $childName) {
        return $this->sendUserNotification(
            $userId,
            'matching',
            'Match Accepted!',
            "Congratulations! Your match with {$childName} has been accepted. The adoption process will begin soon.",
            ['status' => 'match_accepted', 'childName' => $childName]
        );
    }
    
    /**
     * Send adoption step completed notification
     */
    public function sendAdoptionStepCompleted($userId, $stepNumber) {
        return $this->sendUserNotification(
            $userId,
            'adoption',
            "Step {$stepNumber} Completed!",
            "Congratulations! Step {$stepNumber} of your adoption process has been completed.",
            ['status' => 'step_completed', 'stepNumber' => $stepNumber]
        );
    }
    
    /**
     * Send adoption step started notification
     */
    public function sendAdoptionStepStarted($userId, $stepNumber) {
        return $this->sendUserNotification(
            $userId,
            'adoption',
            "Step {$stepNumber} Started",
            "Step {$stepNumber} of your adoption process is now ready to begin!",
            ['status' => 'step_started', 'stepNumber' => $stepNumber]
        );
    }
    
    /**
     * Send mobile app approval notification
     */
    public function sendMobileAppApproval($userId, $stepNumber) {
        return $this->sendUserNotification(
            $userId,
            'adoption',
            "Step {$stepNumber} Approved by Admin!",
            "Great news! An admin has approved Step {$stepNumber} of your adoption process on their mobile device.",
            ['status' => 'mobile_approved', 'stepNumber' => $stepNumber, 'source' => 'mobile_app']
        );
    }
    
    /**
     * Send mobile app step started notification
     */
    public function sendMobileAppStepStarted($userId, $stepNumber) {
        return $this->sendUserNotification(
            $userId,
            'adoption',
            "Step {$stepNumber} Started by Admin",
            "An admin has started Step {$stepNumber} of your adoption process. You can now proceed!",
            ['status' => 'mobile_step_started', 'stepNumber' => $stepNumber, 'source' => 'mobile_app']
        );
    }
    
    /**
     * Get all admin user IDs from session storage or database
     */
    private function getAdminUserIds() {
        $adminIds = [];
        
        try {
            // Method 1: Check if we have a simple admin file
            $adminFile = 'admin_users.json';
            if (file_exists($adminFile)) {
                $content = file_get_contents($adminFile);
                $data = json_decode($content, true);
                if (is_array($data)) {
                    return $data;
                }
            }
            
            // Method 2: Scan notification files for admin role users
            // This is a fallback method by checking existing notifications
            $notifications = $this->loadNotifications();
            foreach ($notifications as $notification) {
                if (isset($notification['data']['userRole']) && $notification['data']['userRole'] === 'admin') {
                    $adminIds[] = $notification['userId'];
                }
            }
            
            // Remove duplicates
            $adminIds = array_unique($adminIds);
            
            // If we found admins, save them for future use
            if (!empty($adminIds)) {
                file_put_contents($adminFile, json_encode($adminIds, JSON_PRETTY_PRINT));
            }
            
        } catch (Exception $e) {
            error_log("Error getting admin user IDs: " . $e->getMessage());
        }
        
        return $adminIds;
    }
    
    /**
     * Register a user as admin (call this when admin logs in)
     */
    public function registerAdminUser($userId) {
        try {
            $adminIds = $this->getAdminUserIds();
            if (!in_array($userId, $adminIds)) {
                $adminIds[] = $userId;
                file_put_contents('admin_users.json', json_encode($adminIds, JSON_PRETTY_PRINT));
            }
            return true;
        } catch (Exception $e) {
            error_log("Error registering admin user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification to all admin users
     */
    public function sendAdminNotification($type, $title, $message, $data = []) {
        $adminIds = $this->getAdminUserIds();
        
        if (empty($adminIds)) {
            error_log("⚠️ No admin users found for notification: {$title}");
            return false;
        }
        
        $success = true;
        foreach ($adminIds as $adminId) {
            // Mark this as an admin-specific notification
            $adminData = array_merge($data, [
                'isAdminNotification' => true,
                'targetRole' => 'admin',
                'notificationSource' => 'admin_system'
            ]);
            $result = $this->sendNotification($adminId, $type, $title, $message, $adminData);
            if (!$result) {
                $success = false;
            }
        }
        
        error_log("📧 Admin notification sent to " . count($adminIds) . " admins: {$title}");
        
        // ENHANCED: Send admin notifications to mobile using Firebase Admin SDK
        if (file_exists(__DIR__ . '/firebase_admin_notifications.php')) {
            try {
                include_once __DIR__ . '/firebase_admin_notifications.php';
                sendFirebaseAdminNotificationToAdmins($title, $message, $data);
            } catch (Exception $e) {
                error_log("Firebase Admin admin notification failed: " . $e->getMessage());
            }
        } else if (file_exists(__DIR__ . '/cross_platform_notifications.php')) {
            try {
                include_once __DIR__ . '/cross_platform_notifications.php';
                $crossPlatform = new CrossPlatformNotifications();
                
                // Send to each admin on all platforms
                foreach ($adminIds as $adminId) {
                    $crossPlatform->sendCrossPlatformNotification($adminId, $type, 'ADMIN_REVIEW_REQUIRED', $title, $message, $data);
                }
            } catch (Exception $e) {
                error_log("Cross-platform admin notification failed: " . $e->getMessage());
            }
        }
        
        return $success;
    }
    
    /**
     * Send appointment request notification to admins
     */
    public function sendAppointmentRequestToAdmins($userName, $userEmail, $appointmentType, $appointmentDate, $appointmentTime) {
        $title = "New Appointment Request";
        $message = "{$userName} ({$userEmail}) has requested a {$appointmentType} appointment for {$appointmentDate} at {$appointmentTime}.";
        
        return $this->sendAdminNotification('appointment', $title, $message, [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'appointmentType' => $appointmentType,
            'appointmentDate' => $appointmentDate,
            'appointmentTime' => $appointmentTime,
            'actionRequired' => true
        ]);
    }
    
    /**
     * Send donation submission notification to admins
     */
    public function sendDonationSubmissionToAdmins($userName, $userEmail, $donationType, $amount = null) {
        $title = "New Donation Submission";
        $amountText = $amount ? " worth ₱" . number_format($amount, 2) : "";
        $message = "{$userName} ({$userEmail}) has submitted a {$donationType} donation{$amountText}. Please review and approve.";
        
        return $this->sendAdminNotification('donation', $title, $message, [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'donationType' => $donationType,
            'amount' => $amount,
            'actionRequired' => true
        ]);
    }
    
    /**
     * Send adoption step completion notification to admins
     */
    public function sendAdoptionStepCompletionToAdmins($userName, $userEmail, $stepNumber, $stepTitle) {
        $title = "Adoption Step Completed";
        $message = "{$userName} ({$userEmail}) has completed Step {$stepNumber}: {$stepTitle}. Please review the submission.";
        
        return $this->sendAdminNotification('adoption', $title, $message, [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'stepNumber' => $stepNumber,
            'stepTitle' => $stepTitle,
            'actionRequired' => true
        ]);
    }
    
    /**
     * Send matching request notification to admins
     */
    public function sendMatchingRequestToAdmins($userName, $userEmail, $preferences) {
        $title = "New Matching Request";
        $message = "{$userName} ({$userEmail}) has submitted new matching preferences. Please review and process the match.";
        
        return $this->sendAdminNotification('matching', $title, $message, [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'preferences' => $preferences,
            'actionRequired' => true
        ]);
    }
    
    /**
     * Send step unlock notification to admins
     */
    public function sendStepUnlockToAdmins($userName, $userEmail, $stepNumber, $stepTitle) {
        $title = "Step {$stepNumber} Unlocked";
        $message = "Admin manually unlocked {$stepTitle} (Step {$stepNumber}) for user {$userName} ({$userEmail}). User can now proceed with this step.";
        
        return $this->sendAdminNotification('step_unlock', $title, $message, [
            'userName' => $userName,
            'userEmail' => $userEmail,
            'stepNumber' => $stepNumber,
            'stepTitle' => $stepTitle,
            'action' => 'unlock',
            'processType' => 'step_unlock'
        ]);
    }
    
    /**
     * Send adoption notification - MOBILE APP LOGIC COMPATIBLE
     */
    public function sendAdoptionNotification($userId, $status, $stepNumber = 0, $data = []) {
        // MOBILE APP LOGIC: Create notification titles exactly like NotificationOrchestrator
        $titles = [
            'adoption_started' => '🎉 Adoption Process Started',
            'step_completed' => "✅ Step {$stepNumber} Completed",
            'step_rejected' => "❌ Step {$stepNumber} Requires Changes",
            'admin_comment_added' => "💬 New Comment on Step {$stepNumber}",
            'step_in_progress' => "🔄 Step {$stepNumber} Set In Progress",
            'document_uploaded' => "📄 Document Uploaded for Step {$stepNumber}",
            'step_started' => "🚀 Step {$stepNumber} Started",
            'matches_found' => "🔍 Matches Found for You!",
            'child_selected' => "👶 Child Selection Confirmed",
            'appointment_requested' => "📅 Appointment Request Submitted",
            'appointment_accepted' => "✅ Appointment Approved!"
        ];
        
        // SPECIAL CASE: Step 10 completion gets a congratulatory title
        if ($status === 'step_completed' && $stepNumber == 10) {
            $titles['step_completed'] = "🎊 CONGRATULATIONS! Adoption Process Complete! 🎊";
        }
        
        $comment = $data['comment'] ?? '';
        $commentPreview = strlen($comment) > 50 ? substr($comment, 0, 50) . '...' : $comment;
        
        // MOBILE APP LOGIC: Create messages exactly like NotificationOrchestrator
        $messages = [
            'adoption_started' => 'Welcome to your adoption journey! You can now begin with Step 1.',
            'step_completed' => "Great progress! Step {$stepNumber} has been approved by admin. You can now proceed to the next step.",
            'step_rejected' => "Step {$stepNumber} requires some changes. Please review the admin comments and resubmit.",
            'admin_comment_added' => "Admin has added a comment to your Step {$stepNumber} progress" . ($commentPreview ? ": {$commentPreview}" : ". Please check the details."),
            'step_in_progress' => "Step {$stepNumber} has been set in progress by admin. You can now work on this step.",
            'document_uploaded' => "You have successfully uploaded a document for Step {$stepNumber}. Admin will review it soon.",
            'step_started' => "You have started working on Step {$stepNumber}. Good luck!",
            'matches_found' => "We found " . ($data['matchCount'] ?? 1) . " ethical " . (($data['matchCount'] ?? 1) === 1 ? 'match' : 'matches') . " based on your preferences! View them in Stage 6.",
            'child_selected' => "You have selected " . ($data['childName'] ?? 'a child') . " for adoption. A social worker will review your selection.",
            'appointment_requested' => "Your appointment request with " . ($data['childName'] ?? 'the child') . " has been submitted and is awaiting approval.",
            'appointment_accepted' => "Great news! Your appointment with " . ($data['childName'] ?? 'the child') . " has been approved by the social worker. You can now proceed to the next stage."
        ];
        
        // SPECIAL CASE: Step 10 completion gets a special congratulatory message
        if ($status === 'step_completed' && $stepNumber == 10) {
            $messages['step_completed'] = "🎉 CONGRATULATIONS! 🎉 You have successfully completed the entire adoption process! All 10 steps have been approved by our admin team. Thank you for your dedication and patience throughout this journey. You are now ready for the next phase of your adoption. Please wait for further instructions from our team.";
        }
        
        $title = $titles[$status] ?? "📋 Adoption Update";
        $message = $messages[$status] ?? "Your adoption progress has been updated.";
        
        // Get icon based on status
        $icon = '🎉';
        if (strpos($status, 'completed') !== false) {
            // SPECIAL CASE: Step 10 completion gets a special celebration icon
            if ($stepNumber == 10) {
                $icon = '🎊';
            } else {
                $icon = '✅';
            }
        }
        elseif (strpos($status, 'rejected') !== false) $icon = '❌';
        elseif (strpos($status, 'comment') !== false) $icon = '💬';
        elseif (strpos($status, 'progress') !== false) $icon = '🔄';
        elseif (strpos($status, 'document') !== false) $icon = '📄';
        elseif (strpos($status, 'started') !== false) $icon = '🚀';
        elseif (strpos($status, 'matches_found') !== false) $icon = '🔍';
        elseif (strpos($status, 'child_selected') !== false) $icon = '👶';
        elseif (strpos($status, 'appointment_requested') !== false) $icon = '📅';
        elseif (strpos($status, 'appointment_accepted') !== false) $icon = '✅';
        
        // MOBILE APP LOGIC: Create notification data structure exactly like NotificationOrchestrator
        $notificationData = array_merge($data, [
            'processType' => 'ADOPTION',
            'notificationType' => 'STATUS_UPDATE',
            'actionType' => 'adoption',
            'stepNumber' => $stepNumber,
            'status' => $status,
            'stepName' => "step{$stepNumber}",
            'commentType' => $status === 'admin_comment_added' ? 'admin_progress_comment' : ''
        ]);
        
        // Send notification to user
        $userResult = $this->sendUserNotification(
            $userId,
            'adoption',
            $title,
            $message,
            $notificationData
        );

        // COLLECTION-BASED ADMIN NOTIFICATIONS: Send to admins when users take actions
        $adminNotificationStatuses = ['document_uploaded', 'step_completed', 'step_started'];
        
        if (in_array($status, $adminNotificationStatuses)) {
            // Get user info for admin notification - FETCH FROM FIREBASE IF NOT PROVIDED
            $userName = $data['userName'] ?? 'User';
            $userEmail = $data['userEmail'] ?? '';
            
            // If user info is missing or default, try to fetch from Firebase
            if ($userName === 'User' || empty($userEmail)) {
                error_log("⚠️ User info missing for admin notification, attempting to fetch from Firebase for userId: {$userId}");
                
                // Try to get user info from Firebase via a simple HTTP request
                $firebaseUrl = "https://firestore.googleapis.com/v1/projects/ally-user/databases/(default)/documents/users/{$userId}";
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 5,
                        'ignore_errors' => true
                    ]
                ]);
                
                $response = @file_get_contents($firebaseUrl, false, $context);
                if ($response) {
                    $userData = json_decode($response, true);
                    if (isset($userData['fields'])) {
                        // Extract user info from Firestore format
                        $displayName = $userData['fields']['displayName']['stringValue'] ?? 
                                     $userData['fields']['name']['stringValue'] ?? 
                                     $userData['fields']['username']['stringValue'] ?? null;
                        $email = $userData['fields']['email']['stringValue'] ?? null;
                        
                        if ($displayName) {
                            $userName = $displayName;
                            error_log("✅ Retrieved user name from Firebase: {$userName}");
                        }
                        if ($email) {
                            $userEmail = $email;
                            error_log("✅ Retrieved user email from Firebase: {$userEmail}");
                        }
                    }
                }
                
                // If still no user info, log the issue
                if ($userName === 'User') {
                    error_log("❌ Could not retrieve user info from Firebase for userId: {$userId}");
                }
            }
            
            // Create admin-specific notification
            $adminTitles = [
                'document_uploaded' => "📄 New Document Upload - Step {$stepNumber}",
                'step_completed' => "✅ Step Completion - Step {$stepNumber}",
                'step_started' => "🚀 Step Started - Step {$stepNumber}"
            ];
            
            // SPECIAL CASE: Step 10 completion gets a special admin title
            if ($status === 'step_completed' && $stepNumber == 10) {
                $adminTitles['step_completed'] = "🎊 ADOPTION PROCESS COMPLETED! - Step 10";
            }
            
            $adminMessages = [
                'document_uploaded' => "{$userName} ({$userEmail}) has uploaded a document for Step {$stepNumber}. Please review and approve.",
                'step_completed' => "{$userName} ({$userEmail}) has completed Step {$stepNumber}. Please review the submission.",
                'step_started' => "{$userName} ({$userEmail}) has started working on Step {$stepNumber}."
            ];
            
            // SPECIAL CASE: Step 11 completion gets a special admin message
            if ($status === 'step_completed' && $stepNumber == 11) {
                $adminMessages['step_completed'] = "🎉 {$userName} ({$userEmail}) has successfully completed the ENTIRE adoption process! All 11 steps are now complete. Please proceed with final adoption arrangements and congratulate the new family!";
            }
            
            $adminTitle = $adminTitles[$status] ?? "📋 Adoption Update - Step {$stepNumber}";
            $adminMessage = $adminMessages[$status] ?? "{$userName} has updated their adoption progress.";
            
            // Send admin notification using collection-based system
            $adminResult = $this->sendAdminNotification(
                'adoption',
                $adminTitle,
                $adminMessage,
                array_merge($notificationData, [
                    'userName' => $userName,
                    'userEmail' => $userEmail,
                    'userId' => $userId,
                    'actionRequired' => true,
                    'adminAction' => 'review_required'
                ])
            );
            
            error_log("📧 COLLECTION-BASED: Admin notification sent for {$status} - Step {$stepNumber} by {$userName}");
        }

        return $userResult;
    }
}
?> 