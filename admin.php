<?php
require_once 'session_check.php';

// Redirect if user is not logged in
if (!$isLoggedIn) {
    header('Location: Signin.php');
    exit;
}

// Check if user is admin - only admins can access this page
if (!$isAdmin) {
    header('Location: Dashboard.php');
    exit;
}

// Optional: Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}
?>

<?php include('navbar.php'); // Include your universal navigation bar ?>
<?php include('chatbot.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin History Dashboard - Ally</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #F2F2F2;
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
            padding: 0;
            box-sizing: border-box;
            background-color: #F2F2F2;
            display: flex;
            flex-direction: column;
        }

        .header {
            background-color: #6EC6FF;
            padding: 12px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-content {
            text-align: center;
            color: white;
        }

        .logo {
            height: 40px;
            margin-bottom: 4px;
        }

        .header-text {
            font-size: 14px;
            font-weight: bold;
        }

        .page-title {
            color: #333;
            margin: 0;
            padding: 16px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            background-color: #F2F2F2;
        }

        .search-filter-section {
            background-color: #ffffff;
            padding: 16px;
            margin: 0;
        }

        .search-bar {
            width: 100%;
            height: 48px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            font-size: 14px;
            box-sizing: border-box;
            margin-bottom: 12px;
        }

        .search-bar:focus {
            outline: none;
            border-color: #6EC6FF;
            box-shadow: 0 0 0 2px rgba(110, 198, 255, 0.2);
        }

        .filter-row {
            display: flex;
            align-items: center;
        }

        .filter-label {
            font-size: 16px;
            color: #333;
            margin-right: 8px;
            font-weight: 500;
        }

        .filter-select {
            flex: 1;
            height: 48px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            font-size: 14px;
            box-sizing: border-box;
        }

        .filter-select:focus {
            outline: none;
            border-color: #6EC6FF;
            box-shadow: 0 0 0 2px rgba(110, 198, 255, 0.2);
        }

        .history-container {
            flex: 1;
            padding: 8px;
            overflow-y: auto;
            background-color: #F2F2F2;
        }

        .history-item {
            background-color: #fff;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #6EC6FF;
        }

        .history-item:last-child {
            margin-bottom: 0;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .history-type {
            font-weight: bold;
            color: #333;
            background-color: #e3f2fd;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .history-date {
            font-size: 12px;
            color: #666;
        }

        .history-details {
            font-size: 14px;
            color: #555;
            line-height: 1.4;
        }

        .history-user {
            font-weight: 500;
            color: #6EC6FF;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-matched {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .no-data {
            text-align: center;
            padding: 60px 32px;
            color: #999;
            font-size: 18px;
            background-color: #F2F2F2;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-indicator {
            text-align: center;
            padding: 40px;
            color: #666;
            background-color: #F2F2F2;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-message {
            padding: 15px;
            margin: 16px;
            border-radius: 5px;
            font-weight: bold;
            display: none;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            
            .filter-label {
                margin-right: 0;
                margin-bottom: 4px;
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
                padding: 0;
            }

            .page-title {
                font-size: 18px;
                padding: 12px;
            }

            .search-filter-section {
                padding: 12px;
            }

            .search-bar {
                height: 44px;
                font-size: 16px;
            }

            .filter-select {
                height: 44px;
                font-size: 16px;
            }

            .history-container {
                padding: 8px;
            }

            .history-item {
                padding: 12px;
                margin-bottom: 8px;
            }

            .history-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .history-type {
                font-size: 11px;
            }

            .history-date {
                font-size: 11px;
            }

            .history-details {
                font-size: 13px;
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
            .header {
                height: 56px;
                padding: 8px;
            }

            .logo {
                height: 32px;
            }

            .header-text {
                font-size: 12px;
            }

            .page-title {
                font-size: 16px;
                padding: 10px;
            }

            .search-filter-section {
                padding: 10px;
            }

            .history-item {
                padding: 10px;
                margin-bottom: 6px;
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
                padding: 0;
            }

            .page-title {
                font-size: 24px;
                padding: 20px;
            }

            .search-filter-section {
                padding: 20px;
            }

            .history-container {
                padding: 12px;
            }

            .history-item {
                padding: 20px;
                margin-bottom: 16px;
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
                
                <!-- Matching is now integrated into Stage 7 of the adoption process -->
                
                <?php if ($isAdmin): ?>
                <li><a href="ChildStatus.php">👶 Child Status Information</a></li>
                <li><a href="admin.php?filter=donation-reports">📊 Donation Reports</a></li>
                <li><a href="admin.php">⚙️ Admin History Dashboard</a></li>
                <li><a href="history.php">📜 History</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main class="main-content">
            <!-- Header with logo -->
            <div class="header">
                <div class="header-content">
                    <img src="https://www.meritxellchildrensfoundation.org/images/logo-with-words-3.png" alt="Logo" class="logo">
                    <div class="header-text">MERITXELL</div>
                </div>
            </div>

            <!-- Alert Message -->
            <div id="admin-status-message" class="alert-message alert-info">Loading admin dashboard...</div>

            <!-- Title -->
            <h1 class="page-title">Admin History Dashboard</h1>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <!-- Search Bar -->
                <input type="text" 
                       class="search-bar" 
                       id="searchHistory" 
                       placeholder="Search history records..."
                       autocomplete="off">

                <!-- Filter Dropdown -->
                <div class="filter-row">
                    <label class="filter-label">Filter:</label>
                    <select class="filter-select" id="historyFilter">
                        <option value="all">All Records</option>
                        <option value="adoption">Completed Adoptions</option>
                        <option value="donation">Donation History</option>
                        <option value="donation-reports">📊 Donation Reports</option>
                        <option value="appointment">Appointment History</option>
                        <option value="matching">Matching History</option>
                    </select>
                </div>
            </div>

            <!-- History Records List -->
            <div class="history-container" id="historyContainer">
                <div class="loading-indicator" id="loadingIndicator">
                    <div>
                        <i class="fas fa-spinner fa-spin"></i><br>
                        Loading history records...
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

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>

    <script>
        // Firebase configuration (matching other pages)
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.firebasestorage.app",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15",
            measurementId: "G-0D35XC4HQ4"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        const db = firebase.firestore();

        // Global variables
        let allHistoryData = [];
        let filteredHistoryData = [];
        let searchQuery = '';
        let selectedFilter = 'all';

        // DOM elements
        let adminStatusMessage;
        let searchInput;
        let filterSelect;
        let historyContainer;
        let loadingIndicator;

        // Display admin status message function
        function displayAdminStatusMessage(message, type) {
            if (adminStatusMessage) {
                adminStatusMessage.textContent = message;
                adminStatusMessage.className = `alert-message alert-${type}`;
                adminStatusMessage.style.display = 'block';
                setTimeout(() => {
                    adminStatusMessage.style.display = 'none';
                }, 3000);
            }
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Get DOM elements
            adminStatusMessage = document.getElementById('admin-status-message');
            searchInput = document.getElementById('searchHistory');
            filterSelect = document.getElementById('historyFilter');
            historyContainer = document.getElementById('historyContainer');
            loadingIndicator = document.getElementById('loadingIndicator');

            // Add event listeners
            searchInput.addEventListener('input', function() {
                searchQuery = this.value.trim();
                applyFilters();
            });

            filterSelect.addEventListener('change', function() {
                selectedFilter = this.value;
                applyFilters();
            });

            // Firebase auth state change listener
            auth.onAuthStateChanged(async (user) => {
                if (user) {
                    try {
                        const userDoc = await db.collection('users').doc(user.uid).get();
                        if (userDoc.exists && userDoc.data().role === 'admin') {
                            displayAdminStatusMessage('Admin dashboard loaded successfully.', 'success');
                            
                            // Check URL parameters for filter
                            const urlParams = new URLSearchParams(window.location.search);
                            const filterParam = urlParams.get('filter');
                            
                            // Load all data
                            await loadAllHistoryData();
                            
                            // Apply filter if specified in URL
                            if (filterParam) {
                                selectedFilter = filterParam;
                                filterSelect.value = filterParam;
                            }
                            applyFilters();
                            
                        } else {
                            displayAdminStatusMessage('Access Denied: You do not have administrator privileges.', 'error');
                            historyContainer.innerHTML = '<div class="no-data">You do not have permission to view this page.<br>Please log in with an administrator account.</div>';
                        }
                    } catch (error) {
                        console.error('Error checking admin role:', error);
                        displayAdminStatusMessage('Error verifying admin role: ' + error.message, 'error');
                    }
                } else {
                    displayAdminStatusMessage('Please sign in to access the admin dashboard.', 'error');
                    historyContainer.innerHTML = '<div class="no-data">Please <a href="signin.php">sign in</a> to access the admin dashboard.</div>';
                }
            });
        });

        // Load all history data from Firebase (exactly like mobile app)
        async function loadAllHistoryData() {
            allHistoryData = [];

            try {
                // Load adoption history
                await loadAdoptionHistory();
                
                // Load donation history
                await loadDonationHistory();
                
                // Load appointment history
                await loadAppointmentHistory();
                
                // Load matching history
                await loadMatchingHistory();

                // Sort by timestamp (most recent first)
                allHistoryData.sort((a, b) => b.timestamp - a.timestamp);
                
            } catch (error) {
                console.error('Error loading history data:', error);
                displayAdminStatusMessage('Error loading history data: ' + error.message, 'error');
            }
        }

        // Load adoption history (matching mobile app logic)
        async function loadAdoptionHistory() {
            try {
                const snapshot = await db.collection('adoption_progress').get();
                
                snapshot.forEach(doc => {
                    const data = doc.data();
                    const completedAdoptions = getAllCompletedAdoptions(data, doc.id);
                    
                    completedAdoptions.forEach(adoption => {
                        const timestamp = adoption.completedAt?.toDate?.() || new Date(adoption.completedAt || 0);
                        
                        allHistoryData.push({
                            id: adoption.id,
                            type: 'Adoption',
                            category: 'adoption',
                            user: adoption.username || 'Unknown User',
                            userId: adoption.userId,
                            title: adoption.title,
                            description: `Completed all 10 adoption steps`,
                            status: 'Completed',
                            timestamp: timestamp,
                            details: adoption
                        });
                    });
                });
            } catch (error) {
                console.error('Error loading adoption history:', error);
            }
        }

        // Load donation history (matching mobile app logic)
        async function loadDonationHistory() {
            try {
                const donationCollections = ['donations', 'toysdonation', 'clothesdonation', 'fooddonation', 'educationdonation'];
                
                for (const collection of donationCollections) {
                    const snapshot = await db.collection(collection).get();
                    
                    snapshot.forEach(doc => {
                        const donation = doc.data();
                        const status = donation.status?.toLowerCase();
                        
                        // Only include approved and rejected donations
                        if (!['approved', 'rejected'].includes(status)) {
                            return;
                        }

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

                        const timestamp = donation.timestamp?.toDate?.() || new Date(donation.timestamp || 0);
                        const amount = typeof donation.amount === 'string' ? donation.amount : (donation.amount || 0).toLocaleString();

                        allHistoryData.push({
                            id: doc.id,
                            type: 'Donation',
                            category: 'donation',
                            user: donation.username || 'Unknown User',
                            userId: donation.userId,
                            title: `${donationType} Donation - ${status.toUpperCase()}`,
                            description: `Donated ${amount} - Status: ${status.toUpperCase()}`,
                            status: status.charAt(0).toUpperCase() + status.slice(1),
                            timestamp: timestamp,
                            details: donation
                        });
                    });
                }
            } catch (error) {
                console.error('Error loading donation history:', error);
            }
        }

        // Load appointment history (matching mobile app logic)
        async function loadAppointmentHistory() {
            try {
                const snapshot = await db.collection('appointments').get();
                
                snapshot.forEach(doc => {
                    const appointment = doc.data();
                    const status = appointment.status?.toLowerCase();
                    
                    // Only include completed and cancelled appointments
                    if (!['completed', 'cancelled'].includes(status)) {
                        return;
                    }

                    const timestamp = appointment.completedAt?.toDate?.() || 
                                    appointment.cancelledAt?.toDate?.() || 
                                    appointment.scheduledTimestamp?.toDate?.() || 
                                    new Date(appointment.scheduledTimestamp || 0);

                    const scheduledDateTime = appointment.date && appointment.time ? 
                        `${appointment.date} at ${appointment.time}` : 
                        `${appointment.date || 'Unknown Date'}`;

                    allHistoryData.push({
                        id: doc.id,
                        type: 'Appointment',
                        category: 'appointment',
                        user: appointment.username || 'Unknown User',
                        userId: appointment.userId,
                        title: `${appointment.appointmentType || 'Appointment'} - ${status.toUpperCase()}`,
                        description: `Scheduled: ${scheduledDateTime} - Status: ${status.toUpperCase()}`,
                        status: status.charAt(0).toUpperCase() + status.slice(1),
                        timestamp: timestamp,
                        details: appointment
                    });
                });
            } catch (error) {
                console.error('Error loading appointment history:', error);
            }
        }

        // Load matching history (matching mobile app logic)
        async function loadMatchingHistory() {
            try {
                // Load successful matches
                const successfulSnapshot = await db.collection('matching_preferences').get();
                successfulSnapshot.forEach(doc => {
                    const match = doc.data();
                    const status = match.status?.toLowerCase();
                    
                    if (['matched', 'accepted', 'completed'].includes(status)) {
                        const timestamp = match.timestamp?.toDate?.() || new Date(match.timestamp || 0);
                        
                        allHistoryData.push({
                            id: doc.id,
                            type: 'Matching',
                            category: 'matching',
                            user: match.username || 'Unknown User',
                            userId: match.userId,
                            title: `Successful Match - ${status.toUpperCase()}`,
                            description: `Matching status: ${status.toUpperCase()}`,
                            status: status.charAt(0).toUpperCase() + status.slice(1),
                            timestamp: timestamp,
                            details: match
                        });
                    }
                });

                // Load previous match history
                const historySnapshot = await db.collection('matching_history')
                    .where('type', '==', 'previous_match')
                    .get();

                historySnapshot.forEach(doc => {
                    const historyData = doc.data();
                    const previousMatch = historyData.previousMatch;
                    const childDetails = previousMatch?.childDetails;
                    const childName = childDetails?.name || 'Unknown Child';
                    const timestamp = new Date(historyData.createdAt || 0);

                    allHistoryData.push({
                        id: doc.id,
                        type: 'Matching',
                        category: 'matching',
                        user: historyData.senderUsername || 'Unknown User',
                        userId: historyData.senderId,
                        title: `Match with ${childName} - Previous Match`,
                        description: `Previous match with child: ${childName}`,
                        status: 'Previous Match',
                        timestamp: timestamp,
                        details: historyData
                    });
                });
            } catch (error) {
                console.error('Error loading matching history:', error);
            }
        }

        // Get all completed adoptions from a document (matching mobile app logic)
        function getAllCompletedAdoptions(data, docId) {
            const completedAdoptions = [];
            
            // Check for versioned structure first
            if (data.adoptions) {
                // New versioned structure
                for (const [adoptionKey, adoptionData] of Object.entries(data.adoptions)) {
                    const adoption = adoptionData;
                    if (adoption.status === 'completed') {
                        const adoptProgress = adoption.adopt_progress;
                        if (adoptProgress) {
                            let completedSteps = 0;
                            for (let i = 1; i <= 10; i++) {
                                if (adoptProgress[`step${i}`] === 'complete') {
                                    completedSteps++;
                                }
                            }
                            if (completedSteps === 10) {
                                completedAdoptions.push({
                                    id: `${docId}_adoption_${adoptionKey}`,
                                    adoptionKey: adoptionKey,
                                    title: `Completed Adoption #${adoptionKey}`,
                                    completedAt: adoption.completedAt,
                                    startedAt: adoption.startedAt,
                                    details: adoption,
                                    username: data.username,
                                    userId: data.userId || docId
                                });
                            }
                        }
                    }
                }
            } else {
                // Old structure
                const adoptProgress = data.adopt_progress;
                if (adoptProgress) {
                    let completedSteps = 0;
                    for (let i = 1; i <= 10; i++) {
                        if (adoptProgress[`step${i}`] === 'complete') {
                            completedSteps++;
                        }
                    }
                    if (completedSteps === 10) {
                        completedAdoptions.push({
                            id: docId,
                            adoptionKey: '1',
                            title: 'Completed Adoption Process',
                            completedAt: data.completedAt,
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

        // Apply filters and search
        function applyFilters() {
            // TASK 3: Handle donation reports separately
            if (selectedFilter === 'donation-reports') {
                displayDonationReports();
                return;
            }
            
            filteredHistoryData = allHistoryData.filter(record => {
                // Filter by category
                const matchesFilter = selectedFilter === 'all' || record.category === selectedFilter;
                
                // Filter by search query
                const matchesSearch = searchQuery === '' || 
                    record.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    record.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    record.user.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    record.status.toLowerCase().includes(searchQuery.toLowerCase());
                
                return matchesFilter && matchesSearch;
            });

            // Sort by timestamp (newest first)
            filteredHistoryData.sort((a, b) => b.timestamp - a.timestamp);
            
            displayHistoryRecords();
        }

        // Display history records
        function displayHistoryRecords() {
            loadingIndicator.style.display = 'none';
            
            if (filteredHistoryData.length === 0) {
                const message = searchQuery !== '' 
                    ? `No records found matching '${searchQuery}'`
                    : 'No completed records found';
                    
                historyContainer.innerHTML = `
                    <div class="no-data">
                        ${message}
                    </div>
                `;
                return;
            }

            let html = '';
            filteredHistoryData.forEach(item => {
                const statusClass = getStatusClass(item.status);
                html += `
                    <div class="history-item">
                        <div class="history-header">
                            <span class="history-type">${item.type}</span>
                            <span class="history-date">${formatDate(item.timestamp)}</span>
                        </div>
                        <div class="history-details">
                            <strong>${item.title}</strong><br>
                            <span class="history-user">${item.user}</span> - ${item.description}
                            <div class="status-badge ${statusClass}">${item.status}</div>
                        </div>
                    </div>
                `;
            });
            
            historyContainer.innerHTML = html;
        }

        // Get status class for styling
        function getStatusClass(status) {
            const statusLower = status.toLowerCase();
            switch (statusLower) {
                case 'approved':
                case 'completed':
                    return 'status-approved';
                case 'rejected':
                case 'cancelled':
                    return 'status-rejected';
                case 'matched':
                case 'accepted':
                    return 'status-matched';
                default:
                    return 'status-completed';
            }
        }

        // Format date for display
        function formatDate(timestamp) {
            if (!timestamp) return 'N/A';
            
            let date;
            if (typeof timestamp.toDate === 'function') {
                date = timestamp.toDate();
            } else if (timestamp instanceof Date) {
                date = timestamp;
            } else {
                date = new Date(timestamp);
            }

            if (isNaN(date.getTime())) {
                return 'Invalid Date';
            }

            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) + ' ' + date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // TASK 3: Display donation reports with category totals and detailed breakdown
        async function displayDonationReports() {
            loadingIndicator.style.display = 'none';
            
            try {
                const donationData = await generateDonationReports();
                
                let html = `
                    <div style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h2 style="color: #2c3e50; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 24px;">📊</span>
                            Donation Reports & Analytics
                        </h2>
                        
                        <!-- Overall Summary -->
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 24px;">
                            <h3 style="margin: 0 0 15px 0; font-size: 20px;">📈 Overall Summary</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 28px; font-weight: bold;">${donationData.totalCount}</div>
                                    <div style="opacity: 0.9;">Total Donations</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 28px; font-weight: bold;">₱${donationData.totalMoneyAmount.toLocaleString()}</div>
                                    <div style="opacity: 0.9;">Total Money Donations</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 28px; font-weight: bold;">${donationData.totalInKindCount}</div>
                                    <div style="opacity: 0.9;">In-Kind Donations</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Category Breakdown -->
                        <div style="margin-bottom: 24px;">
                            <h3 style="color: #2c3e50; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                                <span>📋</span> Donations by Category
                            </h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                `;
                
                // Add category cards
                Object.entries(donationData.byCategory).forEach(([category, data]) => {
                    const categoryIcons = {
                        'Money Sponsorship': '💰',
                        'Education Sponsorship': '🎓',
                        'Medicine Sponsorship': '💊',
                        'Toys': '🧸',
                        'Clothes': '👕',
                        'Food': '🍎',
                        'Education': '📚'
                    };
                    
                    const icon = categoryIcons[category] || '💝';
                    const isMoneyCategory = category.includes('Sponsorship') || category === 'Money';
                    
                    html += `
                        <div style="background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 8px; padding: 16px; text-align: center;">
                            <div style="font-size: 32px; margin-bottom: 8px;">${icon}</div>
                            <h4 style="margin: 0 0 8px 0; color: #495057;">${category}</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #28a745; margin-bottom: 4px;">
                                ${data.count}
                            </div>
                            <div style="color: #6c757d; font-size: 14px;">donations</div>
                            ${isMoneyCategory ? `
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #dee2e6;">
                                    <div style="font-weight: bold; color: #007bff;">₱${data.totalAmount.toLocaleString()}</div>
                                    <div style="color: #6c757d; font-size: 12px;">total amount</div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                
                html += `
                            </div>
                        </div>
                        
                        <!-- Detailed Breakdown -->
                        <div>
                            <h3 style="color: #2c3e50; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                                <span>📝</span> Detailed Donation List
                            </h3>
                            
                            <div style="margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap;">
                                <button onclick="filterDonationDetails('all')" class="report-filter-btn active" data-category="all">
                                    All (${donationData.totalCount})
                                </button>
                `;
                
                // Add filter buttons for each category
                Object.entries(donationData.byCategory).forEach(([category, data]) => {
                    html += `
                        <button onclick="filterDonationDetails('${category}')" class="report-filter-btn" data-category="${category}">
                            ${category} (${data.count})
                        </button>
                    `;
                });
                
                html += `
                            </div>
                            
                            <div id="donation-details-list" style="max-height: 500px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px;">
                `;
                
                // Add detailed donation list
                donationData.allDonations.forEach(donation => {
                    const categoryClass = donation.category.replace(/\s+/g, '-').toLowerCase();
                    const isMoneyDonation = donation.category.includes('Sponsorship') || donation.category === 'Money';
                    
                    html += `
                        <div class="donation-detail-item" data-category="${donation.category}" 
                             style="background: white; border-bottom: 1px solid #e9ecef; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">
                                    ${donation.donor || 'Anonymous Donor'}
                                </div>
                                <div style="color: #6c757d; font-size: 14px; margin-bottom: 4px;">
                                    ${donation.category} ${isMoneyDonation && donation.amount ? `- ₱${donation.amount.toLocaleString()}` : ''}
                                </div>
                                <div style="color: #868e96; font-size: 12px;">
                                    ${donation.dateTime} • Status: <span style="color: ${donation.status === 'approved' ? '#28a745' : donation.status === 'rejected' ? '#dc3545' : '#6c757d'};">${donation.status}</span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #495057;">
                                    ID: ${donation.id.substring(0, 8)}...
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += `
                            </div>
                        </div>
                    </div>
                    
                    <style>
                        .report-filter-btn {
                            padding: 8px 16px;
                            border: 2px solid #dee2e6;
                            background: white;
                            border-radius: 20px;
                            cursor: pointer;
                            font-size: 14px;
                            transition: all 0.2s ease;
                        }
                        
                        .report-filter-btn:hover {
                            border-color: #007bff;
                            background: #f8f9fa;
                        }
                        
                        .report-filter-btn.active {
                            background: #007bff;
                            color: white;
                            border-color: #007bff;
                        }
                        
                        .donation-detail-item[data-category]:not(.visible) {
                            display: none;
                        }
                    </style>
                `;
                
                historyContainer.innerHTML = html;
                
                // Initialize with all donations visible
                filterDonationDetails('all');
                
            } catch (error) {
                console.error('Error generating donation reports:', error);
                historyContainer.innerHTML = `
                    <div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; text-align: center;">
                        <h3>❌ Error Loading Donation Reports</h3>
                        <p>Unable to load donation data: ${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Generate donation reports data
        async function generateDonationReports() {
            const donationCollections = ['donations', 'toysdonation', 'clothesdonation', 'fooddonation', 'educationdonation'];
            const reportData = {
                totalCount: 0,
                totalMoneyAmount: 0,
                totalInKindCount: 0,
                byCategory: {},
                allDonations: []
            };
            
            for (const collection of donationCollections) {
                try {
                    const snapshot = await db.collection(collection).get();
                    
                    snapshot.forEach(doc => {
                        const donation = doc.data();
                        
                        // Determine category
                        const category = (() => {
                            switch (collection) {
                                case 'toysdonation': return 'Toys';
                                case 'clothesdonation': return 'Clothes';
                                case 'fooddonation': return 'Food';
                                case 'educationdonation': return 'Education Sponsorship';
                                case 'donations': 
                                    const donationType = donation.donationType?.toLowerCase() || '';
                                    if (donationType.includes('money')) return 'Money Sponsorship';
                                    if (donationType.includes('medicine')) return 'Medicine Sponsorship';
                                    if (donationType.includes('education')) return 'Education Sponsorship';
                                    return donation.donationType || 'Money Sponsorship';
                                default: return collection.charAt(0).toUpperCase() + collection.slice(1);
                            }
                        })();
                        
                        // Initialize category if needed
                        if (!reportData.byCategory[category]) {
                            reportData.byCategory[category] = {
                                count: 0,
                                totalAmount: 0
                            };
                        }
                        
                        // Parse amount for money donations
                        let amount = 0;
                        if (donation.amount) {
                            if (typeof donation.amount === 'string') {
                                amount = parseFloat(donation.amount.replace(/[^\d.-]/g, '')) || 0;
                            } else {
                                amount = donation.amount || 0;
                            }
                        }
                        
                        // Update totals
                        reportData.totalCount++;
                        reportData.byCategory[category].count++;
                        
                        const isMoneyCategory = category.includes('Sponsorship') || category === 'Money';
                        if (isMoneyCategory && amount > 0) {
                            reportData.totalMoneyAmount += amount;
                            reportData.byCategory[category].totalAmount += amount;
                        } else {
                            reportData.totalInKindCount++;
                        }
                        
                        // Format date and time
                        const timestamp = donation.timestamp?.toDate?.() || new Date(donation.timestamp || 0);
                        const formattedDate = timestamp.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                        const formattedTime = timestamp.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        const formattedDateTime = `${formattedDate} at ${formattedTime}`;
                        
                        // Add to detailed list
                        reportData.allDonations.push({
                            id: doc.id,
                            donor: donation.donorName || donation.senderUsername || donation.username || donation.fullName || 'Anonymous',
                            category: category,
                            amount: amount,
                            date: formattedDate,
                            dateTime: formattedDateTime,
                            status: donation.status || 'pending',
                            collection: collection,
                            timestamp: timestamp
                        });
                    });
                } catch (error) {
                    console.error(`Error loading ${collection}:`, error);
                }
            }
            
            // Sort donations by date (newest first)
            reportData.allDonations.sort((a, b) => b.timestamp - a.timestamp);
            
            return reportData;
        }
        
        // Filter donation details by category
        function filterDonationDetails(category) {
            // Update active button
            document.querySelectorAll('.report-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-category="${category}"]`).classList.add('active');
            
            // Show/hide donation items
            document.querySelectorAll('.donation-detail-item').forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Make function globally accessible
        window.filterDonationDetails = filterDonationDetails;
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