<?php
require_once 'session_check.php';

// Redirect if user is not logged in
if (!$isLoggedIn) {
    header('Location: Signin.php');
    exit;
}

?>
<?php include('navbar.php'); ?>
<?php include('chatbot.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
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
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
            text-align: left;
        }

        .small-note {
            font-size: 0.85em;
            color: #555;
            margin-bottom: 15px;
        }

        .reminder {
            border-left: 4px solid red;
            background-color: #fdf4f4;
            padding: 10px 15px;
            margin-bottom: 20px;
        }

        .terms {
            max-width: 800px;
            margin: 0 auto 20px auto;
            text-align: justify;
            padding: 0 20px;
        }

        .terms h3 {
            text-align: center;
            font-size: 1.8em;
            margin-bottom: 15px;
        }

        .terms p {
            margin-bottom: 10px;
        }

        .checkbox-section {
            margin-top: 20px;
            text-align: center;
        }

        .checkbox-section input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 10px;
        }

        .checkbox-section label {
            font-size: 1em;
        }

        .btn {
            display: block;
            margin: 20px auto 0 auto;
            padding: 13px 15px;
            background-color: #7CB9E8;
            color: #FFFFFF;
            font-size: 1em;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #6ea4ce;
        }

        .bottom-note {
            font-size: 0.7em;
            text-align: center;
            margin-top: 5px;
            color: #666;
        }

        #appointment-details select,
        #appointment-details input[type="date"],
        #appointment-details input[type="text"] {
            /* Added style for text input */
            padding: 10px;
            margin: 5px 0 15px 0;
            font-size: 1em;
            width: 200px;
            display: inline-block;
            border: 1px solid #ccc;
            /* Add border for select and input */
            border-radius: 4px;
        }

        #appointment-details select {
            /* Specific width for select, adjust as needed */
            width: 210px;
            /* Slightly wider to accommodate dropdown arrow */
        }


        label {
            font-weight: bold;
        }

        /* Styles for the confirmation message box */
        .confirmation-box {
            background-color: #d4edda;
            /* Light green */
            color: #155724;
            /* Dark green text */
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
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

            .terms {
                padding: 0 10px;
            }

            #appointment-details select,
            #appointment-details input[type="date"],
            #appointment-details input[type="text"] {
                /* Added style for text input */
                width: 100%;
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
            <div id="main-content-area">
                <h2>Schedule an Appointment</h2>

                <div id="pending-appointment-message" class="alert-message alert-info" style="display: none;">
                    You already have a pending appointment. Please await confirmation or cancellation of your existing appointment before scheduling a new one. You can view your current appointments <a href="Appointments.php">here</a>.
                </div>

                <div id="not-logged-in-message" class="alert-message alert-error" style="display: none;">
                    You must be signed in to schedule an appointment. Please <a href="signin.php">Sign In</a>.
                </div>

                <div id="terms-section" style="display: none;">
                    <p class="small-note">
                        Welcome to the Adoption Assistance and Donation Management System. Please review all fields in the online form carefully and ensure that all information provided is complete, accurate, and truthful.
                    </p>

                    <div class="reminder">
                        <strong>Reminder:</strong><br>
                        Applicants are recommended to use Google or Yahoo email accounts in securing an appointment to avoid any technical incompatibilities.
                    </div>

                    <section class="terms">
                        <h3>Terms and Conditions</h3>
                        <p>This appointment and scheduling system allocates slots on a first-come, first-served basis.</p>
                        <p>Prospective Adoptive Parents (PAPs) are encouraged to book their appointments with the CSWDO officers as early as possible to secure their preferred schedule. Failure to attend a scheduled appointment without prior notice may require rebooking, subject to availability, and may cause delays in the adoption process.</p>
                        <p>Additionally, users must ensure that all uploaded documents are authentic, clear, and complete, as any falsified or inconsistent submission may result in rejection.</p>

                        <div class="checkbox-section">
                            <input type="checkbox" id="agree" required>
                            <label for="agree">By continuing, I acknowledge that I have read, understood, and agreed to these Terms and Conditions.</label>
                        </div>

                        <button class="btn" id="start-btn">Get Started</button>
                        <div class="bottom-note">
                            After agreeing to the Terms and Conditions above, you may start your online application by clicking “Get Started”
                        </div>
                    </section>
                </div>

                <div id="appointment-form" style="display: none;">
                    <div id="appointment-details">
                        <label for="appt-type">Appointment Type <span style="color: red;">*</span></label><br>
                        <select id="appt-type" required>
                            <option value="">--Select Appointment Type--</option>
                            <option value="Initial Consultation">Initial Consultation</option>
                            <option value="Submission of Documents">Submission of Documents</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Others">Others</option>
                        </select><br>

                        <label>Date & Time <span style="color: red;">*</span></label><br>
                        <input type="date" id="appt-date" required>
                        <select id="appt-time" required>
                            <option value="">--Select Time--</option>
                            <option value="08:00">8:00 A.M.</option>
                            <option value="09:00">9:00 A.M.</option>
                            <option value="10:00">10:00 A.M.</option>
                            <option value="11:00">11:00 A.M.</option>
                            <option value="13:00">1:00 P.M.</option>
                            <option value="14:00">2:00 P.M.</option>
                        </select><br>

                        <button class="btn" id="next-btn">Next</button>
                    </div>

                    <div id="confirmation-section" style="display: none; margin-top: 20px;">
                        <h3>Appointment Confirmation</h3>
                        <p>Your appointment request has been successfully submitted. However, please note that all appointments are subject to review and approval by the administrators of the foundation. A final confirmation will be sent via email and SMS once your appointment has been verified. Until confirmed, your appointment remains pending and may be subject to rescheduling based on availability.</p>
                        <p>Once your appointment is approved, you will receive a unique confirmation code via email. This code will be required to access your appointment schedule and any related details. Please ensure you keep this code secure, as it will be needed for verification on the day of your appointment.</p>
                        <button class="btn" id="submit-btn">Submit</button>
                    </div>
                    <div id="success-message" class="confirmation-box" style="display: none;">
                        Appointment successfully submitted! Redirecting...
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

    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    <!-- Include Simple Notifications for appointment scheduling -->
    
    <script>
        // Make session data available to JavaScript
        window.sessionUserId = '<?php echo $_SESSION['uid'] ?? ''; ?>';
        window.sessionUserEmail = '<?php echo $_SESSION['user_email'] ?? ''; ?>';
        window.sessionUserRole = '<?php echo $_SESSION['user_role'] ?? ''; ?>';
    </script>

    <script>
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };
        // Check if Firebase is already initialized
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const db = firebase.firestore(); // Initialize Firestore
        const auth = firebase.auth(); // Initialize Auth

        // Get elements for display control
        const termsSection = document.getElementById('terms-section');
        const appointmentForm = document.getElementById('appointment-form');
        const pendingAppointmentMessage = document.getElementById('pending-appointment-message');
        const notLoggedInMessage = document.getElementById('not-logged-in-message');

        // Function to hide all content sections initially
        function hideAllSections() {
            termsSection.style.display = 'none';
            appointmentForm.style.display = 'none';
            pendingAppointmentMessage.style.display = 'none';
            notLoggedInMessage.style.display = 'none';
        }

        // Function to check for existing pending appointments
        async function checkPendingAppointments(user) {
            hideAllSections(); // Hide everything first

            if (!user) {
                notLoggedInMessage.style.display = 'block';
                notLoggedInMessage.querySelector('a').href = 'signin.php';
                console.log('Firebase Auth: User not logged in, cannot schedule appointment.');
                return;
            }

            try {
                const appointmentsRef = db.collection('appointments');
                const querySnapshot = await appointmentsRef
                    .where('userId', '==', user.uid)
                    .where('status', '==', 'pending')
                    .get();

                if (!querySnapshot.empty) {
                    // User has a pending appointment
                    pendingAppointmentMessage.style.display = 'block';
                } else {
                    // No pending appointments, show terms and conditions
                    termsSection.style.display = 'block';
                }
            } catch (error) {
                console.error('Error checking for pending appointments:', error);
                // Display a generic error message, or allow scheduling if it's a transient error
                notLoggedInMessage.textContent = 'Error loading scheduling options. Please try again. ' + error.message;
                notLoggedInMessage.classList.remove('alert-error'); // Remove error style, add info style
                notLoggedInMessage.classList.add('alert-info');
                notLoggedInMessage.style.display = 'block';
            }
        }

        // Listen for Firebase Auth state changes
        auth.onAuthStateChanged(user => {
            checkPendingAppointments(user);
        });

        // Event listener for "Get Started" button
        document.getElementById('start-btn').addEventListener('click', function() {
            const checkbox = document.getElementById('agree');
            if (checkbox.checked) {
                termsSection.style.display = 'none';
                appointmentForm.style.display = 'block';
            } else {
                alert('Please agree to the Terms and Conditions before proceeding.');
            }
        });

        // Event listener for "Next" button in the form
        document.getElementById('next-btn').addEventListener('click', function() {
            const type = document.getElementById('appt-type').value; // Get value from select
            const date = document.getElementById('appt-date').value;
            const time = document.getElementById('appt-time').value;

            if (!type || !date || !time) {
                alert('Please fill in all required fields.');
                return;
            }

            // Hide the appointment details section and show the confirmation section
            document.getElementById('appointment-details').style.display = 'none';
            document.getElementById('confirmation-section').style.display = 'block';
        });

        // Event listener for "Submit" button
        document.getElementById('submit-btn').addEventListener('click', async function() {
            const user = auth.currentUser;

            if (!user) {
                alert('You must be logged in to schedule an appointment. Please sign in.');
                window.location.href = 'signin.php';
                return;
            }

            const username = user.displayName || user.email?.split('@')[0] || 'User';
            const userId = user.uid;
            const userEmail = user.email;

            const type = document.getElementById('appt-type').value; // Get value from select
            const date = document.getElementById('appt-date').value;
            const time = document.getElementById('appt-time').value; // This is now already "HH:mm" from the <option value>

            const appointmentCode = Date.now().toString() + Math.random().toString(36).substring(2, 8); // Define appointmentCode
            // Ensure the time is in 24-hour format (HH:mm) for consistent parsing
            const fullDateTimeString = `${date}T${time}:00`; // e.g., "2024-05-23T08:00:00"
            const scheduledDateObject = new Date(fullDateTimeString);

            // Crucially, check if the date object is valid
            if (isNaN(scheduledDateObject.getTime())) { // getTime() returns NaN for invalid dates
                console.error("Invalid date or time created:", fullDateTimeString);
                alert("Error: The selected date or time is invalid. Please select a valid date and time.");
                return;
            }

            const scheduledTimestamp = firebase.firestore.Timestamp.fromDate(scheduledDateObject);

            const appointmentData = {
                appointmentCode: appointmentCode,
                appointmentType: type,
                date: date, // Store as YYYY-MM-DD
                time: document.getElementById('appt-time').options[document.getElementById('appt-time').selectedIndex].text, // Store original display time (e.g., "8:00 A.M.")
                scheduledTimestamp: scheduledTimestamp, // Now a Timestamp object
                status: "pending", // Initial status
                userId: userId,
                username: username,
                userEmail: userEmail
            };

            try {
                const docRef = await db.collection("appointments").add(appointmentData); // Await the add operation
                console.log("Appointment submitted with ID: ", docRef.id);
                
                // Send notifications for appointment request using MOBILE APP LOGIC
                const appointmentDate = `${date} at ${document.getElementById('appt-time').options[document.getElementById('appt-time').selectedIndex].text}`;
                const appointmentTime = document.getElementById('appt-time').options[document.getElementById('appt-time').selectedIndex].text;
                
                // Send notification to user using MOBILE APP LOGIC
                sendAppointmentNotification('scheduled', {
                    userId: userId,
                    userName: username,
                    userEmail: userEmail,
                    appointmentDate: appointmentDate,
                    appointmentType: type,
                    appointmentCode: appointmentCode,
                    appointmentId: docRef.id
                });
                
                // Send appointment message via messaging system
                fetch('adoption_message_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'appointment_scheduled',
                        userId: userId,
                        appointmentId: docRef.id,
                        appointmentType: type,
                        appointmentDate: appointmentDate,
                        status: 'scheduled'
                    })
                }).catch(error => {
                    console.error('Error sending appointment message:', error);
                });
                
                document.getElementById('appointment-form').style.display = 'none';
                document.getElementById('success-message').style.display = 'block';

                setTimeout(() => {
                    window.location.href = 'Appointments.php';
                }, 3000);
            } catch (error) {
                console.error('Firestore Error:', error);
                alert('There was an error submitting your appointment: ' + error.message);
            }
        });

        // Send appointment notification - MOBILE APP LOGIC
        function sendAppointmentNotification(status, appointmentData) {
            try {
                console.log('📅 MOBILE APP LOGIC: Sending appointment notification:', status, appointmentData);
                
                if (!appointmentData || !appointmentData.userId) {
                    console.log('❌ MOBILE APP LOGIC: No appointment data for notification');
                    return;
                }
                
                // Send notification to user using MOBILE APP LOGIC (super_simple_notifications.php)
                fetch('super_simple_notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'send_appointment_notification',
                        userId: appointmentData.userId,
                        status: status,
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
                        console.log(`✅ MOBILE APP LOGIC: ${status} appointment notification sent successfully`);
                    } else {
                        console.log(`❌ MOBILE APP LOGIC: Failed to send ${status} appointment notification:`, result.error);
                    }
                })
                .catch(error => {
                    console.log(`❌ MOBILE APP LOGIC: Appointment notification error:`, error);
                });
            } catch (error) {
                console.log(`❌ MOBILE APP LOGIC: Send appointment notification error:`, error);
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