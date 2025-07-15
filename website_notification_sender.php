<?php
/**
 * WEBSITE NOTIFICATION SENDER
 * Handles all notification triggers from website actions
 * Sends notifications to users and admins using the notification service
 */

require_once 'session_check.php';
require_once 'notification_service.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

$action = $data['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing action']);
    exit;
}

try {
    $notificationService = getNotificationService();
    $result = false;
    
    // Debug logging
    error_log("Website notification sender called with action: $action");
    error_log("Data: " . json_encode($data));

    switch ($action) {
        
        // ============ DONATION NOTIFICATIONS ============
        case 'donation_submitted':
            $userId = $data['userId'] ?? null;
            $donationType = $data['donationType'] ?? 'general';
            $amount = $data['amount'] ?? null;
            $userName = $data['userName'] ?? 'User';
            
            if ($userId) {
                // Send confirmation to user
                $result = $notificationService->sendDonationNotification($userId, $donationType, 'submitted', [
                    'amount' => $amount,
                    'submittedAt' => date('c'),
                    'userName' => $userName
                ]);
                error_log("User donation confirmation sent for user: $userId, type: $donationType");
            }
            break;

        case 'admin_donation_alert':
            $userName = $data['userName'] ?? 'Unknown User';
            $userEmail = $data['userEmail'] ?? '';
            $donationType = $data['donationType'] ?? 'general';
            $amount = $data['amount'] ?? null;
            $donationId = $data['donationId'] ?? '';
            
            // Send alert to all admins
            $amountText = $amount ? " (Amount: $" . number_format($amount, 2) . ")" : "";
            $title = "🎁 New Donation Submitted";
            $message = "$userName submitted a $donationType donation$amountText. Please review and contact them.";
            
            $result = $notificationService->sendAdminNotification(
                'donation',
                'process_initiated',
                $title,
                $message,
                [
                    'userName' => $userName,
                    'userEmail' => $userEmail,
                    'donationType' => $donationType,
                    'amount' => $amount,
                    'donationId' => $donationId,
                    'needsReview' => true
                ]
            );
            error_log("Admin donation alert sent for donation: $donationId");
            break;

        // ============ ADOPTION NOTIFICATIONS ============
        case 'adoption_started':
            $userId = $data['userId'] ?? null;
            $userName = $data['userName'] ?? 'User';
            
            if ($userId) {
                // Send confirmation to user
                $result = $notificationService->sendAdoptionNotification($userId, 'process_started', null, [
                    'userName' => $userName,
                    'startedAt' => date('c')
                ]);
                
                // Send alert to admins
                $notificationService->sendAdminNotification(
                    'adoption',
                    'process_initiated',
                    "👶 New Adoption Process Started",
                    "$userName has started the adoption process. Please monitor their progress.",
                    [
                        'userName' => $userName,
                        'userId' => $userId,
                        'startedAt' => date('c')
                    ]
                );
                error_log("Adoption started notifications sent for user: $userId");
            }
            break;

        case 'step_document_uploaded':
            $userId = $data['userId'] ?? null;
            $stepNumber = $data['stepNumber'] ?? null;
            $userName = $data['userName'] ?? 'User';
            $documentType = $data['documentType'] ?? 'document';
            
            if ($userId && $stepNumber) {
                // Send confirmation to user
                $result = $notificationService->sendAdoptionNotification($userId, 'document_uploaded', $stepNumber, [
                    'userName' => $userName,
                    'documentType' => $documentType,
                    'uploadedAt' => date('c')
                ]);
                
                // Send alert to admins
                $notificationService->sendAdminNotification(
                    'adoption',
                    'document_uploaded',
                    "📄 Document Uploaded - Step $stepNumber",
                    "$userName uploaded a $documentType for Step $stepNumber. Please review.",
                    [
                        'userName' => $userName,
                        'userId' => $userId,
                        'stepNumber' => $stepNumber,
                        'documentType' => $documentType,
                        'needsReview' => true
                    ]
                );
                error_log("Document upload notifications sent for user: $userId, step: $stepNumber");
            }
            break;

        // ============ APPOINTMENT NOTIFICATIONS ============
        case 'appointment_requested':
            $userId = $data['userId'] ?? null;
            $userName = $data['userName'] ?? 'User';
            $userEmail = $data['userEmail'] ?? '';
            $appointmentType = $data['appointmentType'] ?? 'appointment';
            $appointmentDate = $data['appointmentDate'] ?? '';
            $appointmentTime = $data['appointmentTime'] ?? '';
            
            if ($userId) {
                // Send confirmation to user
                $result = $notificationService->sendAppointmentNotification($userId, 'scheduled', $appointmentDate, [
                    'userName' => $userName,
                    'appointmentType' => $appointmentType,
                    'appointmentTime' => $appointmentTime,
                    'requestedAt' => date('c')
                ]);
                
                // Send alert to admins
                $notificationService->sendAdminNotification(
                    'appointment',
                    'process_initiated',
                    "📅 New Appointment Request",
                    "$userName requested a $appointmentType appointment for $appointmentDate at $appointmentTime. Please confirm.",
                    [
                        'userName' => $userName,
                        'userEmail' => $userEmail,
                        'userId' => $userId,
                        'appointmentType' => $appointmentType,
                        'appointmentDate' => $appointmentDate,
                        'appointmentTime' => $appointmentTime,
                        'needsConfirmation' => true
                    ]
                );
                error_log("Appointment request notifications sent for user: $userId");
            }
            break;

        // ============ MATCHING NOTIFICATIONS ============
        case 'matching_requested':
            $userId = $data['userId'] ?? null;
            $userName = $data['userName'] ?? 'User';
            $userEmail = $data['userEmail'] ?? '';
            $preferences = $data['preferences'] ?? [];
            
            if ($userId) {
                // Send confirmation to user
                $result = $notificationService->sendMatchingNotification($userId, 'request_submitted', null, [
                    'userName' => $userName,
                    'preferences' => $preferences,
                    'requestedAt' => date('c')
                ]);
                
                // Send alert to admins
                $preferencesText = is_array($preferences) ? implode(', ', $preferences) : $preferences;
                $notificationService->sendAdminNotification(
                    'matching',
                    'process_initiated',
                    "💕 New Matching Request",
                    "$userName submitted a matching request. Preferences: $preferencesText. Please review and find matches.",
                    [
                        'userName' => $userName,
                        'userEmail' => $userEmail,
                        'userId' => $userId,
                        'preferences' => $preferences,
                        'needsMatching' => true
                    ]
                );
                error_log("Matching request notifications sent for user: $userId");
            }
            break;

        // ============ CHAT NOTIFICATIONS ============
        case 'chat_message_sent':
            $recipientId = $data['recipientId'] ?? null;
            $senderName = $data['senderName'] ?? 'Someone';
            $senderId = $data['senderId'] ?? null;
            $message = $data['message'] ?? '';
            $messagePreview = substr($message, 0, 100);
            
            if ($recipientId && $senderId) {
                $result = $notificationService->sendChatNotification($recipientId, $senderName, $messagePreview, $senderId, [
                    'fullMessage' => $message,
                    'sentAt' => date('c')
                ]);
                error_log("Chat notification sent to: $recipientId from: $senderName");
            }
            break;

        // ============ PROFILE NOTIFICATIONS ============
        case 'profile_updated':
            $userId = $data['userId'] ?? null;
            $userName = $data['userName'] ?? 'User';
            $fieldsUpdated = $data['fieldsUpdated'] ?? [];
            
            if ($userId) {
                $fieldsText = is_array($fieldsUpdated) ? implode(', ', $fieldsUpdated) : $fieldsUpdated;
                $result = $notificationService->sendProcessNotification(
                    'profile',
                    'status_update',
                    $userId,
                    "✅ Profile Updated",
                    "Your profile has been updated successfully. Updated fields: $fieldsText",
                    [
                        'userName' => $userName,
                        'fieldsUpdated' => $fieldsUpdated,
                        'updatedAt' => date('c')
                    ]
                );
                error_log("Profile update notification sent for user: $userId");
            }
            break;

        // ============ SYSTEM NOTIFICATIONS ============
        case 'system_maintenance':
            $title = $data['title'] ?? 'System Maintenance';
            $message = $data['message'] ?? 'System maintenance is scheduled.';
            $maintenanceDate = $data['maintenanceDate'] ?? '';
            
            // Send to all admins
            $result = $notificationService->sendAdminNotification(
                'system',
                'reminder_urgent',
                $title,
                $message,
                [
                    'maintenanceDate' => $maintenanceDate,
                    'priority' => 'high'
                ]
            );
            error_log("System maintenance notification sent to admins");
            break;

        // ============ SECURITY NOTIFICATIONS ============
        case 'security_alert':
            $userId = $data['userId'] ?? null;
            $securityEvent = $data['securityEvent'] ?? 'unknown_event';
            $details = $data['details'] ?? '';
            $actionRequired = $data['actionRequired'] ?? false;
            
            if ($userId) {
                $result = $notificationService->sendSecurityNotification($userId, $securityEvent, $details, $actionRequired);
                error_log("Security notification sent for user: $userId, event: $securityEvent");
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
            exit;
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send notification']);
    }

} catch (Exception $e) {
    error_log("Website notification sender error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
}
?> 