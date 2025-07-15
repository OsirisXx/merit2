<?php
require_once 'session_check.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!$isLoggedIn) {
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$requestEmail = $input['email'] ?? '';
$requestUserId = $input['userId'] ?? '';

// Validate request matches session
if ($requestEmail !== $currentUserEmail || $requestUserId !== $currentUserId) {
    echo json_encode([
        'success' => false,
        'error' => 'Request data does not match session'
    ]);
    exit;
}

// For now, we'll use a simpler approach - tell frontend to redirect for fresh auth
// In a production environment, you would:
// 1. Use Firebase Admin SDK to create custom tokens
// 2. Store user credentials securely
// 3. Implement proper token refresh mechanism

// Since we don't have the user's password and Firebase Admin SDK setup is complex,
// we'll redirect to signin page for fresh authentication
echo json_encode([
    'success' => true,
    'requiresPassword' => true,
    'message' => 'Please re-authenticate to sync your progress'
]);
?> 