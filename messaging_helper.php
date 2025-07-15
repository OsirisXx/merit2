<?php
/**
 * Messaging Helper Class
 * Handles all chat operations, system messages, and automated messaging
 * Replicates the functionality from the mobile app's messaging system
 */

class MessagingHelper {
    private $firebaseConfig;
    private $realtimeDbUrl;
    
    public function __construct() {
        $this->firebaseConfig = [
            'apiKey' => 'AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk',
            'authDomain' => 'ally-user.firebaseapp.com',
            'databaseURL' => 'https://ally-user-default-rtdb.asia-southeast1.firebasedatabase.app',
            'projectId' => 'ally-user',
            'storageBucket' => 'ally-user.firebasestorage.app',
            'messagingSenderId' => '567088674192',
            'appId' => '1:567088674192:web:76b5ef895c1181fa4aaf15'
        ];
        
        $this->realtimeDbUrl = $this->firebaseConfig['databaseURL'];
    }
    
    /**
     * Create or get existing chat between two users
     */
    public function createOrGetChat($userId1, $userId2, $connectionType = 'manual', $metadata = []) {
        error_log("🔥 MESSAGING HELPER: Creating/getting chat between $userId1 and $userId2");
        error_log("Connection type: $connectionType");
        
        // Generate consistent chat ID - matches mobile app
        $chatId = $userId1 < $userId2 ? "{$userId1}_{$userId2}" : "{$userId2}_{$userId1}";
        
        error_log("Generated chat ID: $chatId");
        
        // Check if chat exists
        $chatData = $this->getChatData($chatId);
        
        if (!$chatData) {
            // Create new chat - match mobile app structure exactly
            $chatData = [
                'connection_type' => $connectionType,
                'last_message' => '',
                'last_message_timestamp' => time() * 1000,
                'created_by' => $userId1,
                'participant_user' => $this->determineUserRole($userId1) === 'admin' ? $userId2 : $userId1,
                'participant_admin' => $this->determineUserRole($userId1) === 'admin' ? $userId1 : $userId2,
                'created_at' => time() * 1000,
                'unread_count' => 0,
                'last_activity' => time() * 1000,
                'auto_created' => true
            ];
            
            // Add metadata if provided
            if (!empty($metadata)) {
                foreach ($metadata as $key => $value) {
                    $chatData[$key] = $value;
                }
            }
            
            $this->setChatData($chatId, $chatData);
            
            error_log("✅ Created new chat: $chatId with connection type: $connectionType");
        }
        
        return $chatId;
    }
    
    /**
     * Send system message to chat - matches mobile app structure exactly
     */
    public function sendSystemMessage($chatId, $message, $messageType = 'system', $metadata = []) {
        error_log("🔥 MESSAGING HELPER: Sending system message to chat $chatId");
        error_log("Message: $message");
        error_log("Message type: $messageType");
        
        $messageId = $this->generateMessageId();
        $timestamp = time() * 1000;
        
        // Match the exact structure from mobile app
        $messageData = [
            'messageId' => $messageId,
            'senderId' => 'system',
            'receiverId' => '',
            'senderName' => 'System',
            'message' => $message,
            'timestamp' => $timestamp,
            'serverTimestamp' => $timestamp, // Firebase server timestamp
            'read_by_receiver' => false,
            'deleted_by_sender' => false,
            'deleted_by_receiver' => false,
            'isSystemMessage' => true,
            'donationId' => $metadata['donationId'] ?? '',
            'donationType' => $metadata['donationType'] ?? '',
            'edited' => false,
            'editedTimestamp' => 0,
            'messageType' => $messageType,
            'priority' => ($messageType === 'system_alert') ? 'high' : 'normal',
            'created_at' => $timestamp
        ];
        
        // Send message to Firebase Realtime Database
        $this->setMessageData($chatId, $messageId, $messageData);
        
        // Update chat's last message and increment unread count
        $this->updateChatLastMessage($chatId, $message, $timestamp);
        $this->incrementUnreadCount($chatId);
        
        error_log("✅ System message sent to chat $chatId: $message");
        
        return $messageId;
    }
    
