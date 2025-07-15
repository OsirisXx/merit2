<?php
// Start the session if it hasn't been started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optional: Redirect if user is not logged in (adjust as per your auth flow)
// This PHP check ensures the user is logged into your PHP session before rendering page.
// Firebase Auth will handle client-side authentication.
if (!isset($_SESSION['username'])) {
    header('Location: signin.php');
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
    <title>Matching - Meritxell Adoption System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Body and Container Styles */
        body {
            margin: 0;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            display: flex;
            flex-grow: 1;
            /* Allows container to take remaining height */
        }

        /* Modern Sidebar Styles */
        .sidebar {
            width: 260px;
            background-color: #ffffff;
            border-right: 1px solid #e0e0e0;
            padding: 30px 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            border-radius: 0 15px 15px 0;
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

        /* Modern Main Content Area */
        .main-content {
            flex: 1;
            padding: 30px;
            box-sizing: border-box;
            background-color: #f5f7fa;
            margin: 0;
            max-width: none;
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            flex-direction: column;
        }

        /* Match Preferences/Results Containers (from matching.php) */
        .match-preferences-container,
        .match-results-container {
            width: 100%;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            box-sizing: border-box;
            /* Include padding in width */
            margin-bottom: 20px;
            /* Space between containers if stacked */
        }

        /* Page Header Section */
        .header-section {
            background: linear-gradient(135deg, #6ea4ce 0%, #61C2C7 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 30px rgba(110, 164, 206, 0.3);
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) rotate(45deg); }
            50% { transform: translateX(100%) rotate(45deg); }
        }

        .header-section h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            z-index: 1;
            color: white;
            text-align: center;
        }

        .header-section .subtitle {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        h2 {
            color: #333;
            text-align: left;
            margin-bottom: 20px;
        }

        .small-note {
            font-size: 0.85em;
            color: #555;
            margin-bottom: 15px;
        }

        /* Modern Matching Page Content Sections */
        .matching-intro-section,
        .matching-status-message,
        .not-logged-in-message,
        .latest-match-display,
        .matching-form-container {
            width: 100%;
            max-width: 900px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            margin-bottom: 25px;
            box-sizing: border-box;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .matching-intro-section:hover,
        .latest-match-display:hover,
        .matching-form-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.15);
        }

        /* Titles inside content blocks (e.g., "Your Latest Matching Request") */
        .matching-intro-section h2,
        .matching-status-message h2,
        .not-logged-in-message h2,
        .latest-match-display h2 {
            text-align: center;
            /* Keep these main section titles centered */
            margin-bottom: 20px;
            font-size: 1.7em;
            /* Make the main title a bit larger */
            color: #222;
            /* Make the main title slightly darker */
        }

        .terms {
            text-align: justify;
            margin-top: 25px;
            margin-bottom: 25px;
            border: 1px solid #e8f4fd;
            padding: 25px;
            border-radius: 15px;
            background: linear-gradient(145deg, #f8fcff, #e8f4fd);
            box-shadow: inset 0 2px 4px rgba(110, 164, 206, 0.1);
        }

        .terms h3 {
            text-align: center;
            font-size: 1.6em;
            margin-bottom: 20px;
            color: #2c5aa0;
            font-weight: 600;
        }

        .terms p {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .checkbox-section {
            margin-top: 25px;
            text-align: center;
            padding: 20px;
            background: rgba(110, 164, 206, 0.05);
            border-radius: 12px;
            border: 2px solid rgba(110, 164, 206, 0.2);
        }

        .checkbox-section input[type="checkbox"] {
            transform: scale(1.3);
            margin-right: 12px;
            cursor: pointer;
            accent-color: #6ea4ce;
        }

        .checkbox-section label {
            font-size: 1.1em;
            color: #2c5aa0;
            cursor: pointer;
            font-weight: 500;
            line-height: 1.5;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 25px;
            align-items: center;
        }

        .btn {
            display: block;
            width: auto;
            min-width: 400px;
            max-width: 98%;
            padding: 18px 40px;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #FFFFFF;
            font-size: 0.85em;
            font-weight: 600;
            border-radius: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(110, 164, 206, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.1px;
            white-space: nowrap;
            margin: 0 auto;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .bottom-note {
            font-size: 0.9em;
            text-align: center;
            margin-top: 15px;
            color: #6ea4ce;
            font-style: italic;
            font-weight: 500;
        }

        /* Alert Messages */
        .alert-message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            border: 1px solid;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: linear-gradient(145deg, #d4edda, #c3e6cb);
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-error {
            background: linear-gradient(145deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-color: #f5c6cb;
        }

        .alert-info {
            background: linear-gradient(145deg, #d1ecf1, #bee5eb);
            color: #0c5460;
            border-color: #bee5eb;
        }

        /* Modern Form Styles */
        .matching-form-container h3 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c5aa0;
            font-size: 1.8em;
            font-weight: 600;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .matching-form-container .input-group {
            margin-bottom: 25px;
            padding: 0;
            width: 100%;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 15px;
            align-items: center;
        }

        .matching-form-container label {
            display: block;
            margin-bottom: 0;
            font-weight: 600;
            color: #2c5aa0;
            font-size: 1.1em;
            text-align: left;
            justify-self: start;
        }

        .matching-form-container input[type="text"],
        .matching-form-container select,
        .matching-form-container textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1em;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background-color: #ffffff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: block;
        }

        .matching-form-container input[type="text"]:focus,
        .matching-form-container select:focus,
        .matching-form-container textarea:focus {
            outline: none;
            border-color: #6ea4ce;
            box-shadow: 0 0 0 3px rgba(110, 164, 206, 0.1);
            transform: translateY(-1px);
        }

        .matching-form-container textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Special handling for the textarea input group */
        .matching-form-container .input-group:has(textarea) {
            grid-template-columns: 200px 1fr;
            align-items: start;
        }

        .matching-form-container .input-group:has(textarea) label {
            align-self: start;
            padding-top: 15px;
        }

        .matching-form-container form {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
        }

        .matching-form-container button[type="submit"] {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            margin-top: 35px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: block;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 8px 25px rgba(110, 164, 206, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.1px;
            width: 100%;
            max-width: 300px;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
            text-overflow: ellipsis;
        }

        .matching-form-container button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .matching-form-container button[type="submit"]:hover::before {
            left: 100%;
        }

        .matching-form-container button[type="submit"]:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(110, 164, 206, 0.5);
        }

        .matching-form-container button[type="submit"]:active {
            transform: translateY(-1px) scale(1.01);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .matching-form-container button[type="submit"]:disabled {
            background: linear-gradient(135deg, #cccccc, #aaaaaa);
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .matching-form-container button[type="submit"]:focus {
            outline: none;
            box-shadow: 0 8px 25px rgba(110, 164, 206, 0.4), 0 0 0 3px rgba(110, 164, 206, 0.2);
        }

        /* Loading state for button */
        .matching-form-container button[type="submit"].loading {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            cursor: progress;
            position: relative;
        }

        .matching-form-container button[type="submit"].loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Button icon styling */
        .matching-form-container button[type="submit"] span,
        .btn span {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .matching-form-container button[type="submit"]:hover span,
        .btn:hover span {
            transform: scale(1.1) rotate(5deg);
        }

        /* Modern Match Display Styles */
        .latest-match-display {
            text-align: left;
            padding: 30px;
            line-height: 1.8;
            font-size: 1.1em;
        }

        .latest-match-display h2 {
            text-align: center;
            color: #2c5aa0;
            font-size: 1.9em;
            font-weight: 600;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .latest-match-display .detail-item {
            margin-bottom: 18px;
            display: flex;
            align-items: baseline;
            padding: 15px;
            background: linear-gradient(145deg, #f8fcff, #e8f4fd);
            border-radius: 12px;
            border-left: 4px solid #6ea4ce;
            transition: all 0.3s ease;
        }

        .latest-match-display .detail-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(110, 164, 206, 0.2);
        }

        .latest-match-display .detail-item strong {
            display: inline-block;
            width: 200px;
            min-width: 150px;
            font-weight: 700;
            color: #2c5aa0;
            margin-right: 20px;
            flex-shrink: 0;
            font-size: 1em;
        }

        .latest-match-display .detail-item span {
            flex-grow: 1;
            word-wrap: break-word;
            color: #333;
            font-weight: 500;
        }

        .matched-child-section {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(145deg, #e8f5e8, #d4edda);
            border-radius: 15px;
            border: 2px solid #20c997;
        }

        .matched-child-section h4 {
            color: #155724;
            font-size: 1.4em;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }

        /* For the sub-headings like "Your submitted preferences:" and "Matched Child Details:" */
        .latest-match-display>p,
        /* If it's a direct paragraph */
        .latest-match-display>div>p,
        /* If it's a paragraph inside a div */
        .latest-match-display h4 {
            /* For the h4 inside .matched-child-section */
            font-weight: bold;
            margin-top: 25px;
            /* More space above these sub-sections */
            margin-bottom: 15px;
            font-size: 1.2em;
            /* Slightly larger font size for these sub-headings */
            color: #444;
            text-align: left;
            /* Ensure these sub-headings are left-aligned */
        }

        /* Specific styling for the 'Matched Child Details' section, maintaining its h4 center alignment */
        .matched-child-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .matched-child-section h4 {
            text-align: center;
            /* Keeping this h4 centered as it's the main heading for the match results */
            margin-bottom: 15px;
            color: #444;
        }

        .matched-child-section .btn-group {
            flex-direction: row;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .matched-child-section .btn {
            width: 150px;
        }


        /* Messages */
        .alert-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            display: none;
            /* Hidden by default as JS would control these */
            text-align: center;
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
        /* Enhanced Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
                position: static;
                border-radius: 0;
                padding: 15px 20px;
            }

            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            .sidebar li {
                margin: 5px 8px;
            }

            .sidebar a {
                padding: 8px 12px;
                font-size: 0.9em;
            }

            .main-content {
                padding: 20px 15px;
            }

            .header-section {
                padding: 25px 20px;
                margin-bottom: 25px;
            }

            .header-section h2 {
                font-size: 24px;
            }

            .header-section .subtitle {
                font-size: 14px;
            }

            .matching-intro-section,
            .matching-form-container,
            .latest-match-display {
                padding: 20px;
                margin-bottom: 20px;
            }

            .matching-form-container h3 {
                font-size: 1.5em;
            }

            .latest-match-display .detail-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 12px;
            }

            .latest-match-display .detail-item strong {
                width: auto;
                margin-bottom: 5px;
                margin-right: 0;
            }

            .btn {
                width: 100%;
                max-width: 100%;
                padding: 12px 20px;
                font-size: 0.95em;
                white-space: normal;
                line-height: 1.4;
            }

            .btn-group .btn {
                width: 100%;
            }

            .matching-form-container .input-group {
                max-width: 100%;
                margin-bottom: 20px;
                grid-template-columns: 1fr;
                gap: 8px;
                align-items: stretch;
            }

            .matching-form-container label {
                font-size: 1em;
                margin-bottom: 6px;
                justify-self: stretch;
            }

            .matching-form-container .input-group:has(textarea) {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .matching-form-container .input-group:has(textarea) label {
                align-self: stretch;
                padding-top: 0;
                margin-bottom: 6px;
            }

            .matching-form-container input[type="text"],
            .matching-form-container select,
            .matching-form-container textarea {
                padding: 12px;
                font-size: 0.95em;
            }

            .matching-form-container button[type="submit"] {
                max-width: 100%;
                padding: 14px 20px;
                font-size: 0.95em;
                margin-top: 25px;
            }

            .terms {
                padding: 20px;
            }

            .checkbox-section {
                padding: 15px;
            }

            .checkbox-section label {
                font-size: 1em;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 15px 10px;
            }

            .header-section {
                padding: 20px 15px;
            }

            .header-section h2 {
                font-size: 20px;
            }

            .matching-intro-section,
            .matching-form-container,
            .latest-match-display {
                padding: 15px;
            }

            .matching-form-container .input-group {
                margin-bottom: 20px;
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .matching-form-container input[type="text"],
            .matching-form-container select,
            .matching-form-container textarea {
                padding: 12px;
            }

            .matching-form-container button[type="submit"] {
                min-width: 100%;
                max-width: 100%;
                padding: 14px 20px;
                font-size: 0.9em;
                margin-top: 25px;
                letter-spacing: 0.3px;
                white-space: normal;
                line-height: 1.3;
            }

            .btn {
                min-width: 100%;
                max-width: 100%;
                padding: 14px 20px;
                font-size: 0.9em;
                letter-spacing: 0.3px;
                white-space: normal;
                line-height: 1.3;
            }
        }

        @media (max-width: 360px) {
            .matching-form-container button[type="submit"] {
                padding: 12px 15px;
                font-size: 0.85em;
                letter-spacing: 0.2px;
                min-width: 100%;
            }

            .btn {
                padding: 12px 15px;
                font-size: 0.85em;
                letter-spacing: 0.2px;
                min-width: 100%;
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
            <div class="header-section">
                <h2>Child Matching System</h2>
                <p class="subtitle">Find your perfect match through our intelligent matching system</p>
            </div>

            <div id="not-logged-in-message" class="alert-message alert-error" style="display: none;">
                You must be signed in to access the matching system. Please <a href="signin.php">Sign In</a>.
            </div>

            <!-- Removed pending-match-message as there is no more "pending" state for user action -->

            <div id="terms-section" class="matching-intro-section">
                <p class="small-note">
                    Welcome to the Matching Preferences System. Please review all fields in the online form carefully and ensure that all information provided is complete, accurate, and truthful.
                </p>

                <section class="terms">
                    <h3>Terms and Conditions</h3>
                    <p>This matching system processes requests to provide recommendations. You can submit your preferences at any time to receive a new recommendation. All information provided will be kept confidential and used solely for the purpose of finding suitable matches.</p>

                    <div class="checkbox-section">
                        <input type="checkbox" id="agree-matching-terms" required>
                        <label for="agree-matching-terms">By continuing, I acknowledge that I have read, understood, and agreed to these Terms and Conditions.</label>
                    </div>
                </section>

                <div class="btn-group">
                    <button class="btn" id="start-matching-btn">Get Started</button>
                    <div class="bottom-note">
                        After agreeing to the Terms and Conditions above, you may start your online application by clicking 'Get Started'
                    </div>
                </div>
            </div>

            <div id="matching-form" class="matching-form-container" style="display: none;">
                <h3>Submit Your Preferences</h3>
                <form id="matching-preferences-form">
                    <div class="input-group">
                        <label for="preferredGender">Preferred Gender:</label>
                        <select id="preferredGender" name="preferredGender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Any">Any</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="preferredSkinColor">Preferred Skin Color:</label>
                        <select id="preferredSkinColor" name="preferredSkinColor">
                            <option value="">Select Skin Color</option>
                            <option value="Any">Any</option>
                            <option value="Light">Light</option>
                            <option value="Medium">Medium</option>
                            <option value="Dark">Dark</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="preferredCharacteristics">Preferred Characteristics:</label>
                        <select id="preferredCharacteristics" name="preferredCharacteristics">
                            <option value="">Select Characteristics</option>
                            <option value="Any">Any</option>
                            <option value="Playful">Playful</option>
                            <option value="Calm">Calm</option>
                            <option value="Energetic">Energetic</option>
                            <option value="Creative">Creative</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="preferredSize">Preferred Size:</label>
                        <select id="preferredSize" name="preferredSize">
                            <option value="">Select Size</option>
                            <option value="Any">Any</option>
                            <option value="Small">Small</option>
                            <option value="Medium">Medium</option>
                            <option value="Large">Large</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="preferredAge">Preferred Age:</label>
                        <select id="preferredAge" name="preferredAge">
                            <option value="">Select Age</option>
                            <option value="Any">Any</option>
                            <option value="Infant (0-2)">Infant (0-2)</option>
                            <option value="Toddler (3-4)">Toddler (3-4)</option>
                            <option value="Child (5-10)">Child (5-10)</option>
                            <option value="Pre-teen (11-12)">Pre-teen (11-12)</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="otherPreferences">Other preferences (optional):</label>
                        <textarea id="otherPreferences" name="otherPreferences" placeholder="e.g., enjoys outdoor activities"></textarea>
                    </div>

                    <button type="submit" class="btn">Submit Preferences</button>
                </form>
            </div>

            <div id="latest-match-display" class="latest-match-display" style="display: none;">
                <h2>Your Latest Recommendation</h2>
                <div id="user-preferences-display">
                    <!-- Removed Status line -->
                    <p><strong>Your submitted preferences:</strong></p>
                    <div class="detail-item"><strong>Gender:</strong> <span id="display-gender"></span></div>
                    <div class="detail-item"><strong>Skin Color:</strong> <span id="display-skin-color"></span></div>
                    <div class="detail-item"><strong>Characteristics:</strong> <span id="display-characteristics"></span></div>
                    <div class="detail-item"><strong>Preferred Size:</strong> <span id="display-size"></span></div>
                    <div class="detail-item"><strong>Preferred Age:</strong> <span id="display-age"></span></div>
                    <div class="detail-item"><strong>Other:</strong> <span id="display-other"></span></div>
                </div>

                <div id="matched-child-section" class="matched-child-section" style="display: none;">
                    <h4>Here is a child that matches your preferences:</h4>
                    <div class="detail-item"><strong>Name:</strong> <span id="child-name"></span></div>
                    <div class="detail-item"><strong>Gender:</strong> <span id="child-gender"></span></div>
                    <div class="detail-item"><strong>Skin Color:</strong> <span id="child-skin-color"></span></div>
                    <div class="detail-item"><strong>Age:</strong> <span id="child-age"></span></div>
                    <div class="detail-item"><strong>Characteristics:</strong> <span id="child-characteristics"></span></div>
                    <div class="detail-item"><strong>Size:</strong> <span id="child-size"></span></div>

                    <!-- Removed Accept and Decline Match buttons -->
                    <div class="btn-group">
                        <button class="btn" id="submit-new-preferences-btn">
                    <span style="margin-right: 6px;">🔍</span>New Preferences
                </button>
                    </div>
                </div>

                <!-- Removed accepted-match-message and declined-match-message -->
            </div>
            <div id="dynamic-alert" class="alert-message" style="display: none;"></div>

        </main>
    </div>

    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-storage-compat.js"></script>
    <script src="notification_client.js"></script>
    <script>
        // Make session data available to JavaScript
        window.sessionUserId = '<?php echo $_SESSION['uid'] ?? ''; ?>';
        window.sessionUserEmail = '<?php echo $_SESSION['user_email'] ?? ''; ?>';
        window.sessionUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
    </script>

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

        // Global variables to store current user and their match document ID
        let currentUser = null;
        let currentPreferenceDocId = null; // Renamed to reflect 'preference' not 'match' state

        // --- Helper function to show alerts ---
        function showAlert(message, type) {
            const alertDiv = document.getElementById('dynamic-alert');
            alertDiv.textContent = message;
            alertDiv.className = `alert-message alert-${type}`; // e.g., 'alert-success', 'alert-error'
            alertDiv.style.display = 'block';
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000); // Hide after 5 seconds
        }

        // --- Function to update UI based on match status ---
        async function updateUIAfterRecommendation(hasRecommendation, userPreferences = null, recommendedChildDetails = null) {
            console.log('updateUIAfterRecommendation called. Has recommendation:', hasRecommendation, 'preferences:', userPreferences, 'child:', recommendedChildDetails);

            // Get references to all main sections
            const termsSection = document.getElementById('terms-section');
            const matchingForm = document.getElementById('matching-form');
            const latestMatchDisplay = document.getElementById('latest-match-display');
            const notLoggedInMessage = document.getElementById('not-logged-in-message');
            const matchedChildSection = document.getElementById('matched-child-section');
            const submitNewPreferencesBtn = document.getElementById('submit-new-preferences-btn');


            // Hide all main sections initially
            termsSection.style.display = 'none';
            matchingForm.style.display = 'none';
            latestMatchDisplay.style.display = 'none';
            notLoggedInMessage.style.display = 'none';
            matchedChildSection.style.display = 'none'; // Ensure child section is hidden unless there's a child
            document.getElementById('dynamic-alert').style.display = 'none'; // Hide any previous alerts

            if (!currentUser) {
                console.log('User not logged in. Showing not-logged-in-message.');
                notLoggedInMessage.style.display = 'block';
                return;
            }

            if (!hasRecommendation || !userPreferences) {
                // If no preferences or no recommendation found, show the terms/form
                console.log('No current recommendation. Showing terms section.');
                termsSection.style.display = 'block';
            } else {
                // If there's a recommendation, show the latest match display
                console.log('Recommendation found. Showing latest-match-display.');
                latestMatchDisplay.style.display = 'block';

                // Populate user preferences
                document.getElementById('display-gender').textContent = userPreferences.genderPreference || 'N/A';
                document.getElementById('display-skin-color').textContent = userPreferences.skinColorPreference || 'N/A';
                document.getElementById('display-characteristics').textContent = userPreferences.characteristicsPreference || 'N/A';
                document.getElementById('display-size').textContent = userPreferences.preferredSize || 'N/A';
                document.getElementById('display-age').textContent = userPreferences.preferredAge || 'N/A';
                document.getElementById('display-other').textContent = userPreferences.otherPreferences || 'N/A';

                if (recommendedChildDetails) {
                    console.log('Populating recommended child details.');
                    matchedChildSection.style.display = 'block'; // Show the child details section
                    document.getElementById('child-name').textContent = recommendedChildDetails.name || 'N/A';
                    document.getElementById('child-gender').textContent = recommendedChildDetails.gender || 'N/A';
                    document.getElementById('child-skin-color').textContent = recommendedChildDetails.skinColor || 'N/A';
                    document.getElementById('child-age').textContent = recommendedChildDetails.age || 'N/A';
                    // Ensure characteristics are displayed correctly if it's an array
                    document.getElementById('child-characteristics').textContent = recommendedChildDetails.characteristics ? (Array.isArray(recommendedChildDetails.characteristics) ? recommendedChildDetails.characteristics.join(', ') : recommendedChildDetails.characteristics) : 'N/A';
                    document.getElementById('child-size').textContent = recommendedChildDetails.size || 'N/A';
                } else {
                    // If preferences exist but no child was matched, hide the child section
                    console.log('Preferences exist but no child recommendation found. Hiding child section.');
                    matchedChildSection.style.display = 'none';
                    // Maybe show a message indicating no match found for current preferences
                    showAlert('No child found matching your current preferences. Please try adjusting them.', 'info');
                }
            }
        }


        // --- Check user status and display content on page load/auth change ---
        async function checkUserStatusAndDisplayContent(user) {
            currentUser = user;
            console.log('checkUserStatusAndDisplayContent called. Current user:', currentUser ? currentUser.uid : 'None');

            if (user) {
                try {
                    // Fetch the latest matching preference for the current user
                    // We now fetch the latest preference, regardless of its 'status',
                    // as we only care about displaying the last submitted preferences and its recommendation.
                    const preferencesQuery = db.collection('matching_preferences')
                        .where('senderId', '==', user.uid)
                        .orderBy('requestTimestamp', 'desc')
                        .limit(1);

                    const snapshot = await preferencesQuery.get();
                    console.log('Preferences snapshot:', snapshot.empty ? 'Empty' : 'Found');

                    if (!snapshot.empty) {
                        const doc = snapshot.docs[0];
                        currentPreferenceDocId = doc.id; // Store document ID for future updates
                        const userPreferences = doc.data();
                        console.log('Fetched user preferences:', userPreferences);

                        let recommendedChildDetails = userPreferences.matchedChildDetails || null;
                        // Determine if a recommendation was found in the latest preference
                        const hasRecommendation = recommendedChildDetails !== null;

                        updateUIAfterRecommendation(hasRecommendation, userPreferences, recommendedChildDetails);

                    } else {
                        // No preferences found for this user
                        console.log('No preferences found for current user. Setting status to no_preferences.');
                        updateUIAfterRecommendation(false); // No recommendation found
                    }
                } catch (error) {
                    console.error("Error fetching user preferences:", error);
                    showAlert('Error fetching your preferences: ' + error.message, 'error');
                    updateUIAfterRecommendation(false); // Fallback to allow re-submission
                }
            } else {
                // User is not logged in
                console.log('No user logged in. Calling updateUIAfterRecommendation with not_logged_in.');
                updateUIAfterRecommendation(false); // No recommendation as not logged in
            }
        }

        // Function to check and move existing match to history (matching mobile app logic)
        async function checkAndMoveExistingMatchToHistory(userId) {
            try {
                console.log('Checking for existing matches for user:', userId);
                
                // Check for any existing match (not just accepted status)
                const existingMatchSnapshot = await db.collection('matching_preferences')
                    .where('senderId', '==', userId)
                    .get();
                
                if (!existingMatchSnapshot.empty) {
                    console.log('Found existing match, moving to history...');
                    
                    const existingMatch = existingMatchSnapshot.docs[0];
                    const existingMatchData = existingMatch.data();
                    
                    if (existingMatchData) {
                        const childDetails = existingMatchData.matchedChildDetails;
                        const childName = childDetails?.name || "Unknown Child";
                        
                        // Create history record for previous match
                        const historyRecord = {
                            senderId: userId,
                            senderUsername: existingMatchData.senderUsername,
                            previousMatch: {
                                childDetails: existingMatchData.matchedChildDetails,
                                userPreferences: {
                                    genderPreference: existingMatchData.genderPreference,
                                    skinColorPreference: existingMatchData.skinColorPreference,
                                    characteristicsPreference: existingMatchData.characteristicsPreference,
                                    preferredSize: existingMatchData.preferredSize,
                                    preferredAge: existingMatchData.preferredAge,
                                    otherPreferences: existingMatchData.otherPreferences
                                },
                                matchedAt: existingMatchData.requestTimestamp,
                                replacedAt: Date.now()
                            },
                            type: "previous_match",
                            createdAt: Date.now()
                        };
                        
                        console.log('Creating history record:', historyRecord);
                        
                        // Add to history collection
                        await db.collection('matching_history').add(historyRecord);
                        console.log('Successfully added to history');
                        
                        showAlert(`Previous match with ${childName} moved to history`, 'info');
                        
                        // Delete the existing match
                        await existingMatch.ref.delete();
                        console.log('Successfully deleted existing match');
                        
                        // Make the child available again
                        const childId = existingMatchData.receiverId;
                        if (childId) {
                            await db.collection('children').doc(childId).update({
                                status: 'Available'
                            });
                            console.log('Child status updated to Available for child ID:', childId);
                        }
                    }
                } else {
                    console.log('No existing match found for user:', userId);
                }
            } catch (error) {
                console.error('Error checking/moving existing match to history:', error);
                // Don't throw error, just log it and continue
            }
        }

        // --- Event Listeners and Core Logic ---
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded. Initializing event listeners.');

            // Get references to elements
            const agreeCheckbox = document.getElementById('agree-matching-terms');
            const startMatchingBtn = document.getElementById('start-matching-btn');
            const matchingPreferencesForm = document.getElementById('matching-preferences-form');
            const submitNewPreferencesBtn = document.getElementById('submit-new-preferences-btn');


            // Handle "Get Started" button click
            if (startMatchingBtn) {
                startMatchingBtn.addEventListener('click', function() {
                    console.log('Start Matching button clicked.');
                    const termsSection = document.getElementById('terms-section');
                    const matchingForm = document.getElementById('matching-form');

                    if (agreeCheckbox && !agreeCheckbox.checked) {
                        showAlert('You must agree to the Terms and Conditions to proceed.', 'error');
                        return;
                    }
                    termsSection.style.display = 'none';
                    matchingForm.style.display = 'block';
                });
            }

            // Handle "Submit New Preferences" button click (reverts to form)
            if (submitNewPreferencesBtn) {
                submitNewPreferencesBtn.addEventListener('click', function() {
                    console.log('Submit New Preferences button clicked. Showing form.');
                    document.getElementById('latest-match-display').style.display = 'none';
                    document.getElementById('matching-form').style.display = 'block';
                });
            }


            // Handle Matching Preferences Form submission
            if (matchingPreferencesForm) {
                matchingPreferencesForm.addEventListener('submit', async function(e) {
                    e.preventDefault(); // Prevent default form submission
                    console.log('Matching preferences form submitted.');

                    if (!currentUser) {
                        showAlert('You must be logged in to submit preferences.', 'error');
                        return;
                    }

                    showAlert('Searching for a recommendation based on your preferences...', 'info');

                    const preferences = {
                        characteristicsPreference: document.getElementById('preferredCharacteristics').value,
                        genderPreference: document.getElementById('preferredGender').value,
                        otherPreferences: document.getElementById('otherPreferences').value,
                        preferredAge: document.getElementById('preferredAge').value,
                        preferredSize: document.getElementById('preferredSize').value,
                        skinColorPreference: document.getElementById('preferredSkinColor').value,
                        requestTimestamp: Date.now(), // Use client-side timestamp
                        senderId: currentUser.uid,
                        senderUsername: currentUser.displayName || currentUser.email, // Use display name or email
                        // Removed status field as it's no longer 'pending', 'matched', etc. for user interaction
                    };
                    console.log('Preferences to submit:', preferences);

                    // Validate that all required fields are selected (matching mobile app validation)
                    if (!preferences.genderPreference || preferences.genderPreference === '' || preferences.genderPreference === 'Select Gender' ||
                        !preferences.skinColorPreference || preferences.skinColorPreference === '' || preferences.skinColorPreference === 'Select Skin Color' ||
                        !preferences.characteristicsPreference || preferences.characteristicsPreference === '' || preferences.characteristicsPreference === 'Select Characteristics' ||
                        !preferences.preferredSize || preferences.preferredSize === '' || preferences.preferredSize === 'Select Size' ||
                        !preferences.preferredAge || preferences.preferredAge === '' || preferences.preferredAge === 'Select Age') {
                        showAlert('Please select all required preferences.', 'error');
                        return;
                    }

                    try {
                        // --- First, check and move existing match to history (matching mobile app logic) ---
                        console.log('Checking for existing matches to move to history...');
                        await checkAndMoveExistingMatchToHistory(currentUser.uid);
                        
                        // Send matching request notifications using COLLECTION-BASED SYSTEM
                        const userName = currentUser.displayName || currentUser.email?.split('@')[0] || 'User';
                        const userEmail = currentUser.email || '';
                        const userId = currentUser.uid;
                        
                        // Send to collection-based notification system
                        fetch('super_simple_notifications.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'send_matching_notification',
                                userId: userId,
                                status: 'request_submitted',
                                userName: userName,
                                userEmail: userEmail,
                                preferences: preferences
                            })
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                console.log('✅ COLLECTION-BASED: Matching request notifications sent successfully');
                            } else {
                                console.log('❌ COLLECTION-BASED: Failed to send matching notifications:', result.error);
                            }
                        })
                        .catch(error => {
                            console.log('❌ COLLECTION-BASED: Matching notification error:', error);
                        });

                        // --- Recommendation Logic (Exact Mobile App Implementation) ---
                        console.log('Starting recommendation logic...');
                        const childrenSnapshot = await db.collection('children')
                            .get(); // Get all children, filter for Available later
                        console.log('Children snapshot fetched. Total children:', childrenSnapshot.size);

                        let bestMatch = null;
                        let maxMatches = 0;
                        let matchedCriteria = [];
                        let bestMatchId = null;

                        childrenSnapshot.forEach(childDoc => {
                            const child = childDoc.data();
                            
                            // Only consider available children (matching mobile app)
                            if (child && child.status === 'Available') {
                                let currentMatches = 0;
                                let currentMatchedCriteria = [];

                                const childGender = child.gender?.toString();
                                const childSkinColor = child.skinColor?.toString(); // Use skinColor not skin (mobile app field name)
                                const childCharacteristics = child.characteristics?.toString();
                                const childSize = child.size?.toString();
                                const childAge = child.age?.toString();

                                // Gender matching (exclude placeholder "Select Gender")
                                const userPreferredGender = preferences.genderPreference?.toString();
                                if (userPreferredGender && userPreferredGender !== "Select Gender" && userPreferredGender !== "") {
                                    if (userPreferredGender === "Any" || userPreferredGender === childGender) {
                                        currentMatches++;
                                        currentMatchedCriteria.push(`Gender: ${childGender || "N/A"}`);
                                    }
                                }

                                // Skin Color matching (exclude placeholder "Select Skin Color")
                                const userPreferredSkinColor = preferences.skinColorPreference?.toString();
                                if (userPreferredSkinColor && userPreferredSkinColor !== "Select Skin Color" && userPreferredSkinColor !== "") {
                                    if (userPreferredSkinColor === "Any" || userPreferredSkinColor === childSkinColor) {
                                        currentMatches++;
                                        currentMatchedCriteria.push(`Skin Color: ${childSkinColor || "N/A"}`);
                                    }
                                }

                                // Characteristics matching (exclude placeholder "Select Characteristics")
                                const userPreferredCharacteristics = preferences.characteristicsPreference?.toString();
                                if (userPreferredCharacteristics && userPreferredCharacteristics !== "Select Characteristics" && userPreferredCharacteristics !== "") {
                                    if (userPreferredCharacteristics === "Any" || userPreferredCharacteristics === childCharacteristics) {
                                        currentMatches++;
                                        currentMatchedCriteria.push(`Characteristics: ${childCharacteristics || "N/A"}`);
                                    }
                                }

                                // Size matching (exclude placeholder "Select Size")
                                const userPreferredSize = preferences.preferredSize?.toString();
                                if (userPreferredSize && userPreferredSize !== "Select Size" && userPreferredSize !== "") {
                                    if (userPreferredSize === "Any" || userPreferredSize === childSize) {
                                        currentMatches++;
                                        currentMatchedCriteria.push(`Size: ${childSize || "N/A"}`);
                                    }
                                }

                                // Age matching (exclude placeholder "Select Age")
                                const userPreferredAge = preferences.preferredAge?.toString();
                                if (userPreferredAge && userPreferredAge !== "Select Age" && userPreferredAge !== "") {
                                    if (userPreferredAge === "Any" || userPreferredAge === childAge) {
                                        currentMatches++;
                                        currentMatchedCriteria.push(`Age: ${childAge || "N/A"}`);
                                    }
                                }

                                console.log(`Child: ${child.name}, Matches: ${currentMatches}, Criteria: [${currentMatchedCriteria.join(', ')}]`);

                                // Update best match if this child has more matches
                                if (currentMatches > maxMatches) {
                                    maxMatches = currentMatches;
                                    bestMatch = child;
                                    bestMatchId = childDoc.id;
                                    matchedCriteria = [...currentMatchedCriteria];
                                }
                            }
                        });

                        console.log('Recommendation complete. Best match found:', bestMatch, 'Max matches:', maxMatches);

                        // CRITICAL: Mobile app requires 3 or more matches for acceptance
                        if (bestMatch && maxMatches >= 3) {
                            console.log('Match found with 3+ criteria! Auto-accepting match...');
                            
                            // AUTO-ACCEPT: Set status to "accepted" immediately (matching mobile app)
                            const finalPreferences = {
                                ...preferences,
                                receiverId: bestMatchId,
                                matchedChildDetails: { ...bestMatch, id: bestMatchId },
                                status: "accepted", // AUTO-ACCEPT
                                acceptedAt: firebase.firestore.Timestamp.now(),
                                actionTimestamp: Date.now()
                            };

                            // Create new matching preference document
                            const newDocRef = await db.collection('matching_preferences').add(finalPreferences);
                            currentPreferenceDocId = newDocRef.id;

                            // Update child status to "Matched" (matching mobile app)
                            await db.collection('children').doc(bestMatchId).update({
                                status: 'Matched'
                            });

                            showAlert('Match found and automatically accepted! You can now schedule an appointment.', 'success');
                            console.log('Child status updated to Matched for child ID:', bestMatchId);
                            
                            // Note: Match found notification removed per user request
                            
                            // Note: Match accepted notification removed per user request
                            
                            updateUIAfterRecommendation(true, finalPreferences, { ...bestMatch, id: bestMatchId });
                        } else {
                            // No match found or not enough criteria met (less than 3 matches)
                            console.log('No match found or insufficient matches (less than 3)');
                            showAlert('No match found. Please try again with different preferences.', 'info');
                            
                            // Don't create a preference document if no match found (matching mobile app)
                            updateUIAfterRecommendation(false, preferences, null);
                        }

                    } catch (error) {
                        console.error("Error submitting preferences or generating recommendation:", error);
                        showAlert('Error processing your request: ' + error.message, 'error');
                    }
                });
            }

            // Removed Accept Match and Decline Match event listeners and their functions

            // Listen for Firebase Auth state changes to initiate content display on page load
            auth.onAuthStateChanged(user => {
                console.log('Firebase Auth state changed. User:', user ? user.uid : 'None');
                checkUserStatusAndDisplayContent(user);
            });

        });
    </script>
</body>

</html>