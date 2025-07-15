<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - MERITXELL</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .verification-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .verification-container h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .loading {
            color: #666;
            margin: 20px 0;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <h2>Email Verification</h2>
        <div id="status" class="loading">Verifying your email address...</div>
        <div id="action" style="display: none;">
            <a href="Signin.php" class="btn">Continue to Sign In</a>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>

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

        const statusDiv = document.getElementById('status');
        const actionDiv = document.getElementById('action');

        // Check if this is an email verification callback
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');
        const oobCode = urlParams.get('oobCode');

        if (mode === 'verifyEmail' && oobCode) {
            // Apply the email verification code
            auth.applyActionCode(oobCode)
                .then(() => {
                    statusDiv.innerHTML = '<div class="success">✅ Email verified successfully!</div>';
                    statusDiv.classList.remove('loading');
                    
                    // Check if user is signed in to update their Firestore record
                    auth.onAuthStateChanged((user) => {
                        if (user) {
                            // Update Firestore record
                            db.collection('users').doc(user.uid).update({
                                emailVerified: true,
                                isVerified: true,
                                verifiedAt: firebase.firestore.FieldValue.serverTimestamp()
                            }).then(() => {
                                console.log('User verification status updated in Firestore');
                                statusDiv.innerHTML += '<br><div class="success">Your account is now fully activated!</div>';
                                actionDiv.style.display = 'block';
                            }).catch((error) => {
                                console.error('Error updating Firestore:', error);
                                statusDiv.innerHTML += '<br><div class="error">Verification successful but there was an issue updating your profile. Please contact support.</div>';
                                actionDiv.style.display = 'block';
                            });
                        } else {
                            // User not signed in, but verification was successful
                            statusDiv.innerHTML += '<br><div class="success">Please sign in to complete the activation process.</div>';
                            actionDiv.style.display = 'block';
                        }
                    });
                })
                .catch((error) => {
                    console.error('Email verification failed:', error);
                    statusDiv.innerHTML = '<div class="error">❌ Email verification failed. The link may be expired or invalid.</div>';
                    statusDiv.classList.remove('loading');
                    actionDiv.style.display = 'block';
                });
        } else {
            // Not a verification callback, check if user is already signed in and verified
            auth.onAuthStateChanged((user) => {
                if (user) {
                    if (user.emailVerified) {
                        // Update Firestore if needed
                        db.collection('users').doc(user.uid).get()
                            .then((doc) => {
                                if (doc.exists && !doc.data().isVerified) {
                                    return db.collection('users').doc(user.uid).update({
                                        emailVerified: true,
                                        isVerified: true,
                                        verifiedAt: firebase.firestore.FieldValue.serverTimestamp()
                                    });
                                }
                            })
                            .then(() => {
                                statusDiv.innerHTML = '<div class="success">✅ Your email is already verified!</div>';
                                statusDiv.classList.remove('loading');
                                actionDiv.style.display = 'block';
                            });
                    } else {
                        statusDiv.innerHTML = '<div class="error">Your email address has not been verified yet. Please check your email for the verification link.</div>';
                        statusDiv.classList.remove('loading');
                        actionDiv.style.display = 'block';
                    }
                } else {
                    statusDiv.innerHTML = '<div class="error">Please sign in to verify your email address.</div>';
                    statusDiv.classList.remove('loading');
                    actionDiv.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html> 