    /**
     * Send donation notification message
     */
    public function sendDonationNotification($userId, $donationType, $donationId, $status = 'submitted') {
        $adminId = $this->getRandomAdminId();
        if (!$adminId) {
            error_log("❌ No admin found for donation notification");
            return false;
        }
        
        $username = $this->getUsernameById($userId);
        $chatId = $this->createOrGetChat($userId, $adminId, "{$donationType}_donation", [
            'donationId' => $donationId,
            'donationType' => $donationType
        ]);
        
        $messages = [
            'submitted' => "📦 {$username} submitted a " . $this->formatDonationType($donationType) . " donation (ID: {$donationId}). Admin can review the submission details and provide assistance.",
            'approved' => "✅ Your " . $this->formatDonationType($donationType) . " donation (ID: {$donationId}) has been approved! Thank you for your generosity.",
            'rejected' => "❌ Your " . $this->formatDonationType($donationType) . " donation (ID: {$donationId}) needs attention. Please check the details and resubmit if necessary."
        ];
        
        $message = $messages[$status] ?? $messages['submitted'];
        
        return $this->sendSystemMessage($chatId, $message, 'donation', [
            'donationId' => $donationId,
            'donationType' => $donationType,
            'status' => $status
        ]);
    }
    
    /**
     * Send adoption process notification
     */
    public function sendAdoptionNotification($userId, $stepNumber, $stepName, $status = 'completed') {
        $adminId = $this->getRandomAdminId();
        if (!$adminId) {
            error_log("❌ No admin found for adoption notification");
            return false;
        }
        
        $username = $this->getUsernameById($userId);
        $chatId = $this->createOrGetChat($userId, $adminId, 'adoption', [
            'stepNumber' => $stepNumber,
            'stepName' => $stepName
        ]);
        
        // Enhanced step descriptions
        $stepDescriptions = [
            1 => 'Initial Application',
            2 => 'Home Study',
            3 => 'Background Check',
            4 => 'Training Program',
            5 => 'Financial Assessment',
            6 => 'Ethical Preferences',
            7 => 'Matching Process',
            8 => 'Legal Documentation',
            9 => 'Meeting & Bonding',
            10 => 'Final Approval',
            11 => 'Post-Adoption Monitoring'
        ];
        
        $stepDescription = $stepDescriptions[$stepNumber] ?? $stepName;
        
        $messages = [
            'completed' => "🎉 Congratulations {$username}! You have completed Step {$stepNumber}: {$stepDescription}. Your adoption journey is progressing well!",
            'pending' => "⏳ Step {$stepNumber}: {$stepDescription} is pending review. An admin will process your submission shortly.",
            'rejected' => "❌ Step {$stepNumber}: {$stepDescription} needs attention. Please review the requirements and resubmit.",
            'started' => "🚀 Step {$stepNumber}: {$stepDescription} is now available! You can begin working on this step.",
            'in_progress' => "📝 You are currently working on Step {$stepNumber}: {$stepDescription}. Take your time to complete it thoroughly."
        ];
        
        $message = $messages[$status] ?? $messages['completed'];
        
        return $this->sendSystemMessage($chatId, $message, 'adoption', [
            'stepNumber' => $stepNumber,
            'stepName' => $stepDescription,
            'status' => $status
        ]);
    }
    
