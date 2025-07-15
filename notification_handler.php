<?php
require_once 'session_check.php';
require_once 'super_simple_notifications.php';

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

// Check if this is a direct notification (like your working notifications) or the old format
$isDirect = isset($data['userId']) && isset($data['title']) && isset($data['message']);

if ($isDirect) {
    // Handle direct notification format (matches your working notifications)
    $notificationSystem = new SuperSimpleNotifications();
    
    $userId = $data['userId'];
    $title = $data['title'];
    $message = $data['message'];
    $notificationData = $data['data'] ?? [];
    
    // Extract notification details
    $processType = $notificationData['processType'] ?? 'ADOPTION';
    $actionType = $notificationData['actionType'] ?? 'step_update';
    $stepNumber = $notificationData['step'] ?? null;
    
    error_log("Direct notification: $title for user $userId");
    
    // Send the notification using the working system
    $result = $notificationSystem->sendNotification($userId, strtolower($processType), $title, $message, $notificationData);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => $title]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send notification']);
    }
    exit;
}

// Legacy format handling
$type = $data['type'] ?? null;
$notificationData = $data['data'] ?? [];

if (!$type || !$notificationData) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing type or data']);
    exit;
}

try {
    $notificationSystem = new SuperSimpleNotifications();
    $result = false;
    
    // Debug logging
    error_log("Notification handler called with type: $type");
    error_log("Notification data: " . json_encode($notificationData));

    switch ($type) {
        case 'donation':
            $userId = $notificationData['userId'] ?? null;
            $donationType = $notificationData['donationType'] ?? 'general';
            $status = $notificationData['status'] ?? 'submitted';
            
            if ($userId) {
                $result = $notificationSystem->sendDonationNotification($userId, $donationType, $status);
            }
            break;

        case 'adoption':
            $userId = $notificationData['userId'] ?? null;
            $status = $notificationData['status'] ?? 'step_completed';
            $stepNumber = $notificationData['stepNumber'] ?? null;
            $title = $notificationData['title'] ?? null;
            $message = $notificationData['message'] ?? null;
            
            if ($userId) {
                if ($status === 'step_completed') {
                    $result = $notificationSystem->sendAdoptionStepCompleted($userId, $stepNumber);
                } elseif ($status === 'step_started') {
                    $result = $notificationSystem->sendAdoptionStepStarted($userId, $stepNumber);
                } elseif ($status === 'mobile_approved') {
                    $result = $notificationSystem->sendMobileAppApproval($userId, $stepNumber);
                } elseif ($status === 'mobile_step_started') {
                    $result = $notificationSystem->sendMobileAppStepStarted($userId, $stepNumber);
                } elseif ($status === 'adoption_started' && $title && $message) {
                    // Custom adoption started notification
                    $result = $notificationSystem->sendNotification($userId, 'adoption', $title, $message, ['status' => $status, 'stepNumber' => $stepNumber]);
                } elseif ($status === 'step_rejected' && $title && $message) {
                    // Custom step rejection notification
                    $result = $notificationSystem->sendNotification($userId, 'adoption', $title, $message, ['status' => $status, 'stepNumber' => $stepNumber]);
                } elseif ($status === 'admin_comment_added' && $title && $message) {
                    // Custom admin comment notification
                    $result = $notificationSystem->sendNotification($userId, 'adoption', $title, $message, ['status' => $status, 'stepNumber' => $stepNumber]);
                } else {
                    // Fallback for other adoption statuses
                    $fallbackTitle = $title ?? "Adoption Update";
                    $fallbackMessage = $message ?? "Your adoption process has been updated.";
                    if ($stepNumber && !$message) {
                        $fallbackMessage = "Step {$stepNumber} of your adoption process has been updated.";
                    }
                    $result = $notificationSystem->sendNotification($userId, 'adoption', $fallbackTitle, $fallbackMessage, ['status' => $status, 'stepNumber' => $stepNumber]);
                }
            }
            break;

        case 'appointment':
            $userId = $notificationData['userId'] ?? null;
            $status = $notificationData['status'] ?? 'scheduled';
            $appointmentDate = $notificationData['appointmentDate'] ?? null;
            
            if ($userId) {
                $result = $notificationSystem->sendAppointmentNotification($userId, $status, $appointmentDate);
            }
            break;

        case 'matching':
            $userId = $notificationData['userId'] ?? null;
            $status = $notificationData['status'] ?? 'request_submitted';
            $childName = $notificationData['childName'] ?? null;
            
            if ($userId) {
                // Create matching notification based on status
                if ($status === 'request_submitted') {
                    $result = $notificationSystem->sendMatchingRequestNotification($userId);
                } elseif ($status === 'match_found') {
                    $result = $notificationSystem->sendMatchFoundNotification($userId, $childName);
                } elseif ($status === 'match_accepted') {
                    $result = $notificationSystem->sendMatchAcceptedNotification($userId, $childName);
                } else {
                    // Fallback for other matching statuses
                    $result = $notificationSystem->sendTestNotification($userId);
                }
            }
            break;

        case 'chat':
            $userId = $notificationData['userId'] ?? null;
            $senderName = $notificationData['senderName'] ?? 'Unknown';
            $message = $notificationData['message'] ?? '';
            $chatUserId = $notificationData['chatUserId'] ?? null;
            
            if ($userId && $chatUserId) {
                $title = "New Message from {$senderName}";
                $result = $notificationSystem->sendNotification($userId, 'chat', $title, $message, ['senderName' => $senderName, 'chatUserId' => $chatUserId]);
            }
            break;

        case 'security':
            $userId = $notificationData['userId'] ?? null;
            $securityEvent = $notificationData['securityEvent'] ?? 'unknown_event';
            $details = $notificationData['details'] ?? '';
            $actionRequired = $notificationData['actionRequired'] ?? false;
            
            if ($userId) {
                $title = $actionRequired ? "Security Alert - Action Required" : "Security Notification";
                $result = $notificationSystem->sendNotification($userId, 'system', $title, $details, ['securityEvent' => $securityEvent, 'actionRequired' => $actionRequired]);
            }
            break;

        case 'system':
            $userId = $notificationData['userId'] ?? null;
            $title = $notificationData['title'] ?? 'System Notification';
            $message = $notificationData['message'] ?? '';
            
            if ($userId) {
                $result = $notificationSystem->sendNotification($userId, 'system', $title, $message);
            }
            break;

        case 'admin_notification':
            $title = $notificationData['title'] ?? 'Admin Notification';
            $message = $notificationData['message'] ?? '';
            
            // For admin notifications, we'll send to a generic admin user
            $result = $notificationSystem->sendNotification('admin', 'system', $title, $message);
            break;

        case 'admin_appointment_request':
            $userName = $notificationData['userName'] ?? 'Unknown User';
            $userEmail = $notificationData['userEmail'] ?? '';
            $appointmentType = $notificationData['appointmentType'] ?? 'appointment';
            $appointmentDate = $notificationData['appointmentDate'] ?? '';
            $appointmentTime = $notificationData['appointmentTime'] ?? '';
            
            $result = $notificationSystem->sendAppointmentRequestToAdmins($userName, $userEmail, $appointmentType, $appointmentDate, $appointmentTime);
            break;

        case 'admin_donation_submission':
            $userName = $notificationData['userName'] ?? 'Unknown User';
            $userEmail = $notificationData['userEmail'] ?? '';
            $donationType = $notificationData['donationType'] ?? 'general';
            $amount = $notificationData['amount'] ?? null;
            
            $result = $notificationSystem->sendDonationSubmissionToAdmins($userName, $userEmail, $donationType, $amount);
            break;

        case 'admin_adoption_step':
            $userName = $notificationData['userName'] ?? 'Unknown User';
            $userEmail = $notificationData['userEmail'] ?? '';
            $stepNumber = $notificationData['stepNumber'] ?? 0;
            $stepTitle = $notificationData['stepTitle'] ?? 'Step';
            
            $result = $notificationSystem->sendAdoptionStepCompletionToAdmins($userName, $userEmail, $stepNumber, $stepTitle);
            break;

        case 'admin_matching_request':
            $userName = $notificationData['userName'] ?? 'Unknown User';
            $userEmail = $notificationData['userEmail'] ?? '';
            $preferences = $notificationData['preferences'] ?? [];
            
            $result = $notificationSystem->sendMatchingRequestToAdmins($userName, $userEmail, $preferences);
            break;

        case 'admin_step_unlock':
            $userName = $notificationData['userName'] ?? 'Unknown User';
            $userEmail = $notificationData['userEmail'] ?? '';
            $stepNumber = $notificationData['stepNumber'] ?? 0;
            $stepTitle = $notificationData['stepTitle'] ?? 'Step';
            
            $result = $notificationSystem->sendStepUnlockToAdmins($userName, $userEmail, $stepNumber, $stepTitle);
            break;
            
        case 'user_adoption_notification':
            $userId = $notificationData['userId'] ?? '';
            $title = $notificationData['title'] ?? 'Adoption Update';
            $message = $notificationData['message'] ?? '';
            $icon = $notificationData['icon'] ?? '📋';
            $processType = $notificationData['processType'] ?? 'adoption';
            
            if ($userId) {
                $result = $notificationSystem->sendUserNotification($userId, $processType, $title, $message, $notificationData);
            } else {
                $result = ['success' => false, 'error' => 'User ID is required'];
            }
            break;

        case 'register_admin':
            $userId = $notificationData['userId'] ?? null;
            if ($userId) {
                $result = $notificationSystem->registerAdminUser($userId);
            }
            break;

        default:
            $result = false;
            error_log("❌ Unknown notification type: $type");
            break;
    }

    if ($result) {
        $responseMessage = "Notification sent successfully!";
        $stepNumber = $notificationData['stepNumber'] ?? null;
        $status = $notificationData['status'] ?? null;
        
        if ($stepNumber) {
            switch ($status) {
                case 'mobile_approved':
                    $responseMessage = "Step {$stepNumber} Approved by Admin!";
                    break;
                case 'mobile_step_started':
                    $responseMessage = "Step {$stepNumber} Started by Admin";
                    break;
                case 'step_completed':
                    $responseMessage = "Step {$stepNumber} Completed!";
                    break;
                default:
                    $responseMessage = "Step {$stepNumber} notification sent!";
            }
        }
        
        error_log("✅ Notification sent: {$responseMessage}");
        echo json_encode(['success' => true, 'message' => $responseMessage]);
    } else {
        error_log("❌ Failed to send notification");
        echo json_encode(['success' => false, 'error' => 'Failed to send notification']);
    }

} catch (Exception $e) {
    error_log("❌ Notification handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Notification error: ' . $e->getMessage()]);
}
?> 