<?php
session_start();
require_once 'session_check.php';

// Check if user is admin
if (!$isAdmin) {
    die('Access denied. Admin only.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Debug Tool</title>
</head>
<body>
    <h1>Document Debug Tool</h1>
    <p>Check console for debug information</p>
    
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.1.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.1.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.1.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.1.0/firebase-storage-compat.js"></script>

    <script>
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyDilHUkKJB35Wle5mFdnQ6dg0JTQepFxf0",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "1017382122111",
            appId: "1:1017382122111:web:24f8afac03e00d56d85bbe"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const db = firebase.firestore();
        const storage = firebase.storage();

        console.log('🔧 DEBUG TOOL: Testing document access...');
        
        // Test user: h8qq0E8avWO74cqS2Goy1wtENJh1 (from the logs)
        const testUserId = 'h8qq0E8avWO74cqS2Goy1wtENJh1';
        const testStep = 4;
        
        console.log(`Testing for user: ${testUserId}, step: ${testStep}`);
        
        // Check all possible locations
        db.collection('adoption_progress').doc(testUserId).collection(`step${testStep}_uploads`).get()
            .then(snapshot => {
                console.log(`📄 adoption_progress/${testUserId}/step${testStep}_uploads: ${snapshot.size} documents`);
                snapshot.forEach(doc => {
                    console.log('Document:', doc.id, doc.data());
                });
            });
            
        db.collection('user_submissions_status').doc(testUserId).collection(`step${testStep}_documents`).get()
            .then(snapshot => {
                console.log(`📄 user_submissions_status/${testUserId}/step${testStep}_documents: ${snapshot.size} documents`);
                snapshot.forEach(doc => {
                    console.log('Document:', doc.id, doc.data());
                });
            });
            
        db.collection('user_documents').doc(testUserId).collection(`step${testStep}_documents`).get()
            .then(snapshot => {
                console.log(`📄 user_documents/${testUserId}/step${testStep}_documents: ${snapshot.size} documents`);
                snapshot.forEach(doc => {
                    console.log('Document:', doc.id, doc.data());
                });
            });
            
        // Check storage
        storage.ref(`user_documents/${testUserId}/step${testStep}`).listAll()
            .then(result => {
                console.log(`📦 Storage user_documents/${testUserId}/step${testStep}: ${result.items.length} files`);
                result.items.forEach(item => {
                    console.log('File:', item.name, item.fullPath);
                });
            });
    </script>
</body>
</html> 