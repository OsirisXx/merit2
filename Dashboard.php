<?php
require_once 'session_check.php';

if (!isset($_SESSION['alert'])) {
  $_SESSION['alert'] = null;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php include('navbar.php'); ?>
<?php include('chatbot.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f7fa;
      color: #333;
    }

    .container {
      display: flex;
      min-height: 100vh;
      /* Ensures container takes at least full viewport height */
    }

    .sidebar {
      width: 240px;
      background-color: #ffffff;
      border-right: 1px solid #e0e0e0;
      padding: 30px 20px;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
      /* Ensure sidebar is sticky or has min-height if content is short */
      position: sticky;
      /* Makes sidebar stick if main content scrolls */
      top: 0;
      /* Aligns to the top of the viewport */
      height: 100vh;
      /* Takes full viewport height */
      overflow-y: auto;
      /* Adds scrollbar if sidebar content exceeds height */
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
      /* Allows main content to take up remaining space */
      padding: 30px;
      box-sizing: border-box;
      /* Include padding in element's total width and height */
      max-width: 100%;
      overflow-x: hidden;
    }

    h2 {
      color: #333;
      margin-bottom: 25px;
      text-align: left;
    }

    .service-preference-card {
      background: #f0f8ff;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 25px;
      border-left: 4px solid #7CB9E8;
    }

    .service-preference-card h3 {
      margin: 0 0 10px 0;
      color: #333;
    }

    .service-preference-card p {
      margin: 0;
      color: #666;
      font-size: 14px;
    }

    .news,
    .events {
      margin-top: 40px;
    }

    .news-grid,
    .events-grid {
      display: grid;
      gap: 20px;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }

    .news-item,
    .event-item {
      background: #ffffff;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      text-align: center;
      transition: transform 0.2s ease;
      display: flex;
      flex-direction: column;
    }

    .news-item:hover,
    .event-item:hover {
      transform: translateY(-5px);
    }

    .news-item img,
    .event-item img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 4px;
      margin-bottom: 10px;
      flex-shrink: 0;
    }

    .news-item h4,
    .event-item h4 {
      margin: 10px 0 5px;
      font-size: 1.1em;
      color: #333;
      flex-shrink: 0;
    }

    .news-item p,
    .event-item p {
      font-size: 0.9em;
      color: #666;
      flex-grow: 1;
      margin-bottom: 15px;
    }

    .btn {
      display: inline-block;
      margin-top: auto;
      padding: 8px 16px;
      background: #8c3434;
      color: #fff;
      text-decoration: none;
      font-size: 0.85em;
      border-radius: 6px;
      transition: background 0.2s ease;
      align-self: center;
      min-width: 100px;
    }

    .btn:hover {
      background: #722a2a;
    }

    /* Tablet Styles */
    @media (max-width: 1024px) and (min-width: 769px) {
      .sidebar {
        width: 200px;
        padding: 25px 15px;
      }

      .sidebar a {
        font-size: 0.9em;
        gap: 6px;
      }

      .main-content {
        padding: 25px;
      }

      .news-grid,
      .events-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
      }

      .news-item img,
      .event-item img {
        height: 160px;
      }
    }

    /* Mobile Menu Toggle */
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

    /* Mobile Dropdown Menu */
            .mobile-dropdown-menu {
            display: none;
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
            display: block;
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

    /* Mobile Styles */
    @media (max-width: 768px) {
      .mobile-menu-toggle,
      .mobile-dropdown-menu {
        display: block;
      }

      .container {
        flex-direction: column;
      }

      .sidebar {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
        position: absolute !important;
        left: -9999px !important;
        z-index: -1 !important;
      }

      .sidebar * {
        pointer-events: none !important;
        visibility: hidden !important;
      }

      /* Mobile overlay */
      .mobile-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
      }

      .mobile-overlay.active {
        display: block;
        opacity: 1;
      }

      .main-content {
        padding: 20px 15px;
      }

      h2 {
        font-size: 1.4em;
        margin-bottom: 20px;
        text-align: center;
      }

      .service-preference-card {
        padding: 12px;
        margin-bottom: 20px;
      }

      .service-preference-card h3 {
        font-size: 1.1em;
      }

      .service-preference-card p {
        font-size: 13px;
      }

      .news,
      .events {
        margin-top: 30px;
      }

      .news-grid,
      .events-grid {
        grid-template-columns: 1fr;
        gap: 15px;
      }

      .news-item,
      .event-item {
        padding: 12px;
      }

      .news-item img,
      .event-item img {
        height: 150px;
      }

      .news-item h4,
      .event-item h4 {
        font-size: 1em;
      }

      .news-item p,
      .event-item p {
        font-size: 0.85em;
      }

      .btn {
        padding: 6px 12px;
        font-size: 0.8em;
        min-width: 80px;
      }
    }

    /* Small Mobile Styles */
    @media (max-width: 480px) {
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

      .main-content {
        padding: 15px 10px;
      }
    }

    /* Additional mobile protection - ensure no sidebar interference */
    @media (max-width: 768px) {
      .container {
        display: block !important;
      }

      /* Ensure no ghost clicks from sidebar */
      .sidebar a,
      .sidebar li,
      .sidebar ul {
        pointer-events: none !important;
        display: none !important;
        visibility: hidden !important;
        position: absolute !important;
        left: -9999px !important;
        z-index: -999 !important;
      }

      h2 {
        font-size: 1.2em;
        margin-bottom: 15px;
      }

      .service-preference-card {
        padding: 10px;
        margin-bottom: 15px;
      }

      .service-preference-card h3 {
        font-size: 1em;
      }

      .service-preference-card p {
        font-size: 12px;
      }

      .news,
      .events {
        margin-top: 25px;
      }

      .news-grid,
      .events-grid {
        gap: 12px;
      }

      .news-item,
      .event-item {
        padding: 10px;
      }

      .news-item img,
      .event-item img {
        height: 120px;
      }

      .news-item h4,
      .event-item h4 {
        font-size: 0.95em;
        margin: 8px 0 4px;
      }

      .news-item p,
      .event-item p {
        font-size: 0.8em;
        margin-bottom: 10px;
      }

      .btn {
        padding: 5px 10px;
        font-size: 0.75em;
        min-width: 70px;
      }
    }

    /* Large Desktop Styles */
    @media (min-width: 1200px) {
      .main-content {
        padding: 40px;
      }

      .news-grid,
      .events-grid {
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
      }

      .news-item img,
      .event-item img {
        height: 200px;
      }

      .news-item h4,
      .event-item h4 {
        font-size: 1.2em;
      }

      .news-item p,
      .event-item p {
        font-size: 0.95em;
      }
    }

    /* Ultra-wide Desktop Styles */
    @media (min-width: 1600px) {
      .container {
        max-width: 1400px;
        margin: 0 auto;
      }

      .news-grid,
      .events-grid {
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
      }
    }
  </style>
