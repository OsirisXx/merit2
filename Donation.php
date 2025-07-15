<?php
require_once 'session_check.php';

// Redirect if user is not logged in
if (!$isLoggedIn) {
    header('Location: Signin.php');
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

// Check if user is admin - if admin, show admin donation list, otherwise show donation hub
$showAdminView = $isAdmin;
?>
<?php include('navbar.php'); // Include your universal navigation bar 
?>
<?php include('chatbot.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showAdminView ? 'Admin Donation Management' : 'Donation Hub'; ?> - Your App Name</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">

    <style>
        /* --- General Layout Styles (from template) --- */
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

        /* --- Donation Hub Specific Styles --- */
        .donation-section-card {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .donation-section-card h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #333;
            text-align: center;
        }

        .hero-image-container {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(70%);
        }

        .hero-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 2.5em;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            z-index: 2;
            text-align: center;
        }

        .btn-donate-money {
            background-color: #7CB9E8;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            display: inline-block;
        }

        .btn-donate-money:hover {
            background-color: #6ea4ce;
        }

        .item-donation-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }

        .item-donation-section h3 {
            color: #555;
            margin-bottom: 25px;
        }

        /* --- Icon Styles --- */
        .icon-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .icon-wrapper {
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease, filter 0.2s ease;
        }

        .icon-wrapper:hover {
            transform: translateY(-5px);
            filter: brightness(90%);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgb(255, 255, 255);
            overflow: hidden;
            margin: 0 auto;
        }

        .icon-wrapper img {
            width: 50%;
            height: auto;
            object-fit: contain;
        }

        .icon-wrapper p {
            margin-top: 10px;
            font-weight: 500;
            color: #333;
        }

        /* --- Elegant Donation Card Styles --- */
        .elegant-section {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }

        .elegant-section h3 {
            color: #555;
            margin-bottom: 25px;
        }

        .elegant-cards-grid-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 20px 0;
        }

        .elegant-donation-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 300px;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .elegant-donation-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .elegant-card-image-wrapper {
            width: 100%;
            height: 180px;
            overflow: hidden;
        }

        .elegant-card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .elegant-card-content {
            padding: 20px;
            text-align: left;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .elegant-card-title {
            font-size: 1.6em;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
            line-height: 1.3;
        }

        .elegant-card-description {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 20px;
            line-height: 1.5;
            flex-grow: 1;
        }

        .elegant-card-donate-btn {
            background-color: #7CB9E8;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            text-transform: uppercase;
            transition: background-color 0.3s ease, transform 0.1s ease;
            align-self: flex-start;
        }

        .elegant-card-donate-btn:hover {
            background-color: #6ea4ce;
            transform: translateY(-2px);
        }

        .elegant-card-donate-btn:active {
            transform: translateY(0);
        }

        /* --- MODAL STYLES --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            position: relative;
            text-align: center;
            animation: fadeIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 2em;
            flex-grow: 1;
            text-align: left;
        }

        .modal-close-button {
            background: none;
            border: none;
            font-size: 1.8em;
            cursor: pointer;
            color: #aaa;
            transition: color 0.2s ease;
            line-height: 1;
        }

        .modal-close-button:hover {
            color: #666;
        }

        /* Styles for first modal content (Money Donation) */
        .modal-description {
            margin-bottom: 25px;
            color: #555;
            font-size: 1em;
            line-height: 1.6;
            text-align: left;
        }

        .modal-amount-label {
            display: block;
            font-size: 1.1em;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .modal-amount-input {
            width: calc(100% - 20px);
            padding: 12px 10px;
            margin-bottom: 25px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1.2em;
            text-align: center;
            box-sizing: border-box;
        }

        .modal-next-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            text-transform: uppercase;
            font-weight: 600;
        }

        .modal-next-btn:hover {
            background-color: #0056b3;
        }

        /* Styles for second modal (GCash) */
        .gcash-qr-image {
            max-width: 80%;
            height: auto;
            margin: 20px auto;
            display: block;
            border: 1px solid #eee;
            border-radius: 5px;
        }

        .gcash-upload-label {
            display: block;
            font-size: 1.1em;
            color: #333;
            margin-top: 20px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .gcash-file-input {
            margin-bottom: 25px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
            width: calc(100% - 20px);
            box-sizing: border-box;
            background-color: #f8f8f8;
            cursor: pointer;
        }

        .gcash-file-input::file-selector-button {
            background-color: #7CB9E8;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-right: 10px;
        }

        .gcash-file-input::file-selector-button:hover {
            background-color: #6ea4ce;
        }

        .gcash-submit-btn {
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            text-transform: uppercase;
            font-weight: 600;
        }

        .gcash-submit-btn:hover {
            background-color: #218838;
        }

        /* Styles for Item Donation Form (now inside a modal) */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 12px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            background-color: #fcfcfc;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 15px;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .alert-message {
            padding: 10px 15px;
            margin-top: 15px;
            border-radius: 5px;
            font-weight: 500;
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

        /* --- Track Donations Specific Styles --- */
        .btn-track-donation {
            background-color: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            display: inline-block;
        }

        .btn-track-donation:hover {
            background-color: #5a6268;
        }

        .track-donations-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 400px;
            overflow-y: auto;
        }

        .track-donations-list li {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            text-align: left;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        }

        .track-donations-list li strong {
            color: #333;
            font-size: 1.1em;
            display: block;
            margin-bottom: 5px;
        }

        .track-donations-list li span {
            display: block;
            color: #666;
            font-size: 0.95em;
            margin-bottom: 3px;
        }

        .track-donations-list li .status-pending {
            color: orange;
            font-weight: bold;
        }

        .track-donations-list li .status-completed {
            color: green;
            font-weight: bold;
        }

        .track-donations-list li .status-verified {
            color: darkgreen;
            font-weight: bold;
        }

        .track-donations-list li .status-rejected {
            color: red;
            font-weight: bold;
        }

        .no-donations-message {
            text-align: center;
            color: #777;
            font-style: italic;
            margin-top: 20px;
        }

        /* Responsive adjustments for smaller screens */
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

            .donation-section-card {
                padding: 20px;
            }

            .hero-text {
                font-size: 1.8em;
            }

            .icon-circle {
                width: 58px;
                height: 58px;
            }

            .elegant-cards-grid-container {
                gap: 15px;
                padding: 10px 0;
            }

            .elegant-donation-card {
                width: 95%;
                margin: 0 auto;
            }

            .elegant-card-image-wrapper {
                height: 150px;
            }

            .elegant-card-title {
                font-size: 1.4em;
            }

            .elegant-card-description {
                font-size: 0.85em;
            }

            .elegant-card-donate-btn {
                padding: 8px 20px;
                font-size: 0.9em;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }

            .modal-header h2 {
                font-size: 1.8em;
            }

            .modal-close-button {
                font-size: 1.5em;
            }

            .modal-amount-input,
            .gcash-file-input,
            .form-group input,
            .form-group textarea,
            .form-group select {
                font-size: 1em;
                padding: 10px;
            }

            .modal-next-btn,
            .gcash-submit-btn,
            .btn-submit {
                padding: 10px 25px;
                font-size: 1em;
            }
        }

        /* --- ADMIN DONATION LIST STYLES (Mobile App Style) --- */
        .admin-donation-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-header {
            background: linear-gradient(135deg, #6EC6FF 0%, #4A90E2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(110, 198, 255, 0.3);
        }

        .admin-header h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }

        .admin-header .subtitle {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .admin-controls {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-section {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 15px;
        }

        .filter-dropdown {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            min-width: 200px;
            transition: border-color 0.2s ease;
        }

        .filter-dropdown:focus {
            outline: none;
            border-color: #6EC6FF;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #6EC6FF;
        }

        .donations-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .donation-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            border: 1px solid #f0f0f0;
        }

        .donation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .donation-type {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .donation-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
            color: #666;
            font-size: 14px;
        }

        .donation-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-approved {
            background-color: #d1edff;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-donations {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 16px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        /* Admin Detail Modal */
        .admin-detail-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
            box-sizing: border-box;
        }

        .admin-detail-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .admin-detail-header {
            background: linear-gradient(135deg, #6EC6FF 0%, #4A90E2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-detail-header h3 {
            margin: 0;
            font-size: 20px;
        }

        .admin-detail-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .admin-detail-body {
            padding: 25px;
        }

        .detail-section {
            margin-bottom: 20px;
        }

        .detail-section h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 500;
            color: #666;
        }

        .detail-value {
            color: #333;
        }

        .receipt-image {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .admin-btn {
            flex: 1;
            min-width: 120px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
        }

        .proof-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .proof-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .proof-textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        .proof-file-input {
            padding: 10px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            background: white;
            text-align: center;
            cursor: pointer;
        }

        .proof-image-preview {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .btn-save-proof {
            background: #6EC6FF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-save-proof:hover {
            background: #4A90E2;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-dropdown,
            .search-input {
                min-width: auto;
                width: 100%;
            }

            .donation-info {
                grid-template-columns: 1fr;
            }

            .admin-actions {
                flex-direction: column;
            }

            .admin-btn {
                min-width: auto;
            }

            .detail-grid {
                grid-template-columns: 1fr;
                gap: 5px;
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
            <?php if ($showAdminView): ?>
            <!-- Admin Donation Management View -->
            <div class="admin-donation-container">
                <div class="admin-header">
                    <h2>Donation Management</h2>
                    <p class="subtitle">Review and manage pending donation submissions</p>
                </div>
                
                <div class="admin-controls">
                    <div class="filter-section">
                        <select id="donation-type-filter" class="filter-dropdown">
                            <option value="all">All Donation Types</option>
                            <option value="Money Sponsorship">Money Sponsorship</option>
                            <option value="Education Sponsorship">Education Sponsorship</option>
                            <option value="Medicine Sponsorship">Medicine Sponsorship</option>
                            <option value="Toys Donation">Toys Donation</option>
                            <option value="Clothes Donation">Clothes Donation</option>
                            <option value="Food Donation">Food Donation</option>
                            <option value="Education Donation">Education Donation</option>
                        </select>
                        <input type="text" id="search-input" class="search-input" placeholder="Search by donor name or type...">
                    </div>
                </div>

                <div id="donations-loading" class="loading">
                    Loading donations...
                </div>

                <div id="donations-list" class="donations-list" style="display: none;">
                    <!-- Donations will be loaded here -->
                </div>

                <div id="no-donations" class="no-donations" style="display: none;">
                    <p>No pending donations found.</p>
                </div>
            </div>

            <!-- Admin Detail Modal -->
            <div id="admin-detail-modal" class="admin-detail-modal">
                <div class="admin-detail-content">
                    <div class="admin-detail-header">
                        <h3 id="detail-modal-title">Donation Details</h3>
                        <button class="admin-detail-close">&times;</button>
                    </div>
                    <div class="admin-detail-body">
                        <div id="detail-content">
                            <!-- Detail content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Regular User Donation Hub View -->
            <div class="donation-section-card">
                <h2>Donation Hub</h2>
                <div id="auth-status-message" class="alert-message alert-error" style="display: none;">
                    You must be logged in to access donation features. Please <a href="signin.php">Sign In</a>.
                </div>
                <div id="donation-main-content">
                    <div class="hero-image-container">
                        <img src="https://www.meritxellchildrensfoundation.org/images/3.jpg" alt="Children receiving donations" class="hero-image">
                        <div class="hero-text">Welcome!<br>Make a difference by donating.</div>
                    </div>
                    <button class="btn-donate-money" id="donate-money-btn">Donate Money</button>
                    <div class="item-donation-section">
                        <h3>Do you want to donate items?</h3>
                        <div class="icon-container">
                            <div class="icon-wrapper" data-donation-type="FoodDonation" data-donation-name="Food">
                                <div class="icon-circle"> <img src="icons/salad.png" alt="Food Icon">
                                </div>
                                <p>Food</p>
                            </div>
                            <div class="icon-wrapper" data-donation-type="ClothesDonation" data-donation-name="Clothes">
                                <div class="icon-circle"> <img src="icons/tshirt.png" alt="Clothes Icon">
                                </div>
                                <p>Clothes</p>
                            </div>
                            <div class="icon-wrapper" data-donation-type="EducationDonation" data-donation-name="Education Materials">
                                <div class="icon-circle"> <img src="icons/education.png" alt="Education Materials Icon">
                                </div>
                                <p>Education</p>
                            </div>
                            <div class="icon-wrapper" data-donation-type="ToysDonation" data-donation-name="Toys">
                                <div class="icon-circle"> <img src="icons/toys.png" alt="Toys Icon">
                                </div>
                                <p>Toys</p>
                            </div>
                        </div>
                    </div>
                    <div class="elegant-section">
                        <h3>Explore Our Item Donation Categories</h3>
                        <div class="elegant-cards-grid-container">

                            <div class="elegant-donation-card">
                                <div class="elegant-card-image-wrapper">
                                    <img src="https://i.pinimg.com/736x/e2/b7/1b/e2b71bdf7f3c0e92e27acb68001d013a.jpg" alt="Education Donation" class="elegant-card-image">
                                </div>
                                <div class="elegant-card-content">
                                    <h4 class="elegant-card-title">Education</h4>
                                    <p class="elegant-card-description">Donate gently used books, school supplies, and educational toys to foster learning and development in children.</p>
                                    <button class="elegant-card-donate-btn" data-donation-type="EducationDonation" data-donation-name="Education Materials">DONATE</button>
                                </div>
                            </div>
                            <div class="elegant-donation-card">
                                <div class="elegant-card-image-wrapper">
                                    <img src="medicine.jpg" alt="Medicine Donation" class="elegant-card-image">
                                </div>
                                <div class="elegant-card-content">
                                    <h4 class="elegant-card-title">Medicine</h4>
                                    <p class="elegant-card-description">Provide essential healthcare by donating unexpired, sealed medicines and medical supplies.</p>
                                    <button class="elegant-card-donate-btn" data-donation-type="MedicineSponsorship" data-donation-name="Medicine Sponsorship">DONATE</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="track-donation-section" style="margin-top: 40px; text-align: center;">
                        <button class="btn-track-donation" id="track-donation-btn">Track Your Donations</button>
                        <p style="font-size: 14px; color: #666; margin-top: 10px;">
                            <strong>Note:</strong> Donation tracking is available for Money, Medicine Sponsorship, and Education Sponsorship donations only.
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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

    <div id="money-donation-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>MAKE A DONATION</h2>
                <button class="modal-close-button" data-modal="money-donation-modal">&times;</button>
            </div>
            <p class="modal-description">
                Every child in our care receives holistic support nurturing their spiritual, physical, educational,
                emotional, and social well-being ensuring they have everything they need thrive and grow. With the need so great,
                your gift has an immediate and meaningful impact, making all of this possible.
            </p>
            <label for="donation-amount" class="modal-amount-label">How much would you like to give?</label>
            <input type="number" id="donation-amount" class="modal-amount-input" placeholder="Enter amount (e.g., 500)" min="1" step="any" required>
            <button class="modal-next-btn">Next</button>
        </div>
    </div>
    <div id="gcash-payment-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>GCash Payment</h2>
                <button class="modal-close-button" data-modal="gcash-payment-modal">&times;</button>
            </div>
            <img src="gcash_qr.jpg" alt="GCash QR Code / Payment Info" class="gcash-qr-image">
            <label for="gcash-receipt-file" class="gcash-upload-label">Upload your GCash receipt:</label>
            <input type="file" id="gcash-receipt-file" class="gcash-file-input" accept="image/*" required>
            <button class="gcash-submit-btn" id="gcash-submit-btn">Submit Payment</button>
            <div id="gcash-response-message" class="alert-message" style="margin-top: 20px;"></div>
        </div>
    </div>
    <div id="item-donation-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="item-modal-title">Submit Your Item Donation</h2>
                <button class="modal-close-button" data-modal="item-donation-modal">&times;</button>
            </div>
            <form id="donation-form">
                <div class="form-group">
                    <label for="donationType">Donation Type:</label>
                    <select id="donationType" name="donationType" required disabled>
                        <option value="">Select a donation type</option>
                        <option value="FoodDonation">Food</option>
                        <option value="ClothesDonation">Clothes</option>
                        <option value="EducationDonation">Education Materials</option>
                        <option value="EducationSponsorship">Education Sponsorship</option>
                        <option value="ToysDonation">Toys</option>
                        <option value="MedicineSponsorship">Medicine Sponsorship</option>
                        <option value="OtherDonation">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" placeholder="e.g., 09123456789" required>
                </div>
                <div class="form-group">
                    <label for="address1">Street Address:</label>
                    <input type="text" id="address1" name="address1" placeholder="Enter your street address" required>
                </div>
                <div class="form-group">
                    <label for="address2">Address Line 2 (Optional):</label>
                    <input type="text" id="address2" name="address2" placeholder="Apartment, suite, etc. (optional)">
                </div>
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" placeholder="Enter your city" required>
                </div>
                <div class="form-group">
                    <label for="state">State/Province:</label>
                    <input type="text" id="state" name="state" placeholder="Enter your state or province" required>
                </div>
                <div class="form-group">
                    <label for="zip">ZIP/Postal Code:</label>
                    <input type="text" id="zip" name="zip" placeholder="Enter your ZIP or postal code" required>
                </div>
                <div class="form-group" id="amount-field" style="display: none;">
                    <label for="sponsorshipAmount">Sponsorship Amount (PHP):</label>
                    <input type="number" id="sponsorshipAmount" name="sponsorshipAmount" placeholder="Enter amount (e.g., 1000)" min="1" step="any">
                </div>
                <div class="form-group" id="type-specific-field">
                    <label for="typeSpecificInput" id="typeSpecificLabel">Item Description:</label>
                    <textarea id="typeSpecificInput" name="typeSpecificInput" placeholder="Describe your donation item" required></textarea>
                </div>
                <button type="submit" class="btn-submit" id="donate-button">Submit Donation</button>
                <div id="response-message" class="alert-message"></div>
            </form>
        </div>
    </div>
    <div id="track-donations-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Your Donation History</h2>
                <button class="modal-close-button" data-modal="track-donations-modal">&times;</button>
            </div>
            <div id="donations-list-container">
                <ul id="user-donations-list" class="track-donations-list">
                </ul>
                <p id="no-donations-message" class="no-donations-message" style="display: none;">You haven't made any donations yet.</p>
            </div>
        </div>
    </div>
    <!-- Load Firebase scripts - using consistent version to avoid conflicts -->
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-storage-compat.js"></script>
    <!-- Include Simple Notifications for donation updates -->
    
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-database-compat.js"></script>
    <script>
        // Make session data available to JavaScript
        window.sessionUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
        window.sessionUserEmail = '<?php echo $_SESSION['user_email'] ?? ''; ?>';
        window.sessionUserRole = '<?php echo $_SESSION['user_role'] ?? ''; ?>';
    </script>
    <script>
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            databaseURL: "https://ally-user-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "ally-user",
            storageBucket: "ally-user.firebasestorage.app",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };
        
        // Initialize Firebase (check to avoid re-initialization)
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        
        const auth = firebase.auth();
        const db = firebase.firestore();
        const storage = firebase.storage();
        const realtimeDb = firebase.database(); // Add Realtime Database
        window.realtimeDb = realtimeDb; // Make globally available
        
        // Load Firebase Messaging Bridge after Firebase is initialized
        const script = document.createElement('script');
        script.src = 'firebase_messaging_bridge.js';
        script.onload = function() {
            console.log('Firebase Messaging Bridge loaded successfully');
            // Initialize the bridge
            window.firebaseMessagingBridge = new FirebaseMessagingBridge();
        };
        document.head.appendChild(script);

        // Main content and auth status
        const authStatusMessage = document.getElementById('auth-status-message');
        const mainContentArea = document.getElementById('donation-main-content');

        // Money Donation Modal elements (First Step)
        const donateMoneyButton = document.getElementById('donate-money-btn');
        const moneyDonationModal = document.getElementById('money-donation-modal');
        const modalNextButton = moneyDonationModal ? moneyDonationModal.querySelector('.modal-next-btn') : null;
        const donationAmountInput = document.getElementById('donation-amount');

        // GCash Modal elements (Second Step)
        const gcashPaymentModal = document.getElementById('gcash-payment-modal');
        const gcashReceiptFile = document.getElementById('gcash-receipt-file');
        const gcashSubmitBtn = document.getElementById('gcash-submit-btn');
        const gcashResponseMessage = document.getElementById('gcash-response-message');

        // Item Donation Modal elements (NEW Third Modal)
        const itemDonationModal = document.getElementById('item-donation-modal');
        const itemModalTitle = document.getElementById('item-modal-title');
        const donationForm = document.getElementById('donation-form');
        const donationTypeSelect = document.getElementById('donationType');
        const donateButton = document.getElementById('donate-button');
        const responseMessage = document.getElementById('response-message');

        // Selectors for ALL item donation triggers
        const itemDonationTriggers = document.querySelectorAll('.icon-wrapper, .elegant-card-donate-btn');
        const modalCloseButtons = document.querySelectorAll('.modal-close-button'); // Select all close buttons

        // NEW: Track Donation Modal elements
        const trackDonationButton = document.getElementById('track-donation-btn');
        const trackDonationsModal = document.getElementById('track-donations-modal');
        const userDonationsList = document.getElementById('user-donations-list');
        const noDonationsMessage = document.getElementById('no-donations-message');

        let currentUser = null; // Firebase Auth user object
        let currentDonationAmount = 0;

        // Display message helper function
        function displayMessage(elementId, message, type) {
            const element = document.getElementById(elementId);
            element.textContent = message;
            element.className = `alert-message alert-${type}`; // Clears old classes and adds new ones
            element.style.display = 'block';
        }

        function displayGCashMessage(message, type) {
            displayMessage('gcash-response-message', message, type);
        }

        function displayItemMessage(message, type) {
            displayMessage('response-message', message, type);
        }

        // Listen for Firebase Auth state changes
        auth.onAuthStateChanged(user => {
            currentUser = user;
            
            <?php if (!$showAdminView): ?>
            // Regular user view logic
            if (user) {
                if (authStatusMessage) authStatusMessage.style.display = 'none';
                if (mainContentArea) mainContentArea.style.display = 'block'; // Show donation content
            } else {
                if (authStatusMessage) authStatusMessage.style.display = 'block';
                if (mainContentArea) mainContentArea.style.display = 'none'; // Hide donation content
                // Hide all modals and clear forms on logout
                if (moneyDonationModal) moneyDonationModal.style.display = 'none';
                if (gcashPaymentModal) gcashPaymentModal.style.display = 'none';
                if (itemDonationModal) itemDonationModal.style.display = 'none';
                if (trackDonationsModal) trackDonationsModal.style.display = 'none';
                if (gcashResponseMessage) gcashResponseMessage.style.display = 'none';
                if (responseMessage) responseMessage.style.display = 'none';
            }
            <?php endif; ?>
        });

        // Event listener for "Donate Money" button (opens first modal)
        if (donateMoneyButton) {
            donateMoneyButton.addEventListener('click', () => {
                if (!currentUser) {
                    displayMessage('auth-status-message', 'You must be logged in to donate money. Please sign in.', 'error');
                    return;
                }
                if (moneyDonationModal) moneyDonationModal.style.display = 'flex';
                if (donationAmountInput) {
                    donationAmountInput.value = '';
                    donationAmountInput.focus();
                }
            });
        }

        // Event listener for modal close buttons (handles all modals)
        if (modalCloseButtons.length > 0) {
            modalCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modalToClose = document.getElementById(this.dataset.modal);
                if (modalToClose) {
                    modalToClose.style.display = 'none';
                    // Clear messages and inputs specific to each modal
                    if (modalToClose.id === 'gcash-payment-modal') {
                        gcashResponseMessage.style.display = 'none';
                        gcashReceiptFile.value = '';
                    } else if (modalToClose.id === 'item-donation-modal') {
                        donationForm.reset();
                        responseMessage.style.display = 'none';
                    } else if (modalToClose.id === 'track-donations-modal') {
                        userDonationsList.innerHTML = ''; // Clear list when closing
                        noDonationsMessage.style.display = 'none';
                    }
                }
            });
            });
        }

        // Event listener to close modals if clicking outside the content
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    overlay.style.display = 'none';
                    if (overlay.id === 'gcash-payment-modal') {
                        gcashResponseMessage.style.display = 'none';
                        gcashReceiptFile.value = '';
                    } else if (overlay.id === 'item-donation-modal') {
                        donationForm.reset();
                        responseMessage.style.display = 'none';
                    } else if (overlay.id === 'track-donations-modal') {
                        userDonationsList.innerHTML = '';
                        noDonationsMessage.style.display = 'none';
                    }
                }
            });
        });

        // Event listener for Escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (gcashPaymentModal.style.display === 'flex') {
                    gcashPaymentModal.style.display = 'none';
                    gcashResponseMessage.style.display = 'none';
                    gcashReceiptFile.value = '';
                } else if (itemDonationModal.style.display === 'flex') {
                    itemDonationModal.style.display = 'none';
                    donationForm.reset();
                    responseMessage.style.display = 'none';
                } else if (moneyDonationModal.style.display === 'flex') {
                    moneyDonationModal.style.display = 'none';
                } else if (trackDonationsModal.style.display === 'flex') {
                    trackDonationsModal.style.display = 'none';
                    userDonationsList.innerHTML = '';
                    noDonationsMessage.style.display = 'none';
                }
            }
        });

        // Event listener for the "Next" button inside the money donation modal (First Step)
        if (modalNextButton) {
            modalNextButton.addEventListener('click', () => {
            const amount = parseFloat(donationAmountInput.value);

            if (isNaN(amount) || amount <= 0) {
                alert('Please enter a valid donation amount.');
                donationAmountInput.focus();
                return;
            }

            currentDonationAmount = amount;
            if (moneyDonationModal) moneyDonationModal.style.display = 'none';
            if (gcashPaymentModal) gcashPaymentModal.style.display = 'flex';
            if (gcashResponseMessage) gcashResponseMessage.style.display = 'none';
            if (gcashReceiptFile) gcashReceiptFile.value = '';
            });
        }

        // Event listener for GCash receipt submission (Second Step)
        if (gcashSubmitBtn) {
            gcashSubmitBtn.addEventListener('click', async () => {
            // Check 1: User Authentication
            if (!currentUser) {
                displayGCashMessage('You must be logged in to complete this payment.', 'error');
                console.error("DEBUG: User not authenticated. currentUser is null."); // Added debug
                return;
            }

            const file = gcashReceiptFile.files[0];
            // --- DEBUG LOG: Check if file is selected ---
            console.log("DEBUG: Value of 'file' (gcashReceiptFile.files[0]):", file);
            // Check 2: File Selection
            if (!file) {
                displayGCashMessage('Please select a receipt image to upload.', 'error');
                console.error("DEBUG: No file selected."); // Added debug
                return;
            }

            // --- DEBUG LOG: Check donation amount ---
            console.log("DEBUG: Value of 'currentDonationAmount':", currentDonationAmount);
            // Check 3: Donation Amount
            if (currentDonationAmount <= 0) {
                displayGCashMessage('No donation amount specified. Please go back and enter an amount.', 'error');
                console.error("DEBUG: Donation amount is not valid (<= 0).", currentDonationAmount); // Added debug
                return;
            }

            // --- Existing DEBUG LOGS (now confirmed to be reached if above checks pass) ---
            console.log("Attempting GCash receipt upload...");
            console.log("Current user object (from JS):", currentUser);
            if (currentUser && currentUser.uid) {
                console.log("Current user UID (from JS):", currentUser.uid);
            } else {
                // This console.error should ideally not be hit now if the first `if (!currentUser)` passes
                console.error("ERROR: currentUser is null or currentUser.uid is missing when upload initiated!");
            }
            // --- END Existing DEBUG LOGS ---

            gcashSubmitBtn.disabled = true;
            gcashSubmitBtn.textContent = 'Uploading...';
            displayGCashMessage('Uploading receipt...', 'info');

            try {
                const storageRef = storage.ref();
                const fileName = `receipts/${Date.now()}_${currentUser.uid}_${file.name}`; // Flat path to match Firebase rules
                const fileRef = storageRef.child(fileName);

                console.log("DEBUG: Uploading to path:", fileName); // Added debug for path
                const uploadTask = fileRef.put(file);

                await uploadTask; // Wait for the upload to complete
                const receiptUrl = await fileRef.getDownloadURL(); // Get the URL of the uploaded file

                // Get current date/time for the timestamp string (matching mobile app format exactly)
                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                const day = now.getDate().toString().padStart(2, '0');
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const seconds = now.getSeconds().toString().padStart(2, '0');

                const formattedTimestamp = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

                // Fetch actual username from users collection (like mobile app does)
                const userDoc = await db.collection('users').doc(currentUser.uid).get();
                const userData = userDoc.data();
                const actualUsername = userData?.username || currentUser.displayName || currentUser.email.split('@')[0];

                const donationData = {
                    donationType: 'Money Sponsorship', // Match mobile app admin hub filter expectations
                    amount: currentDonationAmount.toString(),
                    receiptUrl: receiptUrl,
                    userId: currentUser.uid,
                    username: actualUsername,
                    status: 'pending',
                    timestamp: formattedTimestamp
                };

                const docRef = await db.collection('donations').add(donationData); // Add donation record to Firestore

                // Create admin notification and chat connection (matching mobile app)
                await createDonationChatConnection(
                    currentUser.uid, 
                    actualUsername, 
                    'money', 
                    {...donationData, donationId: docRef.id}
                );

                displayGCashMessage('Payment submitted successfully! Your donation is awaiting verification.', 'success');
                
                // Send chat message immediately after donation submission
                const sendDonationChatMessage = () => {
                    if (window.firebaseMessagingBridge) {
                        const chatMessage = `📦 Thank you for your money donation submission! Your generous contribution has been received and is currently under review by our team. We'll contact you soon with next steps.`;
                        
                        window.firebaseMessagingBridge.sendCustomMessage(currentUser.uid, chatMessage, 'donation', {
                            hasDonationButton: true,
                            donationUrl: 'Donation.php'
                        })
                            .then(() => console.log('✅ Money donation submission chat message sent'))
                            .catch(error => console.error('❌ Failed to send money donation submission chat message:', error));
                    } else {
                        console.log('⏳ Firebase messaging bridge not ready yet, retrying in 500ms...');
                        setTimeout(sendDonationChatMessage, 500);
                    }
                };
                
                // Send chat message immediately
                sendDonationChatMessage();
                
                // Send notification for money donation submission using COLLECTION-BASED SYSTEM
                const userName = currentUser.displayName || currentUser.email?.split('@')[0] || 'User';
                const userEmail = currentUser.email || '';
                
                // Send to collection-based notification system
                fetch('super_simple_notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'send_donation_notification',
                        userId: currentUser.uid,
                        donationType: 'money',
                        status: 'submitted',
                        userName: userName,
                        userEmail: userEmail,
                        amount: donationData.amount,
                        donationId: docRef.id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        console.log('✅ COLLECTION-BASED: Money donation notification sent successfully');
                    } else {
                        console.log('❌ COLLECTION-BASED: Failed to send money donation notification:', result.error);
                    }
                })
                .catch(error => {
                    console.log('❌ COLLECTION-BASED: Money donation notification error:', error);
                });
                
                setTimeout(() => {
                    gcashPaymentModal.style.display = 'none';
                    gcashReceiptFile.value = ''; // Clear file input
                    gcashResponseMessage.style.display = 'none';
                    currentDonationAmount = 0; // Reset amount
                }, 3000); // Hide modal and clear message after 3 seconds
            } catch (error) {
                console.error('Error submitting GCash payment:', error); // Log full error object
                let userFriendlyMessage = 'An unexpected error occurred. Please try again.';
                if (error.code) {
                    if (error.code === 'storage/unauthorized') {
                        userFriendlyMessage = 'Upload failed: You do not have permission. Check Firebase Storage rules.';
                    } else if (error.code === 'storage/canceled') {
                        userFriendlyMessage = 'Upload cancelled.';
                    } else if (error.code === 'storage/quota-exceeded') {
                        userFriendlyMessage = 'Upload failed: Storage quota exceeded.';
                    } else {
                        userFriendlyMessage = `Error: ${error.message}`;
                    }
                } else if (error.message.includes("blocked by CORS policy")) { // Catch any lingering CORS if it bypasses browser console
                    userFriendlyMessage = "Upload blocked by CORS policy. Ensure Firebase Storage CORS is configured correctly for https://meritxell-ally.org.";
                }
                displayGCashMessage(userFriendlyMessage, 'error');
            } finally {
                gcashSubmitBtn.disabled = false; // Re-enable button
                gcashSubmitBtn.textContent = 'Submit Payment'; // Reset button text
            }
            });
        }

        // Function to update type-specific field based on donation type
        function updateTypeSpecificField(donationType) {
            const typeSpecificLabel = document.getElementById('typeSpecificLabel');
            const typeSpecificInput = document.getElementById('typeSpecificInput');
            const amountField = document.getElementById('amount-field');
            const sponsorshipAmount = document.getElementById('sponsorshipAmount');
            
            if (!typeSpecificLabel || !typeSpecificInput) return;
            
            switch(donationType) {
                case 'FoodDonation':
                    typeSpecificLabel.textContent = 'Food Type:';
                    typeSpecificInput.placeholder = 'Describe the type of food you\'re donating (e.g., canned goods, rice, etc.)';
                    break;
                case 'ToysDonation':
                    typeSpecificLabel.textContent = 'Toys Type:';
                    typeSpecificInput.placeholder = 'Describe the toys you\'re donating (e.g., educational toys, board games, etc.)';
                    break;
                case 'ClothesDonation':
                    typeSpecificLabel.textContent = 'Clothing Type:';
                    typeSpecificInput.placeholder = 'Describe the clothing you\'re donating (e.g., children\'s clothes, sizes, etc.)';
                    break;
                case 'EducationDonation':
                    typeSpecificLabel.textContent = 'Education Type:';
                    typeSpecificInput.placeholder = 'Describe the educational materials (e.g., books, school supplies, etc.)';
                    break;
                case 'EducationSponsorship':
                    typeSpecificLabel.textContent = 'Sponsorship Details:';
                    typeSpecificInput.placeholder = 'Describe what educational needs you want to sponsor (e.g., school fees, uniforms, etc.)';
                    if (amountField) amountField.style.display = 'block';
                    if (sponsorshipAmount) sponsorshipAmount.required = true;
                    break;
                case 'MedicineSponsorship':
                    typeSpecificLabel.textContent = 'Sponsorship Details:';
                    typeSpecificInput.placeholder = 'Describe what medical needs you want to sponsor (e.g., medicine, treatment, etc.)';
                    if (amountField) amountField.style.display = 'block';
                    if (sponsorshipAmount) sponsorshipAmount.required = true;
                    break;
                default:
                    typeSpecificLabel.textContent = 'Item Description:';
                    typeSpecificInput.placeholder = 'Describe your donation item';
                    if (amountField) amountField.style.display = 'none';
                    if (sponsorshipAmount) sponsorshipAmount.required = false;
                    break;
            }
        }

        // Event listeners for item donation triggers (icons and elegant cards)
        if (itemDonationTriggers.length > 0) {
            itemDonationTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                if (!currentUser) {
                    displayItemMessage('You must be logged in to donate items. Please sign in.', 'error');
                    return;
                }
                const selectedType = this.dataset.donationType;
                const selectedName = this.dataset.donationName || 'Items';
                // Set the form values
                donationTypeSelect.value = selectedType;
                donationTypeSelect.disabled = true;
                // Set the modal title dynamically
                itemModalTitle.textContent = `Donate ${selectedName} Items`;
                // Update the type-specific field
                updateTypeSpecificField(selectedType);
                // Reset form fields (except for the pre-selected type)
                donationForm.reset();
                donationTypeSelect.value = selectedType; // Re-set the value after reset()
                // Show the modal
                itemDonationModal.style.display = 'flex';
                responseMessage.style.display = 'none';
            });
            });
        }

        // Event listener for item donation form submission (inside the modal)
        if (donationForm) {
            donationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentUser) {
                displayItemMessage('You must be logged in to make a donation. Please sign in.', 'error');
                return;
            }
            const donationType = donationTypeSelect.value;
            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address1 = document.getElementById('address1').value.trim();
            const address2 = document.getElementById('address2').value.trim();
            const city = document.getElementById('city').value.trim();
            const state = document.getElementById('state').value.trim();
            const zip = document.getElementById('zip').value.trim();
            const typeSpecificInput = document.getElementById('typeSpecificInput').value.trim();
            const sponsorshipAmount = document.getElementById('sponsorshipAmount').value.trim();

            if (!donationType || !fullName || !email || !phone || !address1 || !city || !state || !zip || !typeSpecificInput) {
                displayItemMessage('Please fill in all required fields.', 'error');
                return;
            }

            // Check if sponsorship amount is required and provided
            const isSponsorship = donationType === 'EducationSponsorship' || donationType === 'MedicineSponsorship';
            if (isSponsorship && (!sponsorshipAmount || parseFloat(sponsorshipAmount) <= 0)) {
                displayItemMessage('Please enter a valid sponsorship amount.', 'error');
                return;
            }

            donateButton.disabled = true;
            donateButton.textContent = 'Submitting...';
            responseMessage.style.display = 'none';

            try {
                // Determine the correct collection based on donation type (to match mobile app)
                const collectionMapping = {
                    'FoodDonation': 'fooddonation',
                    'ToysDonation': 'toysdonation',
                    'ClothesDonation': 'clothesdonation',
                    'EducationDonation': 'donations',
                    'MedicineSponsorship': 'donations' // Medicine sponsorship goes to main donations collection
                };
                const targetCollection = collectionMapping[donationType] || 'donations';

                // Create donation data structure to exactly match mobile app format
                const donationData = {
                    fullName: fullName,
                    username: fullName, // Use fullName as username to match mobile app
                    email: email,
                    phone: phone,
                    address1: address1,
                    address2: address2,
                    city: city,
                    state: state,
                    zip: zip,
                    timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                    status: 'pending', // Default status for admin review
                    userId: currentUser.uid || '' // Track which user submitted
                };

                // Add the specific field that mobile app uses for each type and set donationType
                if (donationType === 'FoodDonation') {
                    donationData.foodType = typeSpecificInput;
                    donationData.donationType = 'food'; // Mobile app expects lowercase for the document field
                } else if (donationType === 'ToysDonation') {
                    donationData.toysType = typeSpecificInput;
                    donationData.donationType = 'toys';
                } else if (donationType === 'ClothesDonation') {
                    donationData.clothingType = typeSpecificInput;
                    donationData.donationType = 'clothes';
                } else if (donationType === 'EducationDonation') {
                    donationData.educationType = typeSpecificInput;
                    donationData.donationType = 'education';
                } else if (donationType === 'EducationSponsorship') {
                    // Education sponsorship is a money donation, should have amount field
                    donationData.donationType = 'Education Sponsorship';
                    donationData.sponsorshipType = typeSpecificInput;
                    donationData.amount = sponsorshipAmount;
                } else if (donationType === 'MedicineSponsorship') {
                    // Medicine sponsorship is a money donation, should have amount field
                    donationData.donationType = 'Medicine Sponsorship';
                    donationData.sponsorshipType = typeSpecificInput;
                    donationData.amount = sponsorshipAmount;
                } else {
                    donationData.itemDescription = typeSpecificInput;
                    donationData.donationType = donationType.toLowerCase().replace('donation', '');
                }

                const docRef = await db.collection(targetCollection).add(donationData);
                
                // Send notifications for donation submission
                const userName = currentUser.displayName || currentUser.email?.split('@')[0] || 'User';
                const userEmail = currentUser.email || '';
                const userId = currentUser.uid;
                const amount = donationData.amount || null;
                
                // Create automatic chat connection with admin (matching mobile app)
                const chatDonationType = donationData.donationType; // Use the normalized donation type
                
                await createDonationChatConnection(
                    currentUser.uid, 
                    fullName, 
                    chatDonationType, 
                    {...donationData, donationId: docRef.id}
                );

                displayItemMessage('Donation submitted successfully! Admin will contact you for pickup arrangements.', 'success');
                
                // Send chat message immediately after donation submission
                const donationTypeLowercase = donationData.donationType || 'general';
                const sendDonationChatMessage = () => {
                    if (window.firebaseMessagingBridge) {
                        const chatMessage = `📦 Thank you for your ${donationTypeLowercase} donation submission! Your generous contribution has been received and is currently under review by our team. We'll contact you soon with next steps.`;
                        
                        window.firebaseMessagingBridge.sendCustomMessage(currentUser.uid, chatMessage, 'donation', {
                            hasDonationButton: true,
                            donationUrl: 'Donation.php'
                        })
                            .then(() => console.log('✅ Donation submission chat message sent'))
                            .catch(error => console.error('❌ Failed to send donation submission chat message:', error));
                    } else {
                        console.log('⏳ Firebase messaging bridge not ready yet, retrying in 500ms...');
                        setTimeout(sendDonationChatMessage, 500);
                    }
                };
                
                // Send chat message immediately
                sendDonationChatMessage();
                
                // Send notification for item donation submission using COLLECTION-BASED SYSTEM
                fetch('super_simple_notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'send_donation_notification',
                        userId: currentUser.uid,
                        donationType: donationTypeLowercase,
                        status: 'submitted',
                        userName: userName,
                        userEmail: userEmail,
                        amount: donationData.amount || null,
                        donationId: docRef.id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        console.log('✅ COLLECTION-BASED: Donation notification sent successfully');
                    } else {
                        console.log('❌ COLLECTION-BASED: Failed to send donation notification:', result.error);
                    }
                })
                .catch(error => {
                    console.log('❌ COLLECTION-BASED: Donation notification error:', error);
                });
                
                setTimeout(() => {
                    itemDonationModal.style.display = 'none';
                    donationForm.reset();
                    responseMessage.style.display = 'none';
                }, 3000);
            } catch (error) {
                console.error('Error submitting item donation:', error);
                displayItemMessage('Error submitting donation: ' + error.message, 'error');
            } finally {
                donateButton.disabled = false;
                donateButton.textContent = 'Submit Donation';
            }
            });
        }

        // NEW: Event listener for "Track Your Donations" button
        if (trackDonationButton) {
            trackDonationButton.addEventListener('click', async () => {
            if (!currentUser) {
                displayMessage('auth-status-message', 'You must be logged in to track donations. Please sign in.', 'error');
                return;
            }

            userDonationsList.innerHTML = ''; // Clear previous list
            noDonationsMessage.style.display = 'none'; // Hide "no donations" message initially
            trackDonationsModal.style.display = 'flex'; // Show the modal

            try {
                // Fetch donations from all collections (matching mobile app approach)
                const donationCollections = ['donations', 'educationdonation']; // Only money and education sponsorship
                const donations = [];
                
                for (const collection of donationCollections) {
                    const q = db.collection(collection).where('userId', '==', currentUser.uid);
                    const querySnapshot = await q.get();
                    
                    querySnapshot.forEach(doc => {
                        const data = doc.data();
                        
                        // Filter for trackable donation types only
                        if (collection === 'donations') {
                            // Only allow money donations and medicine sponsorship
                            const donationType = data.donationType?.toLowerCase() || '';
                            if (!donationType.includes('money') && 
                                !donationType.includes('medicine') && 
                                !data.amount) { // Has amount field = money donation
                                return; // Skip non-trackable donations
                            }
                        } else if (collection === 'educationdonation') {
                            // Education sponsorship is trackable
                        } else {
                            return; // Skip other collections
                        }
                        
                        // Normalize donation type for display
                        let displayDonationType = data.donationType;
                        if (collection === 'educationdonation') {
                            displayDonationType = 'Education Sponsorship';
                        } else if (collection === 'donations') {
                            if (data.donationType?.toLowerCase().includes('medicine')) {
                                displayDonationType = 'Medicine Sponsorship';
                            } else {
                                displayDonationType = 'Money Donation';
                            }
                        }
                        
                        donations.push({
                            ...data, 
                            id: doc.id,
                            donationType: displayDonationType,
                            collection: collection
                        });
                    });
                }

                if (donations.length === 0) {
                    noDonationsMessage.textContent = 'No trackable donations found. Only Money, Medicine Sponsorship, and Education Sponsorship donations can be tracked.';
                    noDonationsMessage.style.display = 'block';
                    return;
                }
                
                // Sort by submittedAt or timestamp, newest first
                donations.sort((a, b) => {
                    const timeA = a.submittedAt ? (a.submittedAt.toMillis ? a.submittedAt.toMillis() : a.submittedAt) : 0;
                    const timeB = b.submittedAt ? (b.submittedAt.toMillis ? b.submittedAt.toMillis() : b.submittedAt) : 0;
                    return timeB - timeA;
                });

                donations.forEach(donation => {
                    const listItem = document.createElement('li');

                    // Determine status class for styling
                    let statusClass = '';
                    if (donation.status === 'pending') {
                        statusClass = 'status-pending';
                    } else if (donation.status === 'Verified' || donation.status === 'Completed Pickup') {
                        statusClass = 'status-completed';
                    } else if (donation.status === 'Rejected') {
                        statusClass = 'status-rejected';
                    }

                    // Format timestamp for display (handle both string and Timestamp types)
                    let displayDate = 'N/A';
                    if (donation.timestamp) {
                        if (typeof donation.timestamp === 'string') {
                            displayDate = donation.timestamp.substring(0, 10); // "YYYY-MM-DD"
                        } else if (donation.timestamp.toDate) {
                            displayDate = new Date(donation.timestamp.toDate()).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }
                    } else if (donation.submittedAt && donation.submittedAt.toDate) {
                        displayDate = new Date(donation.submittedAt.toDate()).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    }

                    let detailsHtml = '';
                    if (donation.donationType === 'Money') {
                        detailsHtml = `
                            <span>Amount: ${donation.amount || 'N/A'} PHP</span>
                            <span>Payment Method: ${donation.paymentMethod || 'N/A'}</span>
                            <span>Receipt: ${donation.receiptUrl ? `<a href="${donation.receiptUrl}" target="_blank">View</a>` : 'N/A'}</span>
                        `;
                    } else { // Item donations
                        // Debug log to see what fields are available
                        console.log('Donation data for field mapping:', donation);
                        
                        // Handle different field names and provide fallbacks based on collection type
                        let description, quantity, pickupAddress, contact;
                        
                        if (donation.collection === 'educationdonation' || donation.donationType === 'Education Sponsorship') {
                            // Education sponsorship specific fields (common mobile app field names)
                            description = donation.childName || donation.studentName || donation.recipientName || 
                                         donation.sponsorshipDetails || donation.educationDetails || donation.details ||
                                         donation.itemDescription || donation.description || donation.itemName || 
                                         'Education Sponsorship';
                            quantity = donation.sponsorshipAmount || donation.amount || donation.monthlyAmount || 
                                      donation.quantity || donation.donationQuantity || donation.numberOfMonths || '1 month';
                            pickupAddress = donation.schoolAddress || donation.institutionAddress || donation.schoolName ||
                                           donation.recipientAddress || donation.pickupAddress || donation.address || 
                                           donation.location || 'School/Institution';
                            contact = donation.schoolContact || donation.institutionContact || donation.guardianContact || 
                                     donation.contactNumber || donation.contact || donation.phoneNumber || donation.phone || 'N/A';
                        } else if (donation.collection === 'fooddonation') {
                            // Food donation specific fields
                            description = donation.foodType || donation.itemType || donation.itemName || 
                                         donation.itemDescription || donation.description || 'Food Items';
                            quantity = donation.quantity || donation.donationQuantity || donation.numberOfItems || 
                                      donation.weight || donation.amount || '1';
                            pickupAddress = donation.pickupAddress || donation.address || donation.location || 
                                           donation.donorAddress || 'N/A';
                            contact = donation.contactNumber || donation.contact || donation.phoneNumber || 
                                     donation.donorPhone || donation.phone || 'N/A';
                        } else if (donation.collection === 'toysdonation') {
                            // Toys donation specific fields
                            description = donation.toyType || donation.itemType || donation.itemName || 
                                         donation.itemDescription || donation.description || 'Toys';
                            quantity = donation.quantity || donation.donationQuantity || donation.numberOfToys || 
                                      donation.numberOfItems || '1';
                            pickupAddress = donation.pickupAddress || donation.address || donation.location || 
                                           donation.donorAddress || 'N/A';
                            contact = donation.contactNumber || donation.contact || donation.phoneNumber || 
                                     donation.donorPhone || donation.phone || 'N/A';
                        } else if (donation.collection === 'clothesdonation') {
                            // Clothes donation specific fields
                            description = donation.clothingType || donation.itemType || donation.size || 
                                         donation.itemDescription || donation.description || donation.itemName || 'Clothing Items';
                            quantity = donation.quantity || donation.donationQuantity || donation.numberOfItems || 
                                      donation.pieces || '1';
                            pickupAddress = donation.pickupAddress || donation.address || donation.location || 
                                           donation.donorAddress || 'N/A';
                            contact = donation.contactNumber || donation.contact || donation.phoneNumber || 
                                     donation.donorPhone || donation.phone || 'N/A';
                        } else {
                            // Generic fallback for other donation types
                            description = donation.itemDescription || donation.description || donation.itemName || 
                                         donation.itemType || donation.details || donation.donationType || 'N/A';
                            quantity = donation.quantity || donation.donationQuantity || donation.amount || '1';
                            pickupAddress = donation.pickupAddress || donation.address || donation.location || 'N/A';
                            contact = donation.contactNumber || donation.contact || donation.phoneNumber || 
                                     donation.phone || 'N/A';
                        }
                        
                        // Handle multiple image formats
                        let imagesHtml = '';
                        if (donation.imageUrls && Array.isArray(donation.imageUrls) && donation.imageUrls.length > 0) {
                            imagesHtml = `<div class="donation-images">`;
                            donation.imageUrls.forEach((url, index) => {
                                imagesHtml += `<img src="${url}" alt="Donation Image ${index + 1}" class="donation-thumbnail" style="width: 50px; height: 50px; margin: 2px; border-radius: 4px; object-fit: cover;">`;
                            });
                            imagesHtml += `</div>`;
                        } else if (donation.imageUrl) {
                            imagesHtml = `<img src="${donation.imageUrl}" alt="Donation Image" class="donation-thumbnail" style="width: 50px; height: 50px; margin: 2px; border-radius: 4px; object-fit: cover;">`;
                        } else if (donation.proofOfDonationImageUrl) {
                            imagesHtml = `<img src="${donation.proofOfDonationImageUrl}" alt="Donation Image" class="donation-thumbnail" style="width: 50px; height: 50px; margin: 2px; border-radius: 4px; object-fit: cover;">`;
                        }
                        
                        detailsHtml = `
                            <span>Description: ${description}</span>
                            <span>Quantity: ${quantity}</span>
                            ${pickupAddress && pickupAddress !== 'N/A' ? `<span>Pickup Address: ${pickupAddress}</span>` : ''}
                            ${contact && contact !== 'N/A' ? `<span>Contact: ${contact}</span>` : ''}
                            ${imagesHtml}
                        `;
                    }

                    listItem.innerHTML = `
                        <strong>${donation.donationType} Donation</strong>
                        ${detailsHtml}
                        <span>Date: ${displayDate}</span>
                        <span class="${statusClass}">Status: ${donation.status}</span>
                    `;
                    userDonationsList.appendChild(listItem);
                });

            } catch (error) {
                console.error('Error fetching donations:', error);
                userDonationsList.innerHTML = '<li class="alert-error">Error loading donations. Please try again.</li>';
                noDonationsMessage.style.display = 'none';
            }
            });
        }

        // Function to create donation chat connection with admin (matching mobile app functionality)
        async function createDonationChatConnection(userId, username, donationType, donationData) {
            console.log('Creating donation chat connection for:', {userId, username, donationType});
            
            try {
                // First get Firebase Realtime Database reference
                const realtimeDb = firebase.database();
                
                // Check if user has existing chat with admin
                // Using a simpler query to avoid indexing warnings
                const existingChatSnapshot = await realtimeDb.ref('chats')
                    .once('value');
                
                let adminId = null;
                let chatId = null;
                
                if (existingChatSnapshot.exists()) {
                    // Filter chats client-side to find user's existing chat
                    const allChats = existingChatSnapshot.val();
                    let userChat = null;
                    let userChatKey = null;
                    
                    for (const [key, chat] of Object.entries(allChats)) {
                        if (chat.participant_user === userId) {
                            userChat = chat;
                            userChatKey = key;
                            break;
                        }
                    }
                    
                    if (userChat) {
                        adminId = userChat.participant_admin;
                        chatId = userChatKey;
                        console.log('Found existing chat with admin:', adminId);
                    }
                }
                
                if (!adminId) {
                    // No existing chat, find first admin user
                    const adminSnapshot = await db.collection('users')
                        .where('role', '==', 'admin')
                        .limit(1)
                        .get();
                    
                    if (!adminSnapshot.empty) {
                        const adminDoc = adminSnapshot.docs[0];
                        adminId = adminDoc.id;
                        chatId = getChatRoomId(userId, adminId);
                        console.log('Selected admin for new chat:', adminId);
                    } else {
                        console.error('No admin users found in Firestore');
                        return;
                    }
                }
                
                if (!adminId) {
                    console.error('Could not determine admin ID for chat');
                    return;
                }
                
                // Create or update chat connection
                const donationTypeDisplay = getDonationTypeDisplay(donationType);
                const systemMessage = `${username} submitted a ${donationTypeDisplay} (ID: ${donationData.donationId || 'N/A'}). Admin can review the submission details and provide assistance.`;
                
                const chatRef = realtimeDb.ref(`chats/${chatId}`);
                const chatSnapshot = await chatRef.once('value');
                
                const timestamp = firebase.database.ServerValue.TIMESTAMP;
                
                if (!chatSnapshot.exists()) {
                    // Create new chat
                    console.log('Creating new chat connection');
                    const chatData = {
                        connection_type: `${donationType}_donation`,
                        last_message: systemMessage,
                        last_message_timestamp: timestamp,
                        created_by: userId,
                        participant_user: userId,
                        participant_admin: adminId,
                        created_at: timestamp,
                        unread_count: 1,
                        last_activity: timestamp,
                        auto_created: true
                    };
                    
                    await chatRef.set(chatData);
                } else {
                    // Update existing chat
                    console.log('Updating existing chat');
                    const updates = {
                        last_message: systemMessage,
                        last_message_timestamp: timestamp,
                        last_activity: timestamp
                    };
                    
                    await chatRef.update(updates);
                }
                
                // Send system message
                await sendSystemMessage(chatRef, userId, adminId, username, systemMessage, donationType, donationData);
                
                // Also send via PHP messaging helper for consistency
                fetch('donation_message_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'donation_submitted',
                        userId: userId,
                        donationType: donationType,
                        donationId: donationData.donationId || 'N/A',
                        username: username
                    })
                }).catch(error => {
                    console.error('Error sending donation message:', error);
                });
                
                console.log('Donation chat connection created successfully');
                
            } catch (error) {
                console.error('Error creating donation chat connection:', error);
            }
        }

        // Helper function to generate chat room ID
        function getChatRoomId(userId, adminId) {
            return userId < adminId ? `${userId}_${adminId}` : `${adminId}_${userId}`;
        }

        // Helper function to get donation type display name
        function getDonationTypeDisplay(donationType) {
            const typeMap = {
                'toys': 'Toys Donation',
                'clothes': 'Clothes Donation',
                'food': 'Food Donation',
                'education': 'Education Donation',
                'money': 'Money Donation',
                'medicine': 'Medicine Sponsorship'
            };
            return typeMap[donationType.toLowerCase()] || `${donationType} Donation`;
        }

        // Helper function to send system message to chat
        async function sendSystemMessage(chatRef, userId, adminId, username, message, donationType, donationData) {
            const messageId = Date.now().toString();
            const messageData = {
                messageId: messageId,
                senderId: 'system',
                senderName: 'System',
                message: message,
                timestamp: firebase.database.ServerValue.TIMESTAMP,
                messageType: 'system',
                donationType: donationType,
                donationId: donationData.donationId || null,
                isRead: false
            };
            
            await chatRef.child('messages').child(messageId).set(messageData);
            console.log('System message sent successfully');
        }

        // ADMIN DONATION MANAGEMENT FUNCTIONALITY
        <?php if ($showAdminView): ?>
        // Check if we're on admin view and elements exist
        const isAdminView = <?php echo $showAdminView ? 'true' : 'false'; ?>;
        
        if (isAdminView) {
            // Admin-specific variables and functions
            const donationTypeFilter = document.getElementById('donation-type-filter');
            const searchInput = document.getElementById('search-input');
            const donationsLoading = document.getElementById('donations-loading');
            const donationsList = document.getElementById('donations-list');
            const noDonationsDiv = document.getElementById('no-donations');
            const adminDetailModal = document.getElementById('admin-detail-modal');
            const detailModalTitle = document.getElementById('detail-modal-title');
            const detailContent = document.getElementById('detail-content');
            const adminDetailClose = document.querySelector('.admin-detail-close');

            // Make these variables global for onclick functions
            window.allDonations = [];
            window.currentDonationDetail = null;

            // Initialize admin donation management
            function initAdminDonationManagement() {
                console.log('Initializing admin donation management...');
                window.fetchAllDonations();
                
                // Setup event listeners
                if (donationTypeFilter) donationTypeFilter.addEventListener('change', window.filterAndDisplayDonations);
                if (searchInput) searchInput.addEventListener('input', window.filterAndDisplayDonations);
                if (adminDetailClose) adminDetailClose.addEventListener('click', window.closeDetailModal);
            
                // Close modal when clicking outside
                if (adminDetailModal) {
                    adminDetailModal.addEventListener('click', (e) => {
                        if (e.target === adminDetailModal) {
                            window.closeDetailModal();
                        }
                    });
                }
            }

            // Fetch all pending donations from Firebase (matching mobile app logic)
            window.fetchAllDonations = async function() {
                try {
                    console.log('Fetching donations from Firebase...');
                    window.allDonations = [];
                donationsLoading.style.display = 'block';
                donationsList.style.display = 'none';
                noDonationsDiv.style.display = 'none';

                // Collections to check (matching mobile app)
                const collections = [
                    { name: 'donations', types: ['Money Sponsorship', 'Education Sponsorship', 'Medicine Sponsorship'] },
                    { name: 'toysdonation', type: 'Toys Donation' },
                    { name: 'clothesdonation', type: 'Clothes Donation' },
                    { name: 'fooddonation', type: 'Food Donation' },
                    { name: 'educationdonation', type: 'Education Donation' }
                ];

                for (const collection of collections) {
                    const collectionRef = db.collection(collection.name);
                    // Only fetch non-approved donations (matching mobile app requirement)
                    const query = collectionRef.where('status', '!=', 'approved');
                    
                    const querySnapshot = await query.get();
                    
                    querySnapshot.forEach(doc => {
                        const data = doc.data();
                        
                        // Determine donation type
                        let donationType;
                        if (collection.name === 'donations') {
                            // Normalize donation type for donations collection
                            const rawType = data.donationType?.toLowerCase();
                            if (rawType === 'money') donationType = 'Money Sponsorship';
                            else if (rawType === 'education') donationType = 'Education Sponsorship';
                            else if (rawType === 'medicine') donationType = 'Medicine Sponsorship';
                            else donationType = data.donationType || 'Unknown';
                        } else {
                            donationType = collection.type;
                        }

                        // Handle username field mapping
                        let username = data.username;
                        if (!username && data.fullName) {
                            username = data.fullName;
                        }

                        // Handle timestamp conversion
                        let displayDate = 'N/A';
                        if (data.timestamp) {
                            if (typeof data.timestamp === 'string') {
                                displayDate = data.timestamp.substring(0, 10);
                            } else if (data.timestamp.toDate) {
                                displayDate = data.timestamp.toDate().toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                            }
                        } else if (data.submittedAt && data.submittedAt.toDate) {
                            displayDate = data.submittedAt.toDate().toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }

                        const donation = {
                            id: doc.id,
                            collectionName: collection.name,
                            donationType: donationType,
                            username: username || 'N/A',
                            status: data.status || 'pending',
                            displayDate: displayDate,
                            originalData: data
                        };

                        window.allDonations.push(donation);
                    });
                }

                console.log(`Fetched ${window.allDonations.length} pending donations`);
                filterAndDisplayDonations();

                } catch (error) {
                    console.error('Error fetching donations:', error);
                    donationsLoading.style.display = 'none';
                    donationsList.innerHTML = '<div class="donation-card"><div class="donation-type">Error loading donations. Please refresh the page.</div></div>';
                    donationsList.style.display = 'block';
                }
            };

            // Filter and display donations (matching mobile app logic)
            window.filterAndDisplayDonations = function() {
                const selectedType = donationTypeFilter.value;
                const searchText = searchInput.value.toLowerCase().trim();

                let filteredDonations = window.allDonations.filter(donation => {
                // Type filter
                const matchesType = selectedType === 'all' || donation.donationType === selectedType;
                
                // Search filter
                const matchesSearch = searchText === '' || 
                    donation.username.toLowerCase().includes(searchText) ||
                    donation.donationType.toLowerCase().includes(searchText);

                return matchesType && matchesSearch;
            });

            // Sort by date (newest first)
            filteredDonations.sort((a, b) => {
                const dateA = new Date(a.displayDate);
                const dateB = new Date(b.displayDate);
                return dateB - dateA;
            });

                displayDonations(filteredDonations);
            };

            // Display donations in list (matching mobile app UI)
            window.displayDonations = function(donations) {
            donationsLoading.style.display = 'none';
            
            if (donations.length === 0) {
                donationsList.style.display = 'none';
                noDonationsDiv.style.display = 'block';
                return;
            }

            noDonationsDiv.style.display = 'none';
            donationsList.innerHTML = '';

                donations.forEach(donation => {
                    const donationCard = document.createElement('div');
                    donationCard.className = 'donation-card';
                    donationCard.onclick = () => window.openDonationDetail(donation);

                const statusClass = `status-${donation.status.toLowerCase()}`;

                donationCard.innerHTML = `
                    <div class="donation-type">${donation.donationType}</div>
                    <div class="donation-info">
                        <div>Donor: ${donation.username}</div>
                        <div>Status: <span class="donation-status ${statusClass}">${donation.status}</span></div>
                        <div>Date: ${donation.displayDate}</div>
                    </div>
                `;

                donationsList.appendChild(donationCard);
            });

                donationsList.style.display = 'block';
            };

            // Open donation detail modal (matching mobile app detail view)
            window.openDonationDetail = function(donation) {
                window.currentDonationDetail = donation;
            detailModalTitle.textContent = `${donation.donationType} Details`;
            
            const data = donation.originalData;
            
            let detailHtml = `
                <div class="detail-section">
                    <h4>Basic Information</h4>
                    <div class="detail-grid">
                        <div class="detail-label">Donation Type:</div>
                        <div class="detail-value">${donation.donationType}</div>
                        <div class="detail-label">Donor:</div>
                        <div class="detail-value">${donation.username}</div>
                        <div class="detail-label">Status:</div>
                        <div class="detail-value"><span class="donation-status status-${donation.status.toLowerCase()}">${donation.status}</span></div>
                        <div class="detail-label">Date:</div>
                        <div class="detail-value">${donation.displayDate}</div>
                    </div>
                </div>
            `;

            // Add specific details based on donation type
            if (donation.collectionName === 'donations') {
                // Money/Sponsorship donations
                detailHtml += `
                    <div class="detail-section">
                        <h4>Donation Details</h4>
                        <div class="detail-grid">
                            <div class="detail-label">Amount:</div>
                            <div class="detail-value">${data.amount || 'N/A'}</div>
                            <div class="detail-label">Payment Method:</div>
                            <div class="detail-value">${data.paymentMethod || 'N/A'}</div>
                        </div>
                        ${data.receiptUrl ? `<div><strong>Receipt:</strong><br><img src="${data.receiptUrl}" class="receipt-image" alt="Receipt"></div>` : ''}
                    </div>
                `;
            } else {
                // Item donations
                detailHtml += `
                    <div class="detail-section">
                        <h4>Item Details</h4>
                        <div class="detail-grid">
                            <div class="detail-label">Description:</div>
                            <div class="detail-value">${data.itemDescription || data.description || data.clothingType || data.foodType || data.educationType || data.toyType || 'Not specified'}</div>
                            <div class="detail-label">Quantity:</div>
                            <div class="detail-value">${data.quantity || data.itemQuantity || '1'}</div>
                            <div class="detail-label">Pickup Address:</div>
                            <div class="detail-value">${data.pickupAddress || data.address || 'Not specified'}</div>
                            <div class="detail-label">Contact:</div>
                            <div class="detail-value">${data.contactNumber || data.contact || data.phoneNumber || 'Not provided'}</div>
                        </div>
                    </div>
                `;

                // Add donation images if available
                if (data.imageUrls && Array.isArray(data.imageUrls) && data.imageUrls.length > 0) {
                    detailHtml += `
                        <div class="detail-section">
                            <h4>Donation Images</h4>
                            <div class="donation-images">
                    `;
                    data.imageUrls.forEach((imageUrl, index) => {
                        if (imageUrl) {
                            detailHtml += `<img src="${imageUrl}" class="donation-image" alt="Donation Image ${index + 1}" style="max-width: 200px; margin: 5px; border-radius: 5px;">`;
                        }
                    });
                    detailHtml += `
                            </div>
                        </div>
                    `;
                } else if (data.imageUrl && data.imageUrl !== '') {
                    // Single image URL
                    detailHtml += `
                        <div class="detail-section">
                            <h4>Donation Image</h4>
                            <img src="${data.imageUrl}" class="donation-image" alt="Donation Image" style="max-width: 200px; margin: 5px; border-radius: 5px;">
                        </div>
                    `;
                }
            }

            // Add proof of donation section if exists
            if (data.proofOfDonationText || data.proofOfDonationImageUrl) {
                detailHtml += `
                    <div class="detail-section">
                        <h4>Proof of Donation</h4>
                        ${data.proofOfDonationText ? `<p><strong>Comment:</strong> ${data.proofOfDonationText}</p>` : ''}
                        ${data.proofOfDonationImageUrl ? `<img src="${data.proofOfDonationImageUrl}" class="receipt-image" alt="Proof of Donation">` : ''}
                    </div>
                `;
            }

            // Add admin actions if status is pending
            if (donation.status.toLowerCase() === 'pending') {
                detailHtml += `
                    <div class="proof-section">
                        <h4>Admin Actions</h4>
                        <div class="proof-form">
                            <textarea id="proof-text" class="proof-textarea" placeholder="Add proof of donation comment..."></textarea>
                            <input type="file" id="proof-image" class="proof-file-input" accept="image/*">
                            <button class="btn-save-proof" onclick="saveProofOfDonation()">Save Proof</button>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <button class="admin-btn btn-approve" onclick="approveDonation()">Approve Donation</button>
                        <button class="admin-btn btn-reject" onclick="rejectDonation()">Reject Donation</button>
                    </div>
                `;
            }

                detailContent.innerHTML = detailHtml;
                if (adminDetailModal) adminDetailModal.style.display = 'flex';
            };

            // Close detail modal (make global)
            window.closeDetailModal = function() {
                if (adminDetailModal) adminDetailModal.style.display = 'none';
                window.currentDonationDetail = null;
            };

            // Save proof of donation (matching mobile app functionality)
            window.saveProofOfDonation = async function() {
                if (!window.currentDonationDetail) {
                    alert('No donation selected.');
                    return;
                }

                const proofText = document.getElementById('proof-text')?.value.trim() || '';
                const proofImageFile = document.getElementById('proof-image')?.files[0];

                if (!proofText && !proofImageFile) {
                    alert('Please enter text or select an image for proof.');
                    return;
                }

                try {
                    // Show loading state
                    const saveBtn = document.querySelector('.btn-save-proof');
                    if (saveBtn) {
                        saveBtn.textContent = 'Saving...';
                        saveBtn.disabled = true;
                    }

                    let proofImageUrl = '';
                    
                    // Upload image if provided
                    if (proofImageFile) {
                        console.log('Uploading proof image...');
                        const storageRef = storage.ref(`proof_of_donations/${Date.now()}_${proofImageFile.name}`);
                        const uploadSnapshot = await storageRef.put(proofImageFile);
                        proofImageUrl = await uploadSnapshot.ref.getDownloadURL();
                        console.log('Image uploaded successfully:', proofImageUrl);
                    }

                    // Update Firestore document
                    const updates = {
                        proofOfDonationText: proofText,
                        proofOfDonationImageUrl: proofImageUrl
                    };

                    console.log('Updating Firestore with proof data:', updates);
                    await db.collection(window.currentDonationDetail.collectionName)
                        .doc(window.currentDonationDetail.id)
                        .update(updates);

                    // Update the current donation detail data
                    window.currentDonationDetail.originalData.proofOfDonationText = proofText;
                    window.currentDonationDetail.originalData.proofOfDonationImageUrl = proofImageUrl;

                    alert('Proof of donation saved successfully!');
                    window.closeDetailModal();
                    window.fetchAllDonations(); // Refresh list

                } catch (error) {
                    console.error('Error saving proof:', error);
                    alert('Error saving proof: ' + error.message);
                } finally {
                    // Reset button state
                    const saveBtn = document.querySelector('.btn-save-proof');
                    if (saveBtn) {
                        saveBtn.textContent = 'Save Proof';
                        saveBtn.disabled = false;
                    }
                }
            };

            // Approve donation (matching mobile app functionality)
            window.approveDonation = async function() {
                if (!currentDonationDetail) {
                    alert('No donation selected.');
                    return;
                }
                
                // Check if proof has been provided (check current data)
                const data = window.currentDonationDetail.originalData;
                const hasProofText = data.proofOfDonationText && data.proofOfDonationText.trim() !== '';
                const hasProofImage = data.proofOfDonationImageUrl && data.proofOfDonationImageUrl.trim() !== '';
                
                if (!hasProofText && !hasProofImage) {
                    alert('Please provide proof of donation (image and/or comment) before approving. Use "Save Proof" first.');
                    return;
                }

                if (!confirm('Are you sure you want to approve this donation? This will move it to history.')) {
                    return;
                }

                try {
                    // Show loading state
                    const approveBtn = document.querySelector('.btn-approve');
                    if (approveBtn) {
                        approveBtn.textContent = 'Approving...';
                        approveBtn.disabled = true;
                    }

                    const updates = {
                        status: 'approved',
                        approvedAt: firebase.firestore.FieldValue.serverTimestamp()
                    };

                    console.log('Approving donation:', window.currentDonationDetail.id);
                    await db.collection(window.currentDonationDetail.collectionName)
                        .doc(window.currentDonationDetail.id)
                        .update(updates);

                    alert('Donation approved successfully! Redirecting to history...');
                    
                    // Send notification to user about approval
                    const donationType = window.currentDonationDetail.originalData.donationType || 'general';
                    sendDonationNotification(donationType, 'approved', {
                        userId: window.currentDonationDetail.originalData.userId,
                        donationId: window.currentDonationDetail.id,
                        approvedBy: 'Admin'
                    });
                    
                    window.closeDetailModal();
                    window.fetchAllDonations(); // Refresh list

                } catch (error) {
                    console.error('Error approving donation:', error);
                    alert('Error approving donation: ' + error.message);
                } finally {
                    // Reset button state
                    const approveBtn = document.querySelector('.btn-approve');
                    if (approveBtn) {
                        approveBtn.textContent = 'Approve Donation';
                        approveBtn.disabled = false;
                    }
                }
            };

            // Reject donation (matching mobile app functionality)
            window.rejectDonation = async function() {
                if (!window.currentDonationDetail) {
                    alert('No donation selected.');
                    return;
                }

                if (!confirm('Are you sure you want to reject this donation? This will move it to history.')) {
                    return;
                }

                try {
                    // Show loading state
                    const rejectBtn = document.querySelector('.btn-reject');
                    if (rejectBtn) {
                        rejectBtn.textContent = 'Rejecting...';
                        rejectBtn.disabled = true;
                    }

                    const updates = {
                        status: 'rejected',
                        rejectedAt: firebase.firestore.FieldValue.serverTimestamp()
                    };

                    console.log('Rejecting donation:', window.currentDonationDetail.id);
                    await db.collection(window.currentDonationDetail.collectionName)
                        .doc(window.currentDonationDetail.id)
                        .update(updates);

                    alert('Donation rejected successfully! Redirecting to history...');
                    
                    // Send notification to user about rejection
                    const donationType = window.currentDonationDetail.originalData.donationType || 'general';
                    sendDonationNotification(donationType, 'rejected', {
                        userId: window.currentDonationDetail.originalData.userId,
                        donationId: window.currentDonationDetail.id,
                        rejectedBy: 'Admin',
                        reason: 'Please check the details and resubmit if necessary'
                    });
                    
                    window.closeDetailModal();
                    window.fetchAllDonations(); // Refresh list

                } catch (error) {
                    console.error('Error rejecting donation:', error);
                    alert('Error rejecting donation: ' + error.message);
                } finally {
                    // Reset button state
                    const rejectBtn = document.querySelector('.btn-reject');
                    if (rejectBtn) {
                        rejectBtn.textContent = 'Reject Donation';
                        rejectBtn.disabled = false;
                    }
                }
            };

            // Initialize admin functionality when auth state changes
            auth.onAuthStateChanged(user => {
                if (user) {
                    console.log('Admin user detected, initializing admin donation management');
                    initAdminDonationManagement();
                }
            });
        } // End of isAdminView check
        <?php endif; ?>

        // Send donation notification - MOBILE APP LOGIC  
        function sendDonationNotification(donationType, status, additionalData = {}) {
            try {
                console.log('💝 MOBILE APP LOGIC: Sending donation notification:', donationType, status, additionalData);
                
                // Get user ID from current user or additional data
                const userId = additionalData.userId || (currentUser ? currentUser.uid : null);
                
                if (!userId) {
                    console.log('⚠️ MOBILE APP LOGIC: No user ID available for donation notification');
                    return;
                }
                
                // Only send user notifications for admin actions (approved, rejected)
                // Don't send admin notifications for these actions
                if (status === 'approved' || status === 'rejected') {
                    // Send only user notification using MOBILE APP LOGIC
                fetch('super_simple_notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'send_donation_notification',
                        userId: userId,
                        donationType: donationType,
                        status: status,
                        donationId: additionalData.donationId || null
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        console.log(`✅ MOBILE APP LOGIC: ${status} donation notification sent to user successfully`);
                        
                        // Send chat message using Firebase messaging bridge (with retry logic)
                        const sendDonationStatusChatMessage = () => {
                            if (window.firebaseMessagingBridge) {
                                let chatMessage = '';
                                
                                if (status === 'approved') {
                                    chatMessage = `✅ Excellent news! Your ${donationType} donation has been approved and accepted. Thank you for your generosity in supporting our cause!`;
                                } else if (status === 'rejected') {
                                    chatMessage = `❌ Your ${donationType} donation submission requires attention. Please review the details and resubmit if necessary. Our team is here to help if you have any questions.`;
                                }
                                
                                if (chatMessage) {
                                    window.firebaseMessagingBridge.sendCustomMessage(userId, chatMessage, 'donation', {
                                        hasDonationButton: true,
                                        donationUrl: 'Donation.php'
                                    })
                                        .then(() => console.log(`✅ Donation ${status} chat message sent`))
                                        .catch(error => console.error(`❌ Failed to send donation ${status} chat message:`, error));
                                }
                            } else {
                                console.log('⏳ Firebase messaging bridge not ready yet, retrying in 500ms...');
                                setTimeout(sendDonationStatusChatMessage, 500);
                            }
                        };
                        
                        // Send immediately or retry
                        sendDonationStatusChatMessage();
                    } else {
                        console.log(`❌ MOBILE APP LOGIC: Failed to send ${status} donation notification:`, result.error);
                    }
                })
                .catch(error => {
                    console.log(`❌ MOBILE APP LOGIC: Donation notification error:`, error);
                });
                } else {
                    // For other statuses, don't send anything from this function
                    // Collection-based system will handle 'submitted' status
                    console.log(`ℹ️ MOBILE APP LOGIC: Status '${status}' handled by collection-based system`);
                }
            } catch (error) {
                console.log(`❌ MOBILE APP LOGIC: Send donation notification error:`, error);
            }
        }

        // Track Donation Button - restricted to specific types
        const trackDonationBtn = document.getElementById('track-donation-btn');
        if (trackDonationBtn) {
            trackDonationBtn.addEventListener('click', function() {
                // Only show donations that are trackable (money, medicine sponsorship, education sponsorship)
                const trackModal = document.getElementById('trackDonationModal');
                if (trackModal) {
                    trackModal.style.display = 'flex';
                    fetchTrackableDonations();
                }
            });
        }

        async function fetchTrackableDonations() {
            const trackDonationList = document.getElementById('trackDonationList');
            const trackNoRecordsMsg = document.getElementById('trackNoRecordsMsg');
            
            if (!trackDonationList) return;
            
            trackDonationList.innerHTML = '';
            trackNoRecordsMsg.style.display = 'none';

            const currentUser = auth.currentUser;
            if (!currentUser) {
                trackNoRecordsMsg.textContent = 'Please log in to track your donations.';
                trackNoRecordsMsg.style.display = 'block';
                return;
            }

            try {
                // Only fetch from donations collection (money donations including sponsorships)
                const snapshot = await db.collection('donations')
                    .where('userId', '==', currentUser.uid)
                    .get();

                let trackableDonations = [];

                snapshot.forEach(doc => {
                    const donation = doc.data();
                    const donationType = donation.donationType?.toLowerCase() || '';
                    
                    // Only include money, medicine sponsorship, and education sponsorship
                    if (donationType.includes('money') || 
                        donationType.includes('medicine') || 
                        donationType.includes('education') ||
                        donation.amount) { // Has amount field = money donation
                        
                        trackableDonations.push({
                            ...donation,
                            id: doc.id
                        });
                    }
                });

                if (trackableDonations.length === 0) {
                    trackNoRecordsMsg.textContent = 'No trackable donations found. Only Money, Medicine Sponsorship, and Education Sponsorship donations can be tracked.';
                    trackNoRecordsMsg.style.display = 'block';
                    return;
                }

                // Sort by timestamp
                trackableDonations.sort((a, b) => {
                    const aTime = a.timestamp?.toDate?.() || new Date(a.timestamp || 0);
                    const bTime = b.timestamp?.toDate?.() || new Date(b.timestamp || 0);
                    return bTime - aTime;
                });

                trackableDonations.forEach(donation => {
                    const item = document.createElement('div');
                    item.className = 'track-donation-item';
                    
                    const statusClass = donation.status?.toLowerCase() === 'approved' ? 'status-approved' : 
                                       donation.status?.toLowerCase() === 'rejected' ? 'status-rejected' : 'status-pending';
                    
                    const amount = donation.amount || '0';
                    
                    item.innerHTML = `
                        <h4>${donation.donationType || 'Money Donation'}</h4>
                        <p><strong>Amount:</strong> ₱${amount}</p>
                        <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${donation.status?.toUpperCase() || 'PENDING'}</span></p>
                        <p><strong>Date:</strong> ${formatTimestamp(donation.timestamp)}</p>
                        ${donation.proofOfDonationImageUrl ? `<img src="${donation.proofOfDonationImageUrl}" class="receipt-image" alt="Proof of Donation">` : ''}
                    `;
                    trackDonationList.appendChild(item);
                });

            } catch (error) {
                console.error('Error fetching trackable donations:', error);
                trackNoRecordsMsg.textContent = 'Error loading donations: ' + error.message;
                trackNoRecordsMsg.style.display = 'block';
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