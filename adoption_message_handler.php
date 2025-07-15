<?php
/**
 * Adoption Message Handler
 * Handles adoption-related messaging and notifications
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

// Debug logging
error_log("=== ADOPTION MESSAGE HANDLER DEBUG ===");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Input data: " . json_encode($input));
error_log("Raw input: " . file_get_contents('php://input'));

if (!isset($input['action'])) {
    error_log("❌ No action provided in request");
    echo json_encode(['success' => false, 'error' => 'Action is required']);
    exit;
}

$action = $input['action'];

try {
    switch ($action) {
        case 'step_completed':
            handleStepCompleted($input);
            break;
            
        case 'step_pending':
            handleStepPending($input);
            break;
            
        case 'step_rejected':
            handleStepRejected($input);
            break;
            
        case 'adoption_started':
            handleAdoptionStarted($input);
            break;
            
        case 'matching_completed':
            handleMatchingCompleted($input);
            break;
            
        case 'document_uploaded':
            handleDocumentUploaded($input);
            break;
            
        case 'appointment_scheduled':
            handleAppointmentScheduled($input);
            break;
            
        case 'profile_updated':
            handleProfileUpdated($input);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            exit;
    }
    
} catch (Exception $e) {
    error_log("❌ Adoption message handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}

function handleStepCompleted($input) {
    $userId = $input['userId'] ?? '';
    $stepNumber = $input['stepNumber'] ?? '';
    $stepName = $input['stepName'] ?? '';
    
    if (empty($userId) || empty($stepNumber) || empty($stepName)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendAdoptionMessage($userId, $stepNumber, $stepName, 'completed');
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Step completion message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send step completion message']);
    }
}

function handleStepPending($input) {
    $userId = $input['userId'] ?? '';
    $stepNumber = $input['stepNumber'] ?? '';
    $stepName = $input['stepName'] ?? '';
    
    if (empty($userId) || empty($stepNumber) || empty($stepName)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendAdoptionMessage($userId, $stepNumber, $stepName, 'pending');
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Step pending message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send step pending message']);
    }
}

function handleStepRejected($input) {
    $userId = $input['userId'] ?? '';
    $stepNumber = $input['stepNumber'] ?? '';
    $stepName = $input['stepName'] ?? '';
    $reason = $input['reason'] ?? '';
    
    if (empty($userId) || empty($stepNumber) || empty($stepName)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendAdoptionMessage($userId, $stepNumber, $stepName, 'rejected');
    
    // If there's a specific reason, send an additional message
    if (!empty($reason)) {
        $messaging = new MessagingHelper();
        $adminId = $messaging->getRandomAdminId();
        $chatId = $messaging->createOrGetChat($userId, $adminId, 'adoption');
        $messaging->sendSystemMessage($chatId, "Rejection reason for Step {$stepNumber}: " . $reason, 'adoption_feedback');
    }
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Step rejection message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send step rejection message']);
    }
}

function handleAdoptionStarted($input) {
    $userId = $input['userId'] ?? '';
    $username = $input['username'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    // If no username provided, try to get it from the user's profile
    if (empty($username)) {
        $username = getUsernameFromFirestore($userId);
    }
    
    $messaging = new MessagingHelper();
    $adminId = $messaging->getRandomAdminId();
    $chatId = $messaging->createOrGetChat($userId, $adminId, 'adoption');
    
    // Send welcome message to user
    $userMessage = "🎉 Welcome to the adoption process, {$username}! Your journey begins now. We're here to support you every step of the way.";
    
    $messageId = $messaging->sendSystemMessage($chatId, $userMessage, 'adoption_welcome');
    
    // Send notification to admin
    $adminMessage = "👶 New adoption process started by {$username}. Please provide guidance and support throughout their journey.";
    $messaging->sendSystemMessage($chatId, $adminMessage, 'adoption_admin_notification');
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Adoption started message sent successfully',
            'messageId' => $messageId,
            'chatId' => $chatId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send adoption started message']);
    }
}

function getUsernameFromFirestore($userId) {
    // This is a simplified implementation - in a real app you'd query Firestore
    // For now, return a default username
    return 'User';
}

function handleMatchingCompleted($input) {
    $userId = $input['userId'] ?? '';
    $childId = $input['childId'] ?? '';
    $childName = $input['childName'] ?? '';
    
    if (empty($userId) || empty($childId) || empty($childName)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendMatchingMessage($userId, $childId, $childName, 'matched');
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Matching completed message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send matching completed message']);
    }
}

function handleDocumentUploaded($input) {
    $userId = $input['userId'] ?? '';
    $documentType = $input['documentType'] ?? '';
    $stepNumber = $input['stepNumber'] ?? '';
    
    if (empty($userId) || empty($documentType)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messaging = new MessagingHelper();
    $adminId = $messaging->getRandomAdminId();
    $chatId = $messaging->createOrGetChat($userId, $adminId, 'adoption');
    
    $stepText = $stepNumber ? " for Step {$stepNumber}" : '';
    $message = "📄 Document uploaded: {$documentType}{$stepText}. An admin will review it shortly.";
    
    $messageId = $messaging->sendSystemMessage($chatId, $message, 'document_upload', [
        'documentType' => $documentType,
        'stepNumber' => $stepNumber
    ]);
    
    // Also send admin notification
    sendAdminMessage($userId, 'document_uploaded', $documentType);
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Document upload message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send document upload message']);
    }
}

function handleAppointmentScheduled($input) {
    $userId = $input['userId'] ?? '';
    $appointmentId = $input['appointmentId'] ?? '';
    $appointmentType = $input['appointmentType'] ?? '';
    $appointmentDate = $input['appointmentDate'] ?? '';
    $status = $input['status'] ?? 'scheduled';
    
    if (empty($userId) || empty($appointmentId) || empty($appointmentType)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendAppointmentMessage($userId, $appointmentId, $appointmentType, $status);
    
    // Send additional message with date if provided
    if (!empty($appointmentDate)) {
        $messaging = new MessagingHelper();
        $adminId = $messaging->getRandomAdminId();
        $chatId = $messaging->createOrGetChat($userId, $adminId, 'appointment');
        $messaging->sendSystemMessage($chatId, "📅 Appointment Date: {$appointmentDate}", 'appointment_details');
    }
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Appointment message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send appointment message']);
    }
}

function handleProfileUpdated($input) {
    $userId = $input['userId'] ?? '';
    $activityType = $input['activityType'] ?? '';
    $activityDetails = $input['activityDetails'] ?? '';
    
    if (empty($userId) || empty($activityType) || empty($activityDetails)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $messageId = sendAdminMessage($userId, $activityType, $activityDetails);
    
    if ($messageId) {
        echo json_encode([
            'success' => true, 
            'message' => 'Profile update message sent successfully',
            'messageId' => $messageId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send profile update message']);
    }
}

?> 