    /**
     * Send appointment notification
     */
    public function sendAppointmentNotification($userId, $appointmentId, $appointmentType, $status = 'scheduled') {
        $adminId = $this->getRandomAdminId();
        if (!$adminId) {
            error_log("❌ No admin found for appointment notification");
            return false;
        }
        
        $username = $this->getUsernameById($userId);
        $chatId = $this->createOrGetChat($userId, $adminId, 'appointment', [
            'appointmentId' => $appointmentId,
            'appointmentType' => $appointmentType
        ]);
        
        $messages = [
            'scheduled' => "📅 Your {$appointmentType} appointment has been scheduled (ID: {$appointmentId}). You will receive further details soon.",
            'confirmed' => "✅ Your {$appointmentType} appointment (ID: {$appointmentId}) has been confirmed!",
            'cancelled' => "❌ Your {$appointmentType} appointment (ID: {$appointmentId}) has been cancelled. Please reschedule if needed.",
            'reminder' => "🔔 Reminder: You have a {$appointmentType} appointment coming up (ID: {$appointmentId})."
        ];
        
        $message = $messages[$status] ?? $messages['scheduled'];
        
        return $this->sendSystemMessage($chatId, $message, 'appointment', [
            'appointmentId' => $appointmentId,
            'appointmentType' => $appointmentType,
            'status' => $status
        ]);
    }
    
    /**
     * Send admin notification for user activity
     */
    public function sendAdminNotification($userId, $activityType, $activityDetails) {
        $adminId = $this->getRandomAdminId();
        if (!$adminId) {
            error_log("❌ No admin found for admin notification");
            return false;
        }
        
        $username = $this->getUsernameById($userId);
        $chatId = $this->createOrGetChat($userId, $adminId, 'admin_notification');
        
        $messages = [
            'profile_updated' => "👤 {$username} updated their profile information.",
            'document_uploaded' => "📄 {$username} uploaded a new document: {$activityDetails}",
            'form_submitted' => "📋 {$username} submitted a new form: {$activityDetails}",
            'payment_made' => "💳 {$username} made a payment: {$activityDetails}",
            'status_changed' => "🔄 {$username}'s status changed: {$activityDetails}"
        ];
        
        $message = $messages[$activityType] ?? "🔔 {$username} performed an activity: {$activityType} - {$activityDetails}";
        
        return $this->sendSystemMessage($chatId, $message, 'admin_notification', [
            'activityType' => $activityType,
            'activityDetails' => $activityDetails
        ]);
    }
    
    /**
     * Send matching notification
     */
    public function sendMatchingNotification($userId, $childId, $childName, $status = 'matched') {
        $adminId = $this->getRandomAdminId();
        if (!$adminId) {
            error_log("❌ No admin found for matching notification");
            return false;
        }
        
        $username = $this->getUsernameById($userId);
        $chatId = $this->createOrGetChat($userId, $adminId, 'matching', [
            'childId' => $childId,
            'childName' => $childName
        ]);
        
        $messages = [
            'matched' => "💕 Wonderful news {$username}! You have been matched with {$childName}. This is an exciting step in your adoption journey!",
            'pending' => "⏳ Your matching request is being processed. We're working to find the perfect match for you.",
            'updated' => "🔄 Your matching information has been updated. Please review the new details."
        ];
        
        $message = $messages[$status] ?? $messages['matched'];
        
        return $this->sendSystemMessage($chatId, $message, 'matching', [
            'childId' => $childId,
            'childName' => $childName,
            'status' => $status
        ]);
    }
    
    /**
     * Get user's chats
     */
    public function getUserChats($userId) {
        $url = $this->realtimeDbUrl . "/chats.json";
        $response = $this->makeFirebaseRequest($url, 'GET');
        
        if (!$response) {
            return [];
        }
        
        $chats = [];
        foreach ($response as $chatId => $chatData) {
            if (isset($chatData['participant_user']) && isset($chatData['participant_admin'])) {
                if ($chatData['participant_user'] === $userId || $chatData['participant_admin'] === $userId) {
                    $chats[$chatId] = $chatData;
                }
            }
        }
        
        return $chats;
    }
    
    /**
     * Get chat messages
     */
    public function getChatMessages($chatId) {
        $url = $this->realtimeDbUrl . "/chats/{$chatId}/messages.json";
        $response = $this->makeFirebaseRequest($url, 'GET');
        
        if (!$response) {
            return [];
        }
        
        // Convert to array and sort by timestamp
        $messages = [];
        foreach ($response as $messageId => $messageData) {
            $messageData['id'] = $messageId;
            $messages[] = $messageData;
        }
        
        usort($messages, function($a, $b) {
            return ($a['timestamp'] ?? 0) - ($b['timestamp'] ?? 0);
        });
        
        return $messages;
    }
    
