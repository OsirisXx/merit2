<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['error' => 'Email is required']);
    exit();
}

// Firebase project details
$projectId = 'ally-user';
$apiKey = 'AIzaSyAtI0y8XwjmrRK8CZdbdp5r6gYMvOnyWWo';

// Get user by email from Firebase Auth
$authUrl = "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=$apiKey";

$authData = [
    'email' => [$email]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $authUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($authData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'User not found in Firebase Auth', 'code' => $httpCode]);
    exit();
}

$responseData = json_decode($response, true);

if (!$responseData || !isset($responseData['users']) || empty($responseData['users'])) {
    echo json_encode(['error' => 'No user data found']);
    exit();
}

$user = $responseData['users'][0];
$userId = $user['localId'];
$emailVerified = $user['emailVerified'] ?? false;

// If email is verified in Firebase Auth, update Firestore
if ($emailVerified) {
    $firestoreUrl = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/users/$userId";
    
    $updateData = [
        'fields' => [
            'isVerified' => ['booleanValue' => true],
            'emailVerified' => ['booleanValue' => true],
            'verifiedAt' => ['timestampValue' => date('c')],
            'verificationMethod' => ['stringValue' => 'firebase_auth_verified']
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firestoreUrl . '?updateMask.fieldPaths=isVerified&updateMask.fieldPaths=emailVerified&updateMask.fieldPaths=verifiedAt&updateMask.fieldPaths=verificationMethod');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $firestoreResponse = curl_exec($ch);
    $firestoreHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo json_encode([
        'success' => true,
        'message' => 'User verification status updated in Firestore',
        'userId' => $userId,
        'email' => $email,
        'emailVerified' => true,
        'firestoreUpdated' => $firestoreHttpCode === 200
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Email not yet verified in Firebase Auth',
        'userId' => $userId,
        'email' => $email,
        'emailVerified' => false,
        'instruction' => 'Please check your email and click the verification link'
    ]);
}
?> 