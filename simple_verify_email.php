<?php
// DIRECT EMAIL VERIFICATION - NO BULLSHIT
$oobCode = $_GET['oobCode'] ?? '';

if (empty($oobCode)) {
    echo "Invalid verification link";
    exit();
}

// Step 1: Verify email with Firebase
$verifyUrl = "https://identitytoolkit.googleapis.com/v1/accounts:update?key=AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verifyUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['oobCode' => $oobCode]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "Verification failed";
    exit();
}

$data = json_decode($response, true);
$userId = $data['localId'] ?? '';
$email = $data['email'] ?? '';

// Step 2: Force update Firestore using direct API call
if (!empty($userId)) {
    $updateUrl = "https://firestore.googleapis.com/v1/projects/ally-user/databases/(default)/documents/users/$userId?key=AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk";
    
    // Get current document first
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $updateUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $currentDoc = curl_exec($ch);
    curl_close($ch);
    
    $current = json_decode($currentDoc, true);
    $fields = $current['fields'] ?? [];
    
    // Force set verification fields
    $fields['isVerified'] = ['booleanValue' => true];
    $fields['emailVerified'] = ['booleanValue' => true];
    $fields['verifiedAt'] = ['timestampValue' => date('c')];
    
    // Update document
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $updateUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['fields' => $fields]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $updateResponse = curl_exec($ch);
    $updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

// Always redirect to success
header("Location: verification_success.html?email=" . urlencode($email));
exit();
?> 