    /**
     * Get unread message count for a user
     */
    public function getUnreadMessageCount($userId) {
        $chats = $this->getUserChats($userId);
        $totalUnread = 0;
        
        foreach ($chats as $chatId => $chatData) {
            $messages = $this->getChatMessages($chatId);
            
            foreach ($messages as $messageData) {
                // Count unread messages where the user is the receiver
                if (isset($messageData['receiverId']) && $messageData['receiverId'] === $userId && 
                    !isset($messageData['isRead'])) {
                    $totalUnread++;
                }
                // Also count system messages as unread if they haven't been marked as read
                elseif (isset($messageData['isSystemMessage']) && $messageData['isSystemMessage'] && 
                        !isset($messageData['isRead'])) {
                    $totalUnread++;
                }
            }
        }
        
        return $totalUnread;
    }
    
    /**
     * Private helper methods
     */
    private function getChatData($chatId) {
        $url = $this->realtimeDbUrl . "/chats/{$chatId}.json";
        return $this->makeFirebaseRequest($url, 'GET');
    }
    
    private function setChatData($chatId, $data) {
        $url = $this->realtimeDbUrl . "/chats/{$chatId}.json";
        return $this->makeFirebaseRequest($url, 'PUT', $data);
    }
    
    private function setMessageData($chatId, $messageId, $data) {
        $url = $this->realtimeDbUrl . "/chats/{$chatId}/messages/{$messageId}.json";
        return $this->makeFirebaseRequest($url, 'PUT', $data);
    }
    
    private function updateChatLastMessage($chatId, $message, $timestamp) {
        $url = $this->realtimeDbUrl . "/chats/{$chatId}.json";
        $updateData = [
            'last_message' => $message,
            'last_message_timestamp' => $timestamp,
            'last_activity' => $timestamp
        ];
        return $this->makeFirebaseRequest($url, 'PATCH', $updateData);
    }
    
    private function incrementUnreadCount($chatId) {
        $url = $this->realtimeDbUrl . "/chats/{$chatId}/unread_count.json";
        // Get current count first
        $currentCount = $this->makeFirebaseRequest($url, 'GET');
        $newCount = is_numeric($currentCount) ? $currentCount + 1 : 1;
        return $this->makeFirebaseRequest($url, 'PUT', $newCount);
    }
    
    private function makeFirebaseRequest($url, $method = 'GET', $data = null) {
        error_log("🔥 Making Firebase request: $method $url");
        if ($data) {
            error_log("Request data: " . json_encode($data));
        }
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        error_log("Firebase response HTTP code: $httpCode");
        error_log("Firebase response: " . $response);
        
        if (curl_error($ch)) {
            error_log("❌ Firebase request error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $decodedResponse = json_decode($response, true);
            error_log("✅ Firebase request successful");
            return $decodedResponse;
        } else {
            error_log("❌ Firebase request failed with HTTP code: $httpCode");
            error_log("Response body: " . $response);
            return false;
        }
    }
    
    private function generateMessageId() {
        return uniqid('msg_', true);
    }
    
    private function determineUserRole($userId) {
        // Check user role from Firestore (simplified - you might want to cache this)
        $userData = $this->getUserData($userId);
        return $userData['role'] ?? 'user';
    }
    
    private function getUserData($userId) {
        // This would typically make a Firestore request
        // For now, we'll use a simple file-based approach or session data
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['uid']) && $_SESSION['uid'] === $userId) {
            return [
                'username' => $_SESSION['username'] ?? 'Unknown',
                'role' => $_SESSION['role'] ?? 'user'
            ];
        }
        
