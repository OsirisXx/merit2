<?php
/**
 * WEBSITE NOTIFICATION SENDER
 * Handles all notification triggers from website actions
 */

require_once 'session_check.php';
require_once 'notification_service.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

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

    switch ($action) {
        
        // DONATION NOTIFICATIONS
        case 'donation_submitted':
            $userId = $data['userId'] ?? null;
            $donationType = $data['donationType'] ?? 'general';
            $amount = $data['amount'] ?? null;
            
            if ($userId) {
                $result = $notificationService->sendDonationNotification($userId, $donationType, 'submitted', [
                    'amount' => $amount,
                    'submittedAt' => date('c')
                ]);
            }
            break;

        case 'admin_donation_alert':
            $userName = $data['userName'] ?? 'Unknown User';
            $donationType = $data['donationType'] ?? 'general';
            $amount = $data['amount'] ?? null;
            
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
                    'donationType' => $donationType,
                    'amount' => $amount,
                    'needsReview' => true
                ]
            );
            break;

        // ADOPTION NOTIFICATIONS
        case 'adoption_started':
            $userId = $data['userId'] ?? null;
            $userName = $data['userName'] ?? 'User';
            
            if ($userId) {
                $result = $notificationService->sendAdoptionNotification($userId, 'process_started', null, [
                    'userName' => $userName,
                    'startedAt' => date('c')
                ]);
                
                $notificationService->sendAdminNotification(
                    'adoption',
                    'process_initiated',
                    "👶 New Adoption Process Started",
                    "$userName has started the adoption process. Please monitor their progress.",
                    ['userName' => $userName, 'userId' => $userId]
                );
            }
            break;

        // APPOINTMENT NOTIFICATIONS
        case 'appointment_requested':
            $userId = $data['userId'] ?? null;
            $userName = $data['userName'] ?? 'User';
            $appointmentDate = $data['appointmentDate'] ?? '';
            
            if ($userId) {
                $result = $notificationService->sendAppointmentNotification($userId, 'scheduled', $appointmentDate);
                
                $notificationService->sendAdminNotification(
                    'appointment',
                    'process_initiated',
                    "📅 New Appointment Request",
                    "$userName requested an appointment for $appointmentDate. Please confirm.",
                    ['userName' => $userName, 'userId' => $userId, 'appointmentDate' => $appointmentDate]
                );
            }
            break;

        // MATCHING NOTIFICATIONS
        case 'matching_requested':
            $userId = $data['userId'] ?? null;
            $userName = $data['userName'] ?? 'User';
            
            if ($userId) {
                $result = $notificationService->sendMatchingNotification($userId, 'request_submitted');
                
                $notificationService->sendAdminNotification(
                    'matching',
                    'process_initiated',
                    "💕 New Matching Request",
                    "$userName submitted a matching request. Please review and find matches.",
                    ['userName' => $userName, 'userId' => $userId]
                );
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
    echo json_encode(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
}
?> 