<?php
require_once 'session_check.php';

// Simple redirect if not logged in
if (!$isLoggedIn) {
    header('Location: Signin.php');
    exit;
}

// Optional: Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php include('navbar.php'); // Include your universal navigation bar ?>
<?php include('chatbot.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ally</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .admin-section {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .admin-section h2 {
            margin-top: 0;
            color: #333;
            font-size: 2em;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        /* Loading and Error Messages */
        #admin-status-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            display: none; /* Hidden by default */
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

        /* Available Children Styles */
        .children-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .child-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            background-color: #fefefe;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            cursor: pointer; /* Make it clickable */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .child-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .child-card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #4CAF50; /* A pleasant green for names */
            font-size: 1.8em;
        }

        .child-card p {
            margin: 5px 0;
            font-size: 0.95em;
        }

        .child-card .meta-info {
            font-size: 0.85em;
            color: #777;
            margin-bottom: 10px;
        }

        .child-card .status {
            font-style: italic;
            color: #87CEEB; /* Light blue for "Status" label */
            font-weight: normal; /* Override bold */
            margin-top: 10px;
            margin-bottom: 10px;
            padding-top: 5px;
            border-top: 1px dashed #eee;
        }
        .child-card .status span {
            font-style: normal; /* Make the actual status text not italic */
            font-weight: bold; /* Make the actual status text bold */
        }
        .status-matched {
            color: #FF6347; /* Tomato red/orange for Matched */
        }
        .status-available {
            color: #28a745; /* Bootstrap green for Available */
        }
        .status-pending {
            color: #FFC107; /* Bootstrap yellow for Pending */
        }


        .child-card .description {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .child-card .characteristics {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 20px;
        }

        .child-card .actions {
            margin-top: auto; /* Push buttons to the bottom */
            display: flex;
            gap: 10px;
            justify-content: flex-end; /* Align buttons to the right */
        }

        .child-card .actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s ease;
        }

        .child-card .actions .edit-btn {
            background-color: #007bff; /* Blue */
            color: white;
        }
        .child-card .actions .edit-btn:hover {
            background-color: #0056b3;
        }

        .child-card .actions .delete-btn {
            background-color: #dc3545; /* Red */
            color: white;
        }
        .child-card .actions .delete-btn:hover {
            background-color: #bd2130;
        }

        .add-child-btn {
            background-color: #28a745; /* Green */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 20px;
            transition: background-color 0.2s ease;
            display: block; /* Make it a block element to control margin */
            margin-left: auto; /* Center or push right */
            margin-right: auto;
        }
        .add-child-btn:hover {
            background-color: #218838;
        }


        /* Registered Users Styles */
        .user-list {
            list-style: none;
            padding: 0;
        }

        .user-list li {
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
        }

        .user-list li:last-child {
            border-bottom: none;
        }

        .user-list a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .user-list a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* Accepted Matches Styles */
        .match-list {
            list-style: none;
            padding: 0;
        }

        .match-list li {
            padding: 15px 0;
            border-bottom: 1px dashed #eee;
        }
        .match-list li:last-child {
            border-bottom: none;
        }

        .match-item-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            color: #333;
            font-weight: 500;
        }
        .match-item-summary:hover {
            color: #007bff;
        }
        /* Removed styling for .match-item-summary .cancel-match-btn as it's no longer present */


        /* Modal Styles */
        .modal {
            /* CORRECTED: Changed display: flex; to display: none; */
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.6); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 80%; /* Could be adjusted */
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
            animation-name: animatetop;
            animation-duration: 0.4s;
        }

        /* Add Animation */
        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 15px;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .modal-content p {
            margin-bottom: 10px;
            font-size: 1.05em;
        }

        .modal-content p strong {
            color: #555;
            display: inline-block;
            min-width: 120px; /* Align labels */
        }

        .modal-content .modal-actions {
            text-align: right;
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .modal-content .modal-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s ease;
        }

        .modal-content .modal-actions .cancel-btn { /* This style is still here for matchDetailsModal */
            background-color: #dc3545;
            color: white;
        }
        .modal-content .modal-actions .cancel-btn:hover { /* This style is still here for matchDetailsModal */
            background-color: #bd2130;
        }
        .modal-content .modal-actions .close-modal-btn {
            background-color: #6c757d;
            color: white;
            margin-left: 10px;
        }
        .modal-content .modal-actions .close-modal-btn:hover {
            background-color: #5a6268;
        }

        /* Profile Modal Specific Styles (User Profile Modal) */
        #userProfileModal .profile-modal-picture-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 20px;
            border: 3px solid #7CB9E8;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        #userProfileModal .profile-modal-picture {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        #userProfileModal .modal-profile-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            text-align: left;
            margin-top: 20px;
        }
        #userProfileModal .modal-profile-info p {
            margin: 0;
            padding: 5px 0;
            border-bottom: 1px dotted #f0f0f0;
        }
        #userProfileModal .modal-profile-info p:last-child {
            border-bottom: none;
        }

        /* Child Details Modal Specific Styles */
        #childDetailsModal .child-details-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            text-align: left;
            margin-top: 20px;
        }
        #childDetailsModal .child-details-info p {
            margin: 0;
            padding: 5px 0;
            border-bottom: 1px dotted #f0f0f0;
        }
        #childDetailsModal .child-details-info p:last-child {
            border-bottom: none;
        }

        /* Child Form Modal Specific Styles - New/Enhanced Styling */
        #childFormModal .modal-content form p {
            margin-bottom: 15px; /* More space between form fields */
        }

        #childFormModal .modal-content form label {
            display: block; /* Labels on their own line */
            margin-bottom: 8px;
            font-weight: 600; /* Slightly bolder labels */
            color: #444;
        }

        #childFormModal .modal-content form input[type="text"],
        #childFormModal .modal-content form input[type="number"],
        #childFormModal .modal-content form select,
        #childFormModal .modal-content form textarea {
            width: calc(100% - 20px); /* Full width with padding considered */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box; /* Include padding in width calculation */
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        #childFormModal .modal-content form input[type="text"]:focus,
        #childFormModal .modal-content form input[type="number"]:focus,
        #childFormModal .modal-content form select:focus,
        #childFormModal .modal-content form textarea:focus {
            border-color: #6ea4ce; /* Highlight on focus */
            box-shadow: 0 0 5px rgba(110, 164, 206, 0.5);
            outline: none;
        }

        #childFormModal .modal-content form textarea {
            resize: vertical; /* Allow vertical resizing */
            min-height: 80px; /* Minimum height for textarea */
        }

        #childFormModal .modal-actions button[type="submit"] {
            background-color: #28a745; /* Green for Save */
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        #childFormModal .modal-actions button[type="submit"]:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        #childFormModal .modal-actions button[type="button"] { /* Cancel button */
            background-color: #6c757d;
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        #childFormModal .modal-actions button[type="button"]:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 2px solid #D9D9D9;
                position: static;
                padding: 15px 20px;
            }
            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px 20px;
            }
            .sidebar li {
                margin-bottom: 0;
            }
            .main-content {
                padding: 15px;
            }
            .admin-section {
                padding: 15px;
            }
            .admin-section h2 {
                font-size: 1.8em;
            }
            .children-grid {
                grid-template-columns: 1fr; /* Single column on small screens */
            }
            .modal-content {
                width: 95%;
                padding: 20px;
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
                <li><a href="History.php">📜 History</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main class="main-content">
            <div id="admin-status-message" class="alert-info">Loading admin dashboard...</div>

            <div id="admin-content" style="display: none;">
                <div class="admin-section">
                    <h2>Available Children</h2>
                    <div id="children-list" class="children-grid">
                        <p id="no-children-message" style="display: none;">No children currently registered.</p>
                    </div>
                    <button id="add-new-child-btn" class="add-child-btn">Add New Child</button>
                </div>

                <div class="admin-section">
                    <h2>Registered Users</h2>
                    <ul id="users-list" class="user-list">
                        <p id="no-users-message" style="display: none;">No users currently registered.</p>
                    </ul>
                </div>

                <div class="admin-section">
                    <h2>Accepted Matches</h2>
                    <ul id="accepted-matches-list" class="match-list">
                        <p id="no-accepted-matches-message" style="display: none;">No accepted matches found.</p>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <div id="userProfileModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3>User Profile Details</h3>
            <div class="profile-modal-picture-container">
                <img id="modal-profile-picture" src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png" alt="Profile Picture" class="profile-modal-picture">
            </div>
            <div class="modal-profile-info">
                <p><strong>First Name:</strong> <span id="modal-user-firstName"></span></p>
                <p><strong>Middle Name:</strong> <span id="modal-user-middleName"></span></p>
                <p><strong>Last Name:</strong> <span id="modal-user-lastName"></span></p>
                <p><strong>Username:</strong> <span id="modal-user-username"></span></p>
                <p><strong>Email:</strong> <span id="modal-user-email"></span></p>
                <p><strong>Birthdate:</strong> <span id="modal-user-birthdate"></span></p>
                <p><strong>Role:</strong> <span id="modal-user-role"></span></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="close-modal-btn">Close</button>
            </div>
        </div>
    </div>

    <div id="matchDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3>Accepted Match Details</h3>
            <p><strong>Username:</strong> <span id="modal-match-username"></span></p>
            <p><strong>Matched Child:</strong> <span id="modal-match-child-name"></span></p>
            <p><strong>Status:</strong> <span id="modal-match-status">Accepted</span></p>
            <p><strong>Matched Preferences:</strong> <span id="modal-match-preferences"></span></p>
            <div class="modal-actions">
                <!-- Removed Cancel Match button from here -->
                <button type="button" class="close-modal-btn">Close</button>
            </div>
        </div>
    </div>

    <div id="childFormModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3 id="child-form-title">Add New Child</h3>
            <form id="child-form">
                <input type="hidden" id="child-id">
                <p><label for="child-name">Name:</label><br><input type="text" id="child-name" required></p>
                <p><label for="child-gender">Gender:</label><br>
                    <select id="child-gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </p>
                <p><label for="child-age">Age:</label><br>
                    <select id="child-age" required>
                        <option value="">Select Age Group</option>
                        <option value="Infant (0-2)">Infant (0-2)</option>
                        <option value="Toddler (3-4)">Toddler (3-4)</option>
                        <option value="Child (5-10)">Child (5-10)</option>
                        <option value="Pre-teen (11-12)">Pre-teen (11-12)</option>
                    </select>
                </p>
                <p><label for="child-skin">Skin Color:</label><br>
                    <select id="child-skin" required>
                        <option value="">Select Skin Color</option>
                        <option value="Light">Light</option>
                        <option value="Medium">Medium</option>
                        <option value="Dark">Dark</option>
                    </select>
                </p>
                <p><label for="child-characteristics">Characteristics (Hold Ctrl/Cmd to select multiple):</label><br>
                    <select id="child-characteristics" multiple size="4" required>
                        <option value="Friendly">Friendly</option>
                        <option value="Calm">Calm</option>
                        <option value="Energetic">Energetic</option>
                        <option value="Playful">Playful</option>
                    </select>
                </p>
                <p><label for="child-description">Description:</label><br><textarea id="child-description" rows="4" required></textarea></p>
                <p><label for="child-size">Size:</label><br>
                    <select id="child-size" required>
                        <option value="">Select Size</option>
                        <option value="Small">Small</option>
                        <option value="Medium">Medium</option>
                        <option value="Large">Large</option>
                    </select>
                </p>
                <p><label for="child-status">Status:</label><br>
                    <select id="child-status" required>
                        <option value="Available">Available</option>
                        <option value="Matched">Matched</option>
                        <option value="Pending">Pending</option>
                    </select>
                </p>
                <div class="modal-actions">
                    <button type="submit">Save Child</button>
                    <button type="button" class="close-modal-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Child Details Modal -->
    <div id="childDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3 style="text-align: left; margin-bottom: 5px;">Child Details</h3>
            <hr>
            <h3 style="text-align: center; margin-top: 15px;">Child Profile</h3>
            <div class="child-details-info">
                <p><strong>Name:</strong> <span id="modal-child-details-name"></span></p>
                <p><strong>Age:</strong> <span id="modal-child-details-age"></span></p>
                <p><strong>Gender:</strong> <span id="modal-child-details-gender"></span></p>
                <p><strong>Status:</strong> <span id="modal-child-details-status"></span></p>
                <p><strong>Skin Color:</strong> <span id="modal-child-details-skin"></span></p>
                <p><strong>Characteristics:</strong> <span id="modal-child-details-characteristics"></span></p>
                <p><strong>Size:</strong> <span id="modal-child-details-size"></span></p>
                <p><strong>Description:</strong> <span id="modal-child-details-description"></span></p>
            </div>
            <div class="modal-actions">
                <button id="modal-child-edit-btn" class="edit-btn">Edit</button>
                <button id="modal-child-delete-btn" class="delete-btn">Delete</button>
                <button type="button" class="close-modal-btn">Close</button>
            </div>
        </div>
    </div>


    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-storage.js"></script>

    <script>
        // Ensure this firebaseConfig is correct for your project
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

        const adminStatusMessage = document.getElementById('admin-status-message');
        const adminContentDiv = document.getElementById('admin-content');
        const childrenListDiv = document.getElementById('children-list');
        const noChildrenMessage = document.getElementById('no-children-message');
        const usersListUl = document.getElementById('users-list');
        const noUsersMessage = document.getElementById('no-users-message');
        const acceptedMatchesListUl = document.getElementById('accepted-matches-list');
        const noAcceptedMatchesMessage = document.getElementById('no-accepted-matches-message');
        const addNewChildBtn = document.getElementById('add-new-child-btn');

        // Modals and their elements
        const userProfileModal = document.getElementById('userProfileModal');
        const matchDetailsModal = document.getElementById('matchDetailsModal');
        const childFormModal = document.getElementById('childFormModal');
        const childDetailsModal = document.getElementById('childDetailsModal'); // New child details modal

        // IMPORTANT: Ensure all modals are hidden on load
        if (userProfileModal) {
            userProfileModal.style.display = 'none';
        }
        if (matchDetailsModal) {
            matchDetailsModal.style.display = 'none';
        }
        if (childFormModal) {
            childFormModal.style.display = 'none';
        }
        if (childDetailsModal) { // Hide new modal
            childDetailsModal.style.display = 'none';
        }

        const modalCloseButtons = document.querySelectorAll('.modal .close-button, .modal .close-modal-btn');
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', () => {
                userProfileModal.style.display = 'none';
                matchDetailsModal.style.display = 'none';
                childFormModal.style.display = 'none';
                childDetailsModal.style.display = 'none'; // Close new modal
            });
        });

        // Close modals if clicked outside content
        window.addEventListener('click', (event) => {
            if (event.target == userProfileModal) {
                userProfileModal.style.display = 'none';
            }
            if (event.target == matchDetailsModal) {
                matchDetailsModal.style.display = 'none';
            }
            if (event.target == childFormModal) {
                childFormModal.style.display = 'none';
            }
            if (event.target == childDetailsModal) { // Close new modal if clicked outside
                childDetailsModal.style.display = 'none';
            }
        });

        // Helper function for status messages
        function displayAdminStatusMessage(message, type) {
            adminStatusMessage.textContent = message;
            adminStatusMessage.className = `alert-message alert-${type}`;
            adminStatusMessage.style.display = 'block';
        }

        // --- Fetch and Display Functions ---

        async function fetchAndDisplayChildren() {
            displayAdminStatusMessage('Fetching children data...', 'info');
            childrenListDiv.innerHTML = ''; // Clear previous content
            noChildrenMessage.style.display = 'none';

            try {
                const childrenSnapshot = await db.collection('children').get();
                if (childrenSnapshot.empty) {
                    noChildrenMessage.style.display = 'block';
                    displayAdminStatusMessage('No children found.', 'info');
                    return;
                }

                childrenSnapshot.forEach(doc => {
                    const child = doc.data();
                    const childId = doc.id;
                    const childCard = document.createElement('div');
                    childCard.className = 'child-card';
                    childCard.dataset.id = childId; // Store child ID on the card

                    const statusClass = child.status === 'Matched' ? 'status-matched' :
                                        child.status === 'Available' ? 'status-available' :
                                        'status-pending'; // Default for Pending or other

                    childCard.innerHTML = `
                        <h3>${child.name || 'N/A'}</h3>
                        <p class="meta-info">${child.age || 'N/A'} years old, ${child.gender || 'N/A'}, ${child.skin || 'N/A'} skin</p>
                        <p class="status">Status: <span class="${statusClass}">${child.status || 'N/A'}</span></p>
                        <p class="characteristics"><strong>Characteristics:</strong> ${child.characteristics ? (Array.isArray(child.characteristics) ? child.characteristics.join(', ') : child.characteristics) : 'N/A'}</p>
                        <p class="description"><strong>Description:</strong> ${child.description || 'N/A'}</p>
                        <p><strong>Size:</strong> ${child.size || 'N/A'}</p>
                        <div class="actions">
                            <button class="edit-btn" data-id="${childId}">Edit</button>
                            <button class="delete-btn" data-id="${childId}">Delete</button>
                        </div>
                    `;
                    childrenListDiv.appendChild(childCard);
                });

                // Attach event listeners for click on child card to open details modal
                childrenListDiv.querySelectorAll('.child-card').forEach(card => {
                    card.addEventListener('click', (e) => {
                        // Prevent opening details modal if edit or delete button was clicked directly
                        if (!e.target.classList.contains('edit-btn') && !e.target.classList.contains('delete-btn')) {
                            openChildDetailsModal(card.dataset.id);
                        }
                    });
                });

                // Re-attach event listeners for edit/delete buttons
                // These are now handled within the Child Details Modal but kept here for existing cards too
                childrenListDiv.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.stopPropagation(); // Prevent card click event from firing
                        openChildFormModal(e.target.dataset.id);
                    });
                });
                childrenListDiv.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.stopPropagation(); // Prevent card click event from firing
                        deleteChild(e.target.dataset.id);
                    });
                });

                displayAdminStatusMessage('Children data loaded.', 'success');
                setTimeout(() => adminStatusMessage.style.display = 'none', 2000);

            } catch (error) {
                console.error('Error fetching children:', error);
                displayAdminStatusMessage('Error loading children data: ' + error.message, 'error');
            }
        }

        async function fetchAndDisplayUsers() {
            displayAdminStatusMessage('Fetching registered users...', 'info');
            usersListUl.innerHTML = ''; // Clear previous content
            noUsersMessage.style.display = 'none';

            try {
                const usersSnapshot = await db.collection('users').get();
                if (usersSnapshot.empty) {
                    noUsersMessage.style.display = 'block';
                    displayAdminStatusMessage('No registered users found.', 'info');
                    return;
                }

                usersSnapshot.forEach(doc => {
                    const user = doc.data();
                    const userId = doc.id;
                    const listItem = document.createElement('li');
                    const userName = user.firstName && user.lastName ? `${user.firstName} ${user.lastName}` : user.username || user.email || 'Unknown User';
                    listItem.innerHTML = `<a href="#" data-id="${userId}">${userName}</a>`;
                    usersListUl.appendChild(listItem);
                });

                // Attach event listeners to user names
                usersListUl.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        openUserProfileModal(e.target.dataset.id);
                    });
                });

                displayAdminStatusMessage('Users data loaded.', 'success');
                setTimeout(() => adminStatusMessage.style.display = 'none', 2000);

            } catch (error) {
                console.error('Error fetching users:', error);
                displayAdminStatusMessage('Error loading users data: ' + error.message, 'error');
            }
        }

        async function fetchAndDisplayAcceptedMatches() {
            displayAdminStatusMessage('Fetching accepted matches...', 'info');
            acceptedMatchesListUl.innerHTML = ''; // Clear previous content
            noAcceptedMatchesMessage.style.display = 'none';

            try {
                const matchesSnapshot = await db.collection('matching_preferences')
                                                  .where('status', '==', 'accepted')
                                                  .get();

                if (matchesSnapshot.empty) {
                    noAcceptedMatchesMessage.style.display = 'block';
                    displayAdminStatusMessage('No accepted matches found.', 'info');
                    return;
                }

                const matchesPromises = matchesSnapshot.docs.map(async doc => {
                    const match = doc.data();
                    const matchId = doc.id;
                    const senderId = match.senderId;
                    const childId = match.matchedChildDetails?.id;

                    let userName = 'N/A';
                    // Fetch username from users collection using senderId
                    if (senderId) {
                        const userDoc = await db.collection('users').doc(senderId).get();
                        if (userDoc.exists) {
                            userName = userDoc.data().username || userDoc.data().email || 'N/A';
                        }
                    }

                    const childName = match.matchedChildDetails?.name || 'N/A';

                    const listItem = document.createElement('li');
                    listItem.innerHTML = `
                        <div class="match-item-summary" data-id="${matchId}" data-user-id="${senderId || ''}" data-child-id="${childId || ''}">
                            <span>User: ${userName} | Child: ${childName}</span>
                            <!-- Removed the Cancel Match button from here -->
                        </div>
                    `;
                    acceptedMatchesListUl.appendChild(listItem);
                });

                await Promise.all(matchesPromises); // Wait for all user/child fetches

                // Attach event listeners for summary click (no cancel button in list anymore)
                acceptedMatchesListUl.querySelectorAll('.match-item-summary').forEach(div => {
                    div.addEventListener('click', (e) => {
                        openMatchDetailsModal(div.dataset.id, div.dataset.userId, div.dataset.childId);
                    });
                });
                // Removed event listener for .cancel-match-btn as it's no longer present

                displayAdminStatusMessage('Accepted matches loaded.', 'success');
                setTimeout(() => adminStatusMessage.style.display = 'none', 2000);

            } catch (error) {
                console.error('Error fetching accepted matches:', error);
                displayAdminStatusMessage('Error loading accepted matches: ' + error.message, 'error');
            }
        }


        // --- Modal Functions ---

        async function openUserProfileModal(userId) {
            const modalProfilePicture = document.getElementById('modal-profile-picture');
            const modalUserFirstName = document.getElementById('modal-user-firstName');
            const modalUserMiddleName = document.getElementById('modal-user-middleName');
            const modalUserLastName = document.getElementById('modal-user-lastName');
            const modalUserUsername = document.getElementById('modal-user-username');
            const modalUserEmail = document.getElementById('modal-user-email');
            const modalUserBirthdate = document.getElementById('modal-user-birthdate');
            const modalUserRole = document.getElementById('modal-user-role');

            modalProfilePicture.src = 'https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png'; // Reset to placeholder

            try {
                const userDoc = await db.collection('users').doc(userId).get();
                if (userDoc.exists) {
                    const userData = userDoc.data();
                    modalUserFirstName.textContent = userData.firstName || 'N/A';
                    modalUserMiddleName.textContent = userData.middleName || 'N/A';
                    modalUserLastName.textContent = userData.lastName || 'N/A';
                    modalUserUsername.textContent = userData.username || 'N/A';
                    modalUserEmail.textContent = userData.email || 'N/A';
                    modalUserBirthdate.textContent = userData.birthdate || 'N/A';
                    modalUserRole.textContent = userData.role || 'N/A';
                    if (userData.profilePictureURL) {
                        modalProfilePicture.src = userData.profilePictureURL;
                    }
                } else {
                    console.warn('User not found:', userId);
                    // Use a custom message box instead of alert()
                    displayAdminStatusMessage('User profile not found.', 'error');
                    return;
                }
            } catch (error) {
                console.error('Error fetching user profile for modal:', error);
                // Use a custom message box instead of alert()
                displayAdminStatusMessage('Error loading user profile: ' + error.message, 'error');
                return;
            }

            userProfileModal.style.display = 'flex';
        }

        async function openMatchDetailsModal(matchId, userId, childId) {
            // Store matchId globally or pass it to cancelMatch function
            matchDetailsModal.dataset.currentMatchId = matchId;
            // Also store childId for easier access during cancel operation
            matchDetailsModal.dataset.currentChildId = childId;

            const modalMatchUsername = document.getElementById('modal-match-username');
            const modalMatchChildName = document.getElementById('modal-match-child-name');
            const modalMatchStatus = document.getElementById('modal-match-status'); // Get status span
            const modalMatchPreferences = document.getElementById('modal-match-preferences');

            // Reset content to loading state
            modalMatchUsername.textContent = 'Loading...';
            modalMatchChildName.textContent = 'Loading...';
            modalMatchStatus.textContent = 'Loading...';
            modalMatchPreferences.textContent = 'Loading...';


            try {
                const matchDoc = await db.collection('matching_preferences').doc(matchId).get();
                if (matchDoc.exists) {
                    const matchData = matchDoc.data();

                    let fetchedUsername = 'N/A';
                    if (matchData.senderId) {
                        const userDoc = await db.collection('users').doc(matchData.senderId).get();
                        if (userDoc.exists) {
                            fetchedUsername = userDoc.data().username || userDoc.data().email || 'N/A';
                        }
                    }
                    modalMatchUsername.textContent = fetchedUsername;

                    modalMatchChildName.textContent = matchData.matchedChildDetails?.name || 'N/A';
                    modalMatchStatus.textContent = matchData.status || 'N/A';

                    // Construct preferences string from user's preferences
                    const userPreferences = [];
                    if (matchData.preferredAge) userPreferences.push(`Age: ${matchData.preferredAge}`);
                    if (matchData.genderPreference) userPreferences.push(`Gender: ${matchData.genderPreference}`);
                    if (matchData.skinColorPreference) userPreferences.push(`Skin Color: ${matchData.skinColorPreference}`);
                    if (matchData.preferredSize) userPreferences.push(`Size: ${matchData.preferredSize}`);
                    if (matchData.characteristicsPreference) userPreferences.push(`Characteristics: ${matchData.characteristicsPreference}`);
                    if (matchData.otherPreferences) userPreferences.push(`Other: ${matchData.otherPreferences}`);

                    modalMatchPreferences.textContent = userPreferences.length > 0 ? userPreferences.join('; ') : 'No specific preferences recorded.';

                } else {
                    console.warn('Match details not found for ID:', matchId);
                    // Use a custom message box instead of alert()
                    displayAdminStatusMessage('Match details not found.', 'error');
                    return;
                }
            } catch (error) {
                console.error('Error fetching match details:', error);
                // Use a custom message box instead of alert()
                displayAdminStatusMessage('Error loading match details: ' + error.message, 'error');
                return;
            }

            matchDetailsModal.style.display = 'flex';
        }

        async function openChildFormModal(childId = null) {
            const formTitle = document.getElementById('child-form-title');
            const childIdField = document.getElementById('child-id');
            const childNameField = document.getElementById('child-name');
            const childAgeField = document.getElementById('child-age'); // This is now a select
            const childGenderField = document.getElementById('child-gender');
            const childSkinField = document.getElementById('child-skin'); // This is now a select
            const childDescriptionField = document.getElementById('child-description');
            const childCharacteristicsField = document.getElementById('child-characteristics'); // This is now a multi-select
            const childStatusField = document.getElementById('child-status');
            const childSizeField = document.getElementById('child-size'); // New size field
            const childForm = document.getElementById('child-form');

            // Reset form fields
            childForm.reset();
            childIdField.value = '';
            formTitle.textContent = 'Add New Child';

            // Clear previous selections for multi-select
            Array.from(childCharacteristicsField.options).forEach(option => {
                option.selected = false;
            });

            if (childId) {
                formTitle.textContent = 'Edit Child';
                childIdField.value = childId;
                try {
                    const childDoc = await db.collection('children').doc(childId).get();
                    if (childDoc.exists) {
                        const childData = childDoc.data();
                        childNameField.value = childData.name || '';
                        childAgeField.value = childData.age || ''; // Set value for select
                        childGenderField.value = childData.gender || '';
                        childSkinField.value = childData.skin || ''; // Set value for select
                        childDescriptionField.value = childData.description || '';

                        // Set values for multi-select characteristics
                        if (childData.characteristics && Array.isArray(childData.characteristics)) {
                            Array.from(childCharacteristicsField.options).forEach(option => {
                                if (childData.characteristics.includes(option.value)) {
                                    option.selected = true;
                                }
                            });
                        }

                        childStatusField.value = childData.status || 'Available';
                        childSizeField.value = childData.size || ''; // Set value for size select
                    } else {
                        // Use a custom message box instead of alert()
                        displayAdminStatusMessage('Child not found for editing.', 'error');
                        return;
                    }
                } catch (error) {
                    console.error('Error fetching child for edit:', error);
                    // Use a custom message box instead of alert()
                    displayAdminStatusMessage('Error loading child data for edit: ' + error.message, 'error');
                    return;
                }
            }
            childFormModal.style.display = 'flex';
        }

        // New function to open child details modal
        async function openChildDetailsModal(childId) {
            const modalChildName = document.getElementById('modal-child-details-name');
            const modalChildAge = document.getElementById('modal-child-details-age');
            const modalChildGender = document.getElementById('modal-child-details-gender');
            const modalChildStatus = document.getElementById('modal-child-details-status');
            const modalChildSkin = document.getElementById('modal-child-details-skin');
            const modalChildCharacteristics = document.getElementById('modal-child-details-characteristics');
            const modalChildSize = document.getElementById('modal-child-details-size');
            const modalChildDescription = document.getElementById('modal-child-details-description');
            const modalChildEditBtn = document.getElementById('modal-child-edit-btn');
            const modalChildDeleteBtn = document.getElementById('modal-child-delete-btn');

            // Set data-id on the modal for edit/delete buttons to access
            childDetailsModal.dataset.currentChildId = childId;

            // Reset content
            modalChildName.textContent = 'Loading...';
            modalChildAge.textContent = 'Loading...';
            modalChildGender.textContent = 'Loading...';
            modalChildStatus.textContent = 'Loading...';
            modalChildSkin.textContent = 'Loading...';
            modalChildCharacteristics.textContent = 'Loading...';
            modalChildSize.textContent = 'Loading...';
            modalChildDescription.textContent = 'Loading...';

            try {
                const childDoc = await db.collection('children').doc(childId).get();
                if (childDoc.exists) {
                    const childData = childDoc.data();
                    modalChildName.textContent = childData.name || 'N/A';
                    modalChildAge.textContent = childData.age || 'N/A';
                    modalChildGender.textContent = childData.gender || 'N/A';
                    modalChildStatus.textContent = childData.status || 'N/A';
                    modalChildSkin.textContent = childData.skin || 'N/A';
                    modalChildCharacteristics.textContent = childData.characteristics ? (Array.isArray(childData.characteristics) ? childData.characteristics.join(', ') : childData.characteristics) : 'N/A';
                    modalChildSize.textContent = childData.size || 'N/A'; // Display the size
                    modalChildDescription.textContent = childData.description || 'N/A';

                    // Attach event listeners to buttons within this modal
                    modalChildEditBtn.onclick = () => {
                        childDetailsModal.style.display = 'none'; // Close details modal first
                        openChildFormModal(childId);
                    };
                    modalChildDeleteBtn.onclick = () => {
                        childDetailsModal.style.display = 'none'; // Close details modal first
                        deleteChild(childId);
                    };

                } else {
                    console.warn('Child not found:', childId);
                    // Use a custom message box instead of alert()
                    displayAdminStatusMessage('Child details not found.', 'error');
                    return;
                }
            } catch (error) {
                console.error('Error fetching child details for modal:', error);
                // Use a custom message box instead of alert()
                displayAdminStatusMessage('Error loading child details: ' + error.message, 'error');
                return;
            }

            childDetailsModal.style.display = 'flex'; // Show the modal
        }

        // --- CRUD Operations for Children ---

        document.getElementById('child-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const childId = document.getElementById('child-id').value;
            const name = document.getElementById('child-name').value.trim();
            const age = document.getElementById('child-age').value; // Get value from select
            const gender = document.getElementById('child-gender').value;
            const skin = document.getElementById('child-skin').value; // Get value from select
            const characteristics = Array.from(document.getElementById('child-characteristics').selectedOptions).map(option => option.value); // Get values from multi-select
            const description = document.getElementById('child-description').value.trim();
            const size = document.getElementById('child-size').value; // Get value from select
            const status = document.getElementById('child-status').value;

            // Basic validation for new fields and updated types
            if (!name || !age || !gender || !skin || characteristics.length === 0 || !description || !size || !status) {
                // Use a custom message box instead of alert()
                displayAdminStatusMessage('Please fill in all child fields correctly, including selecting options for Age, Skin Color, Characteristics, and Size.', 'error');
                return;
            }

            const childData = {
                name,
                age, // Now a string (e.g., "Infant (0-2)")
                gender,
                skin, // Now a string (e.g., "Light")
                description,
                characteristics, // Now an array of strings
                size, // New field
                status,
                lastUpdated: firebase.firestore.FieldValue.serverTimestamp() // Update timestamp
            };

            displayAdminStatusMessage('Saving child data...', 'info');
            try {
                if (childId) {
                    // Update existing child
                    await db.collection('children').doc(childId).update(childData);
                    displayAdminStatusMessage('Child updated successfully!', 'success');
                } else {
                    // Add new child
                    await db.collection('children').add({
                        ...childData,
                        createdAt: firebase.firestore.FieldValue.serverTimestamp() // Creation timestamp
                    });
                    displayAdminStatusMessage('Child added successfully!', 'success');
                }
                childFormModal.style.display = 'none'; // Close modal
                fetchAndDisplayChildren(); // Refresh the list
            } catch (error) {
                console.error('Error saving child:', error);
                displayAdminStatusMessage('Error saving child: ' + error.message, 'error');
            }
        });

        async function deleteChild(childId) {
            // Use a custom confirmation dialog or modal instead of confirm()
            if (!confirm('Are you sure you want to delete this child? This action cannot be undone.')) {
                return;
            }
            displayAdminStatusMessage('Deleting child...', 'info');
            try {
                await db.collection('children').doc(childId).delete();
                displayAdminStatusMessage('Child deleted successfully!', 'success');
                fetchAndDisplayChildren(); // Refresh the list
            } catch (error) {
                console.error('Error deleting child:', error);
                displayAdminStatusMessage('Error deleting child: ' + error.message, 'error');
            }
        }

        // --- Removed Match Cancellation Function and its event listener ---
        // The `cancelMatch` function and the event listener for '#cancel-match-btn' are removed.

        // --- Initial Load and Admin Check ---

        // Event listener for "Add New Child" button
        addNewChildBtn.addEventListener('click', () => openChildFormModal());

        auth.onAuthStateChanged(async (user) => {
            if (user) {
                displayAdminStatusMessage('Checking admin privileges...', 'info');
                try {
                    const userDoc = await db.collection('users').doc(user.uid).get();
                    if (userDoc.exists && userDoc.data().role === 'admin') {
                        adminContentDiv.style.display = 'block'; // Show admin content
                        displayAdminStatusMessage('Admin dashboard loaded.', 'success');
                        setTimeout(() => adminStatusMessage.style.display = 'none', 2000);

                        // Load all admin sections
                        fetchAndDisplayChildren();
                        fetchAndDisplayUsers();
                        fetchAndDisplayAcceptedMatches();
                    } else {
                        displayAdminStatusMessage('Access Denied: You do not have administrator privileges.', 'error');
                        adminContentDiv.innerHTML = '<p style="text-align: center; margin-top: 50px; font-size: 1.2em;">You do not have permission to view this page. Please log in with an administrator account.</p>';
                        adminContentDiv.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error checking admin role:', error);
                    displayAdminStatusMessage('Error verifying admin role: ' + error.message, 'error');
                }
            } else {
                displayAdminStatusMessage('Please sign in to access the admin dashboard.', 'error');
                adminContentDiv.innerHTML = '<p style="text-align: center; margin-top: 50px; font-size: 1.2em;">Please <a href="signin.php">sign in</a> to access the admin dashboard.</p>';
                adminContentDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>
