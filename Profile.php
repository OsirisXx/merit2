<?php
// Include session check to get proper session variables
require_once 'session_check.php';

// Redirect if user is not logged in via PHP session
if (!$isLoggedIn) {
    header('Location: signin.php');
    exit;
}

// TASK 3 & 4: Check if viewing another user's profile (for admins) or own profile
$viewingUserId = isset($_GET['userId']) ? $_GET['userId'] : null;
$isViewingOtherUser = $viewingUserId && $viewingUserId !== $currentUserId;

// Define $isAdmin variable to fix the undefined variable warning
$isAdmin = ($currentUserRole === 'admin');

// For non-admin users, only allow viewing their own profile
if (!$isAdmin && $isViewingOtherUser) {
    header('Location: Profile.php'); // Redirect to own profile
    exit;
}

// Optional: For displaying temporary alert messages set in session (e.g., from form submissions)
if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}

// Optional: Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Add service preference update endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_service_preference') {
    header('Content-Type: application/json');
    
    // Check if user is logged in
    if (!isset($_SESSION['uid'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    
    $newPreference = $_POST['servicePreference'] ?? '';
    
    // Validate preference value
    if (!in_array($newPreference, ['adopt_only', 'donate_only', 'both'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid service preference']);
        exit;
    }
    
    // Update PHP session
    $_SESSION['servicePreference'] = $newPreference;
    
    // Return success (Firestore will be updated by JavaScript)
    echo json_encode(['success' => true, 'message' => 'Session updated successfully']);
    exit;
}
?>

<?php include('navbar.php'); // Include your universal navigation bar ?>
<?php include('chatbot.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Ally</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Basic layout styles from your template */
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
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

        /* Profile Page Specific Styles */
        .profile-card {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 700px; /* Increased max-width for better layout */
            margin: 0 auto;
            text-align: center;
        }

        .profile-card h2 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #333;
            font-size: 2em;
        }

        /* Profile Picture Styles */
        .profile-picture-container {
            width: 180px; /* Adjusted size slightly */
            height: 180px;
            border-radius: 50%;
            overflow: visible; /* Changed to visible to allow edit icon to sit outside */
            margin: 0 auto 20px;
            border: 4px solid #7CB9E8; /* More prominent border */
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .profile-picture {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%; /* Ensure image itself is circular within the container */
        }

        .edit-profile-picture {
            position: absolute;
            bottom: 0; /* Align to the bottom edge of the container */
            right: 0; /* Align to the right edge of the container */
            transform: translate(30%, 30%); /* Push it out by 30% of its own size from bottom/right */
            background-color: #7CB9E8; /* Blue background */
            color: white; /* White icon */
            border-radius: 50%;
            width: 38px; /* Larger icon circle */
            height: 38px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.2s ease, transform 0.2s ease;
            z-index: 10; /* Ensure it's above other elements if needed, but primary here is positioning */
        }

        .edit-profile-picture:hover {
            background-color: #5a9bf5;
            transform: translate(30%, 30%) scale(1.1); /* Slight zoom on hover */
        }

        .edit-icon {
            font-size: 1.4em; /* Larger icon */
            color: white; /* Make sure icon is white */
        }
        
        /* Designed Profile Info Layout */
        .profile-info {
            display: grid;
            grid-template-columns: 1fr; /* Default to single column */
            gap: 20px; /* Space between info blocks */
            margin-bottom: 30px;
            text-align: left;
        }

        .profile-info-item {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px 20px;
            display: flex;
            align-items: center; /* Align icon and text vertically */
            min-height: 60px; /* Ensure consistent height */
            overflow: hidden; /* Prevent content from overflowing */
        }

        .profile-info-item i {
            color: #7CB9E8; /* Icon color */
            font-size: 1.2em;
            margin-right: 15px;
            min-width: 25px; /* Ensure icon has minimum space */
            text-align: center;
        }

        .profile-info-item label {
            font-weight: 600;
            color: #555;
            flex-shrink: 0; /* Prevent label from shrinking */
            margin-right: 10px;
        }

        .profile-info-item span {
            color: #333;
            flex-grow: 1; /* Allow value to take remaining space */
            word-wrap: break-word;
        }

        /* Service Preference Dropdown Styling */
        .service-preference-dropdown {
            flex: 1;
            min-width: 0; /* Allow flex item to shrink below content size */
            max-width: 100%; /* Prevent overflow */
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 14px;
            padding-right: 35px;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            box-sizing: border-box; /* Include padding and border in width calculation */
        }

        .service-preference-dropdown:hover {
            border-color: #7CB9E8;
            background-color: #f8fbff;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(124, 185, 232, 0.15);
        }

        .service-preference-dropdown:focus {
            outline: none;
            border-color: #7CB9E8;
            background-color: #f8fbff;
            box-shadow: 0 0 0 3px rgba(124, 185, 232, 0.1), 0 2px 8px rgba(124, 185, 232, 0.15);
        }

        .service-preference-dropdown:disabled {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .service-preference-dropdown:disabled:hover {
            transform: none;
            box-shadow: none;
            border-color: #e0e0e0;
        }

        .service-preference-dropdown option {
            padding: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            background-color: #fff;
        }

        .service-preference-dropdown option:hover {
            background-color: #f8fbff;
            color: #7CB9E8;
        }

        /* Add some visual feedback for the service preference section */
        .profile-info-item:has(.service-preference-dropdown) {
            background: linear-gradient(135deg, #f8fbff 0%, #fff 100%);
            border: 2px solid #e8f4fd;
            transition: all 0.3s ease;
            flex-wrap: nowrap; /* Prevent wrapping */
        }

        .profile-info-item:has(.service-preference-dropdown):hover {
            border-color: #7CB9E8;
            box-shadow: 0 2px 8px rgba(124, 185, 232, 0.1);
        }

        /* Ensure the service preference container handles the dropdown properly */
        .profile-info-item:has(.service-preference-dropdown) {
            justify-content: flex-start; /* Align dropdown with icon */
        }

        /* Loading and Error Messages */
        #profile-status-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
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

        /* Responsive adjustments for smaller screens */
        @media (min-width: 769px) { /* Two columns for larger screens */
            .profile-info {
                grid-template-columns: 1fr 1fr;
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
            .mobile-menu-toggle {
                display: block;
            }
            .main-content {
                padding: 15px;
                width: 100%;
            }
            .profile-card {
                padding: 20px;
            }
            .profile-card h2 {
                font-size: 1.8em;
            }
            .profile-info-item {
                flex-direction: column; /* Stack label and value vertically on small screens */
                align-items: flex-start;
                padding: 10px 15px;
            }
            .profile-info-item i {
                margin-bottom: 5px;
            }
            .profile-info-item label {
                min-width: unset;
                margin-right: 0;
                margin-bottom: 5px;
            }
            
            /* Service preference dropdown mobile adjustments */
            .service-preference-dropdown {
                width: 100%;
                max-width: 100%;
                margin-top: 8px;
            }
            
            .profile-info-item:has(.service-preference-dropdown) {
                align-items: stretch; /* Allow full width on mobile */
            }
            
            .profile-info-item:has(.service-preference-dropdown) {
                flex-direction: row; /* Keep horizontal on mobile since no label */
                align-items: center;
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
                
                <!-- Matching is now integrated into Stage 7 of the adoption process -->
                
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
            <div class="profile-card">
                <h2><?php echo $isViewingOtherUser ? "User Profile" : "My Profile"; ?></h2>

                <div class="profile-picture-container">
                    <img id="profile-picture-main" src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541" alt="Profile Picture" class="profile-picture">
                    <div class="edit-profile-picture">
                        <label for="upload-profile-picture" class="edit-icon"><i class="fas fa-pencil-alt"></i></label>
                        <input type="file" id="upload-profile-picture" accept="image/*" style="display: none;">
                    </div>
                </div>

                <div id="profile-status-message" class="alert-info">Loading profile...</div>

                <div id="profile-details" class="profile-info" style="display: none;">
                    <div class="profile-info-item">
                        <i class="fas fa-user"></i>
                        <label>First Name:</label>
                        <span id="profile-firstName"></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-user"></i>
                        <label>Middle Name:</label>
                        <span id="profile-middleName"></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-user"></i>
                        <label>Last Name:</label>
                        <span id="profile-lastName"></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-id-badge"></i>
                        <label>Username:</label>
                        <span id="profile-username"></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-envelope"></i>
                        <label>Email:</label>
                        <span id="profile-email"></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-birthday-cake"></i>
                        <label>Birthdate:</label>
                        <span id="profile-birthdate"></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-user-tag"></i>
                        <label>Role:</label>
                        <span id="profile-role"></span>
                    </div>
                    <div class="profile-info-item">
                        <i class="fas fa-heart"></i>
                        <select id="profile-servicePreference" class="service-preference-dropdown" onchange="updateServicePreference()">
                            <option value="adopt_only">Adoption Only</option>
                            <option value="donate_only">Donation Only</option>
                            <option value="both">Both Services</option>
                        </select>
                    </div>
                </div>
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

    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-storage.js"></script>

    <script>
        // Ensure this firebaseConfig is correct for your project
        // It should be the ONLY place firebase.initializeApp is called in your app for best practice.
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.firebasestorage.app", // Corrected storageBucket
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        // Initialize Firebase if not already initialized
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        const auth = firebase.auth();
        const db = firebase.firestore();
        const storage = firebase.storage();
        
        // Make Firebase globally available for navbar notifications and profile picture
        window.firebase = firebase;
        window.db = db;
        window.auth = auth;

        // Get DOM elements for profile details
        const profileStatusMessage = document.getElementById('profile-status-message');
        const profileDetailsDiv = document.getElementById('profile-details');
        // const editProfileBtn = document.getElementById('edit-profile-btn'); // Removed as per request
        const profilePictureImg = document.getElementById('profile-picture-main'); // Profile picture image element
        const uploadProfilePictureInput = document.getElementById('upload-profile-picture'); // File input

        const profileElements = {
            firstName: document.getElementById('profile-firstName'),
            middleName: document.getElementById('profile-middleName'),
            lastName: document.getElementById('profile-lastName'),
            username: document.getElementById('profile-username'),
            email: document.getElementById('profile-email'),
            // New elements for Birthdate and Role
            birthdate: document.getElementById('profile-birthdate'),
            role: document.getElementById('profile-role'),
            servicePreference: document.getElementById('profile-servicePreference')
        };

        // Function to update profile picture in Firebase Storage and Firestore
        const uploadImage = async (file) => {
            displayStatusMessage('Uploading profile picture...', 'info');

            try {
                const user = auth.currentUser;
                if (!user) {
                    throw new Error('User not authenticated.');
                }

                // Storage path: profile_images/{userId}/profile.jpg (using .jpg for consistency)
                const storageRef = storage.ref(`profile_images/${user.uid}/profile.jpg`); 
                await storageRef.put(file);
                const downloadURL = await storageRef.getDownloadURL();

                // Update user document in Firestore with the new profile picture URL
                await db.collection("users").doc(user.uid).update({
                    profilePictureURL: downloadURL
                });

                // Update the displayed image on the page and the Firebase Auth profile
                profilePictureImg.src = downloadURL;
                
                // Also update the navbar profile picture
                const navbarProfileImg = document.getElementById('profile-picture');
                if (navbarProfileImg) {
                    navbarProfileImg.src = downloadURL;
                }
                
                await user.updateProfile({ photoURL: downloadURL }); // Update Auth profile too

                displayStatusMessage('Profile picture updated successfully!', 'success');
                setTimeout(() => {
                    profileStatusMessage.style.display = 'none';
                }, 3000);

            } catch (error) {
                console.error('Error uploading profile picture:', error);
                let errorMessage = 'Error updating profile picture. Please try again.';
                if (error.code) {
                    if (error.code === 'storage/unauthorized') {
                        errorMessage = 'Upload failed: You do not have permission. Check Firebase Storage rules for /profile_images.';
                    } else if (error.code === 'storage/canceled') {
                        errorMessage = 'Upload cancelled.';
                    } else if (error.code === 'storage/quota-exceeded') {
                        errorMessage = 'Upload failed: Storage quota exceeded.';
                    } else {
                        errorMessage = `Error: ${error.message}`; // Fallback to Firebase's message
                    }
                }
                displayStatusMessage(errorMessage, 'error');
            }
        };

        // Helper function for profile status messages
        function displayStatusMessage(message, type) {
            profileStatusMessage.textContent = message;
            profileStatusMessage.className = `alert-message alert-${type}`;
            profileStatusMessage.style.display = 'block';
        }


        // Listen for file selection on the hidden input
        uploadProfilePictureInput.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            if (file) {
                // Optional: Basic client-side validation for file type and size
                if (!file.type.startsWith('image/')) {
                    displayStatusMessage('Please select an image file (e.g., JPG, PNG).', 'error');
                    return;
                }
                if (file.size > 2 * 1024 * 1024) { // 2MB limit
                    displayStatusMessage('File size exceeds 2MB limit.', 'error');
                    return;
                }
                uploadImage(file);
            }
        });

        // TASK 3: Get userId from URL parameter if viewing another user's profile
        const urlParams = new URLSearchParams(window.location.search);
        const viewingUserId = urlParams.get('userId');
        const isViewingOtherUser = viewingUserId && viewingUserId !== '<?php echo $currentUserId; ?>';
        
        // Hide profile picture upload for other users
        if (isViewingOtherUser) {
            uploadProfilePictureInput.style.display = 'none';
            document.querySelector('.edit-profile-picture').style.display = 'none';
        }

        // Listen for Firebase Auth state changes
        auth.onAuthStateChanged(async (user) => {
            if (user) {
                const targetUserId = isViewingOtherUser ? viewingUserId : user.uid;
                const profileOwner = isViewingOtherUser ? 'user' : 'your';
                
                displayStatusMessage(`Fetching ${profileOwner} profile data...`, 'info');
                profileDetailsDiv.style.display = 'none';

                // For viewing other users, don't use Firebase Auth profile picture
                let currentProfilePhotoURL = 'https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541';
                if (!isViewingOtherUser) {
                    currentProfilePhotoURL = user.photoURL || currentProfilePhotoURL;
                }
                profilePictureImg.src = currentProfilePhotoURL;

                try {
                    // Fetch user data from Firestore - use targetUserId instead of user.uid
                    const userDoc = await db.collection("users").doc(targetUserId).get();

                    if (userDoc.exists) {
                        const userData = userDoc.data();
                        console.log("User data from Firestore:", userData); // Debugging

                        profileElements.firstName.textContent = userData.firstName || 'N/A';
                        profileElements.middleName.textContent = userData.middleName || 'N/A';
                        profileElements.lastName.textContent = userData.lastName || 'N/A';
                        profileElements.username.textContent = userData.username || 'N/A';
                        profileElements.email.textContent = userData.email || user.email; // Fallback to Auth email
                        profileElements.birthdate.textContent = userData.birthdate || 'N/A'; // Display Birthdate
                        profileElements.role.textContent = userData.role || 'N/A'; // Display Role
                        
                        // Set service preference dropdown value
                        const servicePreference = userData.servicePreference || 'both'; // Default to 'both' if not set
                        profileElements.servicePreference.value = servicePreference;
                        
                        // Disable dropdown if viewing another user's profile
                        if (isViewingOtherUser) {
                            profileElements.servicePreference.disabled = true;
                        }

                        // Override profile picture if URL exists in Firestore (most up-to-date)
                        if (userData.profilePictureURL) {
                            profilePictureImg.src = userData.profilePictureURL;
                            
                            // Also update the navbar profile picture
                            const navbarProfileImg = document.getElementById('profile-picture');
                            if (navbarProfileImg) {
                                navbarProfileImg.src = userData.profilePictureURL;
                            }
                            
                            // Only update Firebase Auth profile if viewing own profile
                            if (!isViewingOtherUser && user.photoURL !== userData.profilePictureURL) {
                                await user.updateProfile({ photoURL: userData.profilePictureURL });
                            }
                        }

                        profileDetailsDiv.style.display = 'grid'; // Changed to 'grid' for new layout
                        // editProfileBtn.style.display = 'inline-block'; // Removed as per request
                        profileStatusMessage.style.display = 'none'; // Hide loading message
                    } else {
                        displayStatusMessage('Profile data not found in Firestore. Please ensure your account was fully registered.', 'error');
                    }
                } catch (error) {
                    console.error("Error fetching profile from Firestore:", error);
                    displayStatusMessage('Error loading profile: ' + error.message, 'error');
                }
            } else {
                // User is not logged in
                displayStatusMessage('You must be logged in to view your profile. Please sign in.', 'error');
                profileDetailsDiv.style.display = 'none';
                // editProfileBtn.style.display = 'none'; // Removed as per request
                // Optionally redirect to login page
                // window.location.href = "signin.php";
            }
        });

        // The "Edit Profile" button event listener and modal logic have been removed.
        // If you later decide to re-add editing, we'll implement a proper modal form.

        // Function to update service preference
        async function updateServicePreference() {
            const user = auth.currentUser;
            if (!user) {
                displayStatusMessage('You must be logged in to update your service preference.', 'error');
                return;
            }

            // Don't allow updating if viewing another user's profile
            if (isViewingOtherUser) {
                displayStatusMessage('You cannot update another user\'s service preference.', 'error');
                return;
            }

            const newPreference = profileElements.servicePreference.value;
            
            displayStatusMessage('Updating service preference...', 'info');

            try {
                // Update both Firestore and PHP session simultaneously
                const [firestoreUpdate, sessionUpdate] = await Promise.all([
                    // Update Firestore
                    db.collection("users").doc(user.uid).update({
                        servicePreference: newPreference
                    }),
                    // Update PHP session
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_service_preference&servicePreference=${encodeURIComponent(newPreference)}`
                    }).then(response => response.json())
                ]);

                // Check if session update was successful
                if (!sessionUpdate.success) {
                    throw new Error('Session update failed: ' + sessionUpdate.message);
                }

                displayStatusMessage('Service preference updated successfully! Changes will be reflected in your navigation.', 'success');
                
                // Send admin notification about profile update
                fetch('adoption_message_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'profile_updated',
                        userId: user.uid,
                        activityType: 'profile_updated',
                        activityDetails: `Service preference changed to: ${newPreference}`
                    })
                }).catch(error => {
                    console.error('Error sending profile update message:', error);
                });
                
                // Hide the success message after 4 seconds
                setTimeout(() => {
                    profileStatusMessage.style.display = 'none';
                }, 4000);

                // Refresh the page after a short delay to update navigation
                setTimeout(() => {
                    window.location.reload();
                }, 2000);

            } catch (error) {
                console.error('Error updating service preference:', error);
                displayStatusMessage('Error updating service preference: ' + error.message, 'error');
            }
        }
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