</head>

<body>
  <!-- Mobile Menu Toggle -->
  <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
    <div class="hamburger" id="hamburger">
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
      <h2>Hi, <?php echo htmlspecialchars($currentUsername ?? 'User'); ?>!</h2>
      
      <?php if (!$isAdmin): ?>
        <div class="service-preference-card">
          <h3>Your Service Preference: 
            <?php 
              switch($currentServicePreference) {
                case 'adopt_only': echo '🏡 Adoption Only'; break;
                case 'donate_only': echo '💖 Donation Only'; break;
                case 'both': echo '🌟 Both Adoption & Donation'; break;
                default: echo '🌟 Both Adoption & Donation'; break;
              }
            ?>
          </h3>
          <p>
            <?php 
              switch($currentServicePreference) {
                case 'adopt_only': echo 'You have access to adoption-related modules, AI bot, and inbox chat.'; break;
                case 'donate_only': echo 'You have access to donation-related modules.'; break;
                case 'both': echo 'You have access to both adoption and donation modules.'; break;
                default: echo 'You have access to both adoption and donation modules.'; break;
              }
            ?>
          </p>
        </div>
      <?php endif; ?>

      <section class="news">
        <h2>📰 Latest News</h2>
        <div class="news-grid">
          <?php if ($isAdmin || $currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
          <div class="news-item">
            <img src="images/step1_image.png" alt="News 1">
            <h4>Latest Updates in Adoption Services</h4>
            <p>Stay informed about the latest changes and improvements in our adoption process.</p>
            <a href="#" class="btn">Read More</a>
          </div>
          <div class="news-item">
            <img src="images/step2_image.png" alt="News 2">
            <h4>Adoption Success Stories</h4>
            <p>Read heartwarming stories from families who have completed their adoption journey.</p>
            <a href="#" class="btn">Read More</a>
          </div>
          <?php endif; ?>
          
          <?php if ($isAdmin || $currentServicePreference === 'donate_only' || $currentServicePreference === 'both'): ?>
          <div class="news-item">
            <img src="images/step3_image.png" alt="News 3">
            <h4>Donation Impact Stories</h4>
            <p>See how your donations are making a difference in children's lives.</p>
            <a href="#" class="btn">Read More</a>
          </div>
          <?php endif; ?>
          
          <div class="news-item">
            <img src="images/step4_image.png" alt="News 4">
            <h4>Important Announcements</h4>
            <p>Keep up with important announcements and policy changes.</p>
            <a href="#" class="btn">Read More</a>
          </div>
        </div>
      </section>

      <section class="events">
        <h2>📅 Upcoming Events</h2>
        <div class="events-grid">
          <?php if ($isAdmin || $currentServicePreference === 'adopt_only' || $currentServicePreference === 'both'): ?>
          <div class="event-item">
            <img src="images/step5_image.png" alt="Event 1">
            <h4>Adoption Information Session</h4>
            <p>Join us for an informative session about the adoption process and requirements.</p>
            <a href="#" class="btn">Learn More</a>
          </div>
          <div class="event-item">
            <img src="images/step6_image.png" alt="Event 2">
            <h4>Family Support Workshop</h4>
            <p>Participate in workshops designed to support adoptive families.</p>
            <a href="#" class="btn">Learn More</a>
          </div>
          <?php endif; ?>
          
          <?php if ($isAdmin || $currentServicePreference === 'donate_only' || $currentServicePreference === 'both'): ?>
          <div class="event-item">
            <img src="images/step7_image.png" alt="Event 3">
            <h4>Donation Drive Event</h4>
            <p>Join our community donation drive to help provide essentials for children in need.</p>
            <a href="#" class="btn">Learn More</a>
          </div>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>

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
    
    console.log('🔥 Dashboard: Firebase initialized for profile picture support');
  </script>

  <script>
    // Mobile dropdown menu functionality
    function toggleMobileMenu() {
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
    function detectMobileAndCleanup() {
      if (window.innerWidth <= 768) {
        document.body.classList.add('mobile-view');
        
        // COMPLETE SIDEBAR REMOVAL on mobile
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebar) {
          // Completely remove the sidebar from DOM on mobile
          sidebar.remove();
          console.log('✅ Dashboard: Sidebar completely removed from DOM on mobile');
        }
        
        // Extra safety: remove any remaining sidebar elements
        const remainingSidebarElements = document.querySelectorAll('.sidebar, .sidebar *, .sidebar a, .sidebar li, aside');
        remainingSidebarElements.forEach(el => {
          if (el.classList.contains('sidebar') || el.closest('.sidebar')) {
            el.remove();
          }
        });
        
        console.log('✅ Dashboard: Mobile mode activated - all sidebar elements removed');
      } else {
        document.body.classList.remove('mobile-view');
        // On desktop, if sidebar was removed, we might need to reload page
        // but for now, just ensure mobile menu is hidden
        const mobileMenu = document.getElementById('mobileDropdownMenu');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        if (mobileMenu) mobileMenu.style.display = 'none';
        if (mobileToggle) mobileToggle.style.display = 'none';
      }
    }

    // Run mobile detection on load with small delay
    setTimeout(() => {
      detectMobileAndCleanup();
    }, 100);

    // Close menu when clicking on a link
    document.querySelectorAll('.mobile-dropdown-menu a').forEach(link => {
      link.addEventListener('click', closeMobileMenu);
    });

    // Close menu on window resize if screen becomes larger
    window.addEventListener('resize', function() {
      detectMobileAndCleanup(); // Re-run mobile detection and cleanup
      if (window.innerWidth > 768) {
        closeMobileMenu();
      }
    });

    // Handle escape key to close menu
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeMobileMenu();
      }
    });

    // Close menu when clicking outside (but not on interactive elements)
    document.addEventListener('click', function(e) {
      const dropdownMenu = document.getElementById('mobileDropdownMenu');
      const hamburger = document.querySelector('.mobile-menu-toggle');
      
      // Don't close menu if clicking on buttons, links, or form elements
      if (e.target.tagName === 'BUTTON' || 
          e.target.tagName === 'A' || 
          e.target.tagName === 'INPUT' || 
          e.target.tagName === 'SELECT' || 
          e.target.tagName === 'TEXTAREA' ||
          e.target.closest('button') ||
          e.target.closest('a') ||
          e.target.closest('.btn') ||
          e.target.closest('.news-item') ||
          e.target.closest('.event-item')) {
        return;
      }
      
      if (dropdownMenu.classList.contains('active') && 
          !dropdownMenu.contains(e.target) && 
          !hamburger.contains(e.target)) {
        closeMobileMenu();
      }
    });
  </script>
</body>

</html>