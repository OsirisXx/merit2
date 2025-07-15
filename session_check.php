<?php
// session_check.php – **MINIMAL** session validator
// Include this at the top of any page that needs authentication.

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn          = isset($_SESSION['uid']);
$currentUserId       = $_SESSION['uid']              ?? null;
$currentUsername     = $_SESSION['username']         ?? null;
$currentUserRole     = $_SESSION['role']             ?? 'user';
$currentUserEmail    = $_SESSION['email']            ?? null;
$currentServicePreference = $_SESSION['servicePreference'] ?? 'both';

// Firebase authentication token handling
$firebaseIdToken = $_SESSION['firebase_id_token'] ?? null;
$firebaseTokenTime = $_SESSION['firebase_token_time'] ?? null;

// Check if Firebase token is expired (Firebase ID tokens expire after 1 hour)
$firebaseTokenValid = false;
if ($firebaseIdToken && $firebaseTokenTime) {
    $tokenAge = time() - $firebaseTokenTime;
    $firebaseTokenValid = $tokenAge < 3600; // 1 hour in seconds
}

// Helper variable for admin check (used in sidebars)
$isAdmin = $isLoggedIn && ($currentUserRole === 'admin');

// Register admin users for notifications
if ($isAdmin && $currentUserId) {
    try {
        require_once 'super_simple_notifications.php';
        $notificationSystem = new SuperSimpleNotifications();
        $notificationSystem->registerAdminUser($currentUserId);
    } catch (Exception $e) {
        error_log("Failed to register admin user for notifications: " . $e->getMessage());
    }
}

// Simple helper to force login
function requireLogin($redirectTo = 'Signin.php') {
    if (!isset($_SESSION['uid'])) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

// Optional: if request wants JSON status (e.g., via fetch) return it
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'isLoggedIn' => $isLoggedIn,
        'uid'        => $currentUserId,
        'username'   => $currentUsername,
        'role'       => $currentUserRole,
        'email'      => $currentUserEmail,
        'servicePreference' => $currentServicePreference,
        'firebaseToken' => $firebaseTokenValid ? $firebaseIdToken : null,
        'firebaseTokenValid' => $firebaseTokenValid,
    ]);
    exit;
}
?> 