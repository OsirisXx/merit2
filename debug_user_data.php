<?php
require_once 'session_check.php';

if (!$isLoggedIn) {
    die('Please log in first');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug User Data</title>
</head>
<body>
    <h1>User Data Debug</h1>
    
    <h2>PHP Session Data:</h2>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <h2>Firebase Data Test:</h2>
    <div id="firebase-debug"></div>
    
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    
    <script>
        // Firebase config
        const firebaseConfig = {
            apiKey: "AIzaSyDDOUEj5uwXTEVfPZlhUJB1CjuJWMOWAl8",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.firebasestorage.app",
            messagingSenderId: "794654001796",
            appId: "1:794654001796:web:4bb15e2fb08dfe9e86d2a9"
        };
        
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        const auth = firebase.auth();
        const db = firebase.firestore();
        
        const debugDiv = document.getElementById('firebase-debug');
        const sessionUserId = '<?php echo addslashes($currentUserId); ?>';
        
        async function debugUserData() {
            debugDiv.innerHTML += '<h3>Session User ID: ' + sessionUserId + '</h3>';
            
            // Check Firebase auth state
            auth.onAuthStateChanged(async (user) => {
                if (user) {
                    debugDiv.innerHTML += '<h3>Firebase User ID: ' + user.uid + '</h3>';
                    debugDiv.innerHTML += '<h3>Firebase Email: ' + user.email + '</h3>';
                    debugDiv.innerHTML += '<h3>IDs Match: ' + (user.uid === sessionUserId) + '</h3>';
                    
                    // Try to read adoption progress
                    try {
                        const doc = await db.collection('adoption_progress').doc(sessionUserId).get();
                        if (doc.exists) {
                            debugDiv.innerHTML += '<h3>Adoption Progress Document Found: YES</h3>';
                            debugDiv.innerHTML += '<pre>' + JSON.stringify(doc.data(), null, 2) + '</pre>';
                        } else {
                            debugDiv.innerHTML += '<h3>Adoption Progress Document Found: NO</h3>';
                        }
                    } catch (error) {
                        debugDiv.innerHTML += '<h3>Error reading document: ' + error.message + '</h3>';
                    }
                } else {
                    debugDiv.innerHTML += '<h3>No Firebase user authenticated</h3>';
                    
                    // Try reading with session user ID anyway
                    try {
                        const doc = await db.collection('adoption_progress').doc(sessionUserId).get();
                        if (doc.exists) {
                            debugDiv.innerHTML += '<h3>Document found with session ID (no auth): YES</h3>';
                            debugDiv.innerHTML += '<pre>' + JSON.stringify(doc.data(), null, 2) + '</pre>';
                        } else {
                            debugDiv.innerHTML += '<h3>Document found with session ID (no auth): NO</h3>';
                        }
                    } catch (error) {
                        debugDiv.innerHTML += '<h3>Error reading document (no auth): ' + error.message + '</h3>';
                    }
                }
            });
        }
        
        debugUserData();
    </script>
</body>
</html> 