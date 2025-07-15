<?php
require_once 'session_check.php';

if (!$isLoggedIn) {
    die('Please log in first');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Firebase Data Inspector</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        button { padding: 10px 20px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Firebase Data Inspector</h1>
    
    <div class="section">
        <h2>PHP Session Info</h2>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($currentUserId); ?></p>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($currentUsername); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUserEmail); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($currentUserRole); ?></p>
    </div>

    <div class="section">
        <h2>Firebase Authentication Status</h2>
        <div id="auth-status">Checking...</div>
    </div>

    <div class="section">
        <h2>User Document Search</h2>
        <button onclick="searchUserDocuments()">Search All User Documents</button>
        <div id="user-search-results"></div>
    </div>

    <div class="section">
        <h2>Adoption Progress Documents</h2>
        <button onclick="searchAdoptionProgress()">Search All Adoption Progress</button>
        <div id="adoption-search-results"></div>
    </div>

    <div class="section">
        <h2>Direct Document Access</h2>
        <button onclick="accessDirectDocument()">Access Document with PHP Session ID</button>
        <div id="direct-access-results"></div>
    </div>

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
        
        // PHP session data
        const sessionUserId = '<?php echo addslashes($currentUserId); ?>';
        const sessionUsername = '<?php echo addslashes($currentUsername); ?>';
        const sessionEmail = '<?php echo addslashes($currentUserEmail); ?>';
        
        // Check Firebase auth status
        auth.onAuthStateChanged((user) => {
            const authDiv = document.getElementById('auth-status');
            if (user) {
                authDiv.innerHTML = `
                    <div class="success">✅ Firebase Authenticated</div>
                    <p><strong>Firebase UID:</strong> ${user.uid}</p>
                    <p><strong>Firebase Email:</strong> ${user.email}</p>
                    <p><strong>Email Verified:</strong> ${user.emailVerified}</p>
                    <p><strong>UID Matches Session:</strong> ${user.uid === sessionUserId ? 'YES' : 'NO'}</p>
                    <p><strong>Email Matches Session:</strong> ${user.email === sessionEmail ? 'YES' : 'NO'}</p>
                `;
            } else {
                authDiv.innerHTML = '<div class="error">❌ Not authenticated with Firebase</div>';
            }
        });

        async function searchUserDocuments() {
            const resultsDiv = document.getElementById('user-search-results');
            resultsDiv.innerHTML = '<p>Searching user documents...</p>';
            
            try {
                // Search by username
                const usernameQuery = await db.collection('users')
                    .where('username', '==', sessionUsername)
                    .get();
                
                // Search by email
                const emailQuery = await db.collection('users')
                    .where('email', '==', sessionEmail)
                    .get();
                
                let results = '<h3>User Document Search Results:</h3>';
                
                results += '<h4>By Username "' + sessionUsername + '":</h4>';
                if (!usernameQuery.empty) {
                    usernameQuery.forEach(doc => {
                        results += `<div class="success">Found: ${doc.id}</div>`;
                        results += `<pre>${JSON.stringify(doc.data(), null, 2)}</pre>`;
                    });
                } else {
                    results += '<div class="error">No documents found</div>';
                }
                
                results += '<h4>By Email "' + sessionEmail + '":</h4>';
                if (!emailQuery.empty) {
                    emailQuery.forEach(doc => {
                        results += `<div class="success">Found: ${doc.id}</div>`;
                        results += `<pre>${JSON.stringify(doc.data(), null, 2)}</pre>`;
                    });
                } else {
                    results += '<div class="error">No documents found</div>';
                }
                
                resultsDiv.innerHTML = results;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }

        async function searchAdoptionProgress() {
            const resultsDiv = document.getElementById('adoption-search-results');
            resultsDiv.innerHTML = '<p>Searching adoption progress documents...</p>';
            
            try {
                // Get all adoption progress documents (this might be limited by security rules)
                const snapshot = await db.collection('adoption_progress').get();
                
                let results = '<h3>All Adoption Progress Documents:</h3>';
                
                if (!snapshot.empty) {
                    snapshot.forEach(doc => {
                        const data = doc.data();
                        const isCurrentUser = doc.id === sessionUserId;
                        
                        results += `<h4>Document ID: ${doc.id} ${isCurrentUser ? '(CURRENT USER)' : ''}</h4>`;
                        if (data.username) {
                            results += `<p><strong>Username:</strong> ${data.username}</p>`;
                        }
                        
                        // Show structure type
                        if (data.adoptions) {
                            results += '<p><strong>Structure:</strong> Versioned (New)</p>';
                            results += `<p><strong>Current Adoption:</strong> ${data.currentAdoption}</p>`;
                            results += `<p><strong>Total Adoptions:</strong> ${data.totalAdoptions}</p>`;
                        } else if (data.adopt_progress) {
                            results += '<p><strong>Structure:</strong> Old</p>';
                        }
                        
                        results += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                        results += '<hr>';
                    });
                } else {
                    results += '<div class="error">No adoption progress documents found</div>';
                }
                
                resultsDiv.innerHTML = results;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }

        async function accessDirectDocument() {
            const resultsDiv = document.getElementById('direct-access-results');
            resultsDiv.innerHTML = '<p>Accessing document directly...</p>';
            
            try {
                const doc = await db.collection('adoption_progress').doc(sessionUserId).get();
                
                let results = `<h3>Direct Access to Document: ${sessionUserId}</h3>`;
                
                if (doc.exists) {
                    const data = doc.data();
                    results += '<div class="success">✅ Document exists</div>';
                    
                    // Analyze the structure
                    if (data.adoptions) {
                        results += '<p><strong>Structure Type:</strong> Versioned (New)</p>';
                        results += `<p><strong>Current Adoption:</strong> ${data.currentAdoption}</p>`;
                        results += `<p><strong>Total Adoptions:</strong> ${data.totalAdoptions}</p>`;
                        
                        // Show each adoption
                        Object.keys(data.adoptions).forEach(adoptionKey => {
                            const adoption = data.adoptions[adoptionKey];
                            results += `<h4>Adoption ${adoptionKey}:</h4>`;
                            results += `<p>Status: ${adoption.status}</p>`;
                            if (adoption.adopt_progress) {
                                const progress = adoption.adopt_progress;
                                const completedSteps = Object.keys(progress).filter(key => progress[key] === 'complete').length;
                                results += `<p>Progress: ${completedSteps}/11 steps completed</p>`;
                            }
                        });
                    } else if (data.adopt_progress) {
                        results += '<p><strong>Structure Type:</strong> Old</p>';
                        const progress = data.adopt_progress;
                        const completedSteps = Object.keys(progress).filter(key => progress[key] === 'complete').length;
                        results += `<p>Progress: ${completedSteps}/11 steps completed</p>`;
                    }
                    
                    results += '<h4>Full Document Data:</h4>';
                    results += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                } else {
                    results += '<div class="error">❌ Document does not exist</div>';
                }
                
                resultsDiv.innerHTML = results;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }
    </script>
</body>
</html> 