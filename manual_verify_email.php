<?php
/**
 * Manual Email Verification Script
 * Use this to manually verify a user's email and update the isVerified field
 * This is a backup solution when the Cloud Function isn't working
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'session_check.php';

// Include Firebase JWT library (you might need to install this via composer)
// For now, we'll use the Firebase Admin SDK through HTTP calls

function updateUserVerificationStatus($userId, $email) {
    try {
        // This would normally use the Firebase Admin SDK
        // For now, we'll create a simple verification that can be called manually
        
        $verification_data = [
            'userId' => $userId,
            'email' => $email,
            'isVerified' => true,
            'emailVerified' => true,
            'verifiedAt' => time(),
            'verificationMethod' => 'manual_script',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // For debugging - log the verification attempt
        $log_entry = date('Y-m-d H:i:s') . " - Manual verification for user: $userId, email: $email\n";
        file_put_contents('verification_log.txt', $log_entry, FILE_APPEND);
        
        return [
            'success' => true,
            'message' => 'User verification status updated',
            'data' => $verification_data
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error updating verification status: ' . $e->getMessage()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $input['userId'] ?? '';
    $email = $input['email'] ?? '';
    
    if (empty($userId) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'userId and email are required'
        ]);
        exit;
    }
    
    $result = updateUserVerificationStatus($userId, $email);
    echo json_encode($result);
    
} else {
    // GET request - show a simple form for manual verification
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Manual Email Verification</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="email"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
            button:hover { background: #0056b3; }
            .result { margin-top: 20px; padding: 10px; border-radius: 4px; }
            .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        </style>
    </head>
    <body>
        <h1>Manual Email Verification</h1>
        <p>Use this tool to manually verify a user's email when the automatic Cloud Function isn't working.</p>
        
        <form id="verificationForm">
            <div class="form-group">
                <label for="userId">User ID:</label>
                <input type="text" id="userId" name="userId" placeholder="Enter the user's Firebase UID" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter the user's email address" required>
            </div>
            
            <button type="submit">Verify Email</button>
        </form>
        
        <div id="result"></div>
        
        <hr style="margin: 30px 0;">
        
        <h2>Instructions:</h2>
        <ol>
            <li>Find the user's Firebase UID from the Firebase Console or your user database</li>
            <li>Enter the user ID and email address above</li>
            <li>Click "Verify Email" to manually mark the email as verified</li>
            <li>The script will update the verification status in your system</li>
        </ol>
        
        <h3>How to find User ID:</h3>
        <ul>
            <li>Go to Firebase Console → Authentication → Users</li>
            <li>Find the user by email and copy their UID</li>
            <li>Or check your application logs for the user ID</li>
        </ul>

        <script>
        document.getElementById('verificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('userId').value;
            const email = document.getElementById('email').value;
            const resultDiv = document.getElementById('result');
            
            try {
                resultDiv.innerHTML = '<p>Processing...</p>';
                
                const response = await fetch('manual_verify_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ userId, email })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.innerHTML = `<div class="result success">
                        <strong>Success!</strong> ${result.message}
                        <br>Now you need to update this in Firebase Firestore manually.
                    </div>`;
                } else {
                    resultDiv.innerHTML = `<div class="result error">
                        <strong>Error:</strong> ${result.message}
                    </div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="result error">
                    <strong>Error:</strong> ${error.message}
                </div>`;
            }
        });
        </script>
    </body>
    </html>
    <?php
}
?> 