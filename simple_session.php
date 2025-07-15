<?php
// simple_session.php –  **MINIMAL** session creation endpoint
// Accepts JSON { uid, username, email?, role?, idToken? } and stores them in PHP session.

session_start();
header('Content-Type: application/json');

// ─────────────────────────────────────────────────────────────
// Grab incoming data (JSON body or form-urlencoded fallback)
// ─────────────────────────────────────────────────────────────
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if ($data === null) {
    $data = $_POST; // fallback for traditional form posts
}

// ─────────────────────────────────────────────────────────────
// Validate required fields
// ─────────────────────────────────────────────────────────────
if (!isset($data['uid']) || !isset($data['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'uid or username missing',
    ]);
    exit;
}

// ─────────────────────────────────────────────────────────────
// Populate session
// ─────────────────────────────────────────────────────────────
$_SESSION['uid']              = $data['uid'];
$_SESSION['username']         = $data['username'];
$_SESSION['email']            = $data['email'] ?? null;
$_SESSION['role']             = $data['role']  ?? 'user';
$_SESSION['servicePreference'] = $data['servicePreference'] ?? 'both';

// Store Firebase ID token if provided (for cross-page auth persistence)
if (isset($data['idToken'])) {
    $_SESSION['firebase_id_token'] = $data['idToken'];
    $_SESSION['firebase_token_time'] = time(); // Store timestamp for token expiration check
}

// Success response
echo json_encode(['success' => true]);
?> 