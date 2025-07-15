<?php
/**
 * Donation Message Handler
 * Handles donation-related messaging and notifications
 */

require_once 'messaging_helper.php';
require_once 'session_check.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Action is required']);
    exit;
}

$action = $input['action'];

try {
    switch ($action) {
        case 'donation_submitted':
            handleDonationSubmitted($input);
            break;
            
        case 'donation_approved':
            handleDonationApproved($input);
            break;
            
        case 'donation_rejected':
            handleDonationRejected($input);
            break;
            
        case 'donation_updated':
            handleDonationUpdated($input);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            exit;
    }
    
} catch (Exception $e) {
    error_log("❌ Donation message handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

function handleDonationSubmitted($input) {
    $userId = $input['userId'] ?? '';
    $donationType = $input['donationType'] ?? '';
    $donationId = $input['donationId'] ?? '';
    
    if (empty($userId) || empty($donationType) || empty($donationId)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendDonationMessage($userId, $donationType, $donationId, 'submitted');
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Donation submission message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send donation message']);
    }
}

function handleDonationApproved($input) {
    $userId = $input['userId'] ?? '';
    $donationType = $input['donationType'] ?? '';
    $donationId = $input['donationId'] ?? '';
    
    if (empty($userId) || empty($donationType) || empty($donationId)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendDonationMessage($userId, $donationType, $donationId, 'approved');
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Donation approval message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send donation approval message']);
    }
}

function handleDonationRejected($input) {
    $userId = $input['userId'] ?? '';
    $donationType = $input['donationType'] ?? '';
    $donationId = $input['donationId'] ?? '';
    $reason = $input['reason'] ?? '';
    
    if (empty($userId) || empty($donationType) || empty($donationId)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendDonationMessage($userId, $donationType, $donationId, 'rejected');
    
    // If there's a specific reason, send an additional message
    if (!empty($reason)) {
        $messaging = new MessagingHelper();
        $chatId = $messaging->createOrGetChat($userId, $messaging->getRandomAdminId(), "{$donationType}_donation");
        $messaging->sendSystemMessage($chatId, "Rejection reason: " . $reason, 'donation_feedback');
    }
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Donation rejection message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send donation rejection message']);
    }
}

function handleDonationUpdated($input) {
    $userId = $input['userId'] ?? '';
    $donationType = $input['donationType'] ?? '';
    $donationId = $input['donationId'] ?? '';
    $updateDetails = $input['updateDetails'] ?? '';
    
    if (empty($userId) || empty($donationType) || empty($donationId)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messaging = new MessagingHelper();
    $adminId = $messaging->getRandomAdminId();
    $chatId = $messaging->createOrGetChat($userId, $adminId, "{$donationType}_donation");
    
    $message = "📋 Your " . $messaging->formatDonationType($donationType) . " donation (ID: {$donationId}) has been updated. " . $updateDetails;
    
    $messageId = $messaging->sendSystemMessage($chatId, $message, 'donation_update', [
        'donationId' => $donationId,
        'donationType' => $donationType,
        'updateDetails' => $updateDetails
    ]);
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Donation update message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send donation update message']);
    }
}

?> 