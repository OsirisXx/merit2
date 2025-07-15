<?php
require_once 'enhanced_session.php';
require_once 'security_logger.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$sessionManager = EnhancedSessionManager::getInstance();
$logger = SecurityLogger::getInstance();

$data = json_decode(file_get_contents("php://input"), true);

// Debug logging
error_log("=== SESSION.PHP RECEIVED DATA ===: " . json_encode($data));

if (isset($data['username'])) {
    // Create enhanced session with security features
    $userData = [
        'uid' => $data['uid'] ?? null,
        'username' => $data['username'],
        'email' => $data['email'] ?? null,
        'role' => $data['role'] ?? 'user'
    ];
    
    $result = $sessionManager->createSession($userData);
    
    // Debug logging
    error_log("=== SESSION CREATION RESULT ===: " . json_encode($result));
    
    // Log successful session creation
    $logger->logEvent(SecurityLogger::EVENT_LOGIN_SUCCESS, [
        'username' => $data['username'],
        'role' => $data['role'] ?? 'user',
        'method' => 'firebase_auth'
    ]);
    
    echo json_encode($result);
} else {
    // Log failed session creation attempt
    $logger->logEvent(SecurityLogger::EVENT_LOGIN_FAILURE, [
        'reason' => 'No username provided in session creation',
        'data_received' => array_keys($data ?? [])
    ]);
    
    echo json_encode(['success' => false, 'message' => 'No username provided']);
}
?>