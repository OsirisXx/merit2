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

// Firebase project details - CORRECTED API KEY
$projectId = 'ally-user';
$webApiKey = 'AIzaSyAtI0y8XwjmrRK8CZdbdp5r6gYMvOnyWWo'; // This is the web API key

// First, find the user in Firestore by email
$firestoreSearchUrl = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/users";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firestoreSearchUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$userId = null;
$found = false;

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['documents'])) {
        foreach ($data['documents'] as $doc) {
            if (isset($doc['fields']['email']['stringValue']) && 
                $doc['fields']['email']['stringValue'] === $email) {
                $userId = basename($doc['name']);
                $found = true;
                break;
            }
        }
    }
}

if (!$found) {
    echo json_encode([
        'error' => 'User not found in Firestore',
        'email' => $email,
        'searched' => true
    ]);
    exit();
}

// FORCE UPDATE - Set user as verified regardless of email verification status
$firestoreUrl = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/users/$userId";

$updateData = [
    'fields' => [
        'isVerified' => ['booleanValue' => true],
        'emailVerified' => ['booleanValue' => true],
        'verifiedAt' => ['timestampValue' => date('c')],
        'verificationMethod' => ['stringValue' => 'force_verified']
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

if ($firestoreHttpCode === 200) {
    echo json_encode([
        'success' => true,
        'message' => 'User FORCE VERIFIED successfully!',
        'userId' => $userId,
        'email' => $email,
        'isVerified' => true,
        'emailVerified' => true,
        'method' => 'force_verified'
    ]);
} else {
    echo json_encode([
        'error' => 'Failed to update Firestore',
        'userId' => $userId,
        'email' => $email,
        'httpCode' => $firestoreHttpCode,
        'response' => $firestoreResponse
    ]);
}
?> 