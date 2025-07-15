<?php
require_once 'session_check.php';

// Redirect if user is not logged in
if (!$isLoggedIn) {
    header('Location: Signin.php');
    exit;
}

if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php include('navbar.php'); ?>
<?php include('chatbot.php'); ?>

<!-- Include Simple Notifications for appointment updates -->


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Ally</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Basic layout styles for container, sidebar, and main-content */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background-color: #ffffff;
            border-right: 1px solid #e0e0e0;
            padding: 30px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            margin-bottom: 15px;
        }

        .sidebar a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 0;
            white-space: nowrap;
            overflow: hidden;
            font-size: 0.95em;
        }

        .sidebar a:hover {
            color: #6ea4ce;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            box-sizing: border-box;
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
            text-align: left;
        }

        /* Styles for the appointment cards */
        .appointments-list {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }

        .appointment-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Specific style for admin view appointment cards */
        .admin-appointment-card {
            border: 2px solid #7CB9E8;
            /* Highlight admin-managed cards */
            box-shadow: 0 4px 15px rgba(124, 185, 232, 0.2);
        }

        .appointment-card h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .appointment-card p {
            margin: 5px 0;
            color: #555;
            font-size: 0.95em;
        }

        .appointment-card .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
            font-size: 0.9em;
        }

        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status.accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .status.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* New style for completed status */
        .status.completed {
            background-color: #e0e0e0;
            color: #555;
        }

        .appointment-actions {
            margin-top: 15px;
            display: flex;
            /* Use flexbox for alignment */
            justify-content: flex-end;
            align-items: center;
            /* Align items vertically */
            gap: 10px;
            /* Space between buttons/checkbox */
            flex-wrap: wrap;
            /* Allow wrapping if needed */
            max-width: 100%;
            /* Ensure it doesn't exceed container width */
        }

        .appointment-actions button,
        .appointment-actions label {
            padding: 8px 12px;
            /* Adjusted padding */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85em;
            transition: background-color 0.2s ease;
            white-space: nowrap;
            /* Prevent text wrapping */
            max-width: 100%;
            /* Ensure buttons don't exceed container width */
            box-sizing: border-box;
            /* Include padding in width calculation */
            flex-shrink: 1;
            /* Allow buttons to shrink if needed */
        }

        .appointment-actions input[type="checkbox"] {
            margin-right: 5px;
            /* Space between checkbox and label text */
            transform: scale(1.1);
            /* Slightly larger checkbox */
            cursor: pointer;
        }

        .cancel-btn {
            background-color: #dc3545;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }

        .accept-checkbox-label {
            background-color: #28a745;
            /* Green background for acceptance */
            color: white;
            display: inline-flex;
            /* To align checkbox and text */
            align-items: center;
            min-width: 0;
            /* Allow flex item to shrink */
            overflow: hidden;
            /* Hide overflow text */
        }

        .accept-checkbox-label:hover {
            background-color: #218838;
        }

        /* Style for when the checkbox is checked */
        .accept-checkbox-label input[type="checkbox"]:checked+span {
            /* This targets the span next to the checked checkbox, you might style the label itself or just the text */
            font-weight: bold;
        }


        /* Message box styles */
        .alert-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            display: none;
            /* Hidden by default */
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        #no-appointments-message {
            text-align: center;
            color: #666;
            margin-top: 50px;
        }


        @media (max-width: 992px) {
            .appointment-actions {
                justify-content: center;
                /* Center buttons on medium screens */
            }
            
            .appointment-actions button,
            .appointment-actions label {
                font-size: 0.8em;
                /* Slightly smaller text on medium screens */
                padding: 6px 10px;
                /* Reduce padding on medium screens */
            }
        }

        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                display: none !important;
                pointer-events: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                z-index: -9999 !important;
                position: absolute !important;
                left: -9999px !important;
            }

            .main-content {
                width: 100%;
                padding: 15px;
            }

            .appointments-list {
                grid-template-columns: 1fr;
            }

            .appointment-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .appointment-actions button,
            .appointment-actions label {
                width: 100%;
                justify-content: center;
                text-align: center;
                font-size: 0.9em;
            }
        }

        /* Mobile Menu Styles */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 80px;
            left: 15px;
            z-index: 1001;
            background: #ffffff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .mobile-menu-toggle:hover {
            background: #f8f9fa;
            border-color: #6ea4ce;
        }

        .mobile-dropdown-menu {
            position: fixed;
            top: 125px;
            left: 15px;
            right: 15px;
            background: #ffffff;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            max-height: 70vh;
            overflow-y: auto;
            pointer-events: none;
            visibility: hidden;
        }

        .mobile-dropdown-menu.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
            visibility: visible;
        }

        .mobile-dropdown-menu ul {
            list-style: none;
            padding: 15px 0;
            margin: 0;
        }

        .mobile-dropdown-menu li {
            margin: 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .mobile-dropdown-menu li:last-child {
            border-bottom: none;
        }

        .mobile-dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 0.95em;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .mobile-dropdown-menu a:hover {
            background: #f0f8ff;
            color: #6ea4ce;
        }

        .hamburger {
            width: 24px;
            height: 18px;
            position: relative;
            transform: rotate(0deg);
            transition: .3s ease-in-out;
            cursor: pointer;
        }

        .hamburger span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: #333;
            border-radius: 2px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .2s ease-in-out;
        }

        .hamburger span:nth-child(1) {
            top: 0px;
        }

        .hamburger span:nth-child(2) {
            top: 7px;
        }

        .hamburger span:nth-child(3) {
            top: 14px;
        }

        .hamburger.active span:nth-child(1) {
            top: 7px;
            transform: rotate(135deg);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
            left: -60px;
        }

        .hamburger.active span:nth-child(3) {
            top: 7px;
            transform: rotate(-135deg);
        }

        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
        }

        /* Enhanced small mobile optimizations */
        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }

            .mobile-menu-toggle {
                top: 70px;
                left: 10px;
                padding: 8px;
            }

            .mobile-dropdown-menu {
                top: 115px;
                left: 10px;
                right: 10px;
            }
        }

        /* Tablet optimizations */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar {
                width: 200px;
                padding: 20px 15px;
            }

            .sidebar a {
                font-size: 0.9em;
            }
        }

        /* Large desktop optimizations */
        @media (min-width: 1200px) {
            .sidebar {
                width: 260px;
                padding: 35px 25px;
            }

            .main-content {
                padding: 40px;
            }
        }

        /* Ultra-wide optimizations */
        @media (min-width: 1600px) {
            .container {
                max-width: 1400px;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="Dashboard.php">🏠 Home</a></li>
                
                <?php if ($isAdmin || $currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
                <li><a href="ProgTracking.php">📈 Progress Tracking</a></li>
                <?php endif; ?>
                
                <?php if ($isAdmin): ?>
                <li><a href="Appointments.php">📅 Appointment/Scheduling</a></li>
                <?php else: ?>
                  <?php if ($currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
                  <li><a href="Appointments.php">📅 Appointments</a></li>
                  <li><a href="Schedule.php">🗓️ Scheduling</a></li>
                  <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($isAdmin || $currentServicePreference === 'donate_only' || $currentServicePreference === 'both'): ?>
                <li><a href="Donation.php">💖 Donation Hub</a></li>
                <?php endif; ?>
                
                <?php if ($isAdmin || $currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
                <!-- Matching is now integrated into Stage 7 of the adoption process -->
                <?php endif; ?>
                
                <?php if ($isAdmin): ?>
                <li><a href="ChildStatus.php">👶 Child Status Information</a></li>
                <li><a href="admin.php?filter=donation-reports">📊 Donation Reports</a></li>
                <li><a href="admin.php">⚙️ Admin History Dashboard</a></li>
                <li><a href="history.php">📜 History</a></li>
                <?php else: ?>
                  <li><a href="user_history.php">📜 My History</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main class="main-content">
            <h2 id="appointments-page-title">My Appointments</h2>
            <div id="status-message" class="alert-message" style="display:none;"></div>
            <div id="appointments-container" class="appointments-list">
            </div>
            <div id="no-appointments-message" style="display:none;">
                <p>You have no appointments scheduled yet. <a href="Schedule.php">Schedule one now!</a></p>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Toggle Button -->
    <div class="mobile-menu-toggle" id="hamburger" onclick="toggleMobileMenu()">
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-dropdown-menu" id="mobileDropdownMenu">
        <ul>
            <li><a href="Dashboard.php">🏠 Home</a></li>
            
            <?php if ($isAdmin || $currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
            <li><a href="ProgTracking.php">📈 Progress Tracking</a></li>
            <?php endif; ?>
            
            <?php if ($isAdmin): ?>
            <li><a href="Appointments.php">📅 Appointment/Scheduling</a></li>
            <?php else: ?>
                <?php if ($currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
                <li><a href="Appointments.php">📅 Appointments</a></li>
                <li><a href="Schedule.php">🗓️ Scheduling</a></li>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($isAdmin || $currentServicePreference === 'donate_only' || $currentServicePreference === 'both'): ?>
            <li><a href="Donation.php">💖 Donation Hub</a></li>
            <?php endif; ?>
            
            <?php if ($isAdmin): ?>
            <li><a href="ChildStatus.php">👶 Child Status Information</a></li>
            <li><a href="admin.php?filter=donation-reports">📊 Donation Reports</a></li>
            <li><a href="admin.php">⚙️ Admin History Dashboard</a></li>
            <li><a href="history.php">📜 History</a></li>
            <?php else: ?>
            <li><a href="user_history.php">📜 My History</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>

    <!-- Firebase compat SDK - Same as ProgTracking.php -->
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-storage-compat.js"></script>

    <script>
        // Firebase compat configuration - Same as other pages
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        // Initialize Firebase compat SDK - Creates global window.firebase, window.db, window.auth
        firebase.initializeApp(firebaseConfig);
        const db = firebase.firestore();
        const auth = firebase.auth();
        
        // Make Firebase globally available for navbar notifications
        window.firebase = firebase;
        window.db = db;
        window.auth = auth;

        const appointmentsContainer = document.getElementById('appointments-container');
        const noAppointmentsMessage = document.getElementById('no-appointments-message');
        const statusMessage = document.getElementById('status-message');
        const appointmentsPageTitle = document.getElementById('appointments-page-title'); // Get title element

        let userRole = 'user'; // Default role, will be updated after auth state is known
        
        // Get user info from PHP session
        const sessionUserId = '<?php echo $currentUserId; ?>';
        const sessionUserEmail = '<?php echo $currentUserEmail; ?>';
        const sessionUserRole = '<?php echo $currentUserRole; ?>';
        const firebaseTokenValid = <?php echo $firebaseTokenValid ? 'true' : 'false'; ?>;
        
        console.log('=== APPOINTMENTS PAGE LOADED ===');
        console.log('Session User ID:', sessionUserId);
        console.log('Session User Email:', sessionUserEmail);
        console.log('Session User Role:', sessionUserRole);
        console.log('Firebase Token Valid:', firebaseTokenValid);

        // Validate session data
        if (!sessionUserId || sessionUserId === '') {
            console.error('❌ Session User ID is empty or invalid');
            console.log('This may indicate a session problem. User may need to re-login.');
        }
        if (!sessionUserEmail || sessionUserEmail === '') {
            console.error('❌ Session User Email is empty or invalid');
        }
        if (!sessionUserRole || sessionUserRole === '') {
            console.warn('⚠️ Session User Role is empty, defaulting to "user"');
        }

        // Function to display messages
        function displayMessage(element, message, type) {
            element.textContent = message;
            element.className = `alert-message alert-${type}`;
            element.style.display = 'block';
        }

        // Show warning when Firebase user doesn't match session
        function showAuthenticationWarning() {
            const warningHtml = `
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 10px; margin-bottom: 15px;">
                    <strong>⚠️ Authentication Mismatch:</strong> Firebase user doesn't match your session. 
                    <a href="Signin.php?redirect=${encodeURIComponent(window.location.pathname)}" style="color: #856404; text-decoration: underline;">
                        Click here to refresh authentication
                    </a>
                </div>
            `;
            statusMessage.innerHTML = warningHtml;
            statusMessage.style.display = 'block';
        }

        // Show message when using session fallback
        function showSessionFallbackMessage() {
            const fallbackHtml = `
                <div style="background: #e8f4fd; border: 1px solid #2196F3; border-radius: 5px; padding: 10px; margin-bottom: 15px;">
                    <strong>🔄 Using Session Data:</strong> Firebase authentication unavailable. 
                    <a href="Signin.php?redirect=${encodeURIComponent(window.location.pathname)}" style="color: #1976D2; text-decoration: underline;">
                        Click here to restore full authentication
                    </a>
                </div>
            `;
            statusMessage.innerHTML = fallbackHtml;
            statusMessage.style.display = 'block';
        }

        // Function to fetch and display appointments based on user role
        let isFetchingAppointments = false;
        
        // Global variable to store appointments data for access by event handlers
        let appointmentsData = [];
        
        async function fetchAndDisplayAppointments() {
            if (isFetchingAppointments) {
                console.log('=== APPOINTMENT FETCH ALREADY IN PROGRESS, SKIPPING ===');
                return;
            }
            
            console.log('=== STARTING APPOINTMENT FETCH ===');
            console.log('Current timestamp:', new Date().toISOString());
            isFetchingAppointments = true;
            
            try {
                console.log('=== CLEARING UI ELEMENTS ===');
                appointmentsContainer.innerHTML = ''; // Clear previous appointments
                noAppointmentsMessage.style.display = 'none'; // Hide no appointments message
                statusMessage.style.display = 'none'; // Hide any previous status messages
                
                console.log('=== UI CLEARED, PROCEEDING WITH FETCH ===');

                const currentUser = auth.currentUser;
                console.log('=== FETCHING APPOINTMENTS ===');
                console.log('Firebase Current User:', currentUser ? currentUser.uid : 'None');
                console.log('Session User ID:', sessionUserId);
                console.log('Session User Role:', sessionUserRole);
                console.log('Session User Email:', sessionUserEmail);
                
                // Use session data as fallback if Firebase auth fails
                let effectiveUserId = null;
                let effectiveUserRole = 'user';
                
                if (currentUser) {
                    // Firebase authentication is working
                    console.log('✅ Using Firebase authentication');
                    effectiveUserId = currentUser.uid;
                    
                    // Verify Firebase user matches session
                    if (currentUser.uid !== sessionUserId || currentUser.email !== sessionUserEmail) {
                        console.warn('⚠️ Firebase user does not match session');
                        console.log('Firebase UID:', currentUser.uid, 'vs Session UID:', sessionUserId);
                        console.log('Firebase Email:', currentUser.email, 'vs Session Email:', sessionUserEmail);
                        showAuthenticationWarning();
                    }
                } else if (sessionUserId) {
                    // Fallback to session data
                    console.log('⚠️ No Firebase user, using session data');
                    effectiveUserId = sessionUserId;
                    effectiveUserRole = sessionUserRole;
                    showSessionFallbackMessage();
                } else {
                    // No authentication available
                    console.log('❌ No authentication available');
                    displayMessage(statusMessage, 'You must be signed in to view appointments.', 'info');
                    appointmentsPageTitle.textContent = "Appointments"; // Generic title
                    console.log("No authentication available.");
                    return;
                }

                console.log('=== EFFECTIVE AUTHENTICATION ===');
                console.log('Effective User ID:', effectiveUserId);
                console.log('Effective User Role:', effectiveUserRole);

                // Use role from Firebase user doc if available, otherwise use session role
                if (currentUser) {
                    const userDocRef = db.collection("users").doc(currentUser.uid);
                    const userDocSnap = await userDocRef.get();

                    if (userDocSnap.exists && userDocSnap.data().role) {
                        effectiveUserRole = userDocSnap.data().role;
                        console.log("✅ Got role from Firebase:", effectiveUserRole);
                    } else {
                        console.warn("User role not found in Firestore. Using session role:", sessionUserRole);
                        effectiveUserRole = sessionUserRole;
                    }
                } else {
                    console.log("Using session role:", effectiveUserRole);
                }

                userRole = effectiveUserRole;
                console.log("Effective user role:", effectiveUserRole);

                const appointmentsRef = db.collection('appointment_requests');
                let q;

                console.log('=== BUILDING QUERY ===');
                if (effectiveUserRole === 'admin') {
                    // Admin: Fetch all appointments (without ordering to avoid index issues)
                    q = appointmentsRef;
                    appointmentsPageTitle.textContent = "All Appointments (Admin View)";
                    console.log('Admin query: fetching all appointments');
                } else {
                    // Regular User: Fetch only their own appointments (without ordering to avoid index issues)
                    q = appointmentsRef.where('userId', '==', effectiveUserId);
                    appointmentsPageTitle.textContent = "My Appointments";
                    console.log("User query: fetching appointments for userId:", effectiveUserId);
                }

                console.log('=== EXECUTING QUERY ===');
                const actualQuerySnapshot = await q.get();
                console.log("Query execution completed");
                console.log("Query snapshot empty?", actualQuerySnapshot.empty);
                console.log("Number of documents fetched:", actualQuerySnapshot.size);

                if (actualQuerySnapshot.empty) {
                    console.log('=== NO APPOINTMENTS FOUND ===');
                    
                    // Debug: Try fetching ALL appointments to see if any exist
                    console.log('=== DEBUG: Checking if ANY appointments exist ===');
                    try {
                        const debugQuery = appointmentsRef;
                        const debugSnapshot = await debugQuery.get();
                        console.log('Total appointments in database:', debugSnapshot.size);
                        if (!debugSnapshot.empty) {
                            console.log('⚠️ Appointments exist but query returned empty. Possible issues:');
                            console.log('1. userId mismatch');
                            console.log('2. Authentication issues');
                            console.log('3. Firestore security rules');
                            
                            // Log some sample appointment data (without sensitive info)
                            let sampleCount = 0;
                            debugSnapshot.forEach(doc => {
                                if (sampleCount < 3) { // Only log first 3 for debugging
                                    const data = doc.data();
                                    console.log(`Sample appointment ${sampleCount + 1}:`, {
                                        docId: doc.id,
                                        userId: data.userId,
                                        appointmentType: data.appointmentType,
                                        status: data.status,
                                        hasScheduledTimestamp: !!data.scheduledTimestamp,
                                        hasCreatedAt: !!data.createdAt
                                    });
                                    sampleCount++;
                                }
                            });
                        } else {
                            console.log('✓ No appointments exist in database');
                        }
                    } catch (debugError) {
                        console.error('Debug query failed:', debugError);
                    }
                    
                    noAppointmentsMessage.style.display = 'block';
                    console.log("No appointments found. Displaying message.");
                    return;
                }

                console.log('=== PROCESSING APPOINTMENTS ===');

                // Collect all appointments and sort them manually
                appointmentsData = []; // Reset the global variable
                actualQuerySnapshot.forEach(doc => {
                    const appointment = doc.data();
                    const docId = doc.id;
                    
                    console.log("Processing appointment:", docId, appointment);
                    
                    let scheduledDate = null;
                    if (appointment.scheduledTimestamp && typeof appointment.scheduledTimestamp.toDate === 'function') {
                        scheduledDate = appointment.scheduledTimestamp.toDate();
                    } else if (appointment.createdAt && typeof appointment.createdAt.toDate === 'function') {
                        // Fallback to createdAt if scheduledTimestamp is missing
                        scheduledDate = appointment.createdAt.toDate();
                    } else if (appointment.appointmentDate && appointment.appointmentTime) {
                        try {
                            // Handle different time formats
                            let timeStr = appointment.appointmentTime;
                            if (timeStr.includes(' ')) {
                                // Handle "8:00 A.M." format
                                timeStr = timeStr.split(' ')[0];
                            }
                            const fullDateTimeString = `${appointment.appointmentDate}T${timeStr}:00`;
                            scheduledDate = new Date(fullDateTimeString);
                            if (isNaN(scheduledDate.getTime())) {
                                scheduledDate = new Date(); // Use current date as fallback
                            }
                        } catch (e) {
                            console.error("Error parsing fallback date/time:", e);
                            scheduledDate = new Date(); // Use current date as fallback
                        }
                    } else {
                        // Last resort: use current date
                        scheduledDate = new Date();
                    }
                    
                    appointmentsData.push({
                        appointment,
                        docId,
                        scheduledDate
                    });
                });
                
                // Sort appointments by scheduled date (newest first)
                appointmentsData.sort((a, b) => b.scheduledDate.getTime() - a.scheduledDate.getTime());
                
                console.log("Sorted appointments:", appointmentsData.length, "total");

                // Display sorted appointments - fetch user data if missing
                await Promise.all(appointmentsData.map(async ({ appointment, docId, scheduledDate }) => {
                    // If username/userEmail is missing, fetch from users collection
                    if ((!appointment.username || appointment.username === 'N/A') && appointment.userId) {
                        try {
                            const userDoc = await db.collection('users').doc(appointment.userId).get();
                            if (userDoc.exists) {
                                const userData = userDoc.data();
                                appointment.username = userData.displayName || userData.name || userData.username || 'User';
                                appointment.userEmail = userData.email || 'No email';
                            }
                        } catch (error) {
                            console.log('Error fetching user data for appointment:', error);
                        }
                    }
                }));
                
                // Now display all appointments with complete user data
                appointmentsData.forEach(({ appointment, docId, scheduledDate }) => {
                    const formattedDate = scheduledDate ? scheduledDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) : 'N/A';

                    const formattedTime = scheduledDate ? scheduledDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }) : 'N/A';

                    const card = document.createElement('div');
                    card.classList.add('appointment-card');

                    let actionsHtml = '';
                    let cardContent = '';
                    const currentStatus = appointment.status ? appointment.status.toLowerCase() : 'unknown';

                    if (effectiveUserRole === 'admin') {
                        card.classList.add('admin-appointment-card');
                        const isaccepted = currentStatus === '';
                        const isCompleted = currentStatus === 'completed';
                        const isCancelled = currentStatus === 'cancelled';

                        cardContent = `
                            <h3>${appointment.meetingType || appointment.appointmentType || 'N/A'} on ${formattedDate} at ${formattedTime}</h3>
                            <p><strong>Status:</strong> <span class="status ${currentStatus}">${appointment.status || 'Unknown'}</span></p>
                            <p><strong>User:</strong> ${appointment.username || 'N/A'} (${appointment.userEmail || 'N/A'})</p>
                            <p><strong>Child:</strong> ${appointment.childName || 'N/A'}</p>
                        `;

                        if (!isCompleted && !isCancelled) { // Only show actions if not completed or cancelled
                            actionsHtml = `
                                <label class="accept-checkbox-label">
                                    <input type="checkbox" class="accept-appointment-checkbox" data-doc-id="${docId}" ${isaccepted ? 'checked disabled' : ''}>
                                    <span>Accept Appointment</span>
                                </label>
                                <button class="cancel-btn" data-doc-id="${docId}">Cancel Appointment</button>
                            `;
                        } else {
                            actionsHtml = ''; // No actions if completed or cancelled
                        }

                    } else {
                        // Regular user view
                        cardContent = `
                            <h3>${appointment.meetingType || appointment.appointmentType || 'N/A'}</h3>
                            <p><strong>Child:</strong> ${appointment.childName || 'N/A'}</p>
                            <p><strong>Date:</strong> ${formattedDate}</p>
                            <p><strong>Time:</strong> ${formattedTime}</p>
                            <p><strong>Status:</strong> <span class="status ${currentStatus}">${appointment.status || 'Unknown'}</span></p>
                        `;
                        // Only show cancel if pending (not for completed or cancelled)
                        actionsHtml = `
                            ${currentStatus === 'pending' ? `<button class="cancel-btn" data-doc-id="${docId}">Cancel Appointment</button>` : ''}
                        `;
                    }

                    card.innerHTML = cardContent + `<div class="appointment-actions">${actionsHtml}</div>`;
                    appointmentsContainer.appendChild(card);
                });

                // Attach event listeners after all cards are added to the DOM
                if (effectiveUserRole === 'admin') {
                    // Only attach listeners to non-completed appointments
                    document.querySelectorAll('.accept-appointment-checkbox:not(:disabled)').forEach(checkbox => {
                        checkbox.addEventListener('change', handleAcceptAppointment);
                    });
                }
                // Attach listeners to cancel buttons that are actually rendered
                document.querySelectorAll('.cancel-btn').forEach(button => {
                    button.addEventListener('click', handleCancelAppointment);
                });

            } catch (error) {
                console.error('Error fetching appointments:', error);
                displayMessage(statusMessage, 'Error loading appointments: ' + error.message + '. Please ensure your Firestore indexes are set up if suggested by Firebase.', 'error');
            } finally {
                isFetchingAppointments = false;
                console.log('=== APPOINTMENT FETCH COMPLETED ===');
            }
        }

        // Function to handle accepting an appointment (Admin only)
        async function handleAcceptAppointment(event) {
            const docId = event.target.dataset.docId;
            if (!confirm('Are you sure you want to accept this appointment?')) {
                return;
            }

            try {
                // Get appointment data before update for notification
                const appointmentData = appointmentsData.find(item => item.docId === docId);
                let appointmentInfo = null;
                if (appointmentData) {
                    appointmentInfo = appointmentData.appointment;
                }

                const appointmentRef = db.collection('appointment_requests').doc(docId);
                await appointmentRef.update({
                    status: 'accepted',
                    acceptedAt: firebase.firestore.FieldValue.serverTimestamp(),
                    acceptedBy: sessionUserEmail || sessionUserId
                });
                
                displayMessage(statusMessage, 'Appointment accepted successfully!', 'success');
                
                // Send acceptance notification
                if (appointmentInfo) {
                    const appointmentDate = `${appointmentInfo.appointmentDate || appointmentInfo.date} at ${appointmentInfo.appointmentTime || appointmentInfo.time}`;
                    sendAppointmentAcceptedNotification(appointmentInfo.userId, appointmentInfo.childName, appointmentDate, appointmentInfo.meetingType || appointmentInfo.appointmentType);
                    
                    // Also send the original notification for compatibility
                    sendAppointmentNotification('accepted', {
                        userId: appointmentInfo.userId,
                        userName: appointmentInfo.username,
                        userEmail: appointmentInfo.userEmail,
                        appointmentDate: appointmentDate,
                        appointmentType: appointmentInfo.meetingType || appointmentInfo.appointmentType,
                        appointmentCode: appointmentInfo.appointmentCode,
                        childName: appointmentInfo.childName
                    });
                    
                    // AUTOMATICALLY COMPLETE STAGE 7 when appointment is accepted
                    if (appointmentInfo.type === 'child_meeting' && appointmentInfo.userId) {
                        console.log('🎉 Appointment accepted for child meeting - automatically completing Stage 7');
                        
                        // Update Stage 7 completion status
                        db.collection('adoption_progress').doc(appointmentInfo.userId).update({
                            step7_completed: true,
                            step7_completion_date: Date.now(),
                            step7_appointment_accepted: true,
                            step7_auto_completion: true
                        })
                        .then(() => {
                            console.log('✅ Stage 7 automatically marked as complete');
                            
                            // Send Stage 7 completion notification
                            fetch('adoption_message_handler.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    action: 'step_completed',
                                    userId: appointmentInfo.userId,
                                    stepNumber: 7,
                                    activityType: 'step_completed',
                                    activityDetails: `Stage 7 completed - appointment accepted for ${appointmentInfo.childName}`
                                })
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    console.log('✅ Stage 7 completion notification sent');
                                } else {
                                    console.error('❌ Failed to send Stage 7 completion notification:', result.message);
                                }
                            })
                            .catch(error => {
                                console.error('❌ Error sending Stage 7 completion notification:', error);
                            });
                        })
                        .catch(error => {
                            console.error('❌ Error auto-completing Stage 7:', error);
                        });
                    }
                }
                
                fetchAndDisplayAppointments(); // Re-fetch to update UI
            } catch (error) {
                console.error('Error accepting appointment:', error);
                displayMessage(statusMessage, 'Error accepting appointment: ' + error.message, 'error');
            }
        }

        // Function to handle canceling an appointment (deletes for both user and admin)
        async function handleCancelAppointment(event) {
            const docId = event.target.dataset.docId;
            if (!confirm('Are you sure you want to cancel this appointment? This action cannot be undone.')) {
                return;
            }

            try {
                // Get appointment data before deletion for notification
                const appointmentData = appointmentsData.find(item => item.docId === docId);
                let appointmentInfo = null;
                if (appointmentData) {
                    appointmentInfo = appointmentData.appointment;
                }

                // Update appointment status to cancelled instead of deleting
                const appointmentRef = db.collection('appointment_requests').doc(docId);
                await appointmentRef.update({
                    status: 'cancelled',
                    cancelledAt: firebase.firestore.FieldValue.serverTimestamp(),
                    cancelledBy: sessionUserEmail || sessionUserId
                });
                
                displayMessage(statusMessage, 'Appointment cancelled successfully!', 'success');
                
                // Send cancellation notification
                if (appointmentInfo) {
                    const appointmentDate = `${appointmentInfo.appointmentDate || appointmentInfo.date} at ${appointmentInfo.appointmentTime || appointmentInfo.time}`;
                    sendAppointmentNotification('cancelled', {
                        userId: appointmentInfo.userId,
                        userName: appointmentInfo.username,
                        userEmail: appointmentInfo.userEmail,
                        appointmentDate: appointmentDate,
                        appointmentType: appointmentInfo.meetingType || appointmentInfo.appointmentType,
                        appointmentCode: appointmentInfo.appointmentCode,
                        childName: appointmentInfo.childName
                    });
                }
                
                fetchAndDisplayAppointments(); // Re-fetch to update UI
            } catch (error) {
                console.error('Error cancelling appointment:', error);
                displayMessage(statusMessage, 'Error cancelling appointment: ' + error.message, 'error');
            }
        }

        // Enhanced authentication state management
        let authSetupComplete = false;
        let authStateChangedCount = 0;
        
        function setupAuthentication() {
            if (authSetupComplete) {
                console.log('=== AUTHENTICATION ALREADY SETUP, SKIPPING ===');
                return;
            }
            
            console.log('=== SETTING UP APPOINTMENTS AUTHENTICATION ===');
            authSetupComplete = true;
            
            // Monitor Firebase authentication state
            auth.onAuthStateChanged((user) => {
                authStateChangedCount++;
                console.log(`=== FIREBASE AUTH STATE CHANGE #${authStateChangedCount} ===`);
                console.log('Firebase User:', user ? user.uid : 'None');
                console.log('Expected User ID:', sessionUserId);
                console.log('Expected Email:', sessionUserEmail);
                
                // Only skip if this is a duplicate rapid fire call (within 100ms)
                if (authStateChangedCount > 1) {
                    console.log('=== MULTIPLE AUTH STATE CHANGES DETECTED - USING THROTTLING ===');
                    setTimeout(() => {
                        fetchAndDisplayAppointments();
                    }, 200); // Delay to avoid race conditions
                } else {
                    // First call, fetch immediately
                    fetchAndDisplayAppointments();
                }
            });
        }

        // Function to send appointment notifications - MOBILE APP LOGIC
        function sendAppointmentAcceptedNotification(userId, childName, appointmentDate, appointmentType) {
            try {
                console.log('✅ Sending appointment accepted notification to user:', userId);
                
                // Send to super_simple_notifications.php for system notifications
                const notificationData = {
                    action: 'send_adoption_notification',
                    userId: userId,
                    status: 'appointment_accepted',
                    stepNumber: 7,
                    data: {
                        childName: childName,
                        appointmentDate: appointmentDate,
                        appointmentType: appointmentType,
                        activityType: 'appointment_accepted',
                        activityDetails: `Your appointment with ${childName} on ${appointmentDate} has been approved by the social worker`
                    }
                };
                
                fetch('super_simple_notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(notificationData)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        console.log('✅ Appointment accepted notification sent successfully');
                    } else {
                        console.error('❌ Failed to send appointment accepted notification:', result.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Error sending appointment accepted notification:', error);
                });
                
                // ALSO send to chat via Firebase bridge (like step completion messages)
                if (window.firebaseMessagingBridge) {
                    const chatMessage = `✅ Excellent news! Your appointment with ${childName} on ${appointmentDate} has been approved by the social worker. You can now proceed to the next stage of your adoption journey.`;
                    window.firebaseMessagingBridge.sendCustomMessage(userId, chatMessage, 'appointment_accepted')
                        .then(() => {
                            console.log('✅ Appointment accepted message sent to chat via Firebase bridge');
                        })
                        .catch(error => {
                            console.error('❌ Error sending appointment accepted message to chat:', error);
                        });
                } else {
                    console.error('❌ Firebase messaging bridge not available for appointment accepted message');
                }
            } catch (error) {
                console.error('❌ Error in sendAppointmentAcceptedNotification:', error);
            }
        }

        function sendAppointmentNotification(action, appointmentData) {
            if (!appointmentData || !appointmentData.userId) {
                console.log('❌ No appointment data for notification');
                return;
            }

            console.log('📅 MOBILE APP LOGIC: Sending appointment notification:', action, appointmentData);

            // Send notification using MOBILE APP LOGIC (super_simple_notifications.php)
            fetch('super_simple_notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_appointment_notification',
                    userId: appointmentData.userId,
                    status: action === 'accepted' ? 'confirmed' : action, // Convert accepted to confirmed for mobile app compatibility
                    appointmentData: {
                        userName: appointmentData.userName,
                        userEmail: appointmentData.userEmail,
                        appointmentDate: appointmentData.appointmentDate,
                        appointmentType: appointmentData.appointmentType,
                        appointmentCode: appointmentData.appointmentCode,
                        appointmentId: appointmentData.appointmentId
                    }
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    console.log(`✅ MOBILE APP LOGIC: ${action} appointment notification sent successfully`);
                } else {
                    console.log(`❌ MOBILE APP LOGIC: Failed to send ${action} appointment notification:`, result.error);
                }
            })
            .catch(error => {
                console.log(`❌ MOBILE APP LOGIC: Appointment notification error:`, error);
            });
        }

        // Initialize authentication when page loads (only once)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== APPOINTMENTS PAGE DOM LOADED ===');
            setupAuthentication();
            
            // Fallback: If no appointments are loaded after 3 seconds, try loading them directly
            setTimeout(() => {
                const appointmentsContainer = document.getElementById('appointmentsContainer');
                if (appointmentsContainer && appointmentsContainer.children.length === 0) {
                    console.log('=== FALLBACK: No appointments loaded, trying direct fetch ===');
                    isFetchingAppointments = false; // Reset flag in case it's stuck
                    fetchAndDisplayAppointments();
                }
            }, 3000);
        });
    </script>

    <!-- Mobile Menu JavaScript -->
    <script>
        // Mobile dropdown menu functionality
        function toggleMobileMenu() {
            console.log('toggleMobileMenu called');
            const dropdownMenu = document.getElementById('mobileDropdownMenu');
            const hamburger = document.getElementById('hamburger');
            const overlay = document.getElementById('mobileOverlay');
            
            if (dropdownMenu && hamburger && overlay) {
                const isActive = dropdownMenu.classList.contains('active');
                
                if (isActive) {
                    // Close the menu
                    closeMobileMenu();
                } else {
                    // Open the menu
                    dropdownMenu.classList.add('active');
                    hamburger.classList.add('active');
                    overlay.classList.add('active');
                    
                    // Prevent body scroll when menu is open
                    document.body.style.overflow = 'hidden';
                }
            }
        }

        function closeMobileMenu() {
            console.log('closeMobileMenu called');
            const dropdownMenu = document.getElementById('mobileDropdownMenu');
            const hamburger = document.getElementById('hamburger');
            const overlay = document.getElementById('mobileOverlay');
            
            if (dropdownMenu) {
                dropdownMenu.classList.remove('active');
            }
            
            if (hamburger) {
                hamburger.classList.remove('active');
            }
            
            if (overlay) {
                overlay.classList.remove('active');
            }
            
            document.body.style.overflow = '';
        }

        // Mobile detection and complete sidebar removal
        function handleMobileSetup() {
            if (window.innerWidth <= 768) {
                console.log('Mobile view detected, removing sidebar from DOM');
                
                setTimeout(() => {
                    const sidebar = document.querySelector('.sidebar');
                    if (sidebar) {
                        console.log('Removing sidebar from DOM');
                        sidebar.remove(); // Completely remove from DOM
                    }
                    
                    // Add mobile-view class to body
                    document.body.classList.add('mobile-view');
                }, 100);
            }
        }

        // Enhanced click-outside logic
        document.addEventListener('click', function(event) {
            const dropdownMenu = document.getElementById('mobileDropdownMenu');
            const hamburger = document.getElementById('hamburger');
            
            if (dropdownMenu && dropdownMenu.classList.contains('active')) {
                // Check if click is outside menu and hamburger
                if (!dropdownMenu.contains(event.target) && 
                    !hamburger.contains(event.target)) {
                    
                    // Additional check: ignore clicks on interactive elements
                    const isInteractiveElement = event.target.closest('input, button, select, textarea, a, [onclick], [role="button"]');
                    
                    if (!isInteractiveElement || 
                        event.target.closest('.mobile-dropdown-menu a')) {
                        closeMobileMenu();
                    }
                }
            }
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdownMenu = document.getElementById('mobileDropdownMenu');
                if (dropdownMenu && dropdownMenu.classList.contains('active')) {
                    closeMobileMenu();
                }
            }
        });

        // Close menu when navigation links are clicked
        document.addEventListener('DOMContentLoaded', function() {
            const menuLinks = document.querySelectorAll('.mobile-dropdown-menu a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(closeMobileMenu, 100);
                });
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            } else {
                handleMobileSetup();
            }
        });

        // Initialize mobile setup
        document.addEventListener('DOMContentLoaded', function() {
            handleMobileSetup();
        });

        // Make functions globally accessible
        window.toggleMobileMenu = toggleMobileMenu;
        window.closeMobileMenu = closeMobileMenu;
    </script>
</body>

</html>