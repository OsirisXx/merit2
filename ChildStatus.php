<?php
include('session_check.php');

// Check admin role
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<?php include('navbar.php'); ?>
<?php include('chatbot.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Status Information - Meritxell</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .sidebar a.active {
            color: #6ea4ce;
            font-weight: 600;
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

        /* Child Card Styling - matching mobile app */
        .child-section {
            padding: 16px 24px;
            border-bottom: 1px solid #f0f0f0;
            background: white;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .child-card {
            margin-bottom: 0;
        }

        .child-image {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            object-fit: cover;
            display: block;
            margin: 0 auto 16px auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .child-basic-info {
            margin-bottom: 16px;
        }

        .child-name {
            font-size: 16px;
            font-weight: bold;
            margin: 8px 0 4px 0;
            color: #000;
        }

        .child-detail {
            font-size: 14px;
            margin: 4px 0;
            color: #333;
        }

        .see-more-btn {
            background: #6EC6FF;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 8px 0 16px 0;
            transition: background-color 0.3s;
        }

        .see-more-btn:hover {
            background: #5ab3f0;
        }

        .divider {
            height: 1px;
            background: #D3D3D3;
            margin: 16px 0;
        }

        /* Extra Information Section */
        .extra-info {
            display: none;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f0f0f0;
        }

        .extra-info.visible {
            display: block;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            color: #000;
        }

        .info-item {
            font-size: 14px;
            margin-bottom: 4px;
            color: #333;
            line-height: 1.4;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .sidebar ul {
                display: flex;
                overflow-x: auto;
                gap: 10px;
                padding: 10px 0;
            }
            
            .sidebar li {
                margin-bottom: 0;
                flex-shrink: 0;
            }
        }

        /* Loading Animation */
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #6EC6FF;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        <!-- Sidebar Navigation -->
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
                <li><a href="ChildStatus.php" class="active">👶 Child Status Information</a></li>
                <li><a href="admin.php?filter=donation-reports">📊 Donation Reports</a></li>
                <li><a href="admin.php">⚙️ Admin History Dashboard</a></li>
                <li><a href="history.php">📜 History</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Title -->
            <h2>Child Status Information</h2>

                <!-- Loading State -->
                <div id="loadingState" class="loading">
                    <div class="spinner"></div>
                    <p>Loading child information...</p>
                </div>

                <!-- Children Container -->
                <div id="childrenContainer" style="display: none;">
                    
                    <!-- Child 1: Andrew Uy -->
                    <div class="child-section">
                        <div class="child-card">
                            <img src="images/andrew.jpg" alt="Andrew Uy" class="child-image" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkFuZHJldyBVeTwvdGV4dD48L3N2Zz4='">
                            
                            <div class="child-basic-info">
                                <div class="child-name">Name: Andrew Uy</div>
                                <div class="child-detail">Nickname: Drew</div>
                                <div class="child-detail">Age: 14</div>
                            </div>

                            <button class="see-more-btn" onclick="toggleExtraInfo('child1')">See more...</button>
                            <div class="divider"></div>

                            <!-- Extra Information -->
                            <div id="child1" class="extra-info">
                                <div class="info-section">
                                    <div class="info-section-title">Physical Attributes</div>
                                    <div class="info-item">Height: 5'2"</div>
                                    <div class="info-item">Weight: 75 lbs</div>
                                    <div class="info-item">Eye Color: Brown</div>
                                    <div class="info-item">Skin Tone: Medium Brown</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Health Information</div>
                                    <div class="info-item">Overall Health Status: Healthy</div>
                                    <div class="info-item">Known Allergies: None</div>
                                    <div class="info-item">Disabilities: None</div>
                                    <div class="info-item">Blood Type: O+</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Education and Development</div>
                                    <div class="info-item">Current Grade Level: Grade 5</div>
                                    <div class="info-item">Language Spoken: Tagalog</div>
                                    <div class="info-item">Social Skills: Friendly and Playful</div>
                                    <div class="info-item">Hobbies: Loves drawing, toy cars, and playing outside</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Case Information</div>
                                    <div class="info-item">Case Status: Surrendered</div>
                                    <div class="info-item">Legal Status: Cleared for Adoption</div>
                                    <div class="info-item">Reason for Adoption: Orphaned at birth</div>
                                    <div class="info-item">Siblings: 3</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Child 2: Xemen Villapando -->
                    <div class="child-section">
                        <div class="child-card">
                            <img src="images/xemen.jpg" alt="Xemen Villapando" class="child-image" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPlhlbWVuPC90ZXh0Pjwvc3ZnPg=='">
                            
                            <div class="child-basic-info">
                                <div class="child-name">Name: Xemen Villapando</div>
                                <div class="child-detail">Nickname: Xem</div>
                                <div class="child-detail">Age: 1</div>
                            </div>

                            <button class="see-more-btn" onclick="toggleExtraInfo('child2')">See more...</button>
                            <div class="divider"></div>

                            <!-- Extra Information -->
                            <div id="child2" class="extra-info">
                                <div class="info-section">
                                    <div class="info-section-title">Physical Attributes</div>
                                    <div class="info-item">Height: 76 cm</div>
                                    <div class="info-item">Weight: 29 pounds</div>
                                    <div class="info-item">Eye Color: Brown</div>
                                    <div class="info-item">Skin Tone: Fair</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Health Information</div>
                                    <div class="info-item">Overall Health Status: Healthy</div>
                                    <div class="info-item">Known Allergies: Dust Mites</div>
                                    <div class="info-item">Disabilities: Mild Asthma</div>
                                    <div class="info-item">Blood Type: A-</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Education and Development</div>
                                    <div class="info-item">Current Grade Level: none</div>
                                    <div class="info-item">Language Spoken: Tagalog</div>
                                    <div class="info-item">Social Skills: Joyful</div>
                                    <div class="info-item">Hobbies: Drinking milk</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Case Information</div>
                                    <div class="info-item">Case Status: Surrendered</div>
                                    <div class="info-item">Legal Status: Cleared for Adoption</div>
                                    <div class="info-item">Reason for Adoption: Orphaned at birth</div>
                                    <div class="info-item">Siblings: 0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Child 3: Jose Rances -->
                    <div class="child-section">
                        <div class="child-card">
                            <img src="images/jose.jpg" alt="Jose Rances" class="child-image" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkpvc2UgUmFuY2VzPC90ZXh0Pjwvc3ZnPg=='">
                            
                            <div class="child-basic-info">
                                <div class="child-name">Name: Jose Rances</div>
                                <div class="child-detail">Nickname: Jo</div>
                                <div class="child-detail">Age: 4</div>
                            </div>

                            <button class="see-more-btn" onclick="toggleExtraInfo('child3')">See more...</button>

                            <!-- Extra Information -->
                            <div id="child3" class="extra-info">
                                <div class="info-section">
                                    <div class="info-section-title">Physical Attributes</div>
                                    <div class="info-item">Height: 112 cm</div>
                                    <div class="info-item">Weight: 40 lbs</div>
                                    <div class="info-item">Eye Color: Brown</div>
                                    <div class="info-item">Skin Tone: Medium Brown</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Health Information</div>
                                    <div class="info-item">Overall Health Status: Good, but needs regular check-ups</div>
                                    <div class="info-item">Known Allergies: Peanuts</div>
                                    <div class="info-item">Disabilities: None</div>
                                    <div class="info-item">Blood Type: B+</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Education and Development</div>
                                    <div class="info-item">Current Grade Level: Pre School</div>
                                    <div class="info-item">Language Spoken: Tagalog</div>
                                    <div class="info-item">Social Skills: Friendly, slightly reserved</div>
                                    <div class="info-item">Hobbies: Chess, Tetris</div>
                                </div>

                                <div class="info-section">
                                    <div class="info-section-title">Case Information</div>
                                    <div class="info-item">Case Status: Surrendered</div>
                                    <div class="info-item">Legal Status: Cleared for Adoption</div>
                                    <div class="info-item">Reason for Adoption: Orphaned at birth</div>
                                    <div class="info-item">Siblings: 0</div>
                                </div>
                            </div>
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

    <script>
        // Toggle extra information visibility - matching mobile app functionality
        function toggleExtraInfo(childId) {
            const extraInfo = document.getElementById(childId);
            const button = event.target;
            
            if (extraInfo.classList.contains('visible')) {
                extraInfo.classList.remove('visible');
                button.textContent = 'See more...';
            } else {
                extraInfo.classList.add('visible');
                button.textContent = 'See less...';
            }
        }

        // Simulate loading (matching mobile app experience)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('childrenContainer').style.display = 'block';
            }, 1500);
        });

        // Add smooth scrolling for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scroll behavior to see more buttons
            const seeMoreButtons = document.querySelectorAll('.see-more-btn');
            seeMoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                });
            });
        });
    </script>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>

    <script>
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.firebasestorage.app",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        // Initialize Firebase if not already initialized
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        
        const auth = firebase.auth();
        const db = firebase.firestore();
        
        // Make Firebase globally available for navbar notifications and profile picture
        window.firebase = firebase;
        window.db = db;
        window.auth = auth;
        
        console.log('🔥 ChildStatus: Firebase initialized for profile picture support');
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