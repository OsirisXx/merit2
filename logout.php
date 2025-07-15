<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
session_destroy();

// Clear any remaining session cookies
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing Out...</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f7fa;
        }
        .logout-container {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #6EC6FF;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <h3>Signing out...</h3>
        <p>Please wait while we securely sign you out.</p>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-app.js";
        import { getAuth, signOut } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-auth.js";
        
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        
        // Sign out from Firebase and then redirect
        async function performLogout() {
            try {
                console.log('🔐 Starting Firebase logout...');
                await signOut(auth);
                console.log('✅ Firebase logout successful');
                
                // Clear any localStorage items that might contain auth data
                localStorage.removeItem('loginAttempts');
                localStorage.removeItem('lockoutEndTime');
                
                // Small delay to ensure Firebase logout is complete
                setTimeout(() => {
                    window.location.href = 'Signin.php';
                }, 500);
                
            } catch (error) {
                console.error('❌ Firebase logout error:', error);
                // Even if Firebase logout fails, still redirect to signin
                window.location.href = 'Signin.php';
            }
        }
        
        // Start logout process immediately
        performLogout();
    </script>
</body>
</html>
