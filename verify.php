<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .btn { background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Email Verification</h1>
    <div id="status">Checking verification status...</div>
    <div id="actions"></div>

    <script>
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        const db = firebase.firestore();

        auth.onAuthStateChanged(async (user) => {
            if (user) {
                const statusDiv = document.getElementById('status');
                const actionsDiv = document.getElementById('actions');
                
                if (user.emailVerified) {
                    statusDiv.innerHTML = '<div class="success">✅ Your email is verified in Firebase Auth!</div>';
                    
                    // Trigger your Cloud Function by updating emailVerifiedInFirestore
                    try {
                        await db.collection('users').doc(user.uid).update({
                            emailVerifiedInFirestore: true,
                            isVerified: true,
                            emailVerified: true,
                            verifiedAt: firebase.firestore.FieldValue.serverTimestamp()
                        });
                        
                        statusDiv.innerHTML += '<div class="success">✅ Firestore updated successfully!</div>';
                        statusDiv.innerHTML += '<div class="info">Your account is now fully verified!</div>';
                        
                        actionsDiv.innerHTML = '<button class="btn" onclick="window.location.href=\'Signin.php\'">Go to Login</button>';
                        
                    } catch (error) {
                        statusDiv.innerHTML += '<div class="error">❌ Error updating Firestore: ' + error.message + '</div>';
                    }
                } else {
                    statusDiv.innerHTML = '<div class="error">❌ Email not verified in Firebase Auth</div>';
                    statusDiv.innerHTML += '<div class="info">Please check your email and click the verification link first.</div>';
                    
                    actionsDiv.innerHTML = `
                        <button class="btn" onclick="sendVerificationEmail()">Resend Verification Email</button>
                        <button class="btn" onclick="checkAgain()">Check Again</button>
                    `;
                }
            } else {
                document.getElementById('status').innerHTML = '<div class="error">❌ Not signed in</div>';
                document.getElementById('actions').innerHTML = '<button class="btn" onclick="window.location.href=\'Signin.php\'">Go to Login</button>';
            }
        });

        function sendVerificationEmail() {
            const user = auth.currentUser;
            if (user) {
                user.sendEmailVerification().then(() => {
                    document.getElementById('status').innerHTML += '<div class="success">✅ Verification email sent!</div>';
                }).catch((error) => {
                    document.getElementById('status').innerHTML += '<div class="error">❌ Error sending email: ' + error.message + '</div>';
                });
            }
        }

        function checkAgain() {
            auth.currentUser.reload().then(() => {
                window.location.reload();
            });
        }
    </script>
</body>
</html> 