        // Fallback - you might want to implement proper Firestore integration
        return [
            'username' => 'Unknown',
            'role' => 'user'
        ];
    }
    
    private function getUsernameById($userId) {
        $userData = $this->getUserData($userId);
        return $userData['username'] ?? 'Unknown User';
    }
    
    public function getRandomAdminId() {
        // This should query your user database for admin users
        // For now, we'll return a default admin ID
        // You should implement proper admin user fetching
        
        // Check if there are any admin users in session or database
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Known admin IDs in the system
        $adminUsers = [
            'h8qq0E8avWO74cqS2Goy1wtENJh1', // Default admin ID
            'admin1', // Add more admin IDs as needed
            'admin2'
        ];
        
        // If current user is admin, don't return their own ID for messaging
        if (isset($_SESSION['uid']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $adminUsers = array_filter($adminUsers, function($id) {
                return $id !== $_SESSION['uid'];
            });
        }
        
        return !empty($adminUsers) ? $adminUsers[array_rand($adminUsers)] : 'h8qq0E8avWO74cqS2Goy1wtENJh1';
    }
    
    private function formatDonationType($donationType) {
        $types = [
            'money' => 'Money',
            'education' => 'Education',
            'medicine' => 'Medicine',
            'toys' => 'Toys',
            'clothes' => 'Clothes',
            'food' => 'Food'
        ];
        
        return $types[strtolower($donationType)] ?? ucfirst($donationType);
    }
}

/**
 * System Message Types - matches mobile app constants
 */
class SystemMessageTypes {
    const ADOPTION_STARTED = 'adoption_started';
    const DONATION_SUBMITTED = 'donation_submitted';
    const DONATION_APPROVED = 'donation_approved';
    const DONATION_REJECTED = 'donation_rejected';
    const APPOINTMENT_SCHEDULED = 'appointment_scheduled';
    const APPOINTMENT_CANCELLED = 'appointment_cancelled';
    const MATCHING_COMPLETED = 'matching_completed';
    const STEP_COMPLETED = 'step_completed';
    const ADMIN_NOTIFICATION = 'admin_notification';
    const USER_NOTIFICATION = 'user_notification';
    const SYSTEM_ALERT = 'system_alert';
}

/**
 * Easy-to-use functions for common messaging operations
 */
function sendDonationMessage($userId, $donationType, $donationId, $status = 'submitted') {
    $messaging = new MessagingHelper();
    return $messaging->sendDonationNotification($userId, $donationType, $donationId, $status);
}

function sendAdoptionMessage($userId, $stepNumber, $stepName, $status = 'completed') {
    $messaging = new MessagingHelper();
    return $messaging->sendAdoptionNotification($userId, $stepNumber, $stepName, $status);
}

function sendAppointmentMessage($userId, $appointmentId, $appointmentType, $status = 'scheduled') {
    $messaging = new MessagingHelper();
    return $messaging->sendAppointmentNotification($userId, $appointmentId, $appointmentType, $status);
}

function sendAdminMessage($userId, $activityType, $activityDetails) {
    $messaging = new MessagingHelper();
    return $messaging->sendAdminNotification($userId, $activityType, $activityDetails);
}

function sendMatchingMessage($userId, $childId, $childName, $status = 'matched') {
    $messaging = new MessagingHelper();
    return $messaging->sendMatchingNotification($userId, $childId, $childName, $status);
}

function createChatWithUser($userId1, $userId2, $connectionType = 'manual') {
    $messaging = new MessagingHelper();
    return $messaging->createOrGetChat($userId1, $userId2, $connectionType);
}

// API Handler for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action'])) {
        header('Content-Type: application/json');
        
        switch ($input['action']) {
            case 'get_unread_count':
                if (isset($input['userId'])) {
                    $messaging = new MessagingHelper();
                    $unreadCount = $messaging->getUnreadMessageCount($input['userId']);
                    
                    echo json_encode([
                        'success' => true,
                        'unreadCount' => $unreadCount
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'User ID is required'
                    ]);
                }
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown action'
                ]);
                break;
        }
        exit;
    }
}

?> 