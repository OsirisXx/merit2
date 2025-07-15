<?php
// Start the session if it hasn't been started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if user is not logged in via PHP session
if (!isset($_SESSION['username'])) {
    header('Location: signin.php');
    exit;
}

// Optional: Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<?php include('navbar.php'); // Include your universal navigation bar 
?>
<?php include('chatbot.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History - Ally</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            display: flex;
            flex-grow: 1;
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
            background-color: #f5f7fa;
            /* Slightly different from card background */
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Center content horizontally */
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            width: 100%;
            /* Ensure heading takes full width for centering */
        }

        .content-section {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 100%;
            max-width: 1200px;
            /* Max width for content consistency */
            box-sizing: border-box;
            text-align: center;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            /* Responsive grid */
            gap: 25px;
            /* Spacing between cards */
            padding: 20px;
            justify-content: center;
            /* Center cards in the grid */
        }

        .card {
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            padding: 25px;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            /* Indicates it's clickable */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            /* Ensure consistent card height */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.12);
        }

        .card-icon {
            width: 60px;
            /* Size for flaticon */
            height: 60px;
            background-color: #e0f2f7;
            /* Light background for the icon area */
            border-radius: 50%;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            /* Placeholder icon size */
            color: #7CB9E8;
            /* Placeholder icon color */
            border: 1px solid #cceeff;
        }

        .card-icon img {
            width: 40px;
            /* Adjust icon size */
            height: 40px;
            /* Adjust icon size */
        }

        .card-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .card-message {
            font-size: 1em;
            color: #666;
            margin-bottom: 0;
        }

        .alert-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            display: none;
            text-align: center;
            width: 100%;
            max-width: 800px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* --- Modal Styles (Common) --- */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.6);
            /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 90%;
            /* Responsive width */
            max-width: 700px;
            /* Max width for larger screens */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            animation-name: animatetop;
            animation-duration: 0.4s;
            display: flex;
            /* Flex container for modal content */
            flex-direction: column;
            max-height: 90vh;
            /* Max height to allow scrolling within modal */
        }

        @keyframes animatetop {
            from {
                top: -300px;
                opacity: 0
            }

            to {
                top: 0;
                opacity: 1
            }
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 15px;
            top: 10px;
            cursor: pointer;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
        }

        .modal-header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
            position: relative;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.8em;
        }

        /* --- Specific Modal Content Styling --- */
        #donationHistoryModal .modal-content,
        #matchingHistoryModal .modal-content,
        #adoptionProgressModal .modal-content,
        #appointmentHistoryModal .modal-content {
            /* Added for appointment modal */
            max-width: 500px;
            padding: 25px;
        }

        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .filter-section label {
            font-weight: 600;
            color: #555;
            margin-right: 10px;
        }

        .filter-section select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            flex-grow: 1;
            max-width: 60%;
        }

        #donation-list,
        #adoption-list,
        #matching-list,
        #appointment-list {
            /* Combined for similar styling */
            flex-grow: 1;
            overflow-y: auto;
            max-height: 50vh;
            padding-right: 5px;
        }

        .donation-item,
        .adoption-item,
        .matching-item,
        .appointment-item {
            /* Combined for similar styling */
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            text-align: left;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .donation-item h4,
        .adoption-item h4,
        .matching-item h4,
        .appointment-item h4 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #2a6496;
            /* Darker blue for type/title */
            font-size: 1.1em;
        }

        .donation-item p,
        .adoption-item p,
        .matching-item p,
        .appointment-item p {
            margin: 3px 0;
            font-size: 0.95em;
            color: #444;
        }

        .donation-item .status-badge,
        .adoption-item .status-badge,
        .matching-item .status-badge,
        .appointment-item .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .status-approved,
        .status-completed,
        .status-accepted {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected,
        .status-in-progress,
        .status-pending,
        .status-declined,
        .status-cancelled {
            /* Added .status-cancelled */
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .donation-item .donation-date,
        .adoption-item .adoption-date,
        .matching-item .matching-date,
        .appointment-item .appointment-date {
            /* Combined for similar styling */
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.85em;
            color: #777;
        }

        #donation-list p.no-records,
        #adoption-list p.no-records,
        #matching-list p.no-records,
        #appointment-list p.no-records {
            text-align: center;
            color: #777;
            padding: 20px;
        }

        /* General modal message area */
        .modal-message {
            text-align: center;
            padding: 10px;
            color: #555;
            font-size: 0.9em;
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

        /* Responsive adjustments */
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

            .cards-container {
                grid-template-columns: 1fr;
                /* Stack cards on small screens */
                padding: 10px;
            }

            .card {
                padding: 20px;
            }

            .modal-content {
                width: 95%;
                padding: 15px;
            }

            .filter-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-section select {
                width: 100%;
                max-width: 100%;
                margin-top: 10px;
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
                
                <?php if ($isAdmin || $currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
                <!-- Matching is now integrated into Stage 7 of the adoption process -->
                <?php endif; ?>
                
                <?php if ($isAdmin): ?>
                <li><a href="ChildStatus.php">👶 Child Status Information</a></li>
                <li><a href="admin.php">⚙️ Admin History Dashboard</a></li>
                <li><a href="history.php">📜 History</a></li>
                <?php else: ?>
                    <li><a href="user_history.php">📜 My History</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main class="main-content">
            <h2 id="page-title">My History</h2>

            <div id="admin-status-message" class="alert-message alert-error" style="display: none;"></div>

            <div class="content-section">
                <div class="cards-container">
                    <?php if ($isAdmin || $currentServicePreference === 'donate_only' || $currentServicePreference === 'both'): ?>
                    <div class="card" id="donation-card">
                        <div class="card-icon">
                            <img src="icons/donate.png" alt="Donation Icon">
                        </div>
                        <div class="card-title">Donation</div>
                        <div class="card-message">
                            Currently: <span id="donation-count">0</span> donations made
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($isAdmin || $currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
                    <div class="card" id="adoption-progress-card">
                        <div class="card-icon">
                            <img src="icons/hand.png" alt="Adoption Progress Icon">
                        </div>
                        <div class="card-title">Adoption Progress</div>
                        <div class="card-message">
                            Currently: <span id="adoption-count">0</span> adoptions completed
                        </div>
                    </div>

                    <div class="card" id="matching-card">
                        <div class="card-icon">
                            <img src="icons/puzzle.png" alt="Matching Icon">
                        </div>
                        <div class="card-title">Matching</div>
                        <div class="card-message">
                            Currently: <span id="matching-count">0</span> matches made
                        </div>
                    </div>

                    <div class="card" id="appointment-card">
                        <div class="card-icon">
                            <img src="icons/schedule.png" alt="Appointment Icon">
                        </div>
                        <div class="card-title">Appointment</div>
                        <div class="card-message">
                            Currently: <span id="appointment-count">0</span> appointments attended
                        </div>
                    </div>
                    <?php endif; ?>
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
            <li><a href="admin.php">⚙️ Admin History Dashboard</a></li>
            <li><a href="history.php">📜 History</a></li>
            <?php else: ?>
            <li><a href="user_history.php">📜 My History</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>

    <div id="donationHistoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Donation History</h3>
                <span class="close-button">&times;</span>
            </div>
            <div class="filter-section">
                <label for="donationFilter">Filter by status:</label>
                <select id="donationFilter">
                    <option value="All Donations">All Donations</option>
                    <option value="Approved">Approved Donations</option>
                    <option value="Rejected">Rejected Donations</option>
                </select>
            </div>
            <div id="donation-list">
                <p class="no-records" style="display: none;">No donations found for the selected filter.</p>
            </div>
            <div class="modal-message" id="donation-modal-message"></div>
        </div>
    </div>

    <div id="adoptionProgressModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Adoption Progress History</h3>
                <span class="close-button">&times;</span>
            </div>
            <div class="filter-section">
                <label for="adoptionFilter">Filter by status:</label>
                <select id="adoptionFilter">
                    <option value="All Adoptions">Completed Adoptions Only</option>
                </select>
            </div>
            <div id="adoption-list">
                <p class="no-records" style="display: none;">No adoption records found for the selected filter.</p>
            </div>
            <div class="modal-message" id="adoption-modal-message"></div>
        </div>
    </div>

    <div id="matchingHistoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Matching History</h3>
                <span class="close-button">&times;</span>
            </div>
            <div class="filter-section">
                <label for="matchingFilter">Filter by status:</label>
                <select id="matchingFilter">
                    <option value="All Matches">Previous Matches Only</option>
                </select>
            </div>
            <div id="matching-list">
                <p class="no-records" style="display: none;">No matching records found for the selected filter.</p>
            </div>
            <div class="modal-message" id="matching-modal-message"></div>
        </div>
    </div>

    <div id="appointmentHistoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Appointment History</h3>
                <span class="close-button">&times;</span>
            </div>
            <div class="filter-section">
                <label for="appointmentFilter">Filter by status:</label>
                <select id="appointmentFilter">
                    <option value="All Appointments">All Appointments</option>
                    <option value="Completed">Completed Appointments</option>
                    <option value="Cancelled">Cancelled Appointments</option>
                </select>
            </div>
            <div id="appointment-list">
                <p class="no-records" style="display: none;">No appointment records found for the selected filter.</p>
            </div>
            <div class="modal-message" id="appointment-modal-message"></div>
        </div>
    </div>


    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-firestore-compat.js"></script>

    <script>
        // Your Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        const db = firebase.firestore();

        // Declare DOM elements *inside* DOMContentLoaded or retrieve them when needed
        let adminStatusMessage;
        let donationCard, adoptionProgressCard, matchingCard, appointmentCard;
        let donationHistoryModal, adoptionProgressModal, matchingHistoryModal, appointmentHistoryModal;
        let donationFilter, donationListDiv, donationNoRecordsMsg;
        let adoptionFilter, adoptionListDiv, adoptionNoRecordsMsg;
        let matchingFilter, matchingListDiv, matchingNoRecordsMsg;
        let appointmentFilter, appointmentListDiv, appointmentNoRecordsMsg;


        // Helper function to display admin status messages
        function displayAdminStatusMessage(message, type) {
            // Ensure the element exists before trying to access its properties
            if (!adminStatusMessage) {
                adminStatusMessage = document.getElementById('admin-status-message');
            }
            if (adminStatusMessage) { // Check again in case it's still null
                adminStatusMessage.textContent = message;
                adminStatusMessage.className = `alert-message alert-${type}`;
                adminStatusMessage.style.display = 'block';
                setTimeout(() => {
                    adminStatusMessage.style.display = 'none';
                }, 3000); // Hide after 3 seconds
            } else {
                console.warn('Admin status message element not found.');
            }
        }

        // Helper function to display modal messages
        function displayModalMessage(modalId, message, type) {
            const modalMessageDiv = document.querySelector(`#${modalId} .modal-message`);
            if (modalMessageDiv) {
                modalMessageDiv.textContent = message;
                modalMessageDiv.className = `modal-message alert-${type}`; // Reuse alert styles if desired, or make specific modal-message styles
                modalMessageDiv.style.display = 'block';
                setTimeout(() => {
                    modalMessageDiv.style.display = 'none';
                }, 3000);
            }
        }

        // Function to open a specific modal
        function openModal(modalElement) {
            modalElement.style.display = 'flex';
        }

        // Function to close all modals
        function closeAllModals() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        }



        // --- Format Timestamp for Display ---
        function formatTimestamp(timestamp) {
            if (!timestamp) return 'N/A';
            let date;
            // Check if it's a Firestore Timestamp object
            if (typeof timestamp.toDate === 'function') {
                date = timestamp.toDate();
            } else if (typeof timestamp === 'number') {
                // Assume it's a Unix timestamp in milliseconds if it's a number
                date = new Date(timestamp);
            } else if (typeof timestamp === 'string') {
                // Attempt to parse string dates, might need more robust parsing for varied formats
                date = new Date(timestamp);
            } else {
                return 'Invalid Date';
            }

            // Check if date is valid
            if (isNaN(date.getTime())) {
                return 'Invalid Date';
            }

            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }) + ' ' + date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // --- Fetch and Display Donation History (user-specific) ---
        async function fetchAndDisplayDonations(filterType = 'All Donations') {
            displayModalMessage('donationHistoryModal', 'Loading donation history...', 'info');
            donationListDiv.innerHTML = ''; // Clear previous content
            donationNoRecordsMsg.style.display = 'none';

            // Get current user
            const currentUser = auth.currentUser;
            if (!currentUser) {
                donationNoRecordsMsg.style.display = 'block';
                displayModalMessage('donationHistoryModal', 'You must be logged in to view your history.', 'error');
                return;
            }

            // Multiple donation collections to check (user-specific)
            const donationCollections = ['donations', 'toysdonation', 'clothesdonation', 'fooddonation', 'educationdonation'];
            let allDonations = [];

            try {
                for (const collection of donationCollections) {
                    const snapshot = await db.collection(collection)
                        .where('userId', '==', currentUser.uid)
                        .get();
                    
                    snapshot.forEach(doc => {
                        const donation = doc.data();
                        
                        // Only show approved and rejected donations (matching mobile app logic)
                        const status = donation.status?.toLowerCase();
                        if (!['approved', 'rejected'].includes(status)) {
                            return;
                        }

                        // Determine donation type based on collection
                        const donationType = (() => {
                            switch (collection) {
                                case 'toysdonation': return 'Toys';
                                case 'clothesdonation': return 'Clothes';
                                case 'fooddonation': return 'Food';
                                case 'educationdonation': return 'Education';
                                case 'donations': return donation.donationType || 'Money';
                                default: return collection.charAt(0).toUpperCase() + collection.slice(1);
                            }
                        })();

                        // Apply filter
                        if (filterType !== 'All Donations') {
                            if (filterType === 'Approved' && status !== 'approved') return;
                            if (filterType === 'Rejected' && status !== 'rejected') return;
                            if (filterType !== 'Approved' && filterType !== 'Rejected' && donationType !== filterType) return;
                        }

                        allDonations.push({
                            ...donation,
                            donationType,
                            id: doc.id,
                            collection
                        });
                    });
                }

                // Sort by timestamp
                allDonations.sort((a, b) => {
                    const aTime = a.timestamp?.toDate?.() || new Date(a.timestamp || 0);
                    const bTime = b.timestamp?.toDate?.() || new Date(b.timestamp || 0);
                    return bTime - aTime;
                });

                if (allDonations.length === 0) {
                    donationNoRecordsMsg.style.display = 'block';
                    displayModalMessage('donationHistoryModal', 'No completed donations found for this filter.', 'info');
                    return;
                }

                allDonations.forEach(donation => {
                    const item = document.createElement('div');
                    item.className = 'donation-item';

                    const statusClass = donation.status?.toLowerCase() === 'approved' ? 'status-approved' : 'status-rejected';
                    const amount = typeof donation.amount === 'string' ? donation.amount : (donation.amount || 0).toLocaleString();

                    item.innerHTML = `
                        <h4>${donation.donationType} Donation - ${donation.status?.toUpperCase()}</h4>
                        <p><strong>Donor:</strong> ${donation.username || 'N/A'}</p>
                        <p><strong>Amount:</strong> ${amount}</p>
                        <span class="status-badge ${statusClass}">${donation.status?.toUpperCase() || 'N/A'}</span>
                        <span class="donation-date">${formatTimestamp(donation.timestamp)}</span>
                    `;
                    donationListDiv.appendChild(item);
                });
                
                displayModalMessage('donationHistoryModal', 'Donation history loaded successfully.', 'success');
            } catch (error) {
                console.error('Error fetching donations:', error);
                displayModalMessage('donationHistoryModal', 'Error loading donations: ' + error.message, 'error');
            }
        }

        // --- Get all completed adoptions from a document (including versioned structure) ---
        function getAllCompletedAdoptions(data, docId) {
            const completedAdoptions = [];
            
            // Check for versioned structure first
            if (data.adoptions) {
                // New versioned structure - process all completed adoptions
                for (const [adoptionKey, adoptionData] of Object.entries(data.adoptions)) {
                    const adoption = adoptionData;
                    const adoptProgress = adoption.adopt_progress;
                    
                    if (adoptProgress) {
                        let completedSteps = 0;
                        for (let i = 1; i <= 10; i++) {
                            if (adoptProgress[`step${i}`] === 'complete') {
                                completedSteps++;
                            }
                        }
                        
                        // Consider adoption completed if:
                        // 1. Status is explicitly 'completed', OR
                        // 2. All 10 steps are marked as 'complete'
                        if (adoption.status === 'completed' || completedSteps === 10) {
                            // Use actual completedAt if available, otherwise use lastUpdated or current timestamp
                            let completionTimestamp = adoption.completedAt;
                            if (!completionTimestamp && completedSteps === 10) {
                                // Fallback to lastUpdated, startedAt, or current time
                                completionTimestamp = data.lastUpdated || adoption.startedAt || new Date();
                            }
                            
                            completedAdoptions.push({
                                id: `${docId}_adoption_${adoptionKey}`,
                                adoptionKey: adoptionKey,
                                title: `Completed Adoption #${adoptionKey}`,
                                completedAt: completionTimestamp,
                                startedAt: adoption.startedAt,
                                details: adoption,
                                username: data.username,
                                userId: data.userId || docId
                            });
                        }
                    }
                }
            } else {
                // Old structure - check if all 10 steps are complete
                const adoptProgress = data.adopt_progress;
                if (adoptProgress) {
                    let completedSteps = 0;
                    for (let i = 1; i <= 10; i++) {
                        if (adoptProgress[`step${i}`] === 'complete') {
                            completedSteps++;
                        }
                    }
                    
                    // Consider adoption completed if:
                    // 1. Status is explicitly 'completed', OR
                    // 2. All 10 steps are marked as 'complete'
                    if (data.currentStatus === 'completed' || completedSteps === 10) {
                        // Use actual completedAt if available, otherwise use lastUpdated or current timestamp
                        let completionTimestamp = data.completedAt;
                        if (!completionTimestamp && completedSteps === 10) {
                            // Fallback to lastUpdated, timestamp, or current time
                            completionTimestamp = data.lastUpdated || data.timestamp || new Date();
                        }
                        
                        completedAdoptions.push({
                            id: docId,
                            adoptionKey: '1',
                            title: 'Completed Adoption Process',
                            completedAt: completionTimestamp,
                            startedAt: data.startedAt,
                            details: data,
                            username: data.username,
                            userId: data.userId || docId
                        });
                    }
                }
            }
            
            return completedAdoptions;
        }

        // --- Fetch and Display Adoption Progress History (user-specific) ---
        async function fetchAndDisplayAdoptionProgress(filterType = 'All Adoptions') {
            displayModalMessage('adoptionProgressModal', 'Loading adoption progress history...', 'info');
            adoptionListDiv.innerHTML = ''; // Clear previous content
            adoptionNoRecordsMsg.style.display = 'none';

            // Get current user
            const currentUser = auth.currentUser;
            if (!currentUser) {
                adoptionNoRecordsMsg.style.display = 'block';
                displayModalMessage('adoptionProgressModal', 'You must be logged in to view your history.', 'error');
                return;
            }

            try {
                const doc = await db.collection('adoption_progress').doc(currentUser.uid).get();
                let allCompletedAdoptions = [];

                if (doc.exists) {
                    const data = doc.data();
                    const completedAdoptions = getAllCompletedAdoptions(data, doc.id);
                    
                    // Add all completed adoptions from this user's document
                    allCompletedAdoptions.push(...completedAdoptions);
                }

                // Sort by completion date (most recent first)
                allCompletedAdoptions.sort((a, b) => {
                    const aTime = a.completedAt?.toDate?.() || new Date(a.completedAt || 0);
                    const bTime = b.completedAt?.toDate?.() || new Date(b.completedAt || 0);
                    return bTime - aTime;
                });

                if (allCompletedAdoptions.length === 0) {
                    adoptionNoRecordsMsg.style.display = 'block';
                    displayModalMessage('adoptionProgressModal', 'No completed adoptions found.', 'info');
                    return;
                }

                allCompletedAdoptions.forEach(adoption => {
                    const item = document.createElement('div');
                    item.className = 'adoption-item';

                    const completedDate = adoption.completedAt ? formatTimestamp(adoption.completedAt) : 'N/A';

                    item.innerHTML = `
                        <h4>${adoption.title} - All 10 steps completed successfully</h4>
                        <p><strong>User:</strong> ${adoption.username || 'N/A'}</p>
                        <p><strong>Completed:</strong> ${completedDate}</p>
                        <span class="status-badge status-completed">COMPLETED</span>
                        <span class="adoption-date">${completedDate}</span>
                    `;
                    adoptionListDiv.appendChild(item);
                });
                
                displayModalMessage('adoptionProgressModal', `${allCompletedAdoptions.length} completed adoption(s) loaded successfully.`, 'success');
            } catch (error) {
                console.error('Error fetching adoption progress:', error);
                displayModalMessage('adoptionProgressModal', 'Error loading adoption progress: ' + error.message, 'error');
            }
        }

        // --- Fetch and Display Matching History (user-specific) ---
        async function fetchAndDisplayMatchingHistory(filterType = 'All Matches') {
            displayModalMessage('matchingHistoryModal', 'Loading matching history...', 'info');
            matchingListDiv.innerHTML = ''; // Clear previous content
            matchingNoRecordsMsg.style.display = 'none';

            // Get current user
            const currentUser = auth.currentUser;
            if (!currentUser) {
                matchingNoRecordsMsg.style.display = 'block';
                displayModalMessage('matchingHistoryModal', 'You must be logged in to view your history.', 'error');
                return;
            }

            let allMatches = [];

            try {
                // 1. Load successful matches (accepted/matched/completed) from matching_preferences for current user
                const successfulSnapshot = await db.collection('matching_preferences')
                    .where('senderId', '==', currentUser.uid)
                    .get();

                successfulSnapshot.forEach(doc => {
                    const match = doc.data();
                    const status = match.status?.toLowerCase();
                    
                    // Only show matches with successful status (matching mobile app)
                    if (['matched', 'accepted', 'completed'].includes(status)) {
                        const childName = match.matchedChildDetails?.name || 'Unknown Child';
                        
                        // Apply filter
                        if (filterType !== 'All Matches' && filterType.toLowerCase() !== 'previous match') {
                            return;
                        }

                        const timestamp = match.actionTimestamp ? new Date(match.actionTimestamp) : new Date(match.requestTimestamp || 0);

                        allMatches.push({
                            id: doc.id,
                            type: 'successful',
                            title: `Match with ${childName} - Previous Match`,
                            description: `Matched with child: ${childName} | Status: Previous Match`,
                            username: match.senderUsername || 'Unknown User',
                            userId: match.senderId || 'N/A',
                            status: 'Previous Match',
                            timestamp: timestamp,
                            details: match
                        });
                    }
                });

                // 2. Load previous match history from matching_history collection for current user
                const historySnapshot = await db.collection('matching_history')
                    .where('type', '==', 'previous_match')
                    .where('senderId', '==', currentUser.uid)
                    .get();

                historySnapshot.forEach(doc => {
                    const historyData = doc.data();
                    
                    // Apply filter
                    if (filterType !== 'All Matches' && filterType.toLowerCase() !== 'previous match') {
                        return;
                    }

                    const previousMatch = historyData.previousMatch;
                    const childDetails = previousMatch?.childDetails;
                    const childName = childDetails?.name || 'Unknown Child';
                    
                    const timestamp = new Date(historyData.createdAt || 0);

                    allMatches.push({
                        id: doc.id,
                        type: 'previous',
                        title: `Match with ${childName} - Previous Match`,
                        description: `Previous match with child: ${childName} | Status: Previous Match`,
                        username: historyData.senderUsername || 'Unknown User',
                        userId: historyData.senderId || 'N/A',
                        status: 'Previous Match',
                        timestamp: timestamp,
                        details: historyData
                    });
                });

                // Sort by timestamp (most recent first)
                allMatches.sort((a, b) => b.timestamp - a.timestamp);

                if (allMatches.length === 0) {
                    matchingNoRecordsMsg.style.display = 'block';
                    displayModalMessage('matchingHistoryModal', 'No completed matches found for the selected filter.', 'info');
                    return;
                }

                allMatches.forEach(match => {
                    const item = document.createElement('div');
                    item.className = 'matching-item';

                    item.innerHTML = `
                        <h4>${match.title}</h4>
                        <p><strong>User:</strong> ${match.username}</p>
                        <p>${match.description}</p>
                        <span class="status-badge status-accepted">${match.status.toUpperCase()}</span>
                        <span class="matching-date">${formatTimestamp(match.timestamp)}</span>
                    `;
                    matchingListDiv.appendChild(item);
                });

                displayModalMessage('matchingHistoryModal', `${allMatches.length} matching record(s) loaded successfully.`, 'success');
            } catch (error) {
                console.error('Error fetching matching history:', error);
                displayModalMessage('matchingHistoryModal', 'Error loading matching history: ' + error.message, 'error');
            }
        }

        // --- Fetch and Display Appointment History (user-specific) ---
        async function fetchAndDisplayAppointmentHistory(filterType = 'All Appointments') {
            displayModalMessage('appointmentHistoryModal', 'Loading appointment history...', 'info');
            appointmentListDiv.innerHTML = ''; // Clear previous content
            appointmentNoRecordsMsg.style.display = 'none';

            // Get current user
            const currentUser = auth.currentUser;
            if (!currentUser) {
                appointmentNoRecordsMsg.style.display = 'block';
                displayModalMessage('appointmentHistoryModal', 'You must be logged in to view your history.', 'error');
                return;
            }

            try {
                const snapshot = await db.collection('appointments')
                    .where('userId', '==', currentUser.uid)
                    .get();
                let completedAppointments = [];

                snapshot.forEach(doc => {
                    const appointment = doc.data();
                    const status = appointment.status?.toLowerCase();
                    
                    // Only show completed appointments (remove cancelled from user history)
                    if (status !== 'completed') {
                        return;
                    }

                    // Apply filter
                    if (filterType !== 'All Appointments') {
                        if (filterType === 'Completed' && status !== 'completed') return;
                    }

                    // Use completion timestamp or scheduled timestamp
                    const timestamp = appointment.completedAt || appointment.scheduledTimestamp;
                    const dateTime = timestamp?.toDate?.() || new Date(timestamp || 0);

                    completedAppointments.push({
                        ...appointment,
                        id: doc.id,
                        sortTimestamp: dateTime
                    });
                });

                // Sort by completion/cancellation date (most recent first)
                completedAppointments.sort((a, b) => b.sortTimestamp - a.sortTimestamp);

                if (completedAppointments.length === 0) {
                    appointmentNoRecordsMsg.style.display = 'block';
                    displayModalMessage('appointmentHistoryModal', 'No completed/cancelled appointments found for this filter.', 'info');
                    return;
                }

                completedAppointments.forEach(appointment => {
                    const item = document.createElement('div');
                    item.className = 'appointment-item';

                    const statusText = appointment.status || 'N/A';
                    let statusClass;
                    switch (statusText.toLowerCase()) {
                        case 'completed':
                            statusClass = 'status-completed';
                            break;
                        case 'cancelled':
                            statusClass = 'status-cancelled';
                            break;
                        default:
                            statusClass = '';
                    }

                    const scheduledDateTime = appointment.date && appointment.time ? 
                        `${appointment.date} at ${appointment.time}` : 
                        `${appointment.date || 'Unknown Date'}`;
                    
                    const actionDate = appointment.completedAt ? 
                        formatTimestamp(appointment.completedAt) : 
                        (appointment.cancelledAt ? formatTimestamp(appointment.cancelledAt) : 'N/A');

                    item.innerHTML = `
                        <h4>${appointment.appointmentType || 'Appointment'} - ${statusText.toUpperCase()}</h4>
                        <p><strong>Scheduled:</strong> ${scheduledDateTime}</p>
                        <p><strong>User:</strong> ${appointment.username || 'N/A'}</p>
                        <p><strong>${statusText === 'completed' ? 'Completed' : 'Cancelled'} At:</strong> ${actionDate}</p>
                        <span class="status-badge ${statusClass}">${statusText.toUpperCase()}</span>
                        <span class="appointment-date">${formatTimestamp(appointment.sortTimestamp)}</span>
                    `;
                    appointmentListDiv.appendChild(item);
                });

                displayModalMessage('appointmentHistoryModal', `${completedAppointments.length} appointment record(s) loaded successfully.`, 'success');
            } catch (error) {
                console.error('Error fetching appointment history:', error);
                displayModalMessage('appointmentHistoryModal', 'Error loading appointment history: ' + error.message, 'error');
            }
        }

        // --- Count Functions (user-specific) ---
        async function loadDonationCounts(currentUserId) {
            try {
                const collections = ['toysdonation', 'clothesdonation', 'fooddonation', 'educationdonation', 'donations'];
                let totalCompleted = 0;

                for (const collection of collections) {
                    const snapshot = await db.collection(collection)
                        .where('userId', '==', currentUserId)
                        .get();
                    snapshot.forEach(doc => {
                        const donation = doc.data();
                        if (['approved', 'rejected'].includes(donation.status?.toLowerCase())) {
                            totalCompleted++;
                        }
                    });
                }

                const donationCountElement = document.getElementById('donation-count');
                if (donationCountElement) donationCountElement.textContent = totalCompleted;
            } catch (error) {
                console.error("Error loading donation counts:", error);
            }
        }

        async function loadAdoptionCounts(currentUserId) {
            try {
                const doc = await db.collection('adoption_progress').doc(currentUserId).get();
                let completedCount = 0;

                if (doc.exists) {
                    const data = doc.data();
                    const completedAdoptions = getAllCompletedAdoptions(data, doc.id);
                    completedCount = completedAdoptions.length;
                }

                const adoptionCountElement = document.getElementById('adoption-count');
                if (adoptionCountElement) adoptionCountElement.textContent = completedCount;
            } catch (error) {
                console.error("Error loading adoption counts:", error);
            }
        }

        async function loadMatchingCounts(currentUserId) {
            try {
                let totalMatches = 0;

                // Count successful matches for current user
                const successfulSnapshot = await db.collection('matching_preferences')
                    .where('senderId', '==', currentUserId)
                    .get();
                successfulSnapshot.forEach(doc => {
                    const match = doc.data();
                    if (['matched', 'accepted', 'completed'].includes(match.status?.toLowerCase())) {
                        totalMatches++;
                    }
                });

                // Count previous match history for current user
                const historySnapshot = await db.collection('matching_history')
                    .where('type', '==', 'previous_match')
                    .where('senderId', '==', currentUserId)
                    .get();
                totalMatches += historySnapshot.size;

                const matchingCountElement = document.getElementById('matching-count');
                if (matchingCountElement) matchingCountElement.textContent = totalMatches;
            } catch (error) {
                console.error("Error loading matching counts:", error);
            }
        }

        async function loadAppointmentCounts(currentUserId) {
            try {
                const snapshot = await db.collection('appointments')
                    .where('userId', '==', currentUserId)
                    .get();
                let completedCount = 0;

                snapshot.forEach(doc => {
                    const appointment = doc.data();
                    // Only count completed appointments (not cancelled) to match what's shown in history
                    if (appointment.status?.toLowerCase() === 'completed') {
                        completedCount++;
                    }
                });

                const appointmentCountElement = document.getElementById('appointment-count');
                if (appointmentCountElement) appointmentCountElement.textContent = completedCount;
            } catch (error) {
                console.error("Error loading appointment counts:", error);
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            // Assign DOM elements AFTER the DOM is fully loaded
            adminStatusMessage = document.getElementById('admin-status-message');
            donationCard = document.getElementById('donation-card');
            adoptionProgressCard = document.getElementById('adoption-progress-card');
            matchingCard = document.getElementById('matching-card');
            appointmentCard = document.getElementById('appointment-card');

            donationHistoryModal = document.getElementById('donationHistoryModal');
            adoptionProgressModal = document.getElementById('adoptionProgressModal');
            matchingHistoryModal = document.getElementById('matchingHistoryModal');
            appointmentHistoryModal = document.getElementById('appointmentHistoryModal');

            donationFilter = document.getElementById('donationFilter');
            donationListDiv = document.getElementById('donation-list');
            donationNoRecordsMsg = donationListDiv.querySelector('.no-records');

            adoptionFilter = document.getElementById('adoptionFilter');
            adoptionListDiv = document.getElementById('adoption-list');
            adoptionNoRecordsMsg = adoptionListDiv.querySelector('.no-records');

            matchingFilter = document.getElementById('matchingFilter');
            matchingListDiv = document.getElementById('matching-list');
            matchingNoRecordsMsg = matchingListDiv.querySelector('.no-records');

            appointmentFilter = document.getElementById('appointmentFilter');
            appointmentListDiv = document.getElementById('appointment-list');
            appointmentNoRecordsMsg = appointmentListDiv.querySelector('.no-records');

            const adminContentDiv = document.querySelector('.content-section'); // Select the content area

            // Attach close listeners to all modal close buttons
            document.querySelectorAll('.modal .close-button').forEach(button => {
                button.addEventListener('click', closeAllModals);
            });

            // Close modals if clicked outside content
            window.addEventListener('click', (event) => {
                document.querySelectorAll('.modal').forEach(modal => {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Event listeners for cards
            if (donationCard) donationCard.addEventListener('click', () => {
                openModal(donationHistoryModal);
                fetchAndDisplayDonations(donationFilter.value); // Load initial data based on current filter
            });

            if (donationFilter) donationFilter.addEventListener('change', (event) => {
                fetchAndDisplayDonations(event.target.value);
            });

            if (adoptionProgressCard) adoptionProgressCard.addEventListener('click', () => {
                openModal(adoptionProgressModal);
                fetchAndDisplayAdoptionProgress(adoptionFilter.value); // Load initial data
            });

            if (adoptionFilter) adoptionFilter.addEventListener('change', (event) => {
                fetchAndDisplayAdoptionProgress(event.target.value);
            });

            if (matchingCard) matchingCard.addEventListener('click', () => {
                openModal(matchingHistoryModal);
                fetchAndDisplayMatchingHistory(matchingFilter.value); // Load initial data
            });

            if (matchingFilter) matchingFilter.addEventListener('change', (event) => {
                fetchAndDisplayMatchingHistory(event.target.value);
            });

            if (appointmentCard) appointmentCard.addEventListener('click', () => {
                openModal(appointmentHistoryModal);
                fetchAndDisplayAppointmentHistory(appointmentFilter.value); // Load initial data
            });

            if (appointmentFilter) appointmentFilter.addEventListener('change', (event) => {
                fetchAndDisplayAppointmentHistory(event.target.value);
            });


            // Initial check for user authentication
            auth.onAuthStateChanged(async (user) => {
                if (user) {
                    try {
                        if (adminContentDiv) adminContentDiv.style.display = 'block';
                        displayAdminStatusMessage('Your history dashboard loaded successfully.', 'success');

                        // Load user-specific counts
                        loadDonationCounts(user.uid);
                        loadAdoptionCounts(user.uid);
                        loadMatchingCounts(user.uid);
                        loadAppointmentCounts(user.uid);

                    } catch (error) {
                        console.error('Error loading user history:', error);
                        displayAdminStatusMessage('Error loading your history: ' + error.message, 'error');
                        if (adminContentDiv) adminContentDiv.innerHTML = '<p style="text-align: center; margin-top: 50px; font-size: 1.2em;">An error occurred. Please try again.</p>';
                        if (adminContentDiv) adminContentDiv.style.display = 'block';
                    }
                } else {
                    displayAdminStatusMessage('Please sign in to access your history.', 'error');
                    if (adminContentDiv) adminContentDiv.innerHTML = '<p style="text-align: center; margin-top: 50px; font-size: 1.2em;">Please <a href="signin.php">sign in</a> to access your history.</p>';
                    if (adminContentDiv) adminContentDiv.style.display = 'block';
                }
            });
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