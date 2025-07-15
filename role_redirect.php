<?php
require_once 'session_check.php';

// Optional: Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in using our enhanced session system
error_log("=== ROLE_REDIRECT.PHP === isLoggedIn: " . ($isLoggedIn ? 'true' : 'false'));
error_log("=== ROLE_REDIRECT.PHP === currentUserRole: " . ($currentUserRole ?? 'null'));
error_log("=== ROLE_REDIRECT.PHP === SESSION: " . json_encode($_SESSION));

if (!$isLoggedIn) {
    error_log("=== ROLE_REDIRECT.PHP === USER NOT LOGGED IN, REDIRECTING TO SIGNIN");
    header('Location: Signin.php');
    exit;
}

$userRole = $currentUserRole;
error_log("=== ROLE_REDIRECT.PHP === USER ROLE: " . $userRole);

// Redirect to appropriate matching page based on user role
if ($userRole === 'admin') {
    error_log("=== ROLE_REDIRECT.PHP === REDIRECTING ADMIN TO MATCHING_ADMIN");
    header('Location: matching_admin.php');
} else {
    error_log("=== ROLE_REDIRECT.PHP === REDIRECTING USER TO MATCHING");
    header('Location: matching.php');
}
exit;

?>
