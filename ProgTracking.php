<?php
require_once 'session_check.php';

if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #F2F2F2;
            min-height: 100vh;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
            /* Ensures container takes at least full viewport height */
        }

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

        /* Maintain sidebar for admin users but adjust main content */
        .admin-layout .main-content {
            padding: 0;
            background-color: #F2F2F2;
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

        /* Add a new wrapper for the sidebar and main content */
        .content-wrapper {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 60px);
        }

        /* Header Section - Enhanced mobile app style */
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
        }

        .header-section .subtitle {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        /* Progress Indicator Bar - Enhanced horizontal scrollable */
        .progress-indicator {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            border: 1px solid #e0e0e0;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 800px;
            padding: 10px 0;
            gap: 10px;
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: transform 0.2s ease;
            min-width: 60px;
        }

        .step-indicator:hover {
            transform: translateY(-2px);
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border: 3px solid;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .step-circle::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            z-index: -1;
            filter: blur(8px);
            opacity: 0.6;
        }

        /* Website theme colors with gradients */
        .step-circle.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #FFFFFF;
            border-color: #28a745;
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
        }

        .step-circle.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #FFFFFF;
            border-color: #6ea4ce;
            box-shadow: 0 4px 20px rgba(110, 164, 206, 0.4);
        }

        .step-circle.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #FFFFFF;
            border-color: #6c757d;
            box-shadow: 0 4px 20px rgba(108, 117, 125, 0.4);
        }

        /* Disabled/locked step styling - make them visually appear disabled */
        .step-indicator[style*="not-allowed"] {
            pointer-events: auto; /* Keep pointer events for the locked message */
        }

        .step-indicator[style*="not-allowed"] .step-circle {
            background: #e0e0e0 !important;
            color: #999 !important;
            border-color: #ccc !important;
            box-shadow: none !important;
        }

        .step-indicator[style*="not-allowed"] .step-label {
            color: #999 !important;
        }

        .step-indicator[style*="not-allowed"]:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        .step-label {
            font-size: 10px;
            text-align: center;
            color: #666;
            font-weight: 500;
        }

        /* Steps Content Section - Vertical cards like mobile */
        .steps-content {
            display: block;
        }

        .step-card {
            background: #ffffff;
            border-radius: 20px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            position: relative;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6ea4ce, #61C2C7);
            border-radius: 20px 20px 0 0;
        }

        .step-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 8px 25px rgba(110, 164, 206, 0.2);
        }

        .step-card.locked {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .step-card.locked:hover {
            transform: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .step-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .step-card:hover .step-image {
            transform: scale(1.05);
        }

        .step-content {
            padding: 25px;
            position: relative;
        }

        .step-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .step-status {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(110, 164, 206, 0.1);
            border-radius: 15px;
            margin-top: 15px;
        }

        .status-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .status-text {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-text.complete {
            color: #28a745;
        }

        .status-text.in_progress {
            color: #6ea4ce;
        }

        .status-text.locked {
            color: #6c757d;
        }

        .status-icon.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }

        .status-icon.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #fff;
        }

        .status-icon.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #fff;
        }

        /* Submitted Documents Section Styling */
        .submitted-documents-section {
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .documents-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e8f4fd;
            border-radius: 8px;
            padding: 10px;
            background: #f8fcff;
        }

        .document-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: #ffffff;
            transition: all 0.3s ease;
            border-left: 4px solid #6ea4ce;
        }

        .document-item:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(110, 164, 206, 0.2);
        }

        .document-item:last-child {
            margin-bottom: 0;
        }

        /* Admin Progress View Styling */
        .user-progress-view {
            width: 100%;
        }

        .user-progress-view .progress-indicator,
        .user-progress-view .steps-content,
        .user-progress-view .step-detail-view {
            /* Inherit all styling from regular user view */
        }

        /* Admin Step Cards - Mobile App Style */
        .admin-step-card {
            background: #ffffff;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .admin-step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.15);
        }

        .admin-step-header {
            display: flex;
            align-items: center;
            padding: 20px;
            cursor: pointer;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            transition: background 0.3s ease;
        }

        .admin-step-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .admin-step-title {
            display: flex;
            align-items: center;
            flex: 1;
            gap: 15px;
        }

        .admin-step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(110, 164, 206, 0.3);
        }

        .admin-step-info h4 {
            margin: 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .admin-step-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-step-status.status-complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .admin-step-status.status-in-progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .admin-step-status.status-locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .admin-step-expand {
            font-size: 20px;
            color: #6ea4ce;
            margin-left: 15px;
            transition: transform 0.3s ease;
        }

        .admin-step-expand.expanded {
            transform: rotate(180deg);
        }

        .admin-step-content {
            display: none;
            padding: 0;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .admin-step-content.visible {
            display: block;
            padding: 25px;
        }

        .admin-step-content > div {
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .admin-step-content > div:last-child {
            margin-bottom: 0;
        }

        .admin-step-content h5 {
            margin: 0 0 15px 0;
            color: #2c5aa0;
            font-size: 16px;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }

        /* Admin Steps Container */
        .admin-steps-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px 0;
        }

        /* Loading and Error States */
        .loading-message {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }

        .login-required {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .login-required h3 {
            color: #c62828;
            margin-bottom: 20px;
        }

        .login-required a {
            background: #6ea4ce;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .login-required a:hover {
            background: #5bb3f0;
        }

        .confirmation-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .dialog-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .dialog-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .dialog-message {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.5;
        }

        .dialog-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .btn-secondary {
            background: #ffffff;
            color: #333;
            border: 2px solid #6ea4ce;
        }

        .btn-secondary:hover {
            background: #6ea4ce;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.3);
        }

        /* --- Progress Bar Styles --- */
        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 30px;
            height: 20px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            /* Will be updated by JavaScript */
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            border-radius: 10px;
            text-align: center;
            color: white;
            line-height: 20px;
            /* Center text vertically */
            font-size: 12px;
            transition: width 0.5s ease-in-out;
        }

        /* --- Grid Container Styles (Like the image) --- */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            /* Responsive grid */
            gap: 25px;
            padding-bottom: 20px;
            /* Space at the bottom */
        }

        .progress-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 180px;
            /* Ensure cards have a minimum height */
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            /* For percentage circle */
        }

        .progress-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .card-percentage {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0e0e0;
            /* Default grey for uncompleted */
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            flex-shrink: 0;
            /* ADD THIS LINE to prevent it from shrinking */
        }

        .card-percentage.complete {
            background-color: #28a745;
            /* Green for completed */
            color: white;
            border-color: #28a745;
        }

        .card-percentage.partial {
            background-color: #ffc107;
            /* Yellow for partial */
            color: #333;
            border-color: #ffc107;
        }

        .card-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #444;
            flex-grow: 1;
            /* Allow title to take up available space */
            margin-right: 10px;
        }

        .card-meta {
            font-size: 0.9em;
            color: #777;
            margin-top: 10px;
            /* Space between title and meta */
        }

<?php
require_once 'session_check.php';

if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #F2F2F2;
            min-height: 100vh;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
            /* Ensures container takes at least full viewport height */
        }

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

        /* Maintain sidebar for admin users but adjust main content */
        .admin-layout .main-content {
            padding: 0;
            background-color: #F2F2F2;
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

        /* Add a new wrapper for the sidebar and main content */
        .content-wrapper {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 60px);
        }

        /* Header Section - Enhanced mobile app style */
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
        }

        .header-section .subtitle {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        /* Progress Indicator Bar - Enhanced horizontal scrollable */
        .progress-indicator {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            border: 1px solid #e0e0e0;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 800px;
            padding: 10px 0;
            gap: 10px;
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: transform 0.2s ease;
            min-width: 60px;
        }

        .step-indicator:hover {
            transform: translateY(-2px);
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border: 3px solid;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .step-circle::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            z-index: -1;
            filter: blur(8px);
            opacity: 0.6;
        }

        /* Website theme colors with gradients */
        .step-circle.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #FFFFFF;
            border-color: #28a745;
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
        }

        .step-circle.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #FFFFFF;
            border-color: #6ea4ce;
            box-shadow: 0 4px 20px rgba(110, 164, 206, 0.4);
        }

        .step-circle.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #FFFFFF;
            border-color: #6c757d;
            box-shadow: 0 4px 20px rgba(108, 117, 125, 0.4);
        }

        /* Disabled/locked step styling - make them visually appear disabled */
        .step-indicator[style*="not-allowed"] {
            pointer-events: auto; /* Keep pointer events for the locked message */
        }

        .step-indicator[style*="not-allowed"] .step-circle {
            background: #e0e0e0 !important;
            color: #999 !important;
            border-color: #ccc !important;
            box-shadow: none !important;
        }

        .step-indicator[style*="not-allowed"] .step-label {
            color: #999 !important;
        }

        .step-indicator[style*="not-allowed"]:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        .step-label {
            font-size: 10px;
            text-align: center;
            color: #666;
            font-weight: 500;
        }

        /* Steps Content Section - Vertical cards like mobile */
        .steps-content {
            display: block;
        }

        .step-card {
            background: #ffffff;
            border-radius: 20px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            position: relative;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6ea4ce, #61C2C7);
            border-radius: 20px 20px 0 0;
        }

        .step-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 8px 25px rgba(110, 164, 206, 0.2);
        }

        .step-card.locked {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .step-card.locked:hover {
            transform: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .step-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .step-card:hover .step-image {
            transform: scale(1.05);
        }

        .step-content {
            padding: 25px;
            position: relative;
        }

        .step-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .step-status {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(110, 164, 206, 0.1);
            border-radius: 15px;
            margin-top: 15px;
        }

        .status-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .status-text {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-text.complete {
            color: #28a745;
        }

        .status-text.in_progress {
            color: #6ea4ce;
        }

        .status-text.locked {
            color: #6c757d;
        }

        .status-icon.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }

        .status-icon.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #fff;
        }

        .status-icon.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #fff;
        }

        /* Submitted Documents Section Styling */
        .submitted-documents-section {
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .documents-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e8f4fd;
            border-radius: 8px;
            padding: 10px;
            background: #f8fcff;
        }

        .document-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: #ffffff;
            transition: all 0.3s ease;
            border-left: 4px solid #6ea4ce;
        }

        .document-item:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(110, 164, 206, 0.2);
        }

        .document-item:last-child {
            margin-bottom: 0;
        }

        /* Admin Progress View Styling */
        .user-progress-view {
            width: 100%;
        }

        .user-progress-view .progress-indicator,
        .user-progress-view .steps-content,
        .user-progress-view .step-detail-view {
            /* Inherit all styling from regular user view */
        }

        /* Admin Step Cards - Mobile App Style */
        .admin-step-card {
            background: #ffffff;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .admin-step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.15);
        }

        .admin-step-header {
            display: flex;
            align-items: center;
            padding: 20px;
            cursor: pointer;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            transition: background 0.3s ease;
        }

        .admin-step-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .admin-step-title {
            display: flex;
            align-items: center;
            flex: 1;
            gap: 15px;
        }

        .admin-step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(110, 164, 206, 0.3);
        }

        .admin-step-info h4 {
            margin: 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .admin-step-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-step-status.status-complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .admin-step-status.status-in-progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .admin-step-status.status-locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .admin-step-expand {
            font-size: 20px;
            color: #6ea4ce;
            margin-left: 15px;
            transition: transform 0.3s ease;
        }

        .admin-step-expand.expanded {
            transform: rotate(180deg);
        }

        .admin-step-content {
            display: none;
            padding: 0;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .admin-step-content.visible {
            display: block;
            padding: 25px;
        }

        .admin-step-content > div {
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .admin-step-content > div:last-child {
            margin-bottom: 0;
        }

        .admin-step-content h5 {
            margin: 0 0 15px 0;
            color: #2c5aa0;
            font-size: 16px;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }

        /* Admin Steps Container */
        .admin-steps-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px 0;
        }

        /* Loading and Error States */
        .loading-message {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }

        .login-required {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .login-required h3 {
            color: #c62828;
            margin-bottom: 20px;
        }

        .login-required a {
            background: #6ea4ce;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .login-required a:hover {
            background: #5bb3f0;
        }

        .confirmation-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .dialog-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .dialog-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .dialog-message {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.5;
        }

        .dialog-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .btn-secondary {
            background: #ffffff;
            color: #333;
            border: 2px solid #6ea4ce;
        }

        .btn-secondary:hover {
            background: #6ea4ce;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.3);
        }

        /* --- Progress Bar Styles --- */
        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 30px;
            height: 20px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            /* Will be updated by JavaScript */
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            border-radius: 10px;
            text-align: center;
            color: white;
            line-height: 20px;
            /* Center text vertically */
            font-size: 12px;
            transition: width 0.5s ease-in-out;
        }

        /* --- Grid Container Styles (Like the image) --- */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            /* Responsive grid */
            gap: 25px;
            padding-bottom: 20px;
            /* Space at the bottom */
        }

        .progress-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 180px;
            /* Ensure cards have a minimum height */
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            /* For percentage circle */
        }

        .progress-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .card-percentage {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0e0e0;
            /* Default grey for uncompleted */
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            flex-shrink: 0;
            /* ADD THIS LINE to prevent it from shrinking */
        }

        .card-percentage.complete {
            background-color: #28a745;
            /* Green for completed */
            color: white;
            border-color: #28a745;
        }

        .card-percentage.partial {
            background-color: #ffc107;
            /* Yellow for partial */
            color: #333;
            border-color: #ffc107;
        }

        .card-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #444;
            flex-grow: 1;
            /* Allow title to take up available space */
            margin-right: 10px;
        }

        .card-meta {
            font-size: 0.9em;
            color: #777;
            margin-top: 10px;
            /* Space between title and meta */
        }

        .card-status {
            font-size: 0.9em;
            font-weight: 500;
            color: #888;
            margin-top: 10px;
        }

        /* --- Individual Step View Styles --- */
        .step-detail-view {
            display: none;
            /* Hidden by default */
            padding: 0;
            background-color: #f5f7fa;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
        }

        .step-detail-header {
            background: linear-gradient(135deg, #6ea4ce 0%, #61C2C7 100%);
            color: white;
            padding: 25px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: relative;
        }

        .step-detail-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .step-detail-header .subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }

        .step-detail-content {
            padding: 25px;
        }

        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            position: relative;
            z-index: 2;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            min-width: 140px;
            justify-content: center;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .back-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .admin-comment-section {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            display: none;
        }

        .admin-comment-section.visible {
            display: block;
        }

        .admin-comment-title {
            font-size: 18px;
            font-weight: 600;
            color: #856404;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-comment-text {
            color: #856404;
            line-height: 1.6;
            font-size: 15px;
        }

        .requirement-card {
            background: #ffffff;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .requirement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.15);
        }

        .requirement-header {
            background: rgba(110, 164, 206, 0.1);
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .requirement-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .requirement-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .requirement-link {
            color: #6ea4ce;
            text-decoration: none;
            font-weight: 500;
            word-break: break-all;
        }

        .requirement-link:hover {
            color: #5a8fb5;
            text-decoration: underline;
        }

        .requirement-content {
            padding: 20px;
        }

        .submission-status {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(110, 164, 206, 0.1);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .status-icon-large {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            flex-shrink: 0;
        }

        .status-icon-large.submitted {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-icon-large.pending {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        .status-icon-large.not-submitted {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .status-details {
            flex: 1;
        }

        .status-text-large {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .status-text-large.submitted {
            color: #28a745;
        }

        .status-text-large.pending {
            color: #fd7e14;
        }

        .status-text-large.not-submitted {
            color: #6c757d;
        }

        .attempts-text {
            font-size: 14px;
            color: #666;
        }

        .upload-section {
            margin-top: 20px;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 2px dashed #6ea4ce;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: #ffffff;
            border: 2px solid #6ea4ce;
            border-radius: 10px;
            color: #6ea4ce;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background: #6ea4ce;
            color: white;
        }

        .upload-button {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(110, 164, 206, 0.3);
        }

        .upload-button:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .upload-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .reupload-button {
            background: linear-gradient(135deg, #61C2C7, #4fa8ad);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .reupload-button:hover {
            background: linear-gradient(135deg, #4fa8ad, #3e8e93);
            transform: translateY(-1px);
        }

        .selected-file {
            background: #e8f5e8;
            border: 1px solid #28a745;
            padding: 12px;
            border-radius: 8px;
            color: #155724;
            font-weight: 500;
        }

        .form-link-section {
            background: #e8f4fd;
            border: 1px solid #2196F3;
            border-radius: 12px;
            padding: 20px;
            margin-top: 15px;
        }

        .form-link-title {
            font-weight: 600;
            color: #1976D2;
            margin-bottom: 10px;
        }

        .form-submit-button {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .form-submit-button:hover {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            transform: translateY(-1px);
            text-decoration: none;
            color: white;
        }

        .requirement-link {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px 0;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .requirement-link:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-1px);
            text-decoration: none;
            color: white;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .view-document-button {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-document-button:hover {
            background: linear-gradient(135deg, #138496, #117a8b);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            /* Remove body flex-direction change here, it's now handled by .content-wrapper */
            .content-wrapper {
                flex-direction: column;
                /* Stack sidebar and main content on small screens */
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
                padding: 15px 20px;
            }

            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            .sidebar li {
                margin: 5px 10px;
            }

            .main-content {
                padding: 20px;
            }

            .progress-grid {
                grid-template-columns: 1fr;
                /* Stack cards on small screens */
            }

            .progress-card {
                min-height: auto;
            }

            .step-detail-view .upload-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .step-detail-view .upload-form input[type="file"],
            .step-detail-view .upload-form button {
                width: 100%;
            }

            .step-detail-header {
                padding: 20px;
            }

            .step-detail-header h3 {
                font-size: 20px;
            }

            .step-detail-content {
                padding: 15px;
            }

            .requirement-header,
            .requirement-content {
                padding: 15px;
            }

            .submission-status {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .status-details {
                text-align: center;
            }

            .upload-form {
                padding: 15px;
            }

            .back-button {
                padding: 10px 16px;
                font-size: 12px;
                min-width: 120px;
            }
            
            .mobile-user-header {
                padding: 16px 20px;
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            
            .mobile-user-name-title {
                font-size: 18px;
                order: 1;
            }
            
            .back-button {
                order: 2;
                align-self: center;
            }
        }

        /* Mobile App Style Header */
        .mobile-app-header {
            width: 100%;
            height: 64px;
            background-color: #6EC6FF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            margin-bottom: 0;
        }

        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .header-logo {
            width: 180px;
            height: 40px;
            object-fit: contain;
        }

        .header-text {
            color: white;
            font-weight: bold;
            font-size: 14px;
            margin-top: 2px;
        }

        /* Page Title */
        .page-title {
            padding: 16px;
            background-color: #FFFFFF;
        }

        .page-title h2 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            color: #333333;
        }

                 /* Mobile App Style Users Container */
         .mobile-users-container {
             background-color: #F2F2F2;
             min-height: calc(100vh - 64px - 80px);
             padding: 0 16px 16px 16px;
         }

         /* Responsive adjustments for mobile app style */
         @media (max-width: 768px) {
             .mobile-app-header {
                 height: 56px;
                 padding: 8px;
             }

             .header-logo {
                 width: 140px;
                 height: 32px;
             }

             .header-text {
                 font-size: 12px;
             }

             .page-title {
                 padding: 12px 16px;
             }

             .page-title h2 {
                 font-size: 20px;
             }

             .mobile-user-card {
                 padding: 12px;
                 margin-bottom: 6px;
                 margin-top: 6px;
             }

             .mobile-user-name {
                 font-size: 16px;
             }

             .mobile-action-link {
                 font-size: 14px;
             }

             .mobile-user-actions {
                 gap: 6px;
             }
         }

        .mobile-users-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* Mobile App Style User Cards */
        .mobile-user-card {
            background-color: #FFFFFF;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 8px;
            margin-top: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .mobile-user-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .mobile-user-info {
            flex: 1;
            margin-right: 16px;
        }

        .mobile-user-name {
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            margin: 0;
        }

        .mobile-user-actions {
            display: flex;
            gap: 8px;
        }

        .mobile-action-link {
            color: #33B5E5;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .mobile-action-link:hover {
            color: #0099CC;
            text-decoration: underline;
        }

        .no-users-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1em;
            background-color: #FFFFFF;
            border-radius: 8px;
            margin: 16px 0;
        }

        /* User Progress Detail View for Admin */
        .user-progress-view {
            background: #F2F2F2;
            padding: 0;
            margin: 0;
        }

        .user-progress-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e8f4fd;
        }

        .user-progress-title {
            flex: 1;
        }

        .user-progress-title h3 {
            color: #2c5aa0;
            font-size: 1.8em;
            font-weight: 600;
            margin: 0 0 5px 0;
        }

        .user-progress-title p {
            color: #666;
            margin: 0;
        }

        .back-to-list-btn {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .back-to-list-btn:hover {
            background: linear-gradient(135deg, #5a6268, #3d4449);
            transform: translateY(-1px);
        }

        /* Responsive Design for Admin View */
        @media (max-width: 768px) {
            .user-card {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .user-actions {
                width: 100%;
                justify-content: center;
            }

            .action-btn {
                flex: 1;
                max-width: 120px;
            }

                         .user-progress-header {
                 flex-direction: column;
                 gap: 15px;
                 text-align: center;
             }
         }

         /* Mobile App Style Progress Container */
         .admin-steps-container {
             background: #F0F2F5;
             padding: 16px;
             min-height: calc(100vh - 140px);
         }

         /* User Name Header - Better integrated design */
         .mobile-user-header {
             display: flex;
             align-items: center;
             gap: 16px;
             margin-bottom: 20px;
             padding: 20px 24px;
             background: linear-gradient(135deg, #6ea4ce, #7CB9E8);
             border-radius: 12px;
             box-shadow: 0 4px 16px rgba(110, 164, 206, 0.25);
             position: relative;
             overflow: hidden;
         }
         
         .mobile-user-header::before {
             content: '';
             position: absolute;
             top: 0;
             left: 0;
             right: 0;
             bottom: 0;
             background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
             pointer-events: none;
         }

         .mobile-user-name-title {
             font-size: 22px;
             font-weight: 700;
             color: white;
             margin: 0;
             flex: 1;
             overflow: hidden;
             text-overflow: ellipsis;
             white-space: nowrap;
             text-shadow: 0 2px 4px rgba(0,0,0,0.1);
             position: relative;
             z-index: 1;
         }

         /* Mobile App Step Cards */
         .admin-step-card {
             margin-bottom: 16px;
             border: 1px solid #E0E0E0;
             border-radius: 8px;
             background: white;
             overflow: hidden;
         }

         .admin-step-header {
             background: white;
             padding: 16px;
             cursor: pointer;
             border-bottom: none;
         }

         .admin-step-header:hover {
             background: #F8F9FA;
         }

         .admin-step-title {
             font-size: 16px;
             font-weight: bold;
             color: #333333;
             margin: 0 0 8px 0;
         }

         .admin-step-status {
             font-size: 14px;
             margin: 0 0 4px 0;
         }

         .admin-step-status.complete {
             color: #4CAF50;
             font-weight: 500;
         }

         .admin-step-status.in_progress {
             color: #2196F3;
             font-weight: 500;
         }

         .admin-step-status.locked {
             color: #9E9E9E;
             font-weight: 500;
         }

         .admin-step-comment {
             font-size: 14px;
             color: #2196F3;
             margin: 4px 0 0 0;
             display: none;
         }

         .admin-step-comment.visible {
             display: block;
         }

         .admin-step-content {
             display: none;
             background: #FFFFFF;
             border-top: 1px solid #E0E0E0;
             padding: 16px;
         }

         .admin-step-content.visible {
             display: block;
         }

         /* Mobile App Documents Section */
         .documents-section {
             margin-bottom: 16px;
         }

         .documents-header {
             font-size: 16px;
             font-weight: bold;
             margin-bottom: 4px;
             color: #333333;
         }

         .documents-container {
             margin-bottom: 8px;
         }

         /* Mobile App Admin Actions */
         .admin-actions-header {
             font-size: 16px;
             font-weight: bold;
             margin: 16px 0 8px 0;
             color: #333333;
         }

         .admin-controls {
             margin: 8px 0 16px 0;
         }

         .admin-btn {
             width: 100%;
             padding: 12px;
             border: none;
             border-radius: 4px;
             font-weight: 500;
             cursor: pointer;
             transition: background-color 0.2s ease;
             color: white;
             text-transform: uppercase;
             font-size: 14px;
         }

         .btn-mark-complete {
             background: #4CAF50;
         }

         .btn-mark-complete:hover {
             background: #45a049;
         }

         /* Mobile App Comment Section */
         .admin-comment-section {
             margin-top: 12px;
         }

         .comment-input {
             width: calc(100% - 24px);
             min-height: 40px;
             padding: 12px;
             border: 1px solid #E0E0E0;
             border-radius: 20px;
             font-family: inherit;
             font-size: 14px;
             resize: none;
             margin-bottom: 8px;
             box-sizing: border-box;
             background: #F5F5F5;
             outline: none;
         }

         .comment-input::placeholder {
             color: #999999;
         }

         .comment-btn {
             background: #2196F3;
             color: white;
             padding: 10px 20px;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             float: right;
             transition: background-color 0.2s ease;
             text-transform: uppercase;
             font-weight: 500;
             font-size: 14px;
         }

         .comment-btn:hover {
             background: #1976D2;
         }

         .documents-section {
             margin-top: 16px;
         }

         .documents-section h5 {
             color: #333333;
             margin: 0 0 8px 0;
             font-size: 16px;
             font-weight: bold;
         }

         .document-list {
             margin: 8px 0;
         }

         .document-item {
             margin-bottom: 12px;
             line-height: 1.4;
         }

         .document-name {
             font-size: 14px;
             color: #333333;
             margin-bottom: 2px;
             word-break: break-all;
         }

         .btn-view-doc {
             background: none;
             color: #2196F3;
             padding: 0;
             border: none;
             font-size: 14px;
             cursor: pointer;
             text-decoration: underline;
             display: block;
             margin-bottom: 2px;
         }

         .btn-view-doc:hover {
             color: #1976D2;
         }

         .document-date {
             font-size: 12px;
             color: #999999;
         }

         /* Mobile Responsive */
         @media (max-width: 768px) {
             .admin-step-header {
                 padding: 15px;
                 flex-direction: column;
                 gap: 10px;
                 text-align: center;
             }

             .admin-step-title {
                 justify-content: center;
             }

             .admin-controls {
                 flex-direction: column;
             }

             .comment-input-group {
                 flex-direction: column;
             }

             .document-item {
                 flex-direction: column;
                 gap: 10px;
                 text-align: center;
             }

             .document-actions {
                 justify-content: center;
            }
        }

                /* USER VIEW - Mobile App Design Exact Match */
        .user-view-container {
            background: #FFFFFF;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        /* Mobile App Style Header - Exact Match */
        .mobile-app-header {
            width: 100%;
            height: 64px;
            background: #6EC6FF;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            margin-bottom: 0;
            position: relative;
        }

        .back-button {
            width: 24px;
            height: 24px;
            margin-right: 16px;
        }

        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .header-logo {
            width: 180px;
            height: 40px;
            object-fit: contain;
        }

        .header-text {
            color: white;
            font-weight: bold;
            font-size: 14px;
            margin-top: 2px;
        }

        /* Progress Tracking Title */
        .progress-title {
            background: #FFFFFF;
            padding: 16px 8px;
            margin: 0;
            border-bottom: none;
        }

        .progress-title h2 {
            margin: 0 0 16px 0;
            font-size: 18px;
            font-weight: bold;
            color: #000000;
        }

        /* Progress Indicator Circles - Horizontal Scroll */
        .progress-indicator-scroll {
            width: 100%;
            max-width: 379px;
            margin: 0 auto 16px auto;
            padding: 0 8px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .progress-indicator-scroll::-webkit-scrollbar {
            display: none;
        }

        .progress-circles-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            min-width: max-content;
            margin-bottom: 16px;
        }

        .progress-circle-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 8px;
            min-width: 40px;
        }

        .progress-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            margin-bottom: 4px;
        }

        .progress-circle.complete {
            background-color: #4CAF50;
            background-image: url('images/ic_status_complete.png');
        }

        .progress-circle.in_progress {
            background-color: #FF9800;
            background-image: url('images/ic_status_in_progress.png');
        }

        .progress-circle.locked {
            background-color: #9E9E9E;
            background-image: url('images/ic_status_locked.png');
        }

        .progress-circle-text {
            font-size: 10px;
            color: #000000;
            text-align: center;
        }

        /* Main Steps Container - Scrollable */
        .mobile-steps-container {
            background: #FFFFFF;
            padding: 8px;
            flex: 1;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        /* Large Image Cards - Exact Mobile App Match */
        .mobile-step-card {
            background: #61C2C7;
            border: none;
            border-radius: 0;
            margin-bottom: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s ease;
            padding: 0;
            width: 100%;
        }

        .mobile-step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .mobile-step-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .mobile-step-content-overlay {
            padding: 0;
            background: #61C2C7;
        }

        .mobile-step-title {
            font-size: 16px;
            font-weight: normal;
            color: #000000;
            margin: 8px 0 0 0;
            padding: 8px;
        }

        .mobile-step-status-container {
            display: flex;
            align-items: center;
            padding: 8px;
            margin-bottom: 0;
        }

        .mobile-step-status-icon {
            width: 24px;
            height: 24px;
            margin-right: 8px;
            object-fit: contain;
        }

        .mobile-step-status-text {
            font-size: 20px;
            font-weight: normal;
            margin: 0;
            padding: 0;
        }

        .mobile-step-status-text.complete {
            color: #FFFF00;
        }

        .mobile-step-status-text.in_progress {
            color: #000000;
        }

        .mobile-step-status-text.locked {
            color: #FF0000;
        }

        /* Detail Page Styles */
        .step-detail-page {
            display: none;
            background: #F0F2F5;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .step-detail-page.visible {
            display: block;
        }

        .step-detail-header {
            background: #6EC6FF;
            padding: 12px;
            display: flex;
            align-items: center;
            color: white;
        }

        .back-btn {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-right: 16px;
        }

        .step-detail-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .step-detail-content {
            padding: 16px;
        }

        .step-detail-user-name {
            font-size: 24px;
            font-weight: bold;
            color: #333333;
            margin: 0 0 16px 0;
            text-align: left;
        }

        .step-detail-card {
            background: #F0F0F0;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 8px;
        }

        .step-detail-card-title {
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            margin: 0 0 4px 0;
        }

        .step-detail-card-status {
            font-size: 16px;
            color: #666666;
            margin: 4px 0;
        }

        .step-detail-card-comment {
            font-size: 14px;
            color: #2196F3;
            margin: 4px 0 0 0;
            display: none;
        }

        .step-detail-card-comment.visible {
            display: block;
        }

        .step-detail-expanded {
            background: #FFFFFF;
            border-top: 1px solid #E0E0E0;
            padding: 16px;
            margin-top: 0;
        }

        .documents-section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333333;
        }

        .document-item {
            background: #F8F9FA;
            border: 1px solid #E0E0E0;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .document-title {
            font-size: 14px;
            font-weight: 500;
            color: #333333;
            margin: 0 0 4px 0;
        }

        .document-status {
            font-size: 12px;
            color: #666666;
        }

        .document-view-btn {
            background: none;
            border: none;
            color: #2196F3;
            font-size: 14px;
            cursor: pointer;
            text-decoration: underline;
            padding: 4px 8px;
        }

        .document-view-btn:hover {
            color: #1976D2;
        }

        .upload-section {
            margin-top: 16px;
            padding: 16px;
            background: #F8F9FA;
            border-radius: 8px;
            border: 1px solid #E0E0E0;
        }

        .upload-title {
            font-size: 16px;
            font-weight: bold;
            color: #333333;
            margin: 0 0 12px 0;
        }

        .upload-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .upload-btn {
            background: #28A745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
        }

        .upload-btn:hover {
            background: #218838;
        }

        /* Hide old user styles on mobile layout */
        .user-mobile-layout .header-section,
        .user-mobile-layout .progress-indicator,
        .user-mobile-layout .steps-content {
            display: none !important;
        }

        /* Hide sidebar for mobile user layout */
        .user-mobile-layout {
            background: #F0F2F5;
            padding: 0;
            margin: 0;
        }

        .user-mobile-layout .sidebar {
            display: none !important;
        }

        .user-mobile-layout .main-content {
            padding: 0;
            margin: 0;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-app-header {
                height: 56px;
                padding: 8px;
            }

            .header-logo {
                width: 150px;
                height: 32px;
            }

            .header-text {
                font-size: 12px;
            }

            .progress-title {
                padding: 12px;
            }

            .progress-title h2 {
                font-size: 16px;
            }

            .user-name-title {
                font-size: 20px;
            }

            .mobile-steps-container {
                padding: 12px;
            }

            .mobile-step-header {
                padding: 12px;
            }

            .mobile-step-title {
                font-size: 16px;
            }

            .mobile-step-content {
                padding: 12px;
            }
        }
    </style>
</head>

<?php
require_once 'session_check.php';

if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #F2F2F2;
            min-height: 100vh;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
            /* Ensures container takes at least full viewport height */
        }

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

        /* Maintain sidebar for admin users but adjust main content */
        .admin-layout .main-content {
            padding: 0;
            background-color: #F2F2F2;
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

        /* Add a new wrapper for the sidebar and main content */
        .content-wrapper {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 60px);
        }

        /* Header Section - Enhanced mobile app style */
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
        }

        .header-section .subtitle {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        /* Progress Indicator Bar - Enhanced horizontal scrollable */
        .progress-indicator {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            border: 1px solid #e0e0e0;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 800px;
            padding: 10px 0;
            gap: 10px;
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: transform 0.2s ease;
            min-width: 60px;
        }

        .step-indicator:hover {
            transform: translateY(-2px);
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border: 3px solid;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .step-circle::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            z-index: -1;
            filter: blur(8px);
            opacity: 0.6;
        }

        /* Website theme colors with gradients */
        .step-circle.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #FFFFFF;
            border-color: #28a745;
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
        }

        .step-circle.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #FFFFFF;
            border-color: #6ea4ce;
            box-shadow: 0 4px 20px rgba(110, 164, 206, 0.4);
        }

        .step-circle.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #FFFFFF;
            border-color: #6c757d;
            box-shadow: 0 4px 20px rgba(108, 117, 125, 0.4);
        }

        /* Disabled/locked step styling - make them visually appear disabled */
        .step-indicator[style*="not-allowed"] {
            pointer-events: auto; /* Keep pointer events for the locked message */
        }

        .step-indicator[style*="not-allowed"] .step-circle {
            background: #e0e0e0 !important;
            color: #999 !important;
            border-color: #ccc !important;
            box-shadow: none !important;
        }

        .step-indicator[style*="not-allowed"] .step-label {
            color: #999 !important;
        }

        .step-indicator[style*="not-allowed"]:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        .step-label {
            font-size: 10px;
            text-align: center;
            color: #666;
            font-weight: 500;
        }

        /* Steps Content Section - Vertical cards like mobile */
        .steps-content {
            display: block;
        }

        .step-card {
            background: #ffffff;
            border-radius: 20px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            position: relative;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6ea4ce, #61C2C7);
            border-radius: 20px 20px 0 0;
        }

        .step-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 8px 25px rgba(110, 164, 206, 0.2);
        }

        .step-card.locked {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .step-card.locked:hover {
            transform: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .step-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .step-card:hover .step-image {
            transform: scale(1.05);
        }

        .step-content {
            padding: 25px;
            position: relative;
        }

        .step-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .step-status {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(110, 164, 206, 0.1);
            border-radius: 15px;
            margin-top: 15px;
        }

        .status-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .status-text {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-text.complete {
            color: #28a745;
        }

        .status-text.in_progress {
            color: #6ea4ce;
        }

        .status-text.locked {
            color: #6c757d;
        }

        .status-icon.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }

        .status-icon.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #fff;
        }

        .status-icon.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #fff;
        }

        /* Submitted Documents Section Styling */
        .submitted-documents-section {
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .documents-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e8f4fd;
            border-radius: 8px;
            padding: 10px;
            background: #f8fcff;
        }

        .document-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: #ffffff;
            transition: all 0.3s ease;
            border-left: 4px solid #6ea4ce;
        }

        .document-item:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(110, 164, 206, 0.2);
        }

        .document-item:last-child {
            margin-bottom: 0;
        }

        /* Admin Progress View Styling */
        .user-progress-view {
            width: 100%;
        }

        .user-progress-view .progress-indicator,
        .user-progress-view .steps-content,
        .user-progress-view .step-detail-view {
            /* Inherit all styling from regular user view */
        }

        /* Admin Step Cards - Mobile App Style */
        .admin-step-card {
            background: #ffffff;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .admin-step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.15);
        }

        .admin-step-header {
            display: flex;
            align-items: center;
            padding: 20px;
            cursor: pointer;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            transition: background 0.3s ease;
        }

        .admin-step-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .admin-step-title {
            display: flex;
            align-items: center;
            flex: 1;
            gap: 15px;
        }

        .admin-step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(110, 164, 206, 0.3);
        }

        .admin-step-info h4 {
            margin: 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .admin-step-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-step-status.status-complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .admin-step-status.status-in-progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .admin-step-status.status-locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .admin-step-expand {
            font-size: 20px;
            color: #6ea4ce;
            margin-left: 15px;
            transition: transform 0.3s ease;
        }

        .admin-step-expand.expanded {
            transform: rotate(180deg);
        }

        .admin-step-content {
            display: none;
            padding: 0;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .admin-step-content.visible {
            display: block;
            padding: 25px;
        }

        .admin-step-content > div {
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .admin-step-content > div:last-child {
            margin-bottom: 0;
        }

        .admin-step-content h5 {
            margin: 0 0 15px 0;
            color: #2c5aa0;
            font-size: 16px;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }

        /* Admin Steps Container */
        .admin-steps-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px 0;
        }

        /* Loading and Error States */
        .loading-message {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }

        .login-required {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .login-required h3 {
            color: #c62828;
            margin-bottom: 20px;
        }

        .login-required a {
            background: #6ea4ce;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .login-required a:hover {
            background: #5bb3f0;
        }

        .confirmation-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .dialog-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .dialog-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .dialog-message {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.5;
        }

        .dialog-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .btn-secondary {
            background: #ffffff;
            color: #333;
            border: 2px solid #6ea4ce;
        }

        .btn-secondary:hover {
            background: #6ea4ce;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.3);
        }

        /* --- Progress Bar Styles --- */
        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 30px;
            height: 20px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            /* Will be updated by JavaScript */
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            border-radius: 10px;
            text-align: center;
            color: white;
            line-height: 20px;
            /* Center text vertically */
            font-size: 12px;
            transition: width 0.5s ease-in-out;
        }

        /* --- Grid Container Styles (Like the image) --- */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            /* Responsive grid */
            gap: 25px;
            padding-bottom: 20px;
            /* Space at the bottom */
        }

        .progress-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 180px;
            /* Ensure cards have a minimum height */
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            /* For percentage circle */
        }

        .progress-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .card-percentage {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0e0e0;
            /* Default grey for uncompleted */
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            flex-shrink: 0;
            /* ADD THIS LINE to prevent it from shrinking */
        }

        .card-percentage.complete {
            background-color: #28a745;
            /* Green for completed */
            color: white;
            border-color: #28a745;
        }

        .card-percentage.partial {
            background-color: #ffc107;
            /* Yellow for partial */
            color: #333;
            border-color: #ffc107;
        }

        .card-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #444;
            flex-grow: 1;
            /* Allow title to take up available space */
            margin-right: 10px;
        }

        .card-meta {
            font-size: 0.9em;
            color: #777;
            margin-top: 10px;
            /* Space between title and meta */
        }

<?php
require_once 'session_check.php';

if (!isset($_SESSION['alert'])) {
    $_SESSION['alert'] = null;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #F2F2F2;
            min-height: 100vh;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
            /* Ensures container takes at least full viewport height */
        }

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

        /* Maintain sidebar for admin users but adjust main content */
        .admin-layout .main-content {
            padding: 0;
            background-color: #F2F2F2;
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

        /* Add a new wrapper for the sidebar and main content */
        .content-wrapper {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 60px);
        }

        /* Header Section - Enhanced mobile app style */
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
        }

        .header-section .subtitle {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        /* Progress Indicator Bar - Enhanced horizontal scrollable */
        .progress-indicator {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            border: 1px solid #e0e0e0;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 800px;
            padding: 10px 0;
            gap: 10px;
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: transform 0.2s ease;
            min-width: 60px;
        }

        .step-indicator:hover {
            transform: translateY(-2px);
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border: 3px solid;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .step-circle::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            z-index: -1;
            filter: blur(8px);
            opacity: 0.6;
        }

        /* Website theme colors with gradients */
        .step-circle.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #FFFFFF;
            border-color: #28a745;
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
        }

        .step-circle.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #FFFFFF;
            border-color: #6ea4ce;
            box-shadow: 0 4px 20px rgba(110, 164, 206, 0.4);
        }

        .step-circle.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #FFFFFF;
            border-color: #6c757d;
            box-shadow: 0 4px 20px rgba(108, 117, 125, 0.4);
        }

        /* Disabled/locked step styling - make them visually appear disabled */
        .step-indicator[style*="not-allowed"] {
            pointer-events: auto; /* Keep pointer events for the locked message */
        }

        .step-indicator[style*="not-allowed"] .step-circle {
            background: #e0e0e0 !important;
            color: #999 !important;
            border-color: #ccc !important;
            box-shadow: none !important;
        }

        .step-indicator[style*="not-allowed"] .step-label {
            color: #999 !important;
        }

        .step-indicator[style*="not-allowed"]:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        .step-label {
            font-size: 10px;
            text-align: center;
            color: #666;
            font-weight: 500;
        }

        /* Steps Content Section - Vertical cards like mobile */
        .steps-content {
            display: block;
        }

        .step-card {
            background: #ffffff;
            border-radius: 20px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            position: relative;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6ea4ce, #61C2C7);
            border-radius: 20px 20px 0 0;
        }

        .step-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 8px 25px rgba(110, 164, 206, 0.2);
        }

        .step-card.locked {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .step-card.locked:hover {
            transform: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .step-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .step-card:hover .step-image {
            transform: scale(1.05);
        }

        .step-content {
            padding: 25px;
            position: relative;
        }

        .step-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .step-status {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(110, 164, 206, 0.1);
            border-radius: 15px;
            margin-top: 15px;
        }

        .status-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .status-text {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-text.complete {
            color: #28a745;
        }

        .status-text.in_progress {
            color: #6ea4ce;
        }

        .status-text.locked {
            color: #6c757d;
        }

        .status-icon.complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }

        .status-icon.in_progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: #fff;
        }

        .status-icon.locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: #fff;
        }

        /* Submitted Documents Section Styling */
        .submitted-documents-section {
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
        }

        .documents-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e8f4fd;
            border-radius: 8px;
            padding: 10px;
            background: #f8fcff;
        }

        .document-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: #ffffff;
            transition: all 0.3s ease;
            border-left: 4px solid #6ea4ce;
        }

        .document-item:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(110, 164, 206, 0.2);
        }

        .document-item:last-child {
            margin-bottom: 0;
        }

        /* Admin Progress View Styling */
        .user-progress-view {
            width: 100%;
        }

        .user-progress-view .progress-indicator,
        .user-progress-view .steps-content,
        .user-progress-view .step-detail-view {
            /* Inherit all styling from regular user view */
        }

        /* Admin Step Cards - Mobile App Style */
        .admin-step-card {
            background: #ffffff;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .admin-step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.15);
        }

        .admin-step-header {
            display: flex;
            align-items: center;
            padding: 20px;
            cursor: pointer;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            transition: background 0.3s ease;
        }

        .admin-step-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .admin-step-title {
            display: flex;
            align-items: center;
            flex: 1;
            gap: 15px;
        }

        .admin-step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(110, 164, 206, 0.3);
        }

        .admin-step-info h4 {
            margin: 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .admin-step-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-step-status.status-complete {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .admin-step-status.status-in-progress {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .admin-step-status.status-locked {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .admin-step-expand {
            font-size: 20px;
            color: #6ea4ce;
            margin-left: 15px;
            transition: transform 0.3s ease;
        }

        .admin-step-expand.expanded {
            transform: rotate(180deg);
        }

        .admin-step-content {
            display: none;
            padding: 0;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .admin-step-content.visible {
            display: block;
            padding: 25px;
        }

        .admin-step-content > div {
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .admin-step-content > div:last-child {
            margin-bottom: 0;
        }

        .admin-step-content h5 {
            margin: 0 0 15px 0;
            color: #2c5aa0;
            font-size: 16px;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }

        /* Admin Steps Container */
        .admin-steps-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px 0;
        }

        /* Loading and Error States */
        .loading-message {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }

        .login-required {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .login-required h3 {
            color: #c62828;
            margin-bottom: 20px;
        }

        .login-required a {
            background: #6ea4ce;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .login-required a:hover {
            background: #5bb3f0;
        }

        .confirmation-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .dialog-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .dialog-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .dialog-message {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.5;
        }

        .dialog-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .btn-secondary {
            background: #ffffff;
            color: #333;
            border: 2px solid #6ea4ce;
        }

        .btn-secondary:hover {
            background: #6ea4ce;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.3);
        }

        /* --- Progress Bar Styles --- */
        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 30px;
            height: 20px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            /* Will be updated by JavaScript */
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            border-radius: 10px;
            text-align: center;
            color: white;
            line-height: 20px;
            /* Center text vertically */
            font-size: 12px;
            transition: width 0.5s ease-in-out;
        }

        /* --- Grid Container Styles (Like the image) --- */
        .progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            /* Responsive grid */
            gap: 25px;
            padding-bottom: 20px;
            /* Space at the bottom */
        }

        .progress-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 180px;
            /* Ensure cards have a minimum height */
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            /* For percentage circle */
        }

        .progress-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .card-percentage {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0e0e0;
            /* Default grey for uncompleted */
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            border: 2px solid #e0e0e0;
            flex-shrink: 0;
            /* ADD THIS LINE to prevent it from shrinking */
        }

        .card-percentage.complete {
            background-color: #28a745;
            /* Green for completed */
            color: white;
            border-color: #28a745;
        }

        .card-percentage.partial {
            background-color: #ffc107;
            /* Yellow for partial */
            color: #333;
            border-color: #ffc107;
        }

        .card-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #444;
            flex-grow: 1;
            /* Allow title to take up available space */
            margin-right: 10px;
        }

        .card-meta {
            font-size: 0.9em;
            color: #777;
            margin-top: 10px;
            /* Space between title and meta */
        }

        .card-status {
            font-size: 0.9em;
            font-weight: 500;
            color: #888;
            margin-top: 10px;
        }

        /* --- Individual Step View Styles --- */
        .step-detail-view {
            display: none;
            /* Hidden by default */
            padding: 0;
            background-color: #f5f7fa;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
        }

        .step-detail-header {
            background: linear-gradient(135deg, #6ea4ce 0%, #61C2C7 100%);
            color: white;
            padding: 25px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: relative;
        }

        .step-detail-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .step-detail-header .subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }

        .step-detail-content {
            padding: 25px;
        }

        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            position: relative;
            z-index: 2;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            min-width: 140px;
            justify-content: center;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .back-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .admin-comment-section {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            display: none;
        }

        .admin-comment-section.visible {
            display: block;
        }

        .admin-comment-title {
            font-size: 18px;
            font-weight: 600;
            color: #856404;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-comment-text {
            color: #856404;
            line-height: 1.6;
            font-size: 15px;
        }

        .requirement-card {
            background: #ffffff;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .requirement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.15);
        }

        .requirement-header {
            background: rgba(110, 164, 206, 0.1);
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .requirement-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .requirement-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .requirement-link {
            color: #6ea4ce;
            text-decoration: none;
            font-weight: 500;
            word-break: break-all;
        }

        .requirement-link:hover {
            color: #5a8fb5;
            text-decoration: underline;
        }

        .requirement-content {
            padding: 20px;
        }

        .submission-status {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(110, 164, 206, 0.1);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .status-icon-large {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            flex-shrink: 0;
        }

        .status-icon-large.submitted {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-icon-large.pending {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        .status-icon-large.not-submitted {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .status-details {
            flex: 1;
        }

        .status-text-large {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .status-text-large.submitted {
            color: #28a745;
        }

        .status-text-large.pending {
            color: #fd7e14;
        }

        .status-text-large.not-submitted {
            color: #6c757d;
        }

        .attempts-text {
            font-size: 14px;
            color: #666;
        }

        .upload-section {
            margin-top: 20px;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 2px dashed #6ea4ce;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: #ffffff;
            border: 2px solid #6ea4ce;
            border-radius: 10px;
            color: #6ea4ce;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background: #6ea4ce;
            color: white;
        }

        .upload-button {
            background: linear-gradient(135deg, #6ea4ce, #61C2C7);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(110, 164, 206, 0.3);
        }

        .upload-button:hover {
            background: linear-gradient(135deg, #5a8fb5, #4fa8ad);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 164, 206, 0.4);
        }

        .upload-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .reupload-button {
            background: linear-gradient(135deg, #61C2C7, #4fa8ad);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .reupload-button:hover {
            background: linear-gradient(135deg, #4fa8ad, #3e8e93);
            transform: translateY(-1px);
        }

        .selected-file {
            background: #e8f5e8;
            border: 1px solid #28a745;
            padding: 12px;
            border-radius: 8px;
            color: #155724;
            font-weight: 500;
        }

        .form-link-section {
            background: #e8f4fd;
            border: 1px solid #2196F3;
            border-radius: 12px;
            padding: 20px;
            margin-top: 15px;
        }

        .form-link-title {
            font-weight: 600;
            color: #1976D2;
            margin-bottom: 10px;
        }

        .form-submit-button {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .form-submit-button:hover {
            background: linear-gradient(135deg, #1976D2, #1565C0);
            transform: translateY(-1px);
            text-decoration: none;
            color: white;
        }

        .requirement-link {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px 0;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .requirement-link:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-1px);
            text-decoration: none;
            color: white;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .view-document-button {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-document-button:hover {
            background: linear-gradient(135deg, #138496, #117a8b);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            /* Remove body flex-direction change here, it's now handled by .content-wrapper */
            .content-wrapper {
                flex-direction: column;
                /* Stack sidebar and main content on small screens */
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
                padding: 15px 20px;
            }

            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            .sidebar li {
                margin: 5px 10px;
            }

            .main-content {
                padding: 20px;
            }

            .progress-grid {
                grid-template-columns: 1fr;
                /* Stack cards on small screens */
            }

            .progress-card {
                min-height: auto;
            }

            .step-detail-view .upload-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .step-detail-view .upload-form input[type="file"],
            .step-detail-view .upload-form button {
                width: 100%;
            }

            .step-detail-header {
                padding: 20px;
            }

            .step-detail-header h3 {
                font-size: 20px;
            }

            .step-detail-content {
                padding: 15px;
            }

            .requirement-header,
            .requirement-content {
                padding: 15px;
            }

            .submission-status {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .status-details {
                text-align: center;
            }

            .upload-form {
                padding: 15px;
            }

            .back-button {
                padding: 10px 16px;
                font-size: 12px;
                min-width: 120px;
            }
            
            .mobile-user-header {
                padding: 16px 20px;
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            
            .mobile-user-name-title {
                font-size: 18px;
                order: 1;
            }
            
            .back-button {
                order: 2;
                align-self: center;
            }
        }

        /* Mobile App Style Header */
        .mobile-app-header {
            width: 100%;
            height: 64px;
            background-color: #6EC6FF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            margin-bottom: 0;
        }

        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .header-logo {
            width: 180px;
            height: 40px;
            object-fit: contain;
        }

        .header-text {
            color: white;
            font-weight: bold;
            font-size: 14px;
            margin-top: 2px;
        }

        /* Page Title */
        .page-title {
            padding: 16px;
            background-color: #FFFFFF;
        }

        .page-title h2 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            color: #333333;
        }

                 /* Mobile App Style Users Container */
         .mobile-users-container {
             background-color: #F2F2F2;
             min-height: calc(100vh - 64px - 80px);
             padding: 0 16px 16px 16px;
         }

         /* Responsive adjustments for mobile app style */
         @media (max-width: 768px) {
             .mobile-app-header {
                 height: 56px;
                 padding: 8px;
             }

             .header-logo {
                 width: 140px;
                 height: 32px;
             }

             .header-text {
                 font-size: 12px;
             }

             .page-title {
                 padding: 12px 16px;
             }

             .page-title h2 {
                 font-size: 20px;
             }

             .mobile-user-card {
                 padding: 12px;
                 margin-bottom: 6px;
                 margin-top: 6px;
             }

             .mobile-user-name {
                 font-size: 16px;
             }

             .mobile-action-link {
                 font-size: 14px;
             }

             .mobile-user-actions {
                 gap: 6px;
             }
         }

        .mobile-users-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* Mobile App Style User Cards */
        .mobile-user-card {
            background-color: #FFFFFF;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 8px;
            margin-top: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .mobile-user-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .mobile-user-info {
            flex: 1;
            margin-right: 16px;
        }

        .mobile-user-name {
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            margin: 0;
        }

        .mobile-user-actions {
            display: flex;
            gap: 8px;
        }

        .mobile-action-link {
            color: #33B5E5;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .mobile-action-link:hover {
            color: #0099CC;
            text-decoration: underline;
        }

        .no-users-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1em;
            background-color: #FFFFFF;
            border-radius: 8px;
            margin: 16px 0;
        }

        /* User Progress Detail View for Admin */
        .user-progress-view {
            background: #F2F2F2;
            padding: 0;
            margin: 0;
        }

        .user-progress-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e8f4fd;
        }

        .user-progress-title {
            flex: 1;
        }

        .user-progress-title h3 {
            color: #2c5aa0;
            font-size: 1.8em;
            font-weight: 600;
            margin: 0 0 5px 0;
        }

        .user-progress-title p {
            color: #666;
            margin: 0;
        }

        .back-to-list-btn {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .back-to-list-btn:hover {
            background: linear-gradient(135deg, #5a6268, #3d4449);
            transform: translateY(-1px);
        }

        /* Responsive Design for Admin View */
        @media (max-width: 768px) {
            .user-card {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .user-actions {
                width: 100%;
                justify-content: center;
            }

            .action-btn {
                flex: 1;
                max-width: 120px;
            }

                         .user-progress-header {
                 flex-direction: column;
                 gap: 15px;
                 text-align: center;
             }
         }

         /* Mobile App Style Progress Container */
         .admin-steps-container {
             background: #F0F2F5;
             padding: 16px;
             min-height: calc(100vh - 140px);
         }

         /* User Name Header - Better integrated design */
         .mobile-user-header {
             display: flex;
             align-items: center;
             gap: 16px;
             margin-bottom: 20px;
             padding: 20px 24px;
             background: linear-gradient(135deg, #6ea4ce, #7CB9E8);
             border-radius: 12px;
             box-shadow: 0 4px 16px rgba(110, 164, 206, 0.25);
             position: relative;
             overflow: hidden;
         }
         
         .mobile-user-header::before {
             content: '';
             position: absolute;
             top: 0;
             left: 0;
             right: 0;
             bottom: 0;
             background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
             pointer-events: none;
         }

         .mobile-user-name-title {
             font-size: 22px;
             font-weight: 700;
             color: white;
             margin: 0;
             flex: 1;
             overflow: hidden;
             text-overflow: ellipsis;
             white-space: nowrap;
             text-shadow: 0 2px 4px rgba(0,0,0,0.1);
             position: relative;
             z-index: 1;
         }

         /* Mobile App Step Cards */
         .admin-step-card {
             margin-bottom: 16px;
             border: 1px solid #E0E0E0;
             border-radius: 8px;
             background: white;
             overflow: hidden;
         }

         .admin-step-header {
             background: white;
             padding: 16px;
             cursor: pointer;
             border-bottom: none;
         }

         .admin-step-header:hover {
             background: #F8F9FA;
         }

         .admin-step-title {
             font-size: 16px;
             font-weight: bold;
             color: #333333;
             margin: 0 0 8px 0;
         }

         .admin-step-status {
             font-size: 14px;
             margin: 0 0 4px 0;
         }

         .admin-step-status.complete {
             color: #4CAF50;
             font-weight: 500;
         }

         .admin-step-status.in_progress {
             color: #2196F3;
             font-weight: 500;
         }

         .admin-step-status.locked {
             color: #9E9E9E;
             font-weight: 500;
         }

         .admin-step-comment {
             font-size: 14px;
             color: #2196F3;
             margin: 4px 0 0 0;
             display: none;
         }

         .admin-step-comment.visible {
             display: block;
         }

         .admin-step-content {
             display: none;
             background: #FFFFFF;
             border-top: 1px solid #E0E0E0;
             padding: 16px;
         }

         .admin-step-content.visible {
             display: block;
         }

         /* Mobile App Documents Section */
         .documents-section {
             margin-bottom: 16px;
         }

         .documents-header {
             font-size: 16px;
             font-weight: bold;
             margin-bottom: 4px;
             color: #333333;
         }

         .documents-container {
             margin-bottom: 8px;
         }

         /* Mobile App Admin Actions */
         .admin-actions-header {
             font-size: 16px;
             font-weight: bold;
             margin: 16px 0 8px 0;
             color: #333333;
         }

         .admin-controls {
             margin: 8px 0 16px 0;
         }

         .admin-btn {
             width: 100%;
             padding: 12px;
             border: none;
             border-radius: 4px;
             font-weight: 500;
             cursor: pointer;
             transition: background-color 0.2s ease;
             color: white;
             text-transform: uppercase;
             font-size: 14px;
         }

         .btn-mark-complete {
             background: #4CAF50;
         }

         .btn-mark-complete:hover {
             background: #45a049;
         }

         /* Mobile App Comment Section */
         .admin-comment-section {
             margin-top: 12px;
         }

         .comment-input {
             width: calc(100% - 24px);
             min-height: 40px;
             padding: 12px;
             border: 1px solid #E0E0E0;
             border-radius: 20px;
             font-family: inherit;
             font-size: 14px;
             resize: none;
             margin-bottom: 8px;
             box-sizing: border-box;
             background: #F5F5F5;
             outline: none;
         }

         .comment-input::placeholder {
             color: #999999;
         }

         .comment-btn {
             background: #2196F3;
             color: white;
             padding: 10px 20px;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             float: right;
             transition: background-color 0.2s ease;
             text-transform: uppercase;
             font-weight: 500;
             font-size: 14px;
         }

         .comment-btn:hover {
             background: #1976D2;
         }

         .documents-section {
             margin-top: 16px;
         }

         .documents-section h5 {
             color: #333333;
             margin: 0 0 8px 0;
             font-size: 16px;
             font-weight: bold;
         }

         .document-list {
             margin: 8px 0;
         }

         .document-item {
             margin-bottom: 12px;
             line-height: 1.4;
         }

         .document-name {
             font-size: 14px;
             color: #333333;
             margin-bottom: 2px;
             word-break: break-all;
         }

         .btn-view-doc {
             background: none;
             color: #2196F3;
             padding: 0;
             border: none;
             font-size: 14px;
             cursor: pointer;
             text-decoration: underline;
             display: block;
             margin-bottom: 2px;
         }

         .btn-view-doc:hover {
             color: #1976D2;
         }

         .document-date {
             font-size: 12px;
             color: #999999;
         }

         /* Mobile Responsive */
         @media (max-width: 768px) {
             .admin-step-header {
                 padding: 15px;
                 flex-direction: column;
                 gap: 10px;
                 text-align: center;
             }

             .admin-step-title {
                 justify-content: center;
             }

             .admin-controls {
                 flex-direction: column;
             }

             .comment-input-group {
                 flex-direction: column;
             }

             .document-item {
                 flex-direction: column;
                 gap: 10px;
                 text-align: center;
             }

             .document-actions {
                 justify-content: center;
            }
        }

                /* USER VIEW - Mobile App Design Exact Match */
        .user-view-container {
            background: #FFFFFF;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        /* Mobile App Style Header - Exact Match */
        .mobile-app-header {
            width: 100%;
            height: 64px;
            background: #6EC6FF;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            margin-bottom: 0;
            position: relative;
        }

        .back-button {
            width: 24px;
            height: 24px;
            margin-right: 16px;
        }

        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .header-logo {
            width: 180px;
            height: 40px;
            object-fit: contain;
        }

        .header-text {
            color: white;
            font-weight: bold;
            font-size: 14px;
            margin-top: 2px;
        }

        /* Progress Tracking Title */
        .progress-title {
            background: #FFFFFF;
            padding: 16px 8px;
            margin: 0;
            border-bottom: none;
        }

        .progress-title h2 {
            margin: 0 0 16px 0;
            font-size: 18px;
            font-weight: bold;
            color: #000000;
        }

        /* Progress Indicator Circles - Horizontal Scroll */
        .progress-indicator-scroll {
            width: 100%;
            max-width: 379px;
            margin: 0 auto 16px auto;
            padding: 0 8px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .progress-indicator-scroll::-webkit-scrollbar {
            display: none;
        }

        .progress-circles-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            min-width: max-content;
            margin-bottom: 16px;
        }

        .progress-circle-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 8px;
            min-width: 40px;
        }

        .progress-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            margin-bottom: 4px;
        }

        .progress-circle.complete {
            background-color: #4CAF50;
            background-image: url('images/ic_status_complete.png');
        }

        .progress-circle.in_progress {
            background-color: #FF9800;
            background-image: url('images/ic_status_in_progress.png');
        }

        .progress-circle.locked {
            background-color: #9E9E9E;
            background-image: url('images/ic_status_locked.png');
        }

        .progress-circle-text {
            font-size: 10px;
            color: #000000;
            text-align: center;
        }

        /* Main Steps Container - Scrollable */
        .mobile-steps-container {
            background: #FFFFFF;
            padding: 8px;
            flex: 1;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        /* Large Image Cards - Exact Mobile App Match */
        .mobile-step-card {
            background: #61C2C7;
            border: none;
            border-radius: 0;
            margin-bottom: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s ease;
            padding: 0;
            width: 100%;
        }

        .mobile-step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .mobile-step-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .mobile-step-content-overlay {
            padding: 0;
            background: #61C2C7;
        }

        .mobile-step-title {
            font-size: 16px;
            font-weight: normal;
            color: #000000;
            margin: 8px 0 0 0;
            padding: 8px;
        }

        .mobile-step-status-container {
            display: flex;
            align-items: center;
            padding: 8px;
            margin-bottom: 0;
        }

        .mobile-step-status-icon {
            width: 24px;
            height: 24px;
            margin-right: 8px;
            object-fit: contain;
        }

        .mobile-step-status-text {
            font-size: 20px;
            font-weight: normal;
            margin: 0;
            padding: 0;
        }

        .mobile-step-status-text.complete {
            color: #FFFF00;
        }

        .mobile-step-status-text.in_progress {
            color: #000000;
        }

        .mobile-step-status-text.locked {
            color: #FF0000;
        }

        /* Detail Page Styles */
        .step-detail-page {
            display: none;
            background: #F0F2F5;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .step-detail-page.visible {
            display: block;
        }

        .step-detail-header {
            background: #6EC6FF;
            padding: 12px;
            display: flex;
            align-items: center;
            color: white;
        }

        .back-btn {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-right: 16px;
        }

        .step-detail-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .step-detail-content {
            padding: 16px;
        }

        .step-detail-user-name {
            font-size: 24px;
            font-weight: bold;
            color: #333333;
            margin: 0 0 16px 0;
            text-align: left;
        }

        .step-detail-card {
            background: #F0F0F0;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 8px;
        }

        .step-detail-card-title {
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            margin: 0 0 4px 0;
        }

        .step-detail-card-status {
            font-size: 16px;
            color: #666666;
            margin: 4px 0;
        }

        .step-detail-card-comment {
            font-size: 14px;
            color: #2196F3;
            margin: 4px 0 0 0;
            display: none;
        }

        .step-detail-card-comment.visible {
            display: block;
        }

        .step-detail-expanded {
            background: #FFFFFF;
            border-top: 1px solid #E0E0E0;
            padding: 16px;
            margin-top: 0;
        }

        .documents-section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333333;
        }

        .document-item {
            background: #F8F9FA;
            border: 1px solid #E0E0E0;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .document-title {
            font-size: 14px;
            font-weight: 500;
            color: #333333;
            margin: 0 0 4px 0;
        }

        .document-status {
            font-size: 12px;
            color: #666666;
        }

        .document-view-btn {
            background: none;
            border: none;
            color: #2196F3;
            font-size: 14px;
            cursor: pointer;
            text-decoration: underline;
            padding: 4px 8px;
        }

        .document-view-btn:hover {
            color: #1976D2;
        }

        .upload-section {
            margin-top: 16px;
            padding: 16px;
            background: #F8F9FA;
            border-radius: 8px;
            border: 1px solid #E0E0E0;
        }

        .upload-title {
            font-size: 16px;
            font-weight: bold;
            color: #333333;
            margin: 0 0 12px 0;
        }

        .upload-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .upload-btn {
            background: #28A745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
        }

        .upload-btn:hover {
            background: #218838;
        }

        /* Hide old user styles on mobile layout */
        .user-mobile-layout .header-section,
        .user-mobile-layout .progress-indicator,
        .user-mobile-layout .steps-content {
            display: none !important;
        }

        /* Hide sidebar for mobile user layout */
        .user-mobile-layout {
            background: #F0F2F5;
            padding: 0;
            margin: 0;
        }

        .user-mobile-layout .sidebar {
            display: none !important;
        }

        .user-mobile-layout .main-content {
            padding: 0;
            margin: 0;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-app-header {
                height: 56px;
                padding: 8px;
            }

            .header-logo {
                width: 150px;
                height: 32px;
            }

            .header-text {
                font-size: 12px;
            }

            .progress-title {
                padding: 12px;
            }

            .progress-title h2 {
                font-size: 16px;
            }

            .user-name-title {
                font-size: 20px;
            }

            .mobile-steps-container {
                padding: 12px;
            }

            .mobile-step-header {
                padding: 12px;
            }

            .mobile-step-title {
                font-size: 16px;
            }

            .mobile-step-content {
                padding: 12px;
            }
        }
    </style>
</head>

  <style>
    /* Mobile Menu Styles for ProgTracking */
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

    /* Hamburger animation removed - no X transformation */

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

            @media (max-width: 768px) {
            .mobile-menu-toggle,
            .mobile-dropdown-menu {
                display: block;
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

            .main-content {
                padding: 20px 15px;
            }
        }

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
            .content-wrapper {
                display: block !important;
            }

            .container {
                display: block !important;
            }

            /* Ensure no ghost clicks from sidebar */
            body.mobile-view .sidebar,
            body.mobile-view .sidebar *,
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
        }
  </style>

  <body>
      <?php include('navbar.php'); ?>
 <?php include('chatbot.php'); ?>

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

    <div class="content-wrapper <?php echo $isAdmin ? 'admin-layout' : ''; ?>">
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
            <?php if (!$isLoggedIn): ?>
                <div class="login-required">
                    <h3>Login Required</h3>
                    <p>Please log in to view adoption progress.</p>
                    <a href="Signin.php">Go to Login</a>
            </div>
            <?php elseif ($isAdmin): ?>

                <!-- Registered Users Title -->
                <div class="page-title">
                    <h2>Registered Users</h2>
                </div>

                <div id="adminLoadingMessage" class="loading-message">
                    Loading users with active adoption processes...
                </div>

                <div id="adminErrorMessage" class="error-message" style="display: none;">
                    <!-- Error messages will appear here -->
                </div>

                <!-- Mobile App Style Users Container -->
                <div id="usersContainer" class="mobile-users-container" style="display: none;">
                    <div id="usersList" class="mobile-users-list">
                        <!-- User cards will be dynamically generated -->
                    </div>
                </div>

                <!-- User Progress Detail View for Admins -->
                <div id="userProgressView" class="user-progress-view" style="display: none;">
                    <!-- Individual user progress will be shown here -->
                </div>

            <?php else: ?>
                <!-- Regular User View - Mobile App Exact Match -->
                <div class="user-view-container user-mobile-layout">

                    <!-- Progress Tracking Title -->
                    <div class="progress-title">
                        <h2>Progress Tracking</h2>
                        
                        <!-- Progress Indicator Circles -->
                        <div class="progress-indicator-scroll">
                            <div class="progress-circles-container" id="progressCircles">
                                <!-- Progress circles will be generated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Loading Message -->
                    <div id="loadingMessage" class="mobile-loading">
                        Loading your adoption progress...
                    </div>

                    <!-- Main Steps Container - Large Image Cards -->
                    <div id="mobileStepsContainer" class="mobile-steps-container" style="display: none;">
                        <!-- Large image step cards will be dynamically generated here -->
                    </div>

                    <!-- Step Detail Page -->
                    <div id="stepDetailPage" class="step-detail-page">
                        <div class="step-detail-header">
                            <button class="back-btn" onclick="closeMobileStepDetail()">← Back</button>
                            <div class="step-detail-title" id="stepDetailTitle">Step Details</div>
                        </div>
                        
                        <div class="step-detail-content">
                            <div class="step-detail-user-name" id="stepDetailUserName">User: Loading...</div>
                            
                            <!-- ADMIN COMMENT SECTION - RIGHT BELOW USERNAME -->
                            <div id="topAdminCommentSection" style="
                                background: #f0f7ff;
                                border: 2px solid #007bff;
                                border-radius: 8px;
                                padding: 20px;
                                margin: 20px 0;
                                display: none;
                                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                            ">
                                <div style="
                                    display: flex;
                                    align-items: center;
                                    margin-bottom: 12px;
                                    font-size: 16px;
                                    font-weight: bold;
                                    color: #333;
                                ">
                                    <span style="margin-right: 8px; font-size: 20px;">💬</span>
                                    Admin Comment:
                                </div>
                                <div id="topAdminCommentText" style="
                                    font-size: 14px;
                                    color: #555;
                                    line-height: 1.5;
                                    background: white;
                                    padding: 15px;
                                    border-radius: 6px;
                                    border: 1px solid #e0e0e0;
                                ">No admin comment for this step yet.</div>
                            </div>
                            
                            <!-- Step detail content will be generated here -->
                            <div id="stepDetailContent">
                                <!-- Step cards and expanded content -->
                            </div>
                        </div>
                    </div>

                    <!-- Hidden old elements for compatibility -->
                    <div class="header-section" style="display: none;">
                        <h2 id="adoptionTitle">Your Adoption Process</h2>
                        <p class="subtitle">Track your progress through the adoption journey</p>
                    </div>

                    <div class="progress-indicator" style="display: none;">
                        <div class="steps-container" id="stepsContainer">
                            <!-- Steps will be dynamically generated -->
                        </div>
                    </div>

                    <div id="stepsContent" class="steps-content" style="display: none;">
                        <!-- Step cards will be dynamically generated -->
                    </div>

                    <div id="stepDetailView" class="step-detail-view" style="display: none;">
                        <!-- Step detail content will be dynamically generated -->
                    </div>
                </div>
            <?php endif; ?>

                    </main>
                </div>

    <!-- Confirmation Dialog -->
    <div id="confirmationDialog" class="confirmation-dialog">
        <div class="dialog-content">
            <div class="dialog-title" id="dialogTitle">Start Adoption Process</div>
            <div class="dialog-message" id="dialogMessage">Are you sure you want to begin the adoption process?</div>
            <div class="dialog-buttons">
                <button class="btn btn-primary" id="confirmBtn">Yes</button>
                <button class="btn btn-secondary" id="cancelBtn">No</button>
                    </div>
                </div>
            </div>

    <?php if ($isLoggedIn): ?>
    <!-- Firebase Configuration - Only load if user is logged in -->
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-storage-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-database-compat.js"></script>

    <script src="notification_client.js"></script>
    <script>
        // Make session data available to JavaScript
        window.sessionUserId = '<?php echo $currentUserId ?? ''; ?>';
        window.sessionUserEmail = '<?php echo $currentUserEmail ?? ''; ?>';
        window.sessionUserRole = '<?php echo $currentUserRole ?? ''; ?>';
        
        // Debug logging
        console.log('PHP Session variables:');
        console.log('- User ID: <?php echo $currentUserId ?? "not set"; ?>');
        console.log('- User Email: <?php echo $currentUserEmail ?? "not set"; ?>');  
        console.log('- User Role: <?php echo $currentUserRole ?? "not set"; ?>');
    </script>

    <script>
        // Firebase Configuration with error handling
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            databaseURL: "https://ally-user-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "ally-user",
            storageBucket: "ally-user.firebasestorage.app",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15",
            measurementId: "G-0D35XC4HQ4"
        };

        // Initialize Firebase with error handling
        let auth, storage, realtimeDb;
        try {
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }
            window.firebase = firebase; // Make firebase globally available
            auth = firebase.auth();
            window.db = firebase.firestore(); // Make db globally available
            db = window.db; // Local alias for convenience
            storage = firebase.storage();
            realtimeDb = firebase.database(); // Add Realtime Database
            window.realtimeDb = realtimeDb; // Make globally available
            console.log('Firebase initialized successfully');
        } catch (error) {
            console.error('Firebase initialization failed:', error);
            if (isAdminUser) {
                showAdminError('Firebase connection failed. Unable to load user data.');
            } else {
                console.error('Firebase connection failed. Using local session data.');
            }
        }

        // Load Firebase Messaging Bridge after Firebase is initialized
        const script = document.createElement('script');
        script.src = 'firebase_messaging_bridge.js';
        script.onload = function() {
            console.log('Firebase Messaging Bridge loaded successfully');
            // Initialize the bridge
            window.firebaseMessagingBridge = new FirebaseMessagingBridge();
        };
        document.head.appendChild(script);

        // Step definitions - Updated 11-stage adoption process
        console.log('🚀 STEP DEFINITIONS LOADING - Debug Version 2.0');
        const stepDefinitions = [
            {
                number: 1,
                title: "📋 STAGE 1 — LETTER OF INTENT & INITIAL SCREENING",
                image: "step1_image.png",
                requirements: [
                    {
                        title: "Letter of Intent to Adopt (PDF)",
                        description: "Upload your letter of intent to adopt (PDF format).",
                        documentId: "letter_of_intent",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "Pre-screening Questionnaire",
                        description: "Complete the pre-screening questionnaire form.",
                        documentId: "pre_screening_questionnaire",
                        isForm: true,
                        isPAPUpload: true
                    }
                ],
                socialWorkerActions: [
                    "Reviews uploads → Approves or Rejects",
                    "Reason: Ensures PAP is legally eligible & motivated."
                ],
                systemActions: [
                    "If approved → Unlock next stage: Pre-Adoption Seminar."
                ]
            },
            {
                number: 2,
                title: "📋 STAGE 2 — PRE-ADOPTION SEMINAR/FORUM",
                image: "step2_image.png",
                requirements: [
                    {
                        title: "Pre-Adoption Seminar Attendance",
                        description: "Attend online seminar (video or scheduled webinar). Form must be completed in-app, not just a link.",
                        documentId: "seminar_attendance_form",
                        isForm: true,
                        isPAPUpload: true
                    },
                    {
                        title: "Short Quiz Completion",
                        description: "Complete the short quiz in-app after attending seminar.",
                        documentId: "seminar_quiz",
                        isForm: true,
                        isPAPUpload: true
                    }
                ],
                socialWorkerActions: [
                    "Reviews quiz results & approves seminar completion."
                ],
                systemActions: [
                    "Auto-generates Certificate of Attendance (PDF)"
                ]
            },
            {
                number: 3,
                title: "📋 STAGE 3 — COMPLETE DOCUMENT SUBMISSION",
                image: "step3_image.png",
                requirements: [
                    {
                        title: "PSA Birth Certificate",
                        description: "Upload your PSA authenticated birth certificate.",
                        documentId: "psa_birth_certificate",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "PSA Marriage Certificate (if married) or CENOMAR (if single)",
                        description: "Upload PSA marriage certificate or CENOMAR as applicable.",
                        documentId: "psa_marriage_cenomar",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "Barangay Clearance",
                        description: "Upload barangay clearance certificate.",
                        documentId: "barangay_clearance",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "NBI/Police Clearance",
                        description: "Upload valid NBI and police clearance certificates.",
                        documentId: "nbi_police_clearance",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "Medical Certificate (physical & mental fitness)",
                        description: "Upload medical certificate showing physical and mental fitness.",
                        documentId: "medical_certificate",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "Certificate of Employment",
                        description: "Upload certificate of employment from your employer.",
                        documentId: "employment_certificate",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "Proof of Income (ITR, payslips, bank statement)",
                        description: "Upload proof of income including ITR, payslips, or bank statements.",
                        documentId: "income_proof",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "Home Photos (outside, living room, bedrooms, kitchen)",
                        description: "Upload photos of your home including outside view, living room, bedrooms, and kitchen.",
                        documentId: "home_photos",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "Family Photos (with household members)",
                        description: "Upload family photos showing all household members.",
                        documentId: "family_photos",
                        maxAttempts: 3,
                        isPAPUpload: true
                    },
                    {
                        title: "3 Character References (signed letters)",
                        description: "Upload 3 signed character reference letters.",
                        documentId: "character_references",
                        maxAttempts: 3,
                        isPAPUpload: true
                    }
                ],
                socialWorkerActions: [
                    "Verifies & approves documents → Flags any missing/incomplete."
                ]
            },
            {
                number: 4,
                title: "📋 STAGE 4 — APPOINTMENT FOR HOME VISIT",
                image: "step4_image.png",
                requirements: [
                    {
                        title: "Home Visit Appointment Booking",
                        description: "Use in-app calendar to book date for home study visit.",
                        documentId: "home_visit_appointment",
                        isForm: true,
                        isPAPUpload: true
                    }
                ],
                socialWorkerActions: [
                    "Confirms or reschedules → Conducts home visit.",
                    "Reason: Checks home condition, family dynamics."
                ]
            },
            {
                number: 5,
                title: "📋 STAGE 5 — HOME STUDY REPORT",
                image: "step5_image.png",
                requirements: [
                    {
                        title: "Home Study Report Status",
                        description: "View the status of your home study report uploaded by the social worker.",
                        documentId: "home_study_report_status",
                        isPAPView: true, // PAP can only view, not upload
                        viewOnly: true
                    }
                ],
                socialWorkerActions: [
                    "Uploads Home Study Report (PDF)",
                    "Uploads Preliminary Recommendation (Approved/Needs Work)",
                    "Reason: Social Worker confirms suitability of PAP & household."
                ],
                socialWorkerUploads: [
                    {
                        title: "Home Study Report",
                        description: "Upload the detailed home study report (PDF).",
                        documentId: "home_study_report",
                        isSocialWorkerUpload: true,
                        acceptedFormats: [".pdf"]
                    },
                    {
                        title: "Preliminary Recommendation",
                        description: "Upload preliminary recommendation status (Approved/Needs Work).",
                        documentId: "preliminary_recommendation",
                        isSocialWorkerUpload: true,
                        acceptedFormats: [".pdf", ".doc", ".docx"],
                        hasStatusField: true, // This document includes approval status
                        statusOptions: ["Approved", "Needs Work"]
                    }
                ],
                papActions: [
                    "Views report status (Approved or Requires Action)"
                ]
            },
            {
                number: 6,
                title: "📋 STAGE 6 — CHILD PREFERENCE FORM",
                image: "step6_image.png",
                requirements: [
                    {
                        title: "Child Preference Form",
                        description: "Fill in your child preferences based on meaningful and ethical criteria only.",
                        documentId: "child_preference_form",
                        isForm: true,
                        isPAPUpload: true,
                        isEthicalForm: true, // Flag for ethical form handling
                        formFields: [
                            "Desired age range (Infant 0-2, Toddler 3-4, Child 5-10, Pre-teen 11-12, Any)",
                            "Gender preference (Male, Female, Any)",
                            "Open to siblings? (Yes/No)",
                            "Open to special needs/medical conditions? (Yes/No)",
                            "Preferred activity interests (Sports, Arts, Music, Reading, Any)",
                            "Educational background preference (Early learner, Average, Advanced, Any)",
                            "Additional meaningful preferences (personality traits, hobbies, etc.)"
                        ]
                    }
                ],
                systemActions: [
                    "Saves ethical preferences → Triggers automatic matching based on meaningful criteria only."
                ]
            },
            {
                number: 7,
                title: "📋 STAGE 7 — ETHICAL MATCHING & SELECTION",
                image: "step7_image.png",
                requirements: [
                    {
                        title: "View Matched Children",
                        description: "Browse ethically matched children based on meaningful criteria and schedule appointments.",
                        documentId: "ethical_matching_view",
                        isMatchingInterface: true, // Special interface for matching
                        isForm: false,
                        isPAPView: true
                    },
                    {
                        title: "Child Selection Confirmation",
                        description: "Confirm your selection and schedule appointment with chosen child.",
                        documentId: "child_selection_confirmation",
                        isForm: true,
                        isPAPUpload: true,
                        hasAppointmentScheduling: true // Enable appointment scheduling
                    }
                ],
                systemActions: [
                    "Uses ETHICAL matching algorithm:",
                    "• Age compatibility (developmental stage)",
                    "• Gender preference (if specified)",
                    "• Sibling openness compatibility",
                    "• Special needs compatibility",
                    "• Activity/interest alignment",
                    "• Educational compatibility",
                    "• NO discrimination based on appearance",
                    "Ensures minimum 3 children matched per user (when available)",
                    "Shows 1-3 best ethical matches"
                ],
                papActions: [
                    "Views ethical profiles: Age, gender, interests, background, needs",
                    "Schedules appointment with selected child",
                    "Confirms final selection"
                ],
                socialWorkerActions: [
                    "Reviews ethical match → Confirms placement suitability",
                    "Coordinates appointment scheduling",
                    "Updates child's status to Matched"
                ]
            },
            {
                number: 8,
                title: "📋 STAGE 8 — SUPERVISED TRIAL CUSTODY (STC)",
                image: "step8_image.png",
                description: "6-month period of supervised custody.",
                requirements: [
                    {
                        title: "Monthly Family Adjustment Journal (optional)",
                        description: "Upload monthly family adjustment journal entries.",
                        documentId: "monthly_journal",
                        maxAttempts: 12,
                        isPAPUpload: true,
                        isOptional: true
                    },
                    {
                        title: "Monthly Photos of Child in Home",
                        description: "Upload photos of child in your home each month.",
                        documentId: "monthly_photos",
                        maxAttempts: 12,
                        isPAPUpload: true
                    }
                ],
                socialWorkerActions: [
                    "Uploads Monthly Monitoring Reports",
                    "Uploads Certificate of Compliance after 6 months.",
                    "Reason: Confirms placement is stable & healthy."
                ]
            },
            {
                number: 9,
                title: "📋 STAGE 9 — PETITION FOR ADOPTION",
                image: "step9_image.png",
                requirements: [
                    {
                        title: "Petition for Adoption (auto-filled from system)",
                        description: "Review and submit the auto-filled petition for adoption.",
                        documentId: "petition_for_adoption",
                        isPAPUpload: true,
                        isAutoFilled: true
                    },
                    {
                        title: "Optional Personal Statements",
                        description: "Upload any optional personal statements.",
                        documentId: "personal_statements",
                        isPAPUpload: true,
                        isOptional: true
                    }
                ],
                socialWorkerActions: [
                    "Uploads Final Recommendation Report (PDF)",
                    "Reason: Required for NACC to approve final adoption."
                ]
            },
            {
                number: 10,
                title: "📋 STAGE 10 — CERTIFICATE OF ADOPTION",
                image: "step10_image.png",
                requirements: [
                    {
                        title: "Certificate of Adoption",
                        description: "Download your official Certificate of Adoption uploaded by the social worker.",
                        documentId: "certificate_of_adoption_download",
                        isPAPDownload: true, // PAP can download the certificate
                        downloadOnly: true
                    }
                ],
                socialWorkerActions: [
                    "Uploads Signed Certificate of Adoption (issued by NACC)"
                ],
                socialWorkerUploads: [
                    {
                        title: "Certificate of Adoption",
                        description: "Upload the signed Certificate of Adoption issued by NACC.",
                        documentId: "certificate_of_adoption",
                        isSocialWorkerUpload: true,
                        acceptedFormats: [".pdf"],
                        isOfficial: true // This is an official legal document
                    }
                ],
                papActions: [
                    "Downloads certificate from app.",
                    "Reason: Legalizes adoptive parent-child relationship."
                ]
            },
            {
                number: 11,
                title: "📋 STAGE 11 — POST-ADOPTION MONITORING (Optional)",
                image: "step10_image.png",
                requirements: [
                    {
                        title: "Follow-up Updates (optional)",
                        description: "Upload optional follow-up updates.",
                        documentId: "followup_updates",
                        isPAPUpload: true,
                        isOptional: true
                    }
                ],
                socialWorkerActions: [
                    "Uploads periodic post-adoption follow-up reports (if required)",
                    "Reason: Ensures long-term child welfare."
                ]
            }
        ];

        // Debug: Log Stage 6 and 7 definitions to verify they're correct
        console.log('🔍 Stage 6 definition:', stepDefinitions.find(s => s.number === 6));
        console.log('🔍 Stage 7 definition:', stepDefinitions.find(s => s.number === 7));

        const userId = '<?php echo $currentUserId; ?>';
        const username = '<?php echo $currentUsername; ?>';
        const currentUserEmail = '<?php echo $currentUserEmail; ?>';
        const userRole = '<?php echo $currentUserRole; ?>';
        
        // Firebase authentication token from session
        const firebaseIdToken = <?php echo $firebaseIdToken ? "'" . addslashes($firebaseIdToken) . "'" : 'null'; ?>;
        const firebaseTokenValid = <?php echo $firebaseTokenValid ? 'true' : 'false'; ?>;

        let currentUser = null;
        let progressStatus = {};
        let progressListener = null;
        let submissionData = {};
        let currentViewStep = null;
        let isAdminUser = <?php echo json_encode($isAdmin); ?>;
        let currentSelectedUser = null;
        let currentUserId = null;
        let currentUsername = null;
        


        // Initialize the app
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, user logged in via PHP session');
            console.log('User ID:', userId);
            console.log('Username:', username);
            console.log('Is Admin:', isAdminUser);
            
            if (isAdminUser) {
                initializeAdminView();
            } else {
            // Initialize Firebase with proper authentication persistence
            initializeFirebase();
            }
            
            // Setup dialog event listeners
            document.getElementById('confirmBtn').addEventListener('click', confirmStartAdoption);
            document.getElementById('cancelBtn').addEventListener('click', closeDialog);
        });

        // Admin View Functions
        function initializeAdminView() {
            console.log('=== INITIALIZING ADMIN VIEW ===');
            console.log('Auth object:', auth);
            console.log('DB object:', db);
            
            // Add a small delay to ensure Firebase is fully initialized
            setTimeout(() => {
            if (auth && db) {
                console.log('Firebase available for admin operations');
                loadUsersWithAdoptionProgress();
            } else {
                console.error('Firebase not available for admin operations');
                    console.error('Auth:', auth);
                    console.error('DB:', db);
                showAdminError('Firebase connection failed. Unable to load user data.');
            }
            }, 100);
        }

        function loadUsersWithAdoptionProgress() {
            console.log('Loading users with active adoption processes...');
            showAdminLoading(true);
            
            db.collection('adoption_progress')
                .get()
                .then(querySnapshot => {
                    console.log('Found adoption progress documents:', querySnapshot.size);
                    const users = [];
                    let processedCount = 0;
                    const totalDocs = querySnapshot.size;
                    
                    if (totalDocs === 0) {
                        showAdminLoading(false);
                        showNoUsersMessage();
                        return;
                    }
                    
                    querySnapshot.forEach(doc => {
                        const userId = doc.id;
                        const data = doc.data();
                        const username = data.username || `User: ${userId.substring(0, 6)}...`;
                        
                        // Check if adoption process is completed (step 10 finished)
                        checkAdoptionStatus(userId, (isCompleted) => {
                            if (!isCompleted) {
                                users.push({
                                    uid: userId,
                                    username: username,
                                    data: data
                                });
                            }
                            
                            processedCount++;
                            if (processedCount === totalDocs) {
                                showAdminLoading(false);
                                if (users.length > 0) {
                                    displayUsersList(users);
                                } else {
                                    showNoActiveProcessesMessage();
                                }
                            }
                        });
                    });
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                    showAdminLoading(false);
                    showAdminError('Error loading user data: ' + error.message);
                });
        }

        function checkAdoptionStatus(userId, callback) {
            db.collection('adoption_progress').doc(userId)
                .get()
                .then(doc => {
                    if (doc.exists) {
                        const data = doc.data();
                        // Check if all 11 steps are complete
                        const adoptions = data.adoptions || {};
                        const currentAdoptionNumber = data.currentAdoption || 1;
                        const currentAdoption = adoptions[currentAdoptionNumber.toString()];
                        
                        if (currentAdoption && currentAdoption.adopt_progress) {
                            const progress = currentAdoption.adopt_progress;
                            const allStepsComplete = Array.from({length: 11}, (_, i) => i + 1)
                                .every(stepNum => progress[`step${stepNum}`] === 'complete');
                            callback(allStepsComplete);
                        } else {
                            // Legacy check for old format
                            const step10Status = data.step10_status;
                            const isCompleted = step10Status === 'completed';
                            callback(isCompleted);
                        }
                    } else {
                        callback(false);
                    }
                })
                .catch(error => {
                    console.error('Error checking adoption status:', error);
                    callback(false);
                });
        }

        function displayUsersList(users) {
            console.log('Displaying users list:', users.length, 'active users');
            const usersContainer = document.getElementById('usersContainer');
            const usersList = document.getElementById('usersList');
            
            usersList.innerHTML = '';
            
            users.forEach(user => {
                const userCard = createUserCard(user);
                usersList.appendChild(userCard);
            });
            
            usersContainer.style.display = 'block';
        }

        function createUserCard(user) {
            const card = document.createElement('div');
            card.className = 'mobile-user-card';
            
            card.innerHTML = `
                <div class="mobile-user-info">
                    <div class="mobile-user-name">${escapeHtml(user.username)}</div>
                </div>
                <div class="mobile-user-actions">
                    <a href="#" class="mobile-action-link" onclick="viewUserProfile('${escapeHtml(user.uid)}'); return false;">
                        See Profile
                    </a>
                    <a href="#" class="mobile-action-link" onclick="viewUserProgress('${escapeHtml(user.uid)}', '${escapeHtml(user.username)}'); return false;">
                        See Progress
                    </a>
                </div>
            `;
            
            return card;
        }

        function updateHeaderForAdminView(username, userId) {
            // Update page title for user progress view
            const pageTitle = document.querySelector('.page-title h2');
            
            if (pageTitle) {
                pageTitle.textContent = `${username}'s Progress`;
            }
        }

        function createAdminProgressInterface() {
            const userProgressView = document.getElementById('userProgressView');
            
            // Create mobile app style interface with back button
            userProgressView.innerHTML = `
                <div id="adminStepsContainer" class="admin-steps-container">
                    <div class="mobile-user-header">
                        <h2 class="mobile-user-name-title" id="userNameHeader">Loading...</h2>
                        <button class="back-button" onclick="backToUsersList()">
                            <span style="font-size: 16px;">←</span>
                            <span>Back to List</span>
                        </button>
                    </div>
                    <div id="stepsContent">
                        <!-- Step cards will be generated here -->
                    </div>
                </div>
            `;
            
            userProgressView.style.display = 'block';
        }

        function backToUsersList() {
            console.log('Returning to users list');
            currentSelectedUser = null;
            
            // Reset page title
            const pageTitle = document.querySelector('.page-title h2');
            
            if (pageTitle) {
                pageTitle.textContent = 'Registered Users';
            }
            
            // Show users list again
            document.getElementById('usersContainer').style.display = 'block';
            
            // Hide admin progress view
            document.getElementById('userProgressView').style.display = 'none';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function viewUserProfile(userId) {
            console.log('Opening user profile for:', userId);
            // TASK 3: Fix to pass userId parameter to Profile.php
            window.location.href = `Profile.php?userId=${userId}`;
        }

        function viewUserProgress(userId, username) {
            console.log('Viewing progress for user:', userId, username);
            currentSelectedUser = { uid: userId, username: username };
            
            // Hide users list 
            document.getElementById('usersContainer').style.display = 'none';
            
            // Show back button and user info in header
            updateHeaderForAdminView(username, userId);
            
            // Create and show admin progress view
            createAdminProgressInterface();
            
            // Load the user's progress data
            loadUserProgress(userId, username);
        }

        function setupUserProgressView(userId, username) {
            const progressView = document.getElementById('userProgressView');
            progressView.innerHTML = `
                <div class="user-progress-header">
                    <div class="user-progress-title">
                        <h3>${escapeHtml(username)}'s Adoption Progress</h3>
                        <p>User ID: ${escapeHtml(userId.substring(0, 12))}...</p>
                    </div>
                    <button class="back-to-list-btn" onclick="backToUsersList()">
                        ← Back to List
                    </button>
                </div>
                <div id="adminStepsContainer" class="admin-steps-container">
                    <!-- Admin step management interface will be generated here -->
                </div>
            `;
            
            // Load progress for this specific user
            loadUserProgress(userId, username);
        }

        function loadUserProgress(userId, username) {
            console.log('Loading progress for user:', userId);
            currentUserId = userId;
            currentUsername = username;
            
            db.collection('adoption_progress').doc(userId)
                .onSnapshot(snapshot => {
                    if (snapshot.exists) {
                        const data = snapshot.data();
                        console.log('User progress data:', data);
                        
                        let progressData;
                        
                        // Handle both old and new structure
                        if (data.adoptions && data.currentAdoption) {
                            const currentAdoptionId = data.currentAdoption.toString();
                            const currentAdoption = data.adoptions[currentAdoptionId];
                            progressData = currentAdoption ? currentAdoption.adopt_progress : {};
                            console.log('Using versioned structure:', progressData);
                        } else {
                            progressData = data.adopt_progress || {};
                            console.log('Using legacy structure:', progressData);
                        }
                        
                        // Generate admin interface exactly like mobile app
                        generateAdminStepsInterface(progressData);
                        
                    } else {
                        console.log('No progress found for user');
                        // Show all steps as locked
                        generateAdminStepsInterface({});
                    }
                }, error => {
                    console.error('Error loading user progress:', error);
                    alert('Error loading progress data: ' + error.message);
                });
        }

        function generateAdminStepsInterface(progressData) {
            const container = document.getElementById('stepsContent');
            if (!container) {
                console.error('Steps content container not found');
                return;
            }
            
            // Update user name header
            const userNameHeader = document.getElementById('userNameHeader');
            if (userNameHeader && currentSelectedUser) {
                userNameHeader.textContent = `User: ${currentSelectedUser.username}`;
            }
            
            container.innerHTML = '';
            
            // Create mobile app style step cards
            stepDefinitions.forEach((step, index) => {
                const stepCard = createMobileAppStepCard(step, progressData);
                container.appendChild(stepCard);
            });
        }

        function createMobileAppStepCard(step, progressData) {
            const stepStatus = progressData[`step${step.number}`] || 'locked';
            
            const stepCard = document.createElement('div');
            stepCard.className = 'admin-step-card';
            stepCard.id = `step${step.number}`;
            
            // Create step header (matching mobile app exactly)
            const stepHeader = document.createElement('div');
            stepHeader.className = 'admin-step-header';
            stepHeader.id = `step${step.number}Header`;
            stepHeader.onclick = () => toggleMobileStepContent(step.number);
            
            // Step title
            const stepTitle = document.createElement('div');
            stepTitle.className = 'admin-step-title';
            stepTitle.textContent = `Step ${step.number}: ${step.title}`;
            
            // Container for status and comment (right side)
            const statusContainer = document.createElement('div');
            statusContainer.style.cssText = `
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 4px;
            `;
            
            // Step status
            const stepStatusDiv = document.createElement('div');
            stepStatusDiv.className = `admin-step-status ${stepStatus}`;
            stepStatusDiv.id = `step${step.number}Status`;
            stepStatusDiv.textContent = getStatusText(stepStatus);
            
            // Step comment (initially hidden, will be loaded from new location)
            const stepCommentDiv = document.createElement('div');
            stepCommentDiv.className = 'admin-step-comment';
            stepCommentDiv.id = `step${step.number}Comment`;
            stepCommentDiv.style.cssText = `
                color: #2196F3;
                font-size: 14px;
                display: none;
            `;
            
            // Load admin comment from new mobile app location
            loadAdminCommentForStep(step.number);
            
            statusContainer.appendChild(stepStatusDiv);
            statusContainer.appendChild(stepCommentDiv);
            
            stepHeader.appendChild(stepTitle);
            stepHeader.appendChild(statusContainer);
            
            // Create step content (initially hidden) - matching mobile app layout
            const stepContent = document.createElement('div');
            stepContent.className = 'admin-step-content';
            stepContent.id = `step${step.number}Content`;
            
            // Create mobile app style content
            console.log(`Creating step content for step ${step.number}`);
            if (step.number >= 2) {
                console.log(`Adding documents section for step ${step.number}`);
                // Documents section (for steps with document uploads)
                const documentsHeader = document.createElement('h5');
                documentsHeader.className = 'documents-section';
                documentsHeader.textContent = 'Uploaded Documents:';
                stepContent.appendChild(documentsHeader);
                
                const documentsContainer = document.createElement('div');
                documentsContainer.className = 'documents-container';
                documentsContainer.id = `step${step.number}DocumentsContainer`;
                documentsContainer.innerHTML = '<div style="color: #666; font-style: italic;">Loading documents...</div>';
                stepContent.appendChild(documentsContainer);
                
                console.log(`Created documents container: step${step.number}DocumentsContainer`);
                
                // Load documents for this user and step immediately and also with delay
                loadStepDocuments(step.number);
                setTimeout(() => {
                    console.log(`Delayed load for step ${step.number}`);
                    loadStepDocuments(step.number);
                }, 500);
            } else {
                console.log(`Skipping documents section for step ${step.number} (step < 2)`);
            }
            
            // Add special interfaces for steps with special requirements
            if (step.requirements && step.requirements.length > 0) {
                step.requirements.forEach((requirement, index) => {
                                         // Special handling for Stage 6 - Ethical Preferences
                    if (requirement.isEthicalForm) {
                        console.log(`🔧 Creating ethical form interface for step ${step.number}`);
                        const ethicalSection = document.createElement('div');
                        ethicalSection.style.cssText = 'background: #e8f5e8; padding: 15px; border-radius: 6px; margin: 15px 0;';
                        ethicalSection.innerHTML = `
                            <strong>📝 Ethical Preferences Form</strong>
                            <div id="adminEthicalForm_${step.number}_${requirement.documentId}" style="margin-top: 10px;">
                                Loading user's ethical preferences...
                            </div>
                        `;
                        stepContent.appendChild(ethicalSection);
                    }
                    
                                         // Special handling for Stage 7 - Matching Results
                    if (requirement.isMatchingInterface) {
                        console.log(`🔧 Creating matching interface for step ${step.number}`);
                        const matchingSection = document.createElement('div');
                        matchingSection.style.cssText = 'background: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0;';
                        matchingSection.innerHTML = `
                            <strong>🧩 Ethical Matching Results</strong>
                            <div id="adminMatchingInterface_${step.number}_${requirement.documentId}" style="margin-top: 10px;">
                                Loading user's matching results...
                            </div>
                        `;
                        stepContent.appendChild(matchingSection);
                    }
                    
                    // Special handling for Appointment Scheduling
                    if (requirement.hasAppointmentScheduling) {
                        const appointmentSection = document.createElement('div');
                        appointmentSection.style.cssText = 'background: #e3f2fd; padding: 15px; border-radius: 6px; margin: 15px 0;';
                        appointmentSection.innerHTML = `
                            <strong>📅 Appointment Scheduling</strong>
                            <div id="adminAppointmentScheduling_${step.number}_${requirement.documentId}" style="margin-top: 10px;">
                                Loading user's appointment requests...
                            </div>
                        `;
                        stepContent.appendChild(appointmentSection);
                    }
                });
            }
            
            // Admin Actions section
            const adminActionsHeader = document.createElement('div');
            adminActionsHeader.className = 'admin-actions-header';
            adminActionsHeader.textContent = 'Admin Actions:';
            stepContent.appendChild(adminActionsHeader);
            
            // Admin controls (using proper function that includes unlock buttons for steps 9 and 10)
            const adminControls = createAdminControls(step.number, stepStatus);
            stepContent.appendChild(adminControls);
            
            // Comment input section
            const commentInput = document.createElement('textarea');
            commentInput.className = 'comment-input';
            commentInput.id = `step${step.number}AdminCommentInput`;
            commentInput.placeholder = 'Add admin comment...';
            stepContent.appendChild(commentInput);
            
            const commentBtn = document.createElement('button');
            commentBtn.className = 'comment-btn';
            commentBtn.textContent = 'ADD COMMENT';
            commentBtn.onclick = () => addAdminComment(step.number);
            stepContent.appendChild(commentBtn);
            
            stepCard.appendChild(stepHeader);
            stepCard.appendChild(stepContent);
            
            return stepCard;
        }

        // Load admin comment from mobile app location for admin view
        function loadAdminCommentForStep(stepNumber) {
            // Get the target user ID (the user whose progress we're viewing)
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId) {
                console.log(`No target user ID for loading admin comment for step ${stepNumber}`);
                return;
            }

            console.log(`🔍 ADMIN VIEW: Loading comment for step ${stepNumber}, user ${targetUserId}`);
            
            // Load from mobile app location: adoption_progress/{userId}/comments/step{X}
            db.collection('adoption_progress').doc(targetUserId)
                .collection('comments').doc(`step${stepNumber}`)
                .get()
                .then(doc => {
                    const commentDiv = document.getElementById(`step${stepNumber}Comment`);
                    
                    if (doc.exists && commentDiv) {
                        const data = doc.data();
                        const comment = data.comment;
                        
                        if (comment && comment.trim()) {
                            console.log(`✅ ADMIN VIEW: Found comment for step ${stepNumber}: ${comment}`);
                            commentDiv.textContent = `Admin Comment: ${comment}`;
                            commentDiv.style.display = 'block';
                        } else {
                            console.log(`❌ ADMIN VIEW: Empty comment for step ${stepNumber}`);
                            commentDiv.style.display = 'none';
                        }
                    } else {
                        console.log(`❌ ADMIN VIEW: No comment document for step ${stepNumber}`);
                        if (commentDiv) {
                            commentDiv.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error(`❌ ADMIN VIEW: Error loading comment for step ${stepNumber}:`, error);
                });
        }

        function createAdminStatusSection(stepNumber, stepStatus) {
            const sectionDiv = document.createElement('div');
            sectionDiv.className = 'admin-status-section';
            
            const title = document.createElement('h5');
            title.textContent = 'Step Status';
            title.style.cssText = 'color: #2c5aa0; margin-bottom: 10px; font-size: 16px;';
            sectionDiv.appendChild(title);
            
            const statusText = document.createElement('div');
            statusText.className = `step-status-text ${stepStatus}`;
            statusText.id = `stepStatusText${stepNumber}`;
            statusText.style.cssText = 'font-size: 18px; font-weight: 600; margin-bottom: 10px;';
            
            switch (stepStatus) {
                case 'complete':
                    statusText.textContent = 'Marked Complete';
                    statusText.style.color = '#28a745';
                    break;
                case 'in_progress':
                    statusText.textContent = 'In Progress';
                    statusText.style.color = '#6ea4ce';
                    break;
                default:
                    statusText.textContent = 'Locked';
                    statusText.style.color = '#6c757d';
            }
            
            sectionDiv.appendChild(statusText);
            return sectionDiv;
        }

        function createStepRequirementsSection(step, stepNumber) {
            const sectionDiv = document.createElement('div');
            sectionDiv.className = 'step-requirements-section';
            
            const title = document.createElement('h5');
            title.textContent = 'Step Requirements';
            title.style.cssText = 'color: #2c5aa0; margin-bottom: 15px; font-size: 16px;';
            sectionDiv.appendChild(title);
            
            const requirementsList = document.createElement('div');
            requirementsList.className = 'requirements-list';
            
            // Add PAP requirements (what PAPs need to do/upload)
            if (step.requirements && step.requirements.length > 0) {
                step.requirements.forEach((requirement, index) => {
                    const reqDiv = document.createElement('div');
                    reqDiv.className = 'requirement-item';
                    
                    // Different styling for different types of requirements
                    if (requirement.isPAPView || requirement.viewOnly) {
                        reqDiv.style.cssText = 'border: 1px solid #e3f2fd; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #f3f8ff;';
                    } else if (requirement.isPAPDownload || requirement.downloadOnly) {
                        reqDiv.style.cssText = 'border: 1px solid #e8f5e8; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #f0fff0;';
                    } else {
                        reqDiv.style.cssText = 'border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #f8f9fa;';
                    }
                    
                    const reqTitle = document.createElement('div');
                    reqTitle.style.cssText = 'font-weight: 600; color: #333; margin-bottom: 5px;';
                    reqTitle.textContent = requirement.title;
                    
                    const reqDesc = document.createElement('div');
                    reqDesc.style.cssText = 'color: #666; font-size: 14px; margin-bottom: 10px;';
                    reqDesc.textContent = requirement.description;
                    
                    reqDiv.appendChild(reqTitle);
                    reqDiv.appendChild(reqDesc);
                    
                    // Add status/content area for view-only and download requirements
                    if (requirement.isPAPView || requirement.viewOnly) {
                        const statusArea = document.createElement('div');
                        statusArea.id = `papViewStatus_${stepNumber}_${requirement.documentId}`;
                        statusArea.style.cssText = 'margin-top: 10px; padding: 10px; background: #fff; border-radius: 4px; border: 1px solid #ddd;';
                        statusArea.innerHTML = '<span style="color: #666; font-style: italic;">Loading status...</span>';
                        reqDiv.appendChild(statusArea);
                        
                        // Load the status for this view-only requirement
                        setTimeout(() => loadPAPViewStatus(stepNumber, requirement.documentId, statusArea), 100);
                    }
                    
                    if (requirement.isPAPDownload || requirement.downloadOnly) {
                        const downloadArea = document.createElement('div');
                        downloadArea.id = `papDownloadArea_${stepNumber}_${requirement.documentId}`;
                        downloadArea.style.cssText = 'margin-top: 10px;';
                        reqDiv.appendChild(downloadArea);
                        
                        // Load download options for this requirement
                        setTimeout(() => loadPAPDownloadOptions(stepNumber, requirement.documentId, downloadArea), 100);
                    }
                    
                    // Add matching interface for Stage 7
                    if (requirement.isMatchingInterface) {
                        const matchingArea = document.createElement('div');
                        matchingArea.id = `matchingInterface_${stepNumber}_${requirement.documentId}`;
                        matchingArea.style.cssText = 'margin-top: 15px;';
                        reqDiv.appendChild(matchingArea);
                        
                        // Load ethical matching interface
                        setTimeout(() => loadEthicalMatchingInterface(stepNumber, requirement.documentId, matchingArea), 100);
                    }
                    
                    // Add ethical form handling for Stage 6
                    if (requirement.isEthicalForm) {
                        const formArea = document.createElement('div');
                        formArea.id = `ethicalForm_${stepNumber}_${requirement.documentId}`;
                        formArea.style.cssText = 'margin-top: 15px;';
                        reqDiv.appendChild(formArea);
                        
                        // Load ethical preference form
                        setTimeout(() => loadEthicalPreferenceForm(stepNumber, requirement.documentId, formArea), 100);
                    }
                    
                    // Add appointment scheduling for Stage 7 selection
                    if (requirement.hasAppointmentScheduling) {
                        const appointmentArea = document.createElement('div');
                        appointmentArea.id = `appointmentScheduling_${stepNumber}_${requirement.documentId}`;
                        appointmentArea.style.cssText = 'margin-top: 15px;';
                        reqDiv.appendChild(appointmentArea);
                        
                        // Load appointment scheduling interface
                        setTimeout(() => loadAppointmentScheduling(stepNumber, requirement.documentId, appointmentArea), 100);
                        

                    }
                    
                    // Add link if available
                    if (requirement.link) {
                        const reqLink = document.createElement('a');
                        reqLink.href = requirement.link;
                        reqLink.target = '_blank';
                        reqLink.textContent = 'Open Form/Link';
                        reqLink.style.cssText = 'color: #6ea4ce; text-decoration: none; font-weight: 500;';
                        reqDiv.appendChild(reqLink);
                    }
                    
                    requirementsList.appendChild(reqDiv);
                });
            }
            
            // Add Social Worker uploads section (if admin and there are social worker uploads)
            if (isAdminUser && step.socialWorkerUploads && step.socialWorkerUploads.length > 0) {
                const socialWorkerSection = document.createElement('div');
                socialWorkerSection.className = 'social-worker-section';
                socialWorkerSection.style.cssText = 'margin-top: 20px; border-top: 2px solid #ff9800; padding-top: 15px;';
                
                const swTitle = document.createElement('h5');
                swTitle.textContent = '👨‍⚕️ Social Worker Actions';
                swTitle.style.cssText = 'color: #ff9800; margin-bottom: 15px; font-size: 16px;';
                socialWorkerSection.appendChild(swTitle);
                
                step.socialWorkerUploads.forEach((upload, index) => {
                    const uploadDiv = document.createElement('div');
                    uploadDiv.className = 'social-worker-upload-item';
                    uploadDiv.style.cssText = 'border: 1px solid #ffe0b2; border-radius: 8px; padding: 15px; margin-bottom: 10px; background: #fff8e1;';
                    
                    const uploadTitle = document.createElement('div');
                    uploadTitle.style.cssText = 'font-weight: 600; color: #ef6c00; margin-bottom: 5px;';
                    uploadTitle.textContent = upload.title;
                    
                    const uploadDesc = document.createElement('div');
                    uploadDesc.style.cssText = 'color: #666; font-size: 14px; margin-bottom: 15px;';
                    uploadDesc.textContent = upload.description;
                    
                    uploadDiv.appendChild(uploadTitle);
                    uploadDiv.appendChild(uploadDesc);
                    
                    // Add upload interface
                    const uploadInterface = createSocialWorkerUploadInterface(stepNumber, upload);
                    uploadDiv.appendChild(uploadInterface);
                    
                    socialWorkerSection.appendChild(uploadDiv);
                });
                
                requirementsList.appendChild(socialWorkerSection);
            }
            
            // Show message if no requirements
            if ((!step.requirements || step.requirements.length === 0) && 
                (!isAdminUser || !step.socialWorkerUploads || step.socialWorkerUploads.length === 0)) {
                const noReq = document.createElement('div');
                noReq.textContent = 'No specific requirements for this step.';
                noReq.style.cssText = 'color: #666; font-style: italic;';
                requirementsList.appendChild(noReq);
            }
            
            sectionDiv.appendChild(requirementsList);
            return sectionDiv;
        }

        function createAdminControls(stepNumber, stepStatus) {
            const controlsDiv = document.createElement('div');
            controlsDiv.className = 'admin-controls';

            const title = document.createElement('h5');
            title.textContent = '⚙️ Admin Controls';
            title.style.cssText = 'color: #c62828; margin-bottom: 15px; font-size: 16px;';
            controlsDiv.appendChild(title);
            
            const buttonsDiv = document.createElement('div');
            buttonsDiv.style.cssText = 'display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;';
            
            // Mark Complete button (matching mobile app logic)
            if (stepStatus !== 'complete') {
                const markCompleteBtn = document.createElement('button');
                markCompleteBtn.className = 'admin-btn btn-mark-complete';
                markCompleteBtn.textContent = '✓ Mark Complete';
                markCompleteBtn.style.cssText = 'background: #28a745; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
                markCompleteBtn.onclick = () => markStepComplete(stepNumber);
                buttonsDiv.appendChild(markCompleteBtn);
            }
            
            // Mark In Progress button (matching mobile app logic - only for steps 9 and 10 when locked)
            if ((stepNumber === 9 || stepNumber === 10) && stepStatus === 'locked') {
                const markProgressBtn = document.createElement('button');
                markProgressBtn.className = 'admin-btn btn-mark-progress';
                markProgressBtn.textContent = '🔄 Set In Progress';
                markProgressBtn.style.cssText = 'background: #6ea4ce; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
                markProgressBtn.onclick = () => markStepInProgress(stepNumber);
                buttonsDiv.appendChild(markProgressBtn);
            }
            
            controlsDiv.appendChild(buttonsDiv);

            return controlsDiv;
        }

        function createAdminCommentSection(stepNumber) {
            const sectionDiv = document.createElement('div');
            sectionDiv.className = 'admin-comment-section';
            
            const title = document.createElement('h5');
            title.textContent = 'Admin Comments';
            sectionDiv.appendChild(title);
            
            // Current comment display
            const currentComment = document.createElement('div');
            currentComment.className = 'current-comment';
            currentComment.id = `currentComment${stepNumber}`;
            currentComment.style.display = 'none';
            sectionDiv.appendChild(currentComment);
            
            // Comment input group
            const inputGroup = document.createElement('div');
            inputGroup.className = 'comment-input-group';
            
            const commentInput = document.createElement('textarea');
            commentInput.className = 'comment-input';
            commentInput.id = `commentInput${stepNumber}`;
            commentInput.placeholder = 'Add admin comment for this step...';
            commentInput.rows = 3;
            
            const saveBtn = document.createElement('button');
            saveBtn.className = 'btn-save-comment';
            saveBtn.textContent = 'Save Comment';
            saveBtn.onclick = () => saveAdminComment(stepNumber);
            
            inputGroup.appendChild(commentInput);
            inputGroup.appendChild(saveBtn);
            sectionDiv.appendChild(inputGroup);
            
            // Load existing comment
            loadAdminComment(stepNumber);
            
            return sectionDiv;
        }

        function createDocumentsSection(stepNumber) {
            const sectionDiv = document.createElement('div');
            sectionDiv.className = 'documents-section';
            
            const title = document.createElement('h5');
            title.textContent = 'Submitted Documents';
            sectionDiv.appendChild(title);
            
            const documentsList = document.createElement('div');
            documentsList.className = 'document-list';
            documentsList.id = `documents${stepNumber}`;
            sectionDiv.appendChild(documentsList);
            
            // Documents will be loaded when step is expanded
            
            return sectionDiv;
        }

                // Helper function to create social worker upload interface
        function createSocialWorkerUploadInterface(stepNumber, upload) {
            const interfaceDiv = document.createElement('div');
            interfaceDiv.className = 'social-worker-upload-interface';
            
            const fileInputId = `sw_upload_${stepNumber}_${upload.documentId}`;
            const acceptedFormats = upload.acceptedFormats ? upload.acceptedFormats.join(',') : '.pdf,.doc,.docx';
            
            // File input and upload button
            const uploadHTML = `
                <div class="file-input-wrapper" style="margin-bottom: 10px;">
                    <input 
                        type="file" 
                        id="${fileInputId}" 
                        class="file-input" 
                        accept="${acceptedFormats}"
                        onchange="handleSocialWorkerFileSelect(event, '${fileInputId}')"
                        style="display: none;"
                    >
                    <label for="${fileInputId}" class="file-input-label" style="
                        display: inline-block;
                        padding: 10px 15px;
                        background: #ff9800;
                        color: white;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        margin-right: 10px;
                    ">
                        📁 Choose File
                    </label>
                    <button 
                        type="button" 
                        class="social-worker-upload-button" 
                        id="swUploadBtn_${stepNumber}_${upload.documentId}"
                        onclick="uploadSocialWorkerDocument('${upload.documentId}', '${fileInputId}', ${stepNumber}, ${JSON.stringify(upload).replace(/"/g, '&quot;')})"
                        disabled
                        style="
                            background: #4CAF50;
                            color: white;
                            border: none;
                            padding: 10px 15px;
                            border-radius: 5px;
                            cursor: pointer;
                            font-size: 14px;
                        "
                    >
                        Upload Document
                    </button>
                </div>
                <div id="selectedFileName_${fileInputId}" class="selected-file" style="display: none; color: #666; font-size: 12px; margin-bottom: 10px;"></div>
            `;
            
            interfaceDiv.innerHTML = uploadHTML;
            
            // Add status selection if this upload has status field
            if (upload.hasStatusField && upload.statusOptions) {
                const statusDiv = document.createElement('div');
                statusDiv.style.cssText = 'margin-top: 10px;';
                
                const statusLabel = document.createElement('label');
                statusLabel.textContent = 'Recommendation Status:';
                statusLabel.style.cssText = 'display: block; font-weight: 600; margin-bottom: 5px; color: #333;';
                
                const statusSelect = document.createElement('select');
                statusSelect.id = `swStatus_${stepNumber}_${upload.documentId}`;
                statusSelect.style.cssText = 'padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 200px;';
                
                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select status...';
                statusSelect.appendChild(defaultOption);
                
                // Add status options
                upload.statusOptions.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status;
                    statusSelect.appendChild(option);
                });
                
                statusDiv.appendChild(statusLabel);
                statusDiv.appendChild(statusSelect);
                interfaceDiv.appendChild(statusDiv);
            }
            
            // Show existing uploads for this document type
            const existingUploadsDiv = document.createElement('div');
            existingUploadsDiv.id = `swExistingUploads_${stepNumber}_${upload.documentId}`;
            existingUploadsDiv.style.cssText = 'margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;';
            interfaceDiv.appendChild(existingUploadsDiv);
            
            // Load existing uploads
            setTimeout(() => loadExistingSocialWorkerUploads(stepNumber, upload.documentId, existingUploadsDiv), 100);
            
            return interfaceDiv;
        }
        
        // Helper function to load PAP view status (for stage 5)
        function loadPAPViewStatus(stepNumber, documentId, statusArea) {
            if (!currentSelectedUser && !isAdminUser) {
                // Regular user viewing their own status
                const targetUserId = userId;
                loadSocialWorkerDocumentStatus(stepNumber, documentId, statusArea, targetUserId);
            } else if (isAdminUser && currentSelectedUser) {
                // Admin viewing a user's status
                const targetUserId = currentSelectedUser.uid;
                loadSocialWorkerDocumentStatus(stepNumber, documentId, statusArea, targetUserId);
            } else {
                statusArea.innerHTML = '<span style="color: #999;">No user selected</span>';
            }
        }
        
        // Helper function to load PAP download options (for stage 10)
        function loadPAPDownloadOptions(stepNumber, documentId, downloadArea) {
            if (!currentSelectedUser && !isAdminUser) {
                // Regular user downloading their own documents
                const targetUserId = userId;
                loadAvailableDownloads(stepNumber, documentId, downloadArea, targetUserId);
            } else if (isAdminUser && currentSelectedUser) {
                // Admin viewing a user's download options
                const targetUserId = currentSelectedUser.uid;
                loadAvailableDownloads(stepNumber, documentId, downloadArea, targetUserId);
            } else {
                downloadArea.innerHTML = '<span style="color: #999;">No user selected</span>';
            }
        }
        
        // Function to load social worker document status
        function loadSocialWorkerDocumentStatus(stepNumber, documentId, statusArea, targetUserId) {
            // Check for social worker uploads related to this step and document
            db.collection("social_worker_uploads")
                .doc(targetUserId)
                .collection(`step${stepNumber}_uploads`)
                .get()
                .then(querySnapshot => {
                    if (querySnapshot.empty) {
                        statusArea.innerHTML = '<span style="color: #999;">⏳ Awaiting social worker review</span>';
                        return;
                    }
                    
                    let hasRelevantDocument = false;
                    let latestStatus = null;
                    let latestTimestamp = 0;
                    
                    querySnapshot.forEach(doc => {
                        const data = doc.data();
                        if (data.uploadType && data.uploadType.includes(documentId.replace('_status', ''))) {
                            hasRelevantDocument = true;
                            if (data.timestamp > latestTimestamp) {
                                latestTimestamp = data.timestamp;
                                latestStatus = data.status || data.recommendationStatus;
                            }
                        }
                    });
                    
                    if (hasRelevantDocument && latestStatus) {
                        const statusColor = latestStatus === 'Approved' ? '#4CAF50' : '#FF9800';
                        const statusIcon = latestStatus === 'Approved' ? '✅' : '⚠️';
                        statusArea.innerHTML = `
                            <div style="color: ${statusColor}; font-weight: 600;">
                                ${statusIcon} Status: ${latestStatus}
                            </div>
                            <div style="color: #666; font-size: 12px; margin-top: 5px;">
                                Updated: ${new Date(latestTimestamp).toLocaleString()}
                            </div>
                        `;
                    } else {
                        statusArea.innerHTML = '<span style="color: #999;">⏳ Awaiting social worker review</span>';
                    }
                })
                .catch(error => {
                    console.error('Error loading social worker document status:', error);
                    statusArea.innerHTML = '<span style="color: #f44336;">Error loading status</span>';
                });
        }
        
        // Function to load available downloads
        function loadAvailableDownloads(stepNumber, documentId, downloadArea, targetUserId) {
            // Check for social worker uploads that PAPs can download
            db.collection("social_worker_uploads")
                .doc(targetUserId)
                .collection(`step${stepNumber}_uploads`)
                .get()
                .then(querySnapshot => {
                    if (querySnapshot.empty) {
                        downloadArea.innerHTML = '<span style="color: #999;">📄 No documents available for download yet</span>';
                        return;
                    }
                    
                    const downloadableDocuments = [];
                    querySnapshot.forEach(doc => {
                        const data = doc.data();
                        if (data.uploadType && data.uploadType.includes('certificate') && data.fileUrl) {
                            downloadableDocuments.push({
                                fileName: data.fileName,
                                fileUrl: data.fileUrl,
                                timestamp: data.timestamp
                            });
                        }
                    });
                    
                    if (downloadableDocuments.length > 0) {
                        let downloadHTML = '<div style="margin-bottom: 10px; color: #4CAF50; font-weight: 600;">📄 Available Documents:</div>';
                        downloadableDocuments.forEach(doc => {
                            downloadHTML += `
                                <div style="margin-bottom: 8px; padding: 8px; background: #f0fff0; border: 1px solid #c8e6c9; border-radius: 4px;">
                                    <div style="font-weight: 600; margin-bottom: 4px;">${doc.fileName}</div>
                                    <button 
                                        onclick="downloadDocument('${doc.fileUrl}', '${doc.fileName}')"
                                        style="
                                            background: #4CAF50;
                                            color: white;
                                            border: none;
                                            padding: 6px 12px;
                                            border-radius: 4px;
                                            cursor: pointer;
                                            font-size: 12px;
                                        "
                                    >
                                        📥 Download
                                    </button>
                                    <span style="color: #666; font-size: 11px; margin-left: 10px;">
                                        Uploaded: ${new Date(doc.timestamp).toLocaleDateString()}
                                    </span>
                                </div>
                            `;
                        });
                        downloadArea.innerHTML = downloadHTML;
                    } else {
                        downloadArea.innerHTML = '<span style="color: #999;">📄 No documents available for download yet</span>';
                    }
                })
                .catch(error => {
                    console.error('Error loading downloadable documents:', error);
                    downloadArea.innerHTML = '<span style="color: #f44336;">Error loading downloads</span>';
                });
        }

                 // Mobile app style toggle function
        function toggleMobileStepContent(stepNumber) {
            console.log(`🔄 Toggling step ${stepNumber} content`);
            
            // Always use the original step content which includes admin controls
            const content = document.getElementById(`step${stepNumber}Content`);
            console.log(`🔄 Content element:`, content);
            
            if (content) {
                if (content.classList.contains('visible')) {
                    console.log(`🔄 Hiding step ${stepNumber} content`);
                    content.classList.remove('visible');
                } else {
                    console.log(`🔄 Showing step ${stepNumber} content`);
                    content.classList.add('visible');
                    
                    // Load documents when step is expanded (matching mobile app behavior)
                    if (stepNumber >= 2) {
                        console.log(`🔄 Loading documents for expanded step ${stepNumber}`);
                        loadStepDocuments(stepNumber);
                    }
                    
                    // Load special interface data for admin users
                    if (isAdminUser && currentSelectedUser) {
                        console.log(`🔄 Loading special interface data for step ${stepNumber}`);
                        const stepDef = stepDefinitions.find(s => s.number === stepNumber);
                        if (stepDef && stepDef.requirements) {
                            stepDef.requirements.forEach((requirement, index) => {
                                // Load special interfaces for admin view
                                if (requirement.isEthicalForm) {
                                    const container = document.getElementById(`adminEthicalForm_${stepNumber}_${requirement.documentId}`);
                                    if (container) {
                                        loadAdminEthicalPreferences(currentSelectedUser.uid, container);
                                    }
                                } else if (requirement.isMatchingInterface) {
                                    const container = document.getElementById(`adminMatchingInterface_${stepNumber}_${requirement.documentId}`);
                                    if (container) {
                                        loadAdminMatchingResults(currentSelectedUser.uid, container);
                                    }
                                } else if (requirement.hasAppointmentScheduling) {
                                    const container = document.getElementById(`adminAppointmentScheduling_${stepNumber}_${requirement.documentId}`);
                                    if (container) {
                                        loadAdminAppointmentRequests(currentSelectedUser.uid, container);
                                    }
                                }
                            });
                        }
                    }
                }
            } else {
                console.log(`❌ Could not find content element for step ${stepNumber}`);
            }
        }

        // TASK 1: Admin Step Content Toggle with Special Interface Support
        function toggleAdminStepContent(stepNumber) {
            console.log(`🔧 ADMIN: Toggling step ${stepNumber} content for user:`, currentSelectedUser?.uid);
            
            if (!currentSelectedUser) {
                console.error('❌ No user selected for admin view');
                return;
            }
            
            // Find or create the admin step content container
            let contentContainer = document.getElementById(`adminStep${stepNumber}Content`);
            
            if (!contentContainer) {
                // Create the content container if it doesn't exist - look for admin step card
                const stepCard = document.getElementById(`step${stepNumber}`) || 
                               document.querySelector(`[onclick*="toggleMobileStepContent(${stepNumber}"]`)?.closest('.admin-step-card');
                if (!stepCard) {
                    console.error('❌ Could not find admin step card for step', stepNumber);
                    return;
                }
                
                contentContainer = document.createElement('div');
                contentContainer.id = `adminStep${stepNumber}Content`;
                contentContainer.className = 'admin-step-content-special';
                contentContainer.style.cssText = 'margin-top: 15px; padding: 15px; border: 2px solid #007bff; border-radius: 8px; background: #f8f9ff;';
                stepCard.appendChild(contentContainer);
            }
            
            // Toggle visibility
            if (contentContainer.classList.contains('visible')) {
                console.log(`🔄 Hiding admin step ${stepNumber} content`);
                contentContainer.classList.remove('visible');
                return;
            }
            
            console.log(`🔄 Showing admin step ${stepNumber} content`);
            contentContainer.classList.add('visible');
            contentContainer.innerHTML = '<div style="text-align: center; padding: 20px;">Loading step content...</div>';
            
            // Load step-specific content based on step number
            const stepDef = stepDefinitions.find(s => s.number === stepNumber);
            if (!stepDef) {
                contentContainer.innerHTML = '<div style="color: #f44336;">Step definition not found</div>';
                return;
            }
            
            // Generate admin view content
            generateAdminStepContent(stepNumber, stepDef, contentContainer);
        }
        
        // TASK 1: Generate Admin Step Content with Special Interface Support
        function generateAdminStepContent(stepNumber, stepDef, container) {
            console.log(`📋 ADMIN: Generating content for step ${stepNumber}`);
            
            let html = `
                <div class="admin-step-header">
                    <h4>${stepDef.title}</h4>
                </div>
            `;
            
            // Generate requirements sections
            if (stepDef.requirements && stepDef.requirements.length > 0) {
                stepDef.requirements.forEach((requirement, index) => {
                    html += `
                        <div class="admin-requirement-section" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #fff;">
                            <h5>${requirement.title}</h5>
                            <p style="color: #666;">${requirement.description}</p>
                    `;
                    
                    // Special handling for Stage 6 and 7
                    if (requirement.isEthicalForm) {
                        html += `
                            <div style="background: #e8f5e8; padding: 15px; border-radius: 6px; margin: 10px 0;">
                                <strong>📝 Ethical Preferences Form</strong>
                                <div id="adminEthicalForm_${stepNumber}_${requirement.documentId}" style="margin-top: 10px;">
                                    Loading user's ethical preferences...
                                </div>
                            </div>
                        `;
                    } else if (requirement.isMatchingInterface) {
                        html += `
                            <div style="background: #fff3cd; padding: 15px; border-radius: 6px; margin: 10px 0;">
                                <strong>🧩 Ethical Matching Results</strong>
                                <div id="adminMatchingInterface_${stepNumber}_${requirement.documentId}" style="margin-top: 10px;">
                                    Loading user's matching results...
                                </div>
                            </div>
                        `;
                    } else if (requirement.hasAppointmentScheduling) {
                        html += `
                            <div style="background: #e3f2fd; padding: 15px; border-radius: 6px; margin: 10px 0;">
                                <strong>📅 Appointment Scheduling</strong>
                                <div id="adminAppointmentScheduling_${stepNumber}_${requirement.documentId}" style="margin-top: 10px;">
                                    Loading user's appointment requests...
                                </div>
                            </div>
                        `;
                    }
                    
                    // Always show documents section
                    html += `
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 10px 0;">
                            <strong>📄 Submitted Documents</strong>
                            <div id="adminDocuments_${stepNumber}_${requirement.documentId}" style="margin-top: 10px;">
                                Loading submitted documents...
                            </div>
                        </div>
                    `;
                    
                    html += '</div>';
                });
            }
            
            container.innerHTML = html;
            
            // Load the actual data
            setTimeout(() => {
                loadAdminStepData(stepNumber, stepDef);
            }, 100);
        }
        
        // TASK 1: Load Admin Step Data
        function loadAdminStepData(stepNumber, stepDef) {
            console.log(`📊 ADMIN: Loading data for step ${stepNumber}, user:`, currentSelectedUser?.uid);
            
            if (!currentSelectedUser || !stepDef.requirements) return;
            
            stepDef.requirements.forEach((requirement, index) => {
                // Load special interfaces for admin view
                if (requirement.isEthicalForm) {
                    const container = document.getElementById(`adminEthicalForm_${stepNumber}_${requirement.documentId}`);
                    if (container) {
                        loadAdminEthicalPreferences(currentSelectedUser.uid, container);
                    }
                } else if (requirement.isMatchingInterface) {
                    const container = document.getElementById(`adminMatchingInterface_${stepNumber}_${requirement.documentId}`);
                    if (container) {
                        loadAdminMatchingResults(currentSelectedUser.uid, container);
                    }
                } else if (requirement.hasAppointmentScheduling) {
                    const container = document.getElementById(`adminAppointmentScheduling_${stepNumber}_${requirement.documentId}`);
                    if (container) {
                        loadAdminAppointmentRequests(currentSelectedUser.uid, container);
                    }
                }
                
                // Load documents
                const docContainer = document.getElementById(`adminDocuments_${stepNumber}_${requirement.documentId}`);
                if (docContainer) {
                    loadAdminDocumentsForRequirement(stepNumber, requirement.documentId, currentSelectedUser.uid, docContainer);
                }
            });
        }
        
        // TASK 1: Load Admin Ethical Preferences View
        function loadAdminEthicalPreferences(userId, container) {
            console.log('📝 ADMIN: Loading ethical preferences for user:', userId);
            
            db.collection('ethical_preferences').doc(userId).get()
                .then(doc => {
                    if (doc.exists) {
                        const prefs = doc.data();
                        container.innerHTML = `
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 14px;">
                                <div><strong>Age Range:</strong> ${prefs.ageRange || 'Not specified'}</div>
                                <div><strong>Gender:</strong> ${prefs.genderPreference || 'Not specified'}</div>
                                <div><strong>Siblings:</strong> ${prefs.siblingsOpen || 'Not specified'}</div>
                                <div><strong>Special Needs:</strong> ${prefs.specialNeedsOpen || 'Not specified'}</div>
                                <div><strong>Activities:</strong> ${prefs.activityInterests || 'Not specified'}</div>
                                <div><strong>Education:</strong> ${prefs.educationalBackground || 'Not specified'}</div>
                            </div>
                            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                                Saved: ${prefs.savedAt ? new Date(prefs.savedAt).toLocaleString() : 'Unknown'}
                            </div>
                        `;
                    } else {
                        container.innerHTML = '<em style="color: #999;">No ethical preferences saved yet</em>';
                    }
                })
                .catch(error => {
                    console.error('Error loading ethical preferences:', error);
                    container.innerHTML = '<em style="color: #f44336;">Error loading preferences</em>';
                });
        }
        
        // TASK 1: Load Admin Matching Results View
        function loadAdminMatchingResults(userId, container) {
            console.log('🧩 ADMIN: Loading matching results for user:', userId);
            
            // Check for child selections
            db.collection('child_selections').where('userId', '==', userId).get()
                .then(snapshot => {
                    if (!snapshot.empty) {
                        let html = '<div style="font-size: 14px;">';
                        snapshot.docs.forEach(doc => {
                            const selection = doc.data();
                            html += `
                                <div style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 8px;">
                                    <strong>Selected Child:</strong> ${selection.childName || selection.childId}<br>
                                    <strong>Status:</strong> ${selection.status || 'Unknown'}<br>
                                    <strong>Selected:</strong> ${selection.selectedAt ? new Date(selection.selectedAt).toLocaleString() : 'Unknown'}
                                </div>
                            `;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<em style="color: #999;">No child selections made yet</em>';
                    }
                })
                .catch(error => {
                    console.error('Error loading matching results:', error);
                    container.innerHTML = '<em style="color: #f44336;">Error loading matching results</em>';
                });
        }
        
        // TASK 1: Load Admin Appointment Requests View
        function loadAdminAppointmentRequests(userId, container) {
            console.log('📅 ADMIN: Loading appointment requests for user:', userId);
            
            db.collection('appointment_requests').where('userId', '==', userId).get()
                .then(snapshot => {
                    if (!snapshot.empty) {
                        let html = '<div style="font-size: 14px;">';
                        snapshot.docs.forEach(doc => {
                            const appt = doc.data();
                            html += `
                                <div style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 8px;">
                                    <strong>Child:</strong> ${appt.childName || appt.childId}<br>
                                    <strong>Date:</strong> ${appt.appointmentDate} at ${appt.appointmentTime}<br>
                                    <strong>Type:</strong> ${appt.meetingType}<br>
                                    <strong>Status:</strong> ${appt.status || 'Pending'}<br>
                                    <strong>Requested:</strong> ${appt.requestedAt ? new Date(appt.requestedAt).toLocaleString() : 'Unknown'}
                                </div>
                            `;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<em style="color: #999;">No appointment requests yet</em>';
                    }
                })
                .catch(error => {
                    console.error('Error loading appointment requests:', error);
                    container.innerHTML = '<em style="color: #f44336;">Error loading appointments</em>';
                });
        }

                 // Admin comment function for mobile app style - saves to new location
         function addAdminComment(stepNumber) {
             const commentInput = document.getElementById(`step${stepNumber}AdminCommentInput`);
             const comment = commentInput.value.trim();
             
             if (!comment) {
                 alert('Please enter a comment');
                 return;
             }
             
             if (!currentSelectedUser) {
                 alert('No user selected');
                 return;
             }
             
             const targetUserId = currentSelectedUser.uid;
             console.log(`💬 ADMIN: Saving comment for step ${stepNumber}, user ${targetUserId}: ${comment}`);
             
             // Save to NEW MOBILE APP LOCATION: adoption_progress/{userId}/comments/step{X}
             db.collection('adoption_progress').doc(targetUserId)
                 .collection('comments').doc(`step${stepNumber}`)
                 .set({
                     comment: comment,
                     timestamp: Date.now(),
                     createdBy: userId || 'admin' // Current admin user ID
                 })
                 .then(() => {
                     console.log('✅ ADMIN: Comment saved successfully to new location');
                     commentInput.value = '';
                     
                     // Update the comment display in header immediately
                     const commentDiv = document.getElementById(`step${stepNumber}Comment`);
                     if (commentDiv) {
                         commentDiv.textContent = `Admin Comment: ${comment}`;
                         commentDiv.style.display = 'block';
                     }
                 })
                 .catch(error => {
                     console.error('❌ ADMIN: Error saving comment:', error);
                     alert('Error saving comment: ' + error.message);
                 });
         }

         // Update mobile step status in UI
         function updateMobileStepStatus(stepNumber, newStatus) {
             // Update status text in header
             const statusDiv = document.getElementById(`step${stepNumber}Status`);
             if (statusDiv) {
                 statusDiv.className = `admin-step-status ${newStatus}`;
                 statusDiv.textContent = getStatusText(newStatus);
             }
             
             // Update the Mark Complete button if status changed to complete
             if (newStatus === 'complete') {
                 const adminControls = document.querySelector(`#step${stepNumber}Content .admin-controls`);
                 if (adminControls) {
                     const markCompleteBtn = adminControls.querySelector('.btn-mark-complete');
                     if (markCompleteBtn) {
                         markCompleteBtn.remove();
                     }
                 }
             }
         }

         // Load step documents for mobile app style - EXACT SAME AS WORKING FUNCTION
         function loadStepDocuments(stepNumber) {
             console.log(`🔍 loadStepDocuments called for step ${stepNumber}`);
             console.log(`🔍 currentSelectedUser:`, currentSelectedUser);
             
             if (!currentSelectedUser) {
                 console.log('❌ No user selected for document loading');
                 return;
             }
             
             const targetUserId = currentSelectedUser.uid;
             console.log(`🔍 Loading documents for user: ${targetUserId}, step: ${stepNumber}`);
             
             // Check if container exists
             const container = document.getElementById(`step${stepNumber}DocumentsContainer`);
             console.log(`🔍 Container check for step${stepNumber}DocumentsContainer:`, container);
             
             // Initialize submissionData if not exists
             if (!submissionData) {
                 console.log(`🔍 Creating global submissionData`);
                 window.submissionData = {};
             }
             if (!submissionData[stepNumber]) {
                 console.log(`🔍 Creating submissionData for step ${stepNumber}`);
                 submissionData[stepNumber] = {};
             }
             
             // Use EXACT SAME function as the working one
             return db.collection("adoption_progress").doc(targetUserId)
                .get()
                .then(adoptionDoc => {
                    const currentAdoptionNumber = adoptionDoc.exists && adoptionDoc.data().adoptions 
                        ? (adoptionDoc.data().currentAdoption || 1) 
                        : 1;

                    console.log(`Current adoption number for document filtering: ${currentAdoptionNumber}`);

                    const documentsRef = db.collection("adoption_progress")
                         .doc(targetUserId)
                         .collection(`step${stepNumber}_uploads`)
                        .orderBy("timestamp", "desc");

                    return documentsRef.get().then(querySnapshot => {
                        console.log(`Found ${querySnapshot.size} total documents for step ${stepNumber}`);
                        
                        // Show all valid submitted documents (matching mobile app behavior)
                        let documentsToShow = querySnapshot.docs.filter(doc => {
                        const data = doc.data();
                            // Only filter out documents that are clearly invalid or corrupted
                            return data.fileName && data.fileUrl;
                        });

                        console.log(`Showing ${documentsToShow.length} documents for step ${stepNumber}`);

                        // Store documents data for display - merge with existing submission status
                        submissionData[stepNumber].documents = documentsToShow.map(doc => {
                            const data = doc.data();
                            return {
                                id: doc.id,
                                fileName: data.fileName,
                                fileUrl: data.fileUrl,
                                timestamp: data.timestamp,
                                adoptionNumber: data.adoptionNumber
                            };
                        });

                        console.log(`Stored ${submissionData[stepNumber].documents.length} documents for step ${stepNumber}:`, submissionData[stepNumber]);
                    
                        // Update UI to show documents using EXACT SAME function
                        updateMobileStepDocumentsDisplay(stepNumber);
                    });
                })
                .catch(error => {
                    console.error(`Error fetching documents for step ${stepNumber}:`, error);
                    submissionData[stepNumber] = { documents: [] };
                    updateMobileStepDocumentsDisplay(stepNumber);
                    return Promise.resolve();
                });
         }
         
         // Mobile app style document display - based on working function
         function updateMobileStepDocumentsDisplay(stepNumber) {
             const documentsContainer = document.getElementById(`step${stepNumber}DocumentsContainer`);
             if (!documentsContainer) {
                 console.log(`Documents container not found for step ${stepNumber}`);
                 return;
             }

            documentsContainer.innerHTML = ''; // Clear existing content

            const stepData = submissionData[stepNumber];
            if (!stepData || !stepData.documents || stepData.documents.length === 0) {
                documentsContainer.innerHTML = '<div style="color: #666; font-style: italic;">No documents uploaded yet.</div>';
                return;
            }

            console.log(`Displaying ${stepData.documents.length} documents for step ${stepNumber}`);

            stepData.documents.forEach(doc => {
                const docDiv = document.createElement('div');
                docDiv.className = 'document-item';
                
                // Create document name (exactly like mobile app format)
                const docName = document.createElement('div');
                docName.className = 'document-name';
                docName.textContent = doc.fileName || 'Document';
                
                // Create view document link (exactly like mobile app)
                const viewLink = document.createElement('a');
                viewLink.className = 'btn-view-doc';
                viewLink.textContent = 'View Document';
                viewLink.href = '#';
                viewLink.onclick = (e) => {
                    e.preventDefault();
                    viewDocument(doc.fileUrl);
                };
                
                // Create upload date (like mobile app - smaller text)
                const docDate = document.createElement('div');
                docDate.className = 'document-date';
                const uploadDate = doc.timestamp ? new Date(doc.timestamp) : null;
                docDate.textContent = uploadDate ? 
                    `Uploaded: ${uploadDate.toLocaleDateString()} ${uploadDate.toLocaleTimeString()}` : 
                    'Uploaded: Unknown';
                
                docDiv.appendChild(docName);
                docDiv.appendChild(viewLink);
                docDiv.appendChild(docDate);
                
                documentsContainer.appendChild(docDiv);
            });
         }

         // View document function
         function viewDocument(downloadUrl) {
             if (downloadUrl) {
                 window.open(downloadUrl, '_blank');
             } else {
                 alert('Document URL not available');
             }
         }
         
         // Load user documents for specific requirement (same logic as admin view)
         function loadUserDocumentsForRequirement(stepNumber, documentId) {
             console.log(`🔍 loadUserDocumentsForRequirement called for step ${stepNumber}, documentId ${documentId}`);
             
             const targetUserId = userId; // Current user
             console.log(`🔍 Loading documents for user: ${targetUserId}, step: ${stepNumber}`);
             
             // Check if container exists
             const containerId = `userDocuments_${stepNumber}_${documentId}`;
             const container = document.getElementById(containerId);
             console.log(`🔍 Container check for ${containerId}:`, container);
             
             if (!container) {
                 console.log(`❌ Container not found for ${containerId}`);
                 console.log(`❌ Available containers in DOM:`, Array.from(document.querySelectorAll('[id*="userDocuments"]')).map(el => el.id));
                 return;
             }
             
             console.log(`✅ Container found! Current content:`, container.innerHTML);
             
             // Use EXACT SAME function as the admin view
             return db.collection("adoption_progress").doc(targetUserId)
                .get()
                .then(adoptionDoc => {
                    const currentAdoptionNumber = adoptionDoc.exists && adoptionDoc.data().adoptions 
                        ? (adoptionDoc.data().currentAdoption || 1) 
                        : 1;

                    console.log(`Current adoption number for document filtering: ${currentAdoptionNumber}`);

                    // REQUIREMENT-SPECIFIC: Check user_submissions_status for this specific document ID
                    console.log(`🔍 REQUIREMENT-SPECIFIC: Looking for documents for ${documentId} in user_submissions_status`);
                    const submissionStatusRef = db.collection("user_submissions_status")
                         .doc(targetUserId)
                         .collection(`step${stepNumber}_documents`)
                         .doc(documentId);

                    return submissionStatusRef.get().then(docSnapshot => {
                        console.log(`📄 Document ${documentId} exists in submissions: ${docSnapshot.exists}`);
                        
                        let documents = [];
                        
                        if (docSnapshot.exists) {
                            const data = docSnapshot.data();
                            console.log(`📄 Document data for ${documentId}:`, data);
                            
                            // Check if this document has a file
                            if (data.fileName && (data.documentUrl || data.fileUrl)) {
                                documents.push({
                                    id: docSnapshot.id,
                                    fileName: data.fileName,
                                    fileUrl: data.documentUrl || data.fileUrl,
                                    timestamp: data.uploadedAt || data.timestamp,
                                    adoptionNumber: data.adoptionNumber
                                });
                                console.log(`✅ Found document for ${documentId}: ${data.fileName}`);
                            } else {
                                console.log(`⚠️ Document ${documentId} exists but has no file attached`);
                            }
                        } else {
                            console.log(`❌ No document found for ${documentId} in user_submissions_status`);
                        }

                        console.log(`📋 REQUIREMENT-SPECIFIC: Displaying ${documents.length} documents for ${documentId}:`, documents);
                    
                        // Update UI to show documents using EXACT SAME function as admin view
                        updateUserDocumentsDisplay(stepNumber, documentId, documents);
                    });
                })
                .catch(error => {
                    console.error(`Error fetching documents for step ${stepNumber}:`, error);
                    updateUserDocumentsDisplay(stepNumber, documentId, []);
                    return Promise.resolve();
                });
         }
         
         // User document display for requirements
         function updateUserDocumentsDisplay(stepNumber, documentId, documents) {
             const documentsContainer = document.getElementById(`userDocuments_${stepNumber}_${documentId}`);
             if (!documentsContainer) {
                 console.log(`Documents container not found for userDocuments_${stepNumber}_${documentId}`);
                 return;
             }

            documentsContainer.innerHTML = ''; // Clear existing content

            if (!documents || documents.length === 0) {
                documentsContainer.innerHTML = '<div style="color: #999; font-style: italic; font-size: 13px;">📄 No documents uploaded for this requirement yet.</div>';
                return;
            }

            console.log(`Displaying ${documents.length} documents for step ${stepNumber}, requirement ${documentId}`);

            // Create document list
            documents.forEach((doc, index) => {
                const docDiv = document.createElement('div');
                docDiv.style.cssText = `
                    display: flex; 
                    align-items: center; 
                    justify-content: space-between; 
                    padding: 8px 12px; 
                    margin: 5px 0; 
                    background: white; 
                    border: 1px solid #ddd; 
                    border-radius: 4px;
                    border-left: 3px solid #4CAF50;
                `;
                
                // Document info section
                const docInfo = document.createElement('div');
                docInfo.style.cssText = 'flex: 1;';
                
                const docName = document.createElement('div');
                docName.style.cssText = 'font-weight: 500; color: #333; font-size: 14px; margin-bottom: 2px;';
                docName.textContent = doc.fileName || `Document ${index + 1}`;
                
                const docDate = document.createElement('div');
                docDate.style.cssText = 'font-size: 12px; color: #666;';
                const uploadDate = doc.timestamp ? new Date(doc.timestamp) : null;
                docDate.textContent = uploadDate ? 
                    `Uploaded: ${uploadDate.toLocaleDateString()}` : 
                    'Upload date unknown';
                
                docInfo.appendChild(docName);
                docInfo.appendChild(docDate);
                
                // View button
                const viewBtn = document.createElement('button');
                viewBtn.style.cssText = `
                    background: #4CAF50; 
                    color: white; 
                    border: none; 
                    padding: 6px 12px; 
                    border-radius: 4px; 
                    cursor: pointer; 
                    font-size: 12px;
                    transition: background 0.3s;
                `;
                viewBtn.textContent = '👁️ View';
                viewBtn.onmouseover = () => viewBtn.style.background = '#45a049';
                viewBtn.onmouseout = () => viewBtn.style.background = '#4CAF50';
                viewBtn.onclick = () => viewDocument(doc.fileUrl);
                
                docDiv.appendChild(docInfo);
                docDiv.appendChild(viewBtn);
                
                documentsContainer.appendChild(docDiv);
            });
         }

         // Test function to manually load documents (call from browser console)
         function testLoadDocuments(stepNumber = 3) {
             console.log(`🧪 MANUAL TEST: Loading documents for step ${stepNumber}`);
             
             const stepDef = stepDefinitions.find(step => step.number === stepNumber);
             if (!stepDef) {
                 console.log(`❌ Step definition not found for step ${stepNumber}`);
                 return;
             }
             
             stepDef.requirements.forEach((requirement, index) => {
                 const documentId = requirement.documentId;
                 console.log(`🧪 Manually loading documents for ${documentId}`);
                 loadUserDocumentsForRequirement(stepNumber, documentId);
             });
         }

         // Debug function to check document containers (call from browser console)
         function debugDocumentContainers(stepNumber = 3) {
             console.log(`=== DEBUGGING DOCUMENT CONTAINERS FOR STEP ${stepNumber} ===`);
             
             const stepDef = stepDefinitions.find(step => step.number === stepNumber);
             if (!stepDef) {
                 console.log(`❌ Step definition not found for step ${stepNumber}`);
                 return;
             }
             
             console.log(`📋 Step ${stepNumber} has ${stepDef.requirements.length} requirements:`);
             
             stepDef.requirements.forEach((requirement, index) => {
                 const documentId = requirement.documentId;
                 const documentNumber = index + 1;
                 const containerId = `userDocuments_${stepNumber}_${documentId}`;
                 const container = document.getElementById(containerId);
                 
                 console.log(`${documentNumber}. ${requirement.title}`);
                 console.log(`   Document ID: ${documentId}`);
                 console.log(`   Container ID: ${containerId}`);
                 console.log(`   Container exists: ${!!container}`);
                 if (container) {
                     console.log(`   Container HTML:`, container.innerHTML);
                 }
                 console.log(`---`);
             });
             
             console.log(`=== DEBUG COMPLETE ===`);
         }

         // TASK 1: Load Admin Documents for Specific Requirement
         function loadAdminDocumentsForRequirement(stepNumber, documentId, targetUserId, container) {
             console.log(`🔍 ADMIN: Loading documents for step ${stepNumber}, documentId ${documentId}, user ${targetUserId}`);
             
             if (!container) {
                 console.log(`❌ ADMIN: Container not provided for ${documentId}`);
                 return;
             }
             
             console.log(`✅ ADMIN: Container found! Loading documents...`);
             
             // Use EXACT SAME logic as user view but for admin containers
             return db.collection("adoption_progress").doc(targetUserId)
                .get()
                .then(adoptionDoc => {
                    const currentAdoptionNumber = adoptionDoc.exists && adoptionDoc.data().adoptions 
                        ? (adoptionDoc.data().currentAdoption || 1) 
                        : 1;

                    console.log(`ADMIN: Current adoption number for document filtering: ${currentAdoptionNumber}`);

                    // REQUIREMENT-SPECIFIC: Check user_submissions_status for this specific document ID
                    console.log(`🔍 ADMIN: Looking for documents for ${documentId} in user_submissions_status`);
                    const submissionStatusRef = db.collection("user_submissions_status")
                         .doc(targetUserId)
                         .collection(`step${stepNumber}_documents`)
                         .doc(documentId);

                    return submissionStatusRef.get().then(docSnapshot => {
                        console.log(`📄 ADMIN: Document ${documentId} exists in submissions: ${docSnapshot.exists}`);
                        
                        let documents = [];
                        
                        if (docSnapshot.exists) {
                            const data = docSnapshot.data();
                            console.log(`📄 ADMIN: Document data for ${documentId}:`, data);
                            
                            // Check if this document has a file
                            if (data.fileName && (data.documentUrl || data.fileUrl)) {
                                documents.push({
                                    id: docSnapshot.id,
                                    fileName: data.fileName,
                                    fileUrl: data.documentUrl || data.fileUrl,
                                    timestamp: data.uploadedAt || data.timestamp,
                                    adoptionNumber: data.adoptionNumber
                                });
                                console.log(`✅ ADMIN: Found document for ${documentId}: ${data.fileName}`);
                            } else {
                                console.log(`⚠️ ADMIN: Document ${documentId} exists but has no file attached`);
                            }
                        } else {
                            console.log(`❌ ADMIN: No document found for ${documentId} in user_submissions_status`);
                        }

                        console.log(`📋 ADMIN: Displaying ${documents.length} documents for ${documentId}:`, documents);
                    
                        // Update UI to show documents in admin view
                        updateAdminDocumentsDisplay(container, documents);
                    });
                })
                .catch(error => {
                    console.error(`ADMIN: Error fetching documents for step ${stepNumber}:`, error);
                    updateAdminDocumentsDisplay(container, []);
                    return Promise.resolve();
                });
         }
         
         // TASK 1: Update Admin Documents Display
         function updateAdminDocumentsDisplay(container, documents) {
             container.innerHTML = ''; // Clear existing content

             if (!documents || documents.length === 0) {
                 container.innerHTML = '<div style="color: #999; font-style: italic; font-size: 13px;">📄 No documents uploaded for this requirement yet.</div>';
                 return;
             }

             console.log(`ADMIN: Displaying ${documents.length} documents`);

             // Create document list with admin styling
             documents.forEach((doc, index) => {
                 const docDiv = document.createElement('div');
                 docDiv.style.cssText = `
                     display: flex; 
                     align-items: center; 
                     justify-content: space-between; 
                     padding: 8px 12px; 
                     margin: 5px 0; 
                     background: #f8f9fa; 
                     border: 1px solid #dee2e6; 
                     border-radius: 4px;
                     border-left: 3px solid #17a2b8;
                 `;
                 
                 // Document info section
                 const docInfo = document.createElement('div');
                 docInfo.style.cssText = 'flex: 1;';
                 
                 const docName = document.createElement('div');
                 docName.style.cssText = 'font-weight: 500; color: #333; font-size: 13px; margin-bottom: 2px;';
                 docName.textContent = doc.fileName || `Document ${index + 1}`;
                 
                 const docDate = document.createElement('div');
                 docDate.style.cssText = 'font-size: 11px; color: #666;';
                 const uploadDate = doc.timestamp ? new Date(doc.timestamp) : null;
                 docDate.textContent = uploadDate ? 
                     `Uploaded: ${uploadDate.toLocaleDateString()}` : 
                     'Upload date unknown';
                 
                 docInfo.appendChild(docName);
                 docInfo.appendChild(docDate);
                 
                 // View button with admin styling
                 const viewBtn = document.createElement('button');
                 viewBtn.style.cssText = `
                     background: #17a2b8; 
                     color: white; 
                     border: none; 
                     padding: 4px 8px; 
                     border-radius: 3px; 
                     cursor: pointer; 
                     font-size: 11px;
                     transition: background 0.3s;
                 `;
                 viewBtn.textContent = '👁️ View';
                 viewBtn.onmouseover = () => viewBtn.style.background = '#138496';
                 viewBtn.onmouseout = () => viewBtn.style.background = '#17a2b8';
                 viewBtn.onclick = () => viewDocument(doc.fileUrl);
                 
                 docDiv.appendChild(docInfo);
                 docDiv.appendChild(viewBtn);
                 
                 container.appendChild(docDiv);
             });
         }
         
         // Debug function to check Firebase collections (call from browser console)
         function debugDocuments(userId = null, stepNumber = null) {
             const targetUserId = userId || (currentSelectedUser ? currentSelectedUser.uid : null);
             const targetStep = stepNumber || 4; // Default to step 4
             
             if (!targetUserId) {
                 console.log('No user ID provided. Usage: debugDocuments("userId", stepNumber)');
                 return;
             }
             
             console.log(`=== DEBUGGING DOCUMENTS FOR USER: ${targetUserId}, STEP: ${targetStep} ===`);
             
             // Check adoption_progress collection
             db.collection('adoption_progress').doc(targetUserId).get()
                 .then(doc => {
                     console.log('=== ADOPTION_PROGRESS MAIN DOCUMENT ===');
                     if (doc.exists) {
                         console.log('Main document exists:', doc.data());
                     } else {
                         console.log('Main document does not exist');
                     }
                     
                     // Check step uploads subcollection
                     return db.collection('adoption_progress').doc(targetUserId)
                         .collection(`step${targetStep}_uploads`).get();
                 })
                 .then(snapshot => {
                     console.log(`=== ADOPTION_PROGRESS/step${targetStep}_uploads ===`);
                     console.log(`Found ${snapshot.size} documents`);
                     snapshot.forEach(doc => {
                         console.log(`Document ID: ${doc.id}`, doc.data());
                     });
                     
                     // Check user_submissions_status collection
                     return db.collection('user_submissions_status').doc(targetUserId).get();
                 })
                 .then(doc => {
                     console.log('=== USER_SUBMISSIONS_STATUS MAIN DOCUMENT ===');
                     if (doc.exists) {
                         console.log('Main document exists:', doc.data());
                     } else {
                         console.log('Main document does not exist');
                     }
                     
                     // Check step documents subcollection
                     return db.collection('user_submissions_status').doc(targetUserId)
                         .collection(`step${targetStep}_documents`).get();
                 })
                 .then(snapshot => {
                     console.log(`=== USER_SUBMISSIONS_STATUS/step${targetStep}_documents ===`);
                     console.log(`Found ${snapshot.size} documents`);
                     snapshot.forEach(doc => {
                         console.log(`Document ID: ${doc.id}`, doc.data());
                     });
                     
                     console.log('=== DEBUG COMPLETE ===');
                 })
                 .catch(error => {
                     console.error('Debug error:', error);
                 });
         }
         
                 // Make debug function globally available
        window.debugDocuments = debugDocuments;

        // REMOVED OLD BROKEN FUNCTION - Using the working one at line 7924

        function updateAdminStepStatus(stepNumber, newStatus) {
            // Update status badge in header
            const statusBadge = document.getElementById(`status${stepNumber}`);
            if (statusBadge) {
                statusBadge.className = `admin-step-status ${getStatusClass(newStatus)}`;
                statusBadge.textContent = getStatusText(newStatus);
            }
            
            // Update status text in content
            const statusText = document.getElementById(`stepStatusText${stepNumber}`);
            if (statusText) {
                switch (newStatus) {
                    case 'complete':
                        statusText.textContent = 'Marked Complete';
                        statusText.style.color = '#28a745';
                        break;
                    case 'in_progress':
                        statusText.textContent = 'In Progress';
                        statusText.style.color = '#6ea4ce';
                        break;
                    default:
                        statusText.textContent = 'Locked';
                        statusText.style.color = '#6c757d';
                }
            }
            
            // Update admin controls (hide/show buttons based on new status)
            const adminControls = document.querySelector(`#adminContent${stepNumber} .admin-controls`);
            if (adminControls) {
                const buttonsDiv = adminControls.querySelector('div[style*="display: flex"]');
                if (buttonsDiv) {
                    buttonsDiv.innerHTML = '';
                    
                    // Recreate buttons based on new status
                    if (newStatus !== 'complete') {
                        const markCompleteBtn = document.createElement('button');
                        markCompleteBtn.className = 'admin-btn btn-mark-complete';
                        markCompleteBtn.textContent = '✓ Mark Complete';
                        markCompleteBtn.style.cssText = 'background: #28a745; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
                        markCompleteBtn.onclick = () => markStepComplete(stepNumber);
                        buttonsDiv.appendChild(markCompleteBtn);
                    }
                    
                    if ((stepNumber === 9 || stepNumber === 10) && newStatus === 'locked') {
                        const unlockBtn = document.createElement('button');
                        unlockBtn.className = 'admin-btn btn-unlock-step';
                        unlockBtn.textContent = `🔓 Unlock Step ${stepNumber}`;
                        unlockBtn.style.cssText = 'background: #ffc107; color: #212529; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; margin-right: 10px;';
                        unlockBtn.onclick = () => unlockSpecialStep(stepNumber);
                        buttonsDiv.appendChild(unlockBtn);
                    }
                    
                    if (newStatus === 'in_progress') {
                        const markProgressBtn = document.createElement('button');
                        markProgressBtn.className = 'admin-btn btn-mark-progress';
                        markProgressBtn.textContent = '🔄 Set In Progress';
                        markProgressBtn.style.cssText = 'background: #6ea4ce; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;';
                        markProgressBtn.onclick = () => markStepInProgress(stepNumber);
                        buttonsDiv.appendChild(markProgressBtn);
                    }
                }
            }
        }

        function getStatusClass(status) {
            switch (status) {
                case 'complete': return 'status-complete';
                case 'in_progress': return 'status-in-progress';
                default: return 'status-locked';
            }
        }

        function getStatusText(status) {
            switch (status) {
                case 'complete': return 'Marked Complete';
                case 'in_progress': return 'In Progress';
                default: return 'Locked';
            }
        }

        function markStepComplete(stepNumber) {
            // Use appropriate user ID - admin viewing another user or current user
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : currentUserId;
            
            if (!targetUserId) {
                alert('User ID not found.');
                return;
            }

            const userProgressRef = db.collection('adoption_progress').doc(targetUserId);

            userProgressRef.get().then(documentSnapshot => {
                if (!documentSnapshot.exists) {
                    showMessage('User progress not found.', 'error');
                    return;
                }

                const data = documentSnapshot.data();
                const adoptionsMap = data.adoptions;

                if (adoptionsMap) {
                    // Versioned structure
                    const currentAdoptionNumber = data.currentAdoption;
                    const currentAdoptionId = currentAdoptionNumber?.toString();
                    if (currentAdoptionId && adoptionsMap[currentAdoptionId]) {
                        const currentAdoption = adoptionsMap[currentAdoptionId];
                        const adoptProgressMap = currentAdoption.adopt_progress || {};

                        adoptProgressMap[`step${stepNumber}`] = 'complete';

                        // Set next step to in_progress if it exists and is locked
                        // EXCEPT for steps 9, 10, and 11 which must be manually opened by admin
                        const nextStepNumber = stepNumber + 1;
                        let nextStepUnlocked = false;
                        if (nextStepNumber <= 8) {
                            const nextStepKey = `step${nextStepNumber}`;
                            if (!adoptProgressMap[nextStepKey] || adoptProgressMap[nextStepKey] === 'locked') {
                                adoptProgressMap[nextStepKey] = 'in_progress';
                                console.log(`Next step (${nextStepKey}) unlocked and set to 'in_progress'`);
                                nextStepUnlocked = true;
                            }
                        } else if (nextStepNumber >= 9) {
                            console.log(`Step ${nextStepNumber} must be manually opened by admin - not auto-unlocking`);
                            // Explicitly ensure steps 9, 10, and 11 remain locked
                            if (adoptProgressMap[`step${nextStepNumber}`] !== 'complete' && adoptProgressMap[`step${nextStepNumber}`] !== 'in_progress') {
                                adoptProgressMap[`step${nextStepNumber}`] = 'locked';
                            }
                        }

                        // Ensure steps 9, 10, and 11 are always locked unless explicitly set by admin
                        if (adoptProgressMap['step9'] !== 'complete' && adoptProgressMap['step9'] !== 'in_progress') {
                            adoptProgressMap['step9'] = 'locked';
                        }
                        if (adoptProgressMap['step10'] !== 'complete' && adoptProgressMap['step10'] !== 'in_progress') {
                            adoptProgressMap['step10'] = 'locked';
                        }
                        if (adoptProgressMap['step11'] !== 'complete' && adoptProgressMap['step11'] !== 'in_progress') {
                            adoptProgressMap['step11'] = 'locked';
                        }

                        // Ensure all other subsequent steps remain locked until their turn
                        for (let i = nextStepNumber + 1; i <= 11; i++) {
                            const futureStepKey = `step${i}`;
                            if (!adoptProgressMap[futureStepKey]) {
                                adoptProgressMap[futureStepKey] = 'locked';
                            }
                        }

                        currentAdoption.adopt_progress = adoptProgressMap;
                        adoptionsMap[currentAdoptionId] = currentAdoption;

                        userProgressRef.update({ adoptions: adoptionsMap })
                            .then(() => {
                                console.log(`Step ${stepNumber} marked as complete.`);
                                
                                // Send notification about step completion with proper user info
                                // Get target user's information from Firebase users collection
                                db.collection('users').doc(targetUserId).get()
                                    .then(userDoc => {
                                        let userName = 'User';
                                        let userEmail = '';
                                        
                                        if (userDoc.exists) {
                                            const userData = userDoc.data();
                                            userName = userData.displayName || userData.name || userData.username || 'User';
                                            userEmail = userData.email || '';
                                            console.log('✅ Got target user info for step completion notification:', userName, userEmail);
                                        } else {
                                            // Fallback: try to get from currentSelectedUser if admin is viewing
                                            if (isAdminUser && currentSelectedUser) {
                                                userName = currentSelectedUser.displayName || currentSelectedUser.email?.split('@')[0] || 'User';
                                                userEmail = currentSelectedUser.email || '';
                                                console.log('⚠️ Using currentSelectedUser as fallback for step completion:', userName, userEmail);
                                            } else {
                                                console.log('⚠️ No user data found for step completion, using defaults');
                                            }
                                        }
                                        
                                        // Send notification with correct user info
                                sendAdoptionNotification('step_completed', stepNumber, {
                                    userId: targetUserId,
                                    stepNumber: stepNumber,
                                            status: 'complete',
                                            userName: userName,
                                            userEmail: userEmail
                                        });
                                        
                                        // Send adoption message via messaging system
                                const stepNames = {
                                    1: 'Initial Application',
                                    2: 'Home Study',
                                    3: 'Background Check',
                                    4: 'Training Program',
                                    5: 'Financial Assessment',
                                    6: 'Ethical Preferences',
                                    7: 'Matching Process',
                                    8: 'Legal Documentation',
                                    9: 'Meeting & Bonding',
                                    10: 'Final Approval',
                                    11: 'Post-Adoption Monitoring'
                                };
                                
                                // Send adoption message via Firebase bridge
                                if (window.firebaseMessagingBridge) {
                                    window.firebaseMessagingBridge.sendAdoptionNotification(
                                        targetUserId,
                                        stepNumber,
                                        stepNames[stepNumber] || `Step ${stepNumber}`,
                                        'completed'
                                    ).then(() => {
                                        console.log('✅ Adoption step completion message sent via Firebase bridge');
                                        }).catch(error => {
                                        console.error('❌ Error sending adoption message via Firebase bridge:', error);
                                        });
                                } else {
                                    console.error('❌ Firebase messaging bridge not available');
                                }
                                    })
                                    .catch(error => {
                                        console.log('❌ Error getting user info for step completion notification:', error);
                                        // Fallback: send notification without user info
                                        sendAdoptionNotification('step_completed', stepNumber, {
                                            userId: targetUserId,
                                            stepNumber: stepNumber,
                                            status: 'complete',
                                            userName: 'User',
                                            userEmail: ''
                                        });
                                });
                                
                                // Send notification that next step is now available (if unlocked)
                                if (nextStepUnlocked) {
                                    setTimeout(() => {
                                        sendAdoptionNotification('step_started', nextStepNumber, {
                                            userId: targetUserId,
                                            stepNumber: nextStepNumber,
                                            status: 'in_progress',
                                            userName: window.sessionUserEmail || username || 'User',
                                            userEmail: window.sessionUserEmail || ''
                                        });
                                    }, 1000);
                                }
                                

                                
                                // Update the UI immediately
                                updateMobileStepStatus(stepNumber, 'complete');
                                
                                // Check if all 11 steps are complete to show completion message
                                let completedSteps = 0;
                                for (let i = 1; i <= 11; i++) {
                                    if (adoptProgressMap[`step${i}`] === 'complete') {
                                        completedSteps++;
                                    }
                                }
                                
                                // Special congratulatory message for Step 11 completion or all steps complete
                                if (completedSteps === 11) {
                                    alert(`🎊 CONGRATULATIONS! 🎊\n\nYou have just completed the ENTIRE 11-step adoption process for this user!\n\nStep ${stepNumber} has been marked as complete.\n\nAll 11 steps are now complete! The user can view their completed adoption in their history.`);
                                } else if (stepNumber === 11) {
                                    alert(`🎊 FINAL STEP COMPLETED! 🎊\n\nStep ${stepNumber} (Post-Adoption Monitoring) has been marked as complete.\n\nThis completes the adoption process! (${completedSteps}/11 steps completed)`);
                                } else {
                                    alert(`Step ${stepNumber} marked as complete! (${completedSteps}/11 steps completed)`);
                                }
                            })
                            .catch(error => {
                                console.error('Error marking step complete:', error);
                                alert('Error marking step complete: ' + error.message);
                            });
                    }
                } else {
                    // Legacy structure
                    const adoptProgressMap = data.adopt_progress || {};
                    adoptProgressMap[`step${stepNumber}`] = 'complete';

                                            // Set next step to in_progress if it exists and is locked
                        // EXCEPT for steps 9, 10, and 11 which must be manually opened by admin
                        const nextStepNumber = stepNumber + 1;
                        let nextStepUnlockedLegacy = false;
                        if (nextStepNumber <= 8) {
                            const nextStepKey = `step${nextStepNumber}`;
                            if (!adoptProgressMap[nextStepKey] || adoptProgressMap[nextStepKey] === 'locked') {
                                adoptProgressMap[nextStepKey] = 'in_progress';
                                console.log(`Next step (${nextStepKey}) unlocked and set to 'in_progress'`);
                                nextStepUnlockedLegacy = true;
                            }
                        } else if (nextStepNumber >= 9) {
                            console.log(`Step ${nextStepNumber} must be manually opened by admin - not auto-unlocking`);
                            // Explicitly ensure steps 9, 10, and 11 remain locked
                            if (adoptProgressMap[`step${nextStepNumber}`] !== 'complete' && adoptProgressMap[`step${nextStepNumber}`] !== 'in_progress') {
                                adoptProgressMap[`step${nextStepNumber}`] = 'locked';
                            }
                        }

                        // Ensure steps 9, 10, and 11 are always locked unless explicitly set by admin
                        if (adoptProgressMap['step9'] !== 'complete' && adoptProgressMap['step9'] !== 'in_progress') {
                            adoptProgressMap['step9'] = 'locked';
                        }
                        if (adoptProgressMap['step10'] !== 'complete' && adoptProgressMap['step10'] !== 'in_progress') {
                            adoptProgressMap['step10'] = 'locked';
                        }
                        if (adoptProgressMap['step11'] !== 'complete' && adoptProgressMap['step11'] !== 'in_progress') {
                            adoptProgressMap['step11'] = 'locked';
                        }

                        // Ensure all other subsequent steps remain locked until their turn
                        for (let i = nextStepNumber + 1; i <= 11; i++) {
                            const futureStepKey = `step${i}`;
                            if (!adoptProgressMap[futureStepKey]) {
                                adoptProgressMap[futureStepKey] = 'locked';
                            }
                        }

                    userProgressRef.update({ adopt_progress: adoptProgressMap })
                        .then(() => {
                            console.log(`Step ${stepNumber} marked as complete.`);
                            
                            // Send notification about step completion with proper user info
                            // Get target user's information from Firebase users collection
                            db.collection('users').doc(targetUserId).get()
                                .then(userDoc => {
                                    let userName = 'User';
                                    let userEmail = '';
                                    
                                    if (userDoc.exists) {
                                        const userData = userDoc.data();
                                        userName = userData.displayName || userData.name || userData.username || 'User';
                                        userEmail = userData.email || '';
                                        console.log('✅ Got target user info for step completion notification:', userName, userEmail);
                                    } else {
                                        // Fallback: try to get from currentSelectedUser if admin is viewing
                                        if (isAdminUser && currentSelectedUser) {
                                            userName = currentSelectedUser.displayName || currentSelectedUser.email?.split('@')[0] || 'User';
                                            userEmail = currentSelectedUser.email || '';
                                            console.log('⚠️ Using currentSelectedUser as fallback for step completion:', userName, userEmail);
                                        } else {
                                            console.log('⚠️ No user data found for step completion, using defaults');
                                        }
                                    }
                                    
                                    // Send notification with correct user info
                            sendAdoptionNotification('step_completed', stepNumber, {
                                userId: targetUserId,
                                stepNumber: stepNumber,
                                        status: 'complete',
                                        userName: userName,
                                        userEmail: userEmail
                                    });
                                    
                                    // Send adoption message via messaging system
                                    const stepNames = {
                                        1: 'Initial Application',
                                        2: 'Home Study',
                                        3: 'Background Check',
                                        4: 'Training Program',
                                        5: 'Financial Assessment',
                                        6: 'Ethical Preferences',
                                        7: 'Matching Process',
                                        8: 'Legal Documentation',
                                        9: 'Meeting & Bonding',
                                        10: 'Final Approval',
                                        11: 'Post-Adoption Monitoring'
                                    };
                                    
                                    // Send adoption message via Firebase bridge
                                    if (window.firebaseMessagingBridge) {
                                        window.firebaseMessagingBridge.sendAdoptionNotification(
                                            targetUserId,
                                            stepNumber,
                                            stepNames[stepNumber] || `Step ${stepNumber}`,
                                            'completed'
                                        ).then(() => {
                                            console.log('✅ Adoption step completion message sent via Firebase bridge');
                                    }).catch(error => {
                                            console.error('❌ Error sending adoption message via Firebase bridge:', error);
                                    });
                                    } else {
                                        console.error('❌ Firebase messaging bridge not available');
                                    }
                                })
                                .catch(error => {
                                    console.log('❌ Error getting user info for step completion notification:', error);
                                    // Fallback: send notification without user info
                                    sendAdoptionNotification('step_completed', stepNumber, {
                                        userId: targetUserId,
                                        stepNumber: stepNumber,
                                        status: 'complete',
                                        userName: 'User',
                                        userEmail: ''
                                    });
                            });
                            
                            // Send notification that next step is now available (if unlocked)
                            if (nextStepUnlockedLegacy) {
                                setTimeout(() => {
                                    sendAdoptionNotification('step_started', nextStepNumber, {
                                        userId: targetUserId,
                                        stepNumber: nextStepNumber,
                                        status: 'in_progress'
                                    });
                                }, 1000);
                            }
                            

                            
                            // Update the UI immediately
                            updateMobileStepStatus(stepNumber, 'complete');
                            
                            // Special congratulatory message for Step 10 completion
                            if (stepNumber === 10) {
                                alert(`🎊 CONGRATULATIONS! 🎊\n\nYou have just completed the ENTIRE adoption process for this user!\n\nStep ${stepNumber} (Final Step) has been marked as complete.\n\nThe user will receive a special congratulatory notification and the system will prepare for their next adoption if needed.`);
                            } else {
                            alert(`Step ${stepNumber} marked as complete!`);
                            }
                        })
                        .catch(error => {
                            console.error('Error marking step complete:', error);
                            alert('Error marking step complete: ' + error.message);
                        });
                }
            }).catch(error => {
                console.error('Error getting user progress:', error);
                showMessage('Error accessing user progress: ' + error.message, 'error');
            });
        }

        function unlockSpecialStep(stepNumber) {
            // Special function to unlock steps 9 and 10 manually by admin
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : currentUserId;
            
            if (!targetUserId) {
                alert('User ID not found.');
                return;
            }
            
            if (!confirm(`Are you sure you want to unlock Step ${stepNumber}? This step requires special admin authorization.`)) {
                return;
            }

            const userProgressRef = db.collection('adoption_progress').doc(targetUserId);

            userProgressRef.get().then(documentSnapshot => {
                if (!documentSnapshot.exists) {
                    showMessage('User progress not found.', 'error');
                    return;
                }

                const data = documentSnapshot.data();
                const adoptionsMap = data.adoptions;

                if (adoptionsMap) {
                    // Versioned structure
                    const currentAdoptionNumber = data.currentAdoption;
                    const currentAdoptionId = currentAdoptionNumber?.toString();
                    if (currentAdoptionId && adoptionsMap[currentAdoptionId]) {
                        const currentAdoption = adoptionsMap[currentAdoptionId];
                        const adoptProgressMap = currentAdoption.adopt_progress || {};

                        adoptProgressMap[`step${stepNumber}`] = 'in_progress';

                        currentAdoption.adopt_progress = adoptProgressMap;
                        adoptionsMap[currentAdoptionId] = currentAdoption;

                        userProgressRef.update({ adoptions: adoptionsMap })
                            .then(() => {
                                console.log(`Step ${stepNumber} manually unlocked by admin.`);
                                
                                // Send notification about step unlock
                                sendAdoptionNotification('step_started', stepNumber, {
                                    userId: targetUserId,
                                    stepNumber: stepNumber,
                                    status: 'in_progress',
                                    unlocked_by_admin: true
                                });
                                
                                // Send admin notification about step unlock - GET TARGET USER INFO
                                const stepTitles = {
                                    9: "Processing",
                                    10: "Completion",
                                    11: "Post-Adoption Monitoring"
                                };
                                
                                // Get target user's information from Firebase users collection
                                db.collection('users').doc(targetUserId).get()
                                    .then(userDoc => {
                                        let userName = 'User';
                                        let userEmail = '';
                                        
                                        if (userDoc.exists) {
                                            const userData = userDoc.data();
                                            userName = userData.displayName || userData.name || userData.username || 'User';
                                            userEmail = userData.email || '';
                                            console.log('✅ Got target user info for step unlock:', userName, userEmail);
                                        } else {
                                            // Fallback: try to get from currentSelectedUser if admin is viewing
                                            if (isAdminUser && currentSelectedUser) {
                                                userName = currentSelectedUser.displayName || currentSelectedUser.email?.split('@')[0] || 'User';
                                                userEmail = currentSelectedUser.email || '';
                                                console.log('⚠️ Using currentSelectedUser as fallback for step unlock:', userName, userEmail);
                                            } else {
                                                console.log('⚠️ No user data found for step unlock, using defaults');
                                            }
                                        }
                                        
                                        // Send admin notification with target user's info
                                        return fetch('notification_handler.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        type: 'admin_step_unlock',
                                        data: {
                                            userName: userName,
                                            userEmail: userEmail,
                                            stepNumber: stepNumber,
                                            stepTitle: stepTitles[stepNumber] || `Step ${stepNumber}`,
                                            action: 'unlock'
                                        }
                                    })
                                                                                 });
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        console.log('✅ Admin step unlock notification sent successfully');
                                    } else {
                                        console.log('❌ Failed to send admin step unlock notification:', result.error);
                                    }
                                })
                                .catch(error => {
                                    console.log('❌ Admin step unlock notification error:', error);
                                });
                                
                                // Update the UI immediately
                                updateAdminStepStatus(stepNumber, 'in_progress');
                                
                                alert(`Step ${stepNumber} has been unlocked and is now available for the user!`);
                            })
                            .catch(error => {
                                console.error('Error unlocking step:', error);
                                alert('Error unlocking step: ' + error.message);
                            });
                    }
                } else {
                    // Legacy structure
                    const adoptProgressMap = data.adopt_progress || {};
                    adoptProgressMap[`step${stepNumber}`] = 'in_progress';

                    userProgressRef.update({ adopt_progress: adoptProgressMap })
                        .then(() => {
                            console.log(`Step ${stepNumber} manually unlocked by admin.`);
                            
                            // Send notification about step unlock
                            sendAdoptionNotification('step_started', stepNumber, {
                                userId: targetUserId,
                                stepNumber: stepNumber,
                                status: 'in_progress',
                                unlocked_by_admin: true
                            });
                            
                            // Update the UI immediately
                            updateAdminStepStatus(stepNumber, 'in_progress');
                            
                            alert(`Step ${stepNumber} has been unlocked and is now available for the user!`);
                        })
                        .catch(error => {
                            console.error('Error unlocking step:', error);
                            alert('Error unlocking step: ' + error.message);
                        });
                }
            }).catch(error => {
                console.error('Error getting user progress:', error);
                showMessage('Error accessing user progress: ' + error.message, 'error');
            });
        }



        function updateStepUI(stepNumber, newStatus) {
            // Update status badge
            const statusBadge = document.getElementById(`status${stepNumber}`);
            if (statusBadge) {
                statusBadge.className = `admin-step-status ${getStatusClass(newStatus)}`;
                statusBadge.textContent = getStatusText(newStatus);
            }

            // Update admin controls
            const adminControls = document.querySelector(`#adminStep${stepNumber} .admin-controls`);
            if (adminControls) {
                adminControls.innerHTML = '';
                const newControls = createAdminControls(stepNumber, newStatus);
                while (newControls.firstChild) {
                    adminControls.appendChild(newControls.firstChild);
                }
            }
        }

        function saveAdminComment(stepNumber) {
            // Use appropriate user ID - admin viewing another user or current user
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId) {
                showMessage('User ID not found.', 'error');
                return;
            }

            const commentInput = document.getElementById(`commentInput${stepNumber}`);
            const comment = commentInput.value.trim();

            if (!comment) {
                showMessage('Please enter a comment.', 'error');
                return;
            }

            const commentRef = db.collection('adoption_progress').doc(targetUserId)
                .collection('comments').doc(`step${stepNumber}`);

            commentRef.set({
                comment: comment,
                timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                adminId: currentUserEmail || userId
            }).then(() => {
                showMessage('Comment saved successfully.', 'success');
                commentInput.value = '';
                loadAdminComment(stepNumber);
                
                // Send notification to user about new admin comment
                sendAdoptionNotification('admin_comment_added', stepNumber, {
                    userId: targetUserId,
                    stepNumber: stepNumber,
                    comment: comment
                });
                
            }).catch(error => {
                console.error('Error saving comment:', error);
                showMessage('Error saving comment: ' + error.message, 'error');
            });
        }

        function loadAdminComment(stepNumber) {
            // Use appropriate user ID - admin viewing another user or current user
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId) {
                console.log('No target user ID for loading admin comment');
                return;
            }

            const commentRef = db.collection('adoption_progress').doc(targetUserId)
                .collection('comments').doc(`step${stepNumber}`);

            commentRef.get().then(doc => {
                const currentComment = document.getElementById(`currentComment${stepNumber}`);
                if (doc.exists && currentComment) {
                    const data = doc.data();
                    if (data.comment) {
                        currentComment.innerHTML = `<strong>Current Comment:</strong> ${escapeHtml(data.comment)}`;
                        currentComment.style.display = 'block';
                    } else {
                        currentComment.style.display = 'none';
                    }
                } else if (currentComment) {
                    currentComment.style.display = 'none';
                }
            }).catch(error => {
                console.error('Error loading comment:', error);
            });
        }

        function loadStepDocuments(stepNumber) {
            // Use appropriate user ID - admin viewing another user or current user
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            console.log(`🔍 COMPREHENSIVE DOCUMENT LOADING: Step ${stepNumber}, User: ${targetUserId}`);
            
            if (!targetUserId) {
                console.log('❌ No target user ID for loading step documents');
                return;
            }

            const documentsContainer = document.getElementById(`step${stepNumber}DocumentsContainer`);
            if (!documentsContainer) {
                console.log(`❌ Documents container not found for step ${stepNumber}`);
                return;
            }

            documentsContainer.innerHTML = '<div style="color: #666; font-style: italic;">Loading documents...</div>';

            // CHECK ALL POSSIBLE LOCATIONS for documents:
            const promises = [];
            let foundDocuments = [];

            // 1. Check adoption_progress/{userId}/step{X}_uploads
            console.log(`🔍 Checking: adoption_progress/${targetUserId}/step${stepNumber}_uploads`);
            const adoptionProgressRef = db.collection("adoption_progress")
                .doc(targetUserId)
                .collection(`step${stepNumber}_uploads`)
                .orderBy("timestamp", "desc");

            promises.push(
                adoptionProgressRef.get().then(snapshot => {
                    console.log(`📄 adoption_progress found: ${snapshot.size} documents`);
                    snapshot.forEach(doc => {
                        const data = doc.data();
                        if (data.fileName && data.fileUrl) {
                            foundDocuments.push({
                                source: 'adoption_progress',
                                fileName: data.fileName,
                                fileUrl: data.fileUrl,
                                timestamp: data.timestamp,
                                data: data
                            });
                        }
                    });
                }).catch(error => {
                    console.error('Error checking adoption_progress:', error);
                })
            );

            // 2. Check user_submissions_status/{userId}/step{X}_documents
            console.log(`🔍 Checking: user_submissions_status/${targetUserId}/step${stepNumber}_documents`);
            const submissionsRef = db.collection("user_submissions_status")
                .doc(targetUserId)
                .collection(`step${stepNumber}_documents`);

            promises.push(
                submissionsRef.get().then(snapshot => {
                    console.log(`📄 user_submissions_status found: ${snapshot.size} documents`);
                    snapshot.forEach(doc => {
                        const data = doc.data();
                        if (data.fileName && data.documentUrl) {
                            foundDocuments.push({
                                source: 'user_submissions_status',
                                fileName: data.fileName,
                                fileUrl: data.documentUrl,
                                timestamp: data.timestamp,
                                data: data
                            });
                        }
                    });
                }).catch(error => {
                    console.error('Error checking user_submissions_status:', error);
                })
            );

            // 3. Check user_documents/{userId}/step{X}_documents
            console.log(`🔍 Checking: user_documents/${targetUserId}/step${stepNumber}_documents`);
            const userDocsRef = db.collection("user_documents")
                .doc(targetUserId)
                .collection(`step${stepNumber}_documents`);

            promises.push(
                userDocsRef.get().then(snapshot => {
                    console.log(`📄 user_documents found: ${snapshot.size} documents`);
                    snapshot.forEach(doc => {
                        const data = doc.data();
                        if (data.fileName && data.documentUrl) {
                            foundDocuments.push({
                                source: 'user_documents',
                                fileName: data.fileName,
                                fileUrl: data.documentUrl,
                                timestamp: data.timestamp,
                                data: data
                            });
                        }
                    });
                }).catch(error => {
                    console.error('Error checking user_documents:', error);
                })
            );

            // 4. Check Firebase Storage directly
            if (storage) {
                console.log(`🔍 Checking Firebase Storage: user_documents/${targetUserId}/step${stepNumber}`);
                const storageRef = storage.ref(`user_documents/${targetUserId}/step${stepNumber}`);
                
                promises.push(
                    storageRef.listAll().then(result => {
                        console.log(`📦 Storage found: ${result.items.length} files`);
                        const storagePromises = result.items.map(itemRef => {
                            return itemRef.getDownloadURL().then(url => {
                                foundDocuments.push({
                                    source: 'firebase_storage',
                                    fileName: itemRef.name,
                                    fileUrl: url,
                                    timestamp: null,
                                    data: { fileName: itemRef.name, documentUrl: url }
                                });
                            }).catch(error => {
                                console.error(`Error getting download URL for ${itemRef.name}:`, error);
                            });
                        });
                        return Promise.all(storagePromises);
                    }).catch(error => {
                        console.error('Error checking Firebase Storage:', error);
                    })
                );
            }

            // Wait for all checks to complete
            Promise.all(promises).then(() => {
                console.log(`🎯 Total documents found across all sources: ${foundDocuments.length}`);
                
                // Group documents by requirement (documentId) instead of deduplicating by fileName
                const documentsByRequirement = new Map();

                foundDocuments.forEach(doc => {
                    // Extract documentId from various sources
                    let documentId = doc.data.documentId || doc.fileName.split('-')[1] || 'unknown';
                    
                    // Handle documents with timestamp suffix (new format)
                    if (documentId.includes('_') && /\d{13}$/.test(documentId)) {
                        documentId = documentId.replace(/_\d{13}$/, '');
                    }
                    
                    // For each requirement, keep only the latest upload (prefer adoption_progress source)
                    const existingDoc = documentsByRequirement.get(documentId);
                    if (!existingDoc || 
                        (doc.source === 'adoption_progress' && existingDoc.source !== 'adoption_progress') ||
                        (doc.timestamp > (existingDoc.timestamp || 0))) {
                        
                        // Add requirement info to the document
                        const enrichedDoc = {
                            ...doc,
                            requirementId: documentId,
                            requirementTitle: getRequirementTitle(stepNumber, documentId)
                        };
                        
                        documentsByRequirement.set(documentId, enrichedDoc);
                    }
                });

                const uniqueDocuments = Array.from(documentsByRequirement.values());

                console.log(`📋 Unique documents after deduplication: ${uniqueDocuments.length}`);
                
                documentsContainer.innerHTML = '';
                
                if (uniqueDocuments.length === 0) {
                    documentsContainer.innerHTML = '<div style="color: #888; font-size: 14px; padding: 8px 0;">No documents uploaded for this step.</div>';
                    return;
                }

                uniqueDocuments.forEach(doc => {
                    const fileEntry = document.createElement('div');
                    fileEntry.style.cssText = 'margin-bottom: 12px; padding: 8px; background: #f9f9f9; border-radius: 4px; border: 1px solid #ddd;';
                    
                    const uploadDate = doc.timestamp ? new Date(doc.timestamp).toLocaleDateString('en-US', {
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'N/A';
                    
                    // Extract requirement title from document ID if available
                    const requirementTitle = doc.requirementTitle || getRequirementTitle(stepNumber, doc.id);
                    
                    fileEntry.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: bold; margin-bottom: 2px; color: #2c5aa0; font-size: 13px;">📋 ${requirementTitle}</div>
                                <div style="font-weight: bold; margin-bottom: 4px;">📄 ${doc.fileName}</div>
                                <div style="color: #666; font-size: 12px;">Source: ${doc.source} | Uploaded: ${uploadDate}</div>
                            </div>
                            <button onclick="window.open('${doc.fileUrl}', '_blank')" style="
                                background: #4CAF50;
                                color: white;
                                border: none;
                                padding: 8px 12px;
                                border-radius: 4px;
                                cursor: pointer;
                                font-size: 14px;
                            ">View Document</button>
                        </div>
                    `;
                    
                    documentsContainer.appendChild(fileEntry);
                    console.log(`✅ Added document: ${doc.fileName} for requirement: ${requirementTitle} (from ${doc.source})`);
                });
                
                console.log(`✅ COMPREHENSIVE DOCUMENT LOADING COMPLETE for step ${stepNumber}`);
            }).catch(error => {
                console.error(`❌ Error during comprehensive document loading:`, error);
                documentsContainer.innerHTML = '<div style="color: #f44336; font-size: 14px; padding: 8px 0;">Error loading documents.</div>';
            });
        }

        function createDocumentItem(data) {
            const item = document.createElement('div');
            item.className = 'document-item';

            const info = document.createElement('div');
            info.className = 'document-info';

            const name = document.createElement('div');
            name.className = 'document-name';
            name.textContent = data.documentType || 'Document';

            const date = document.createElement('div');
            date.className = 'document-date';
            if (data.submittedAt) {
                const submittedDate = data.submittedAt.toDate();
                date.textContent = `Submitted: ${submittedDate.toLocaleDateString()} ${submittedDate.toLocaleTimeString()}`;
            }

            info.appendChild(name);
            info.appendChild(date);

            const actions = document.createElement('div');
            actions.className = 'document-actions';

            if (data.fileUrl) {
                const viewBtn = document.createElement('button');
                viewBtn.className = 'btn-view-doc';
                viewBtn.textContent = 'View Document';
                viewBtn.onclick = () => window.open(data.fileUrl, '_blank');
                actions.appendChild(viewBtn);
            }

            item.appendChild(info);
            item.appendChild(actions);

            return item;
        }

        function backToUsersList() {
            console.log('Returning to users list');
            document.getElementById('userProgressView').style.display = 'none';
            document.getElementById('usersContainer').style.display = 'block';
            currentSelectedUser = null;
        }

        function showAdminLoading(show) {
            const loadingEl = document.getElementById('adminLoadingMessage');
            if (loadingEl) {
                loadingEl.style.display = show ? 'block' : 'none';
            }
        }

        function showAdminError(message) {
            const errorEl = document.getElementById('adminErrorMessage');
            if (errorEl) {
                errorEl.innerHTML = `<div style="color: #dc3545; padding: 20px; text-align: center;">${escapeHtml(message)}</div>`;
                errorEl.style.display = 'block';
            }
        }

        function showNoUsersMessage() {
            const usersList = document.getElementById('usersList');
            if (usersList) {
                usersList.innerHTML = '<div class="no-users-message">No users found with adoption progress.</div>';
            }
            document.getElementById('usersContainer').style.display = 'block';
        }

        function showNoActiveProcessesMessage() {
            const usersList = document.getElementById('usersList');
            if (usersList) {
                usersList.innerHTML = '<div class="no-users-message">No active adoption processes found. Completed processes are available in history.</div>';
            }
            document.getElementById('usersContainer').style.display = 'block';
        }

        // Initialize Firebase and set up authentication
        function initializeFirebase() {
            console.log('=== INITIALIZING FIREBASE ===');
            console.log('Auth available:', !!auth);
            console.log('Database available:', !!db);
            console.log('User ID:', userId);
            console.log('User Email:', currentUserEmail);
            console.log('Firebase Token Valid:', firebaseTokenValid);
            
            // Set up authentication directly
            setupAuthentication();
        }

        function setupAuthentication() {
            if (auth && db) {
                console.log('=== SETTING UP FIREBASE AUTHENTICATION ===');
                
                // Set up auth state listener
                auth.onAuthStateChanged((user) => {
                    console.log('=== FIREBASE AUTH STATE CHANGE ===');
                    console.log('Firebase User:', user ? user.uid : 'None');
                    console.log('Firebase Email:', user ? user.email : 'None');
                    console.log('Expected User ID:', userId);
                    console.log('Expected Email:', currentUserEmail);
                    
                    if (user) {
                        // User is signed in - verify this matches our PHP session
                        if (user.uid === userId && user.email === currentUserEmail) {
                            console.log('✅ Firebase user matches PHP session perfectly');
                            currentUser = user;
                            checkProgress();
                        } else {
                            console.log('⚠️ Firebase user does not match PHP session');
                            console.log('Firebase UID:', user.uid, 'vs Session UID:', userId);
                            console.log('Firebase Email:', user.email, 'vs Session Email:', currentUserEmail);
                            
                            // Sign out the mismatched user and show auth error
                            auth.signOut().then(() => {
                                console.log('Signed out mismatched Firebase user');
                                showAuthenticationPrompt();
                            });
                        }
                    } else {
                        // No user is signed in - show authentication prompt
                        console.log('❌ No Firebase user authenticated');
                        currentUser = null;
                        showAuthenticationPrompt();
                    }
                });
            } else {
                console.log('❌ Firebase not available - using fallback');
                checkProgressWithFallback();
            }
        }

        function showAuthenticationPrompt() {
            // If we have a valid token in session, this suggests the user was recently authenticated
            // but Firebase auth state was lost (common in web environments)
            if (firebaseTokenValid) {
                showReauthenticationMessage();
            } else {
                showExpiredSessionMessage();
            }
        }

        function showReauthenticationMessage() {
            // Try both admin and regular error message elements
            const adminErrorElement = document.getElementById('adminErrorMessage');
            const regularErrorElement = document.getElementById('errorMessage');
            
            const messageHtml = `
                <div style="background: #e8f4fd; border: 1px solid #2196F3; border-radius: 10px; padding: 20px; margin: 20px 0;">
                    <h3 style="color: #1976D2; margin-top: 0;">🔄 Re-authentication Required</h3>
                    <p style="color: #1976D2;">Your Firebase session expired, but your login is still valid. Click below to refresh your authentication and view your current progress.</p>
                    <button onclick="redirectToSignIn()" style="background: #2196F3; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; margin-top: 10px; font-size: 16px;">
                        Refresh Authentication
                    </button>
                    <button onclick="useOfflineMode()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; margin-top: 10px; margin-left: 10px;">
                        View Offline Progress
                    </button>
                </div>
            `;
            
            if (adminErrorElement && (isAdminUser && currentSelectedUser)) {
                adminErrorElement.innerHTML = messageHtml;
                adminErrorElement.style.display = 'block';
            } else if (regularErrorElement) {
                regularErrorElement.innerHTML = messageHtml;
                regularErrorElement.style.display = 'block';
            } else {
                console.log('No error message element found for re-authentication message');
            }
        }

        function showExpiredSessionMessage() {
            // Try both admin and regular error message elements
            const adminErrorElement = document.getElementById('adminErrorMessage');
            const regularErrorElement = document.getElementById('errorMessage');
            
            const messageHtml = `
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 10px; padding: 20px; margin: 20px 0;">
                    <h3 style="color: #856404; margin-top: 0;">⚠️ Session Expired</h3>
                    <p style="color: #856404;">Your session has expired. Please sign in again to view your current adoption progress.</p>
                    <button onclick="redirectToSignIn()" style="background: #ffc107; color: #212529; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; margin-top: 10px; font-size: 16px;">
                        Sign In Again
                    </button>
                    <button onclick="useOfflineMode()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; margin-top: 10px; margin-left: 10px;">
                        View Offline Progress
                    </button>
                </div>
            `;
            
            if (adminErrorElement && (isAdminUser && currentSelectedUser)) {
                adminErrorElement.innerHTML = messageHtml;
                adminErrorElement.style.display = 'block';
            } else if (regularErrorElement) {
                regularErrorElement.innerHTML = messageHtml;
                regularErrorElement.style.display = 'block';
            } else {
                console.log('No error message element found for expired session message');
            }
        }

        function redirectToSignIn() {
            // Redirect to signin page with return URL
            const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
            window.location.href = `Signin.php?redirect=${returnUrl}`;
        }

        function checkProgress() {
            // Match mobile app authentication check
            if (!userId) {
                console.error('User is not authenticated. PHP session missing user ID.');
                showError('User is not authenticated. Please log in.');
                return;
            }

            if (!currentUser || !db) {
                console.log('No Firebase user or db, falling back to PHP session');
                checkProgressWithFallback();
                return;
            }

            console.log('Checking Firebase progress for user:', currentUser.uid);
            showLoading(true);
            
            setupAdoptionProgressListener(currentUser.uid, username);
        }

        function checkProgressWithFallback() {
            console.log('Using fallback progress check with PHP session data');
            showLoading(true);
            
            // Simulate progress data based on PHP session
            setTimeout(() => {
                showLoading(false);
                
                // Use the same default progress as mobile app
                const fallbackProgress = getDefaultProgressStatus();
                
                console.log('Using fallback progress:', fallbackProgress);
                updateProgressTracking(fallbackProgress);
            }, 1000);
        }

        // Store previous progress state for change detection
        let previousProgressStatus = {};
        let isInitialLoad = true;

        function setupAdoptionProgressListener(userId, username) {
            console.log('Setting up Firebase adoption progress listener for:', userId);
            
            try {
                // Remove existing listener
                if (progressListener) {
                    progressListener();
                }

                progressListener = db.collection('adoption_progress').doc(userId)
                    .onSnapshot(snapshot => {
                        console.log('Firebase snapshot received:', snapshot.exists);
                        showLoading(false);
                        
                        if (snapshot.exists) {
                            const data = snapshot.data();
                            console.log('=== DETAILED FIREBASE DATA DEBUG ===');
                            console.log('Raw Firebase data:', JSON.stringify(data, null, 2));
                            console.log('Data type:', typeof data);
                            console.log('Has adoptions:', 'adoptions' in data);
                            console.log('Adoptions type:', typeof data.adoptions);
                            console.log('Has adopt_progress:', 'adopt_progress' in data);
                            console.log('adopt_progress type:', typeof data.adopt_progress);
                            if (data.adoptions) {
                                console.log('Adoptions keys:', Object.keys(data.adoptions));
                                console.log('currentAdoption:', data.currentAdoption);
                                console.log('totalAdoptions:', data.totalAdoptions);
                            }
                            console.log('=====================================');
                            
                            // Extract current progress before processing
                            let currentProgress = {};
                            if (data.adoptions && typeof data.adoptions === 'object') {
                                const currentAdoptionNumber = data.currentAdoption || 1;
                                const currentAdoptionKey = currentAdoptionNumber.toString();
                                const currentAdoption = data.adoptions[currentAdoptionKey];
                                if (currentAdoption && currentAdoption.adopt_progress) {
                                    currentProgress = currentAdoption.adopt_progress;
                                }
                            } else if (data.adopt_progress && typeof data.adopt_progress === 'object') {
                                currentProgress = data.adopt_progress;
                            }
                            
                            // Detect mobile app changes (skip on initial load)
                            if (!isInitialLoad) {
                                detectMobileAppChanges(previousProgressStatus, currentProgress, userId);
                            }
                            
                            // Update previous status for next comparison
                            previousProgressStatus = { ...currentProgress };
                            isInitialLoad = false;
                            
                            // Check if this is the new versioned structure (matching mobile app exactly)
                            if (data.adoptions && typeof data.adoptions === 'object') {
                                console.log('✅ Using versioned structure');
                                handleVersionedStructure(data);
                            } else if (data.adopt_progress && typeof data.adopt_progress === 'object') {
                                // Old structure - check for adopt_progress
                                console.log('⚠️ Using old structure with progress data:', data.adopt_progress);
                                updateProgressTracking(data.adopt_progress);
                            } else {
                                console.log('❌ No valid progress data found, showing confirmation dialog');
                                showConfirmationDialog(1, username);
                            }
                        } else {
                            console.log('❌ No Firebase adoption progress document found, showing confirmation dialog');
                            showConfirmationDialog(1, username);
                        }
                    }, error => {
                        console.error('Firebase listen failed:', error);
                        showError('Firebase connection failed. Using fallback data.');
                        checkProgressWithFallback();
                    });
            } catch (error) {
                console.error('Failed to setup Firebase listener:', error);
                checkProgressWithFallback();
            }
        }

        function handleVersionedStructure(data) {
            // Match mobile app logic exactly
            const currentAdoptionNumber = data.currentAdoption || 1;
            const totalAdoptions = data.totalAdoptions || 1;
            const adoptions = data.adoptions || {};
            
            console.log('=== HANDLING VERSIONED STRUCTURE ===');
            console.log('Current adoption number:', currentAdoptionNumber);
            console.log('Total adoptions:', totalAdoptions);
            console.log('Available adoptions:', Object.keys(adoptions));
            console.log('Full adoptions object:', JSON.stringify(adoptions, null, 2));
            
            const currentAdoptionKey = currentAdoptionNumber.toString();
            const currentAdoption = adoptions[currentAdoptionKey];
            
            console.log('Looking for adoption key:', currentAdoptionKey);
            console.log('Found current adoption:', currentAdoption ? 'YES' : 'NO');
            
            if (currentAdoption && typeof currentAdoption === 'object') {
                const status = currentAdoption.status || 'in_progress';
                const progressData = currentAdoption.adopt_progress || {};
                
                console.log('=== CURRENT ADOPTION DETAILS ===');
                console.log('Adoption number:', currentAdoptionNumber);
                console.log('Status:', status);
                console.log('Progress data type:', typeof progressData);
                console.log('Progress data keys:', Object.keys(progressData));
                console.log('Full progress data:', JSON.stringify(progressData, null, 2));
                console.log('Has valid progress data:', Object.keys(progressData).length > 0);
                console.log('================================');
                
                if (status === 'in_progress' && typeof progressData === 'object') {
                    // Show current adoption progress (matching mobile app)
                    console.log('✅ Displaying in-progress adoption with progress data');
                    updateProgressTracking(progressData);
                    updateAdoptionTitle(currentAdoptionNumber, totalAdoptions);
                } else {
                    // Current adoption is completed, should not happen but handle gracefully (mobile app comment)
                    console.warn(`⚠️ Current adoption #${currentAdoptionNumber} is marked as completed but still current`);
                    console.log('Using fallback progress or provided data');
                    updateProgressTracking(progressData || getDefaultProgressStatus());
                    updateAdoptionTitle(currentAdoptionNumber, totalAdoptions);
                }
            } else {
                console.error(`❌ Current adoption #${currentAdoptionNumber} not found in adoptions map`);
                console.log('Available adoptions:', Object.keys(adoptions));
                console.log('Showing confirmation dialog for adoption:', currentAdoptionNumber);
                showConfirmationDialog(currentAdoptionNumber, username);
            }
            console.log('=== END VERSIONED STRUCTURE HANDLING ===');
        }

        function updateAdoptionTitle(currentAdoption, totalAdoptions) {
            const titleElement = document.getElementById('adoptionTitle');
            if (currentAdoption === 1) {
                titleElement.textContent = 'Your Adoption Process';
            } else {
                titleElement.textContent = `Your Adoption #${currentAdoption}`;
            }
            console.log(`Displaying adoption #${currentAdoption} of ${totalAdoptions} total adoptions`);
        }

        function getDefaultProgressStatus() {
            // Default progress status matching mobile app logic
            return {
                step1: 'complete',      // Step 1 is immediately complete
                step2: 'in_progress',   // Step 2 is the first active step
                step3: 'locked',
                step4: 'locked',
                step5: 'locked',
                step6: 'locked',
                step7: 'locked',
                step8: 'locked',
                step9: 'locked',
                step10: 'locked',
                step11: 'locked'
            };
        }

        function updateProgressTracking(progress) {
            console.log('Updating progress tracking with:', progress);
            
            // Ensure we have valid progress data with defaults (matching mobile app logic)
            progressStatus = {};
            
            // Copy progress data with validation (matching mobile app)
            for (let i = 1; i <= 11; i++) {
                const stepKey = `step${i}`;
                const status = progress[stepKey];
                
                // Validate status values (matching mobile app validation)
                if (status === 'complete' || status === 'in_progress' || status === 'locked') {
                    progressStatus[stepKey] = status;
                } else {
                    // Check for completion flags (step7_completed, etc.) and convert to proper status
                    const completionKey = `step${i}_completed`;
                    if (progress[completionKey] === true) {
                        progressStatus[stepKey] = 'complete';
                        console.log(`✅ Converted ${completionKey} to ${stepKey}: complete`);
                    } else {
                        // Default to 'locked' if status is not found or invalid (mobile app behavior)
                        progressStatus[stepKey] = 'locked';
                    }
                }
            }
            
            // Apply step progression logic - if a step is complete, next step should be in_progress
            for (let i = 1; i <= 11; i++) {
                const stepKey = `step${i}`;
                const nextStepKey = `step${i + 1}`;
                
                if (progressStatus[stepKey] === 'complete' && i < 11) {
                    // Only auto-unlock steps 1-8, steps 9-11 must be manually unlocked by admin
                    if (i < 9 && progressStatus[nextStepKey] === 'locked') {
                        progressStatus[nextStepKey] = 'in_progress';
                        console.log(`✅ Auto-unlocked ${nextStepKey} to in_progress`);
                    }
                }
            }
            
            console.log('Processed progress status:', progressStatus);
            
            // CHECK FOR RESET NEEDED - MOBILE APP EXACT LOGIC
            // Count completed steps and trigger reset if all 11 are complete
            let completedStepsCount = 0;
            for (let i = 1; i <= 11; i++) {
                if (progressStatus[`step${i}`] === 'complete') {
                    completedStepsCount++;
                }
            }
            
            console.log(`🔍 PROGRESS CHECK: ${completedStepsCount}/11 steps completed`);
            
            if (completedStepsCount === 11) {
                console.log('🎉 PROGRESS CHECK: All 11 steps completed! Showing new adoption confirmation dialog...');
                
                // Get current user ID for reset
                const resetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : (userId || window.sessionUserId);
                
                if (resetUserId && db) {
                    // Show confirmation dialog instead of automatic reset
                    setTimeout(() => {
                        console.log('🔄 PROGRESS CHECK: Showing new adoption confirmation dialog');
                        showNewAdoptionConfirmationDialog(resetUserId);
                    }, 500);
                } else {
                    console.error('❌ PROGRESS CHECK: Cannot show confirmation - missing user ID or database');
                }
            }
            
            // For admin users, always use admin interface (whether viewing own progress or another user's)
            if (isAdminUser) {
                if (currentSelectedUser) {
                    console.log('Updating admin view with another user\'s progress data');
                generateAdminStepsInterface(progress);
                } else {
                    console.log('Updating admin view with own progress data');
                    // Admin viewing their own progress - use desktop interface with admin controls
                    
                    // Hide mobile interface elements for admins
                    const mobileStepsContainer = document.getElementById('mobileStepsContainer');
                    if (mobileStepsContainer) {
                        mobileStepsContainer.style.display = 'none';
                    }
                    const loadingMessage = document.getElementById('loadingMessage');
                    if (loadingMessage) {
                        loadingMessage.style.display = 'none';
                    }
                    
                    // Show desktop interface
                    generateStepsIndicator();
                    generateStepCards();
                    
                    // Show content
                    const contentContainer = document.getElementById('stepsContent');
                    if (contentContainer) {
                        contentContainer.style.display = 'block';
                    }
                }
            } else {
                // Regular user view - Update both old and new mobile interface
                console.log('Updating mobile user interface with real progress data');
                
                // Generate progress circles with real data
                generateProgressCircles();
                
                // Generate mobile large image cards with real data
                generateMobileLargeImageCards();
                
                // Legacy interface for compatibility
                generateStepsIndicator();
                generateStepCards();
                
                // Show content
                const contentContainer = document.getElementById('stepsContent');
                if (contentContainer) {
                    contentContainer.style.display = 'block';
                }
            }
        }

        function generateStepsIndicator() {
            console.log('Generating steps indicator');
            // Use admin container if admin is viewing another user's progress
            const containerId = (isAdminUser && currentSelectedUser) ? 'adminStepsContainer' : 'stepsContainer';
            const container = document.getElementById(containerId);
            if (!container) {
                console.error(`Steps container not found: ${containerId}`);
                return;
            }
            container.innerHTML = '';
            
            stepDefinitions.forEach((step, index) => {
                const stepKey = `step${step.number}`;
                const status = progressStatus[stepKey] || 'locked';
                
                console.log(`Step ${step.number} status:`, status);
                
                const stepDiv = document.createElement('div');
                stepDiv.className = 'step-indicator';
                
                // Only add click handler for accessible steps
                const isAccessible = isStepAccessible(step.number, status);
                if (isAccessible) {
                    stepDiv.onclick = () => handleStepClick(step.number, status);
                    stepDiv.style.cursor = 'pointer';
                } else {
                    stepDiv.style.cursor = 'not-allowed';
                    stepDiv.style.opacity = '0.5';
                    stepDiv.onclick = () => showStepLockedMessage(step.number);
                }
                
                stepDiv.innerHTML = `
                    <div class="step-circle ${status}">
                        ${step.number}
                    </div>
                    <div class="step-label">Step ${step.number}</div>
                `;
                
                container.appendChild(stepDiv);
            });
        }

        function generateStepCards() {
            console.log('Generating step cards');
            // Use admin container if admin is viewing another user's progress
            const containerId = (isAdminUser && currentSelectedUser) ? 'adminStepsContent' : 'stepsContent';
            const container = document.getElementById(containerId);
            if (!container) {
                console.error(`Steps content container not found: ${containerId}`);
                return;
            }
            container.innerHTML = '';
            
            stepDefinitions.forEach(step => {
                const stepKey = `step${step.number}`;
                const status = progressStatus[stepKey] || 'locked';
                
                const stepCard = document.createElement('div');
                stepCard.className = `step-card ${status}`;
                
                // Only add click handler for accessible steps
                const isAccessible = isStepAccessible(step.number, status);
                if (isAccessible) {
                    stepCard.onclick = () => handleStepClick(step.number, status);
                    stepCard.style.cursor = 'pointer';
                } else {
                    stepCard.style.cursor = 'not-allowed';
                    stepCard.style.opacity = '0.6';
                    stepCard.onclick = () => showStepLockedMessage(step.number);
                }
                
                // Create placeholder image with step number matching mobile app style
                const imageUrl = `data:image/svg+xml;base64,${btoa(`
                    <svg width="400" height="200" xmlns="http://www.w3.org/2000/svg">
                        <rect width="100%" height="100%" fill="#61C2C7"/>
                        <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-family="Arial, sans-serif" font-size="24" fill="white">Step ${step.number}</text>
                    </svg>
                `)}`;
                
                stepCard.innerHTML = `
                    <img src="${imageUrl}" alt="Step ${step.number} Image" class="step-image">
                    <div class="step-content">
                        <div class="step-title">Step ${step.number}: ${step.title}</div>
                        <div class="step-status">
                            <img src="${getStatusIcon(status)}" alt="${status}" class="status-icon">
                            <span class="status-text ${status}">${getStatusText(status)}</span>
                        </div>
                        

                        
                        <div class="admin-controls" id="adminControls${step.number}" style="margin-top: 15px; display: none;">
                            <h4 style="color: #c62828; margin-bottom: 10px; font-size: 14px;">⚙️ Admin Controls</h4>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <button class="admin-btn complete-btn" onclick="markStepComplete(${step.number})" 
                                        style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-size: 12px;">
                                    ✓ Mark Complete
                                </button>
                                ${step.number === 9 || step.number === 10 ? `
                                <button class="admin-btn progress-btn" onclick="markStepInProgress(${step.number})" 
                                        style="background: #6ea4ce; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-size: 12px;">
                                    🔄 Set In Progress
                                </button>
                                ` : ''}
                            </div>
                            <div style="margin-top: 10px;">
                                <input type="text" id="adminComment${step.number}" placeholder="Add admin comment..." 
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; box-sizing: border-box;">
                                <button onclick="saveAdminComment(${step.number})" 
                                        style="width: 100%; margin-top: 5px; background: #6c757d; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                    💬 Save Comment
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                container.appendChild(stepCard);
                
                // Show admin controls if user is admin
                if (isAdminUser) {
                    const adminControls = document.getElementById(`adminControls${step.number}`);
                    if (adminControls) {
                        adminControls.style.display = 'block';
                    }
                }
            });
        }

        function getStatusIcon(status) {
            const icons = {
                complete: 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FFFF00"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>'),
                in_progress: 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#000000"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'),
                locked: 'data:image/svg+xml;base64=' + btoa('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FF0000"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM12 17c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM15.1 8H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>')
            };
            return icons[status] || icons.locked;
        }

        function getStatusText(status) {
            const texts = {
                complete: 'Complete',
                in_progress: 'In Progress',
                locked: 'Locked'
            };
            return texts[status] || 'Locked';
        }

        function isStepAccessible(stepNumber, status) {
            // Admins viewing another user's progress can always access any step
            if (isAdminUser && currentSelectedUser) {
                return true;
            }
            
            // Step 1 is always accessible
            if (stepNumber === 1) {
                return true;
            }
            
            // SPECIAL HANDLING: Steps 9 and 10 can ONLY be accessed if admin manually set them to in_progress
            // They should NEVER auto-unlock even if previous step is complete
            if (stepNumber === 9 || stepNumber === 10) {
                // Only accessible if admin has explicitly set them to in_progress or complete
                // AND previous step is complete
                const previousStepNumber = stepNumber - 1;
                const previousStepStatus = progressStatus[`step${previousStepNumber}`];
                
                // If locked, show special message for admin-only steps
                if (status === 'locked') {
                    return false;
                }
                
                return previousStepStatus === 'complete' && (status === 'in_progress' || status === 'complete');
            }
            
            // For other steps (2-8), check if previous step is complete
            const previousStepNumber = stepNumber - 1;
            const previousStepStatus = progressStatus[`step${previousStepNumber}`];
            
            // Previous step must be complete, and current step must be in_progress or complete
            return previousStepStatus === 'complete' && (status === 'in_progress' || status === 'complete');
        }

        function showStepLockedMessage(stepNumber) {
            // Special message for steps 9 and 10
            if (stepNumber === 9 || stepNumber === 10) {
                const stepStatus = progressStatus[`step${stepNumber}`] || 'locked';
                if (stepStatus === 'locked') {
                    let message = `Step ${stepNumber} is locked and must be manually opened by an administrator.`;
                    message += ` Please wait for admin approval to proceed with this step.`;
                    showStepLockedModal(stepNumber, null, message);
                    return;
                }
            }
            
            // Find the first incomplete step
            let firstIncompleteStep = null;
            for (let i = 1; i < stepNumber; i++) {
                const stepStatus = progressStatus[`step${i}`];
                if (stepStatus !== 'complete') {
                    firstIncompleteStep = i;
                    break;
                }
            }
            
            let message = `Step ${stepNumber} is locked.`;
            if (firstIncompleteStep) {
                message += ` Please complete Step ${firstIncompleteStep} first.`;
            } else {
                message += ` Please complete the previous steps first.`;
            }
            
            // Show a nicer modal instead of alert
            showStepLockedModal(stepNumber, firstIncompleteStep, message);
        }

        function showStepLockedModal(stepNumber, firstIncompleteStep, message) {
            // Create modal overlay
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            `;
            
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                border-radius: 12px;
                padding: 30px;
                max-width: 400px;
                text-align: center;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            `;
            
            modalContent.innerHTML = `
                <div style="font-size: 48px; margin-bottom: 15px;">🔒</div>
                <h3 style="margin: 0 0 15px 0; color: #333;">Step Locked</h3>
                <p style="margin: 0 0 20px 0; color: #666; line-height: 1.5;">${message}</p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    ${firstIncompleteStep ? 
                        `<button onclick="goToStep(${firstIncompleteStep}); document.body.removeChild(document.querySelector('[style*=\"z-index: 10000\"]'))" style="
                            background: #6EC6FF;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: bold;
                        ">Go to Step ${firstIncompleteStep}</button>` : ''
                    }
                    <button onclick="document.body.removeChild(this.closest('[style*=\"z-index: 10000\"]'))" style="
                        background: #ccc;
                        color: #333;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 6px;
                        cursor: pointer;
                    ">Close</button>
                </div>
            `;
            
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Close modal when clicking overlay
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            });
        }

        function goToStep(stepNumber) {
            const status = progressStatus[`step${stepNumber}`] || 'locked';
            if (isStepAccessible(stepNumber, status)) {
                handleStepClick(stepNumber, status);
            }
        }

        function handleStepClick(stepNumber, status) {
            console.log(`Step ${stepNumber} clicked with status: ${status}`);
            console.log('Admin context: isAdminUser=', isAdminUser, 'currentSelectedUser=', currentSelectedUser);
            
            // If admin is viewing another user's progress, expand/collapse step instead of navigating
            if (isAdminUser && currentSelectedUser) {
                console.log('Admin viewing another user - expanding step content');
                // Admin viewing another user - expand/collapse the step content (like mobile app)
                toggleAdminStepContent(stepNumber);
                return;
            }
            
            // Double-check accessibility before proceeding
            if (!isStepAccessible(stepNumber, status)) {
                showStepLockedMessage(stepNumber);
                return;
            }
            
            // Navigate to the step
            navigateToStep(stepNumber);
        }

        function navigateToStep(stepNumber) {
            console.log(`Navigating to Step ${stepNumber}: ${stepDefinitions[stepNumber - 1].title}`);
            currentViewStep = stepNumber;
            
            // Hide the main step cards view - use appropriate container
            const contentContainerId = (isAdminUser && currentSelectedUser) ? 'adminStepsContent' : 'stepsContent';
            const contentContainer = document.getElementById(contentContainerId);
            if (contentContainer) {
                contentContainer.style.display = 'none';
            } else {
                console.warn(`Steps content container not found: ${contentContainerId}`);
            }
            
            // Show the detailed step view first (with loading state)
            showStepDetailView(stepNumber);
            
            // Then load submission data for this step (which will refresh the view when done)
            loadStepSubmissionData(stepNumber);
        }

        function loadStepSubmissionData(stepNumber) {
            if (!db || !userId) {
                console.log('Cannot load submission data - missing db or userId');
                return;
            }

            console.log(`Loading submission data for Step ${stepNumber}`);
            
            // Load both submission status and previously submitted documents
            Promise.all([
                fetchStepSubmissionStatus(stepNumber),
                fetchStepDocuments(stepNumber)
            ])
                .then(() => {
                    console.log(`Submission status and documents loaded for step ${stepNumber}`);
                    console.log(`submissionData for step ${stepNumber}:`, submissionData[stepNumber]);
                    
                    // CRITICAL: Refresh the UI with the loaded data
                    refreshStepDetailView(stepNumber);
                })
                .catch(error => {
                    console.error(`Error loading step ${stepNumber} data:`, error);
                    // Even on error, refresh the view to show the step content
                    refreshStepDetailView(stepNumber);
                });
            
            // Also load admin comments
            loadAdminComments(stepNumber);
        }

                 // Function to map legacy document IDs to current step definition IDs
        function mapLegacyDocumentId(documentId, stepNumber) {
            // Handle legacy naming patterns
            const legacyMappings = {
                // Step 3 mappings (these should already match)
                3: {
                    'document_1_application_undertaking': 'document_1_application_undertaking',
                    'document_2_psa_birth_certificate': 'document_2_psa_birth_certificate',
                    'document_3_psa_marriage_divorce': 'document_3_psa_marriage_divorce',
                    'document_4_medical_certificate': 'document_4_medical_certificate',
                    'document_5_income_proof': 'document_5_income_proof',
                    'document_6_police_clearance': 'document_6_nbi_police_clearance', // Legacy mapping
                    'document_6_nbi_police_clearance': 'document_6_nbi_police_clearance',
                    'document_7_barangay_certificate': 'document_7_barangay_certificate',
                    'document_8_whole_body_photos': 'document_8_whole_body_photos',
                    'document_9_character_references': 'document_9_character_references',
                    'document_10_psychological_evaluation': 'document_10_psychological_evaluation'
                },
                // Step 4 mappings
                4: {
                    'document_11_acknowledgment_proof': 'document_11_acknowledgment_proof',
                    'application_review_status': 'document_11_acknowledgment_proof' // Legacy mapping
                },
                // Step 5 mappings
                5: {
                    'document_12_personality_test_result': 'document_12_personality_test_result',
                    'personality_test_results': 'document_12_personality_test_result' // Legacy mapping
                },
                // Step 6 mappings
                6: {
                    'document_13_acceptance_receipt': 'document_13_acceptance_receipt',
                    'acceptance_letter': 'document_13_acceptance_receipt' // Legacy mapping
                },
                // Step 7 mappings
                7: {
                    'document_14_foster_placement_authority': 'document_14_foster_placement_authority',
                    'foster_placement_authority': 'document_14_foster_placement_authority' // Legacy mapping
                },
                // Step 8 mappings
                8: {
                    'document_15_post_adoption_monitoring': 'document_15_post_adoption_monitoring',
                    'post_adoption_reports': 'document_15_post_adoption_monitoring' // Legacy mapping
                },
                // Step 9 mappings
                9: {
                    'document_16_step9_document': 'document_16_step9_document',
                    'step9_requirements': 'document_16_step9_document' // Legacy mapping
                },
                // Step 10 mappings
                10: {
                    'document_17_final_documentation': 'document_17_final_documentation',
                    'final_documentation': 'document_17_final_documentation' // Legacy mapping
                }
            };
            
            // Return mapped ID if exists, otherwise return original
            const stepMappings = legacyMappings[stepNumber];
            if (stepMappings && stepMappings[documentId]) {
                return stepMappings[documentId];
            }
            
            return documentId; // Return original if no mapping found
        }

         // Simplified function to fetch submission status - exactly like mobile app
        function fetchStepSubmissionStatus(stepNumber) {
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId || !db) {
                console.log('Cannot fetch submission status - missing userId or db');
                return Promise.resolve();
            }

            console.log(`Fetching submission status for step ${stepNumber} for user: ${targetUserId}`);
            
            // Initialize step data if not exists
            if (!submissionData[stepNumber]) {
                submissionData[stepNumber] = {};
            }
            
            // For each document requirement in this step, check its submission status
            const stepDef = stepDefinitions.find(step => step.number === stepNumber);
            if (!stepDef || !stepDef.requirements) {
                console.log(`No step definition found for step ${stepNumber}`);
                return Promise.resolve();
            }
            
            const promises = stepDef.requirements.map(requirement => {
                const documentId = requirement.documentId;
                
                return db.collection("user_submissions_status")
                    .doc(targetUserId)
                    .collection(`step${stepNumber}_documents`)
                    .doc(documentId)
                    .get()
                    .then(docSnapshot => {
                        if (docSnapshot.exists) {  // Fixed: removed () - it's a property in Firebase v10
                            const statusData = docSnapshot.data();
                            submissionData[stepNumber][documentId] = {
                                submitted: statusData.submitted || false,
                                attempts: statusData.attempts || 0,
                                status: statusData.status || 'not_submitted',
                                lastSubmissionTimestamp: statusData.lastSubmissionTimestamp,
                                fileName: statusData.fileName,
                                documentUrl: statusData.lastFileUrl
                            };
                            console.log(`✅ Found submission status for ${documentId}:`, submissionData[stepNumber][documentId]);
                        } else {
                            submissionData[stepNumber][documentId] = {
                                submitted: false,
                                attempts: 0,
                                status: 'not_submitted',
                                lastSubmissionTimestamp: null,
                                fileName: null,
                                documentUrl: null
                            };
                            console.log(`❌ No submission status found for ${documentId}`);
                        }
                    })
                    .catch(error => {
                        console.error(`Error fetching status for ${documentId}:`, error);
                        submissionData[stepNumber][documentId] = {
                            submitted: false,
                            attempts: 0,
                            status: 'not_submitted',
                            lastSubmissionTimestamp: null,
                            fileName: null,
                            documentUrl: null
                        };
                    });
            });
            
            return Promise.all(promises);
        }

                 // ENHANCED COMPREHENSIVE DOCUMENT FETCH - Checks all possible collections
         function fetchStepDocuments(stepNumber) {
             // Use appropriate user ID - admin viewing another user or current user
             const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
             
             if (!targetUserId || !db) {
                 console.log('Cannot fetch documents - missing userId or db');
                 return Promise.resolve();
             }

             console.log(`🔍 COMPREHENSIVE FETCH: Loading documents for step ${stepNumber} for user: ${targetUserId}`);
             console.log(`Admin context: isAdminUser=${isAdminUser}, currentSelectedUser=`, currentSelectedUser);

             // Initialize submissionData for this step if not exists
             if (!submissionData[stepNumber]) {
                 submissionData[stepNumber] = { documents: [] };
             }

             // Collection of all document promises
             const documentPromises = [];
             let allFoundDocuments = [];

             // 1. Check adoption_progress collection (primary for admin view)
             const adoptionProgressPromise = db.collection("adoption_progress")
                 .doc(targetUserId)
                 .collection(`step${stepNumber}_uploads`)
                 .orderBy("timestamp", "desc")
                 .get()
                 .then(querySnapshot => {
                     console.log(`📄 adoption_progress collection: Found ${querySnapshot.size} documents`);
                     querySnapshot.docs.forEach(doc => {
                         const data = doc.data();
                         if (data.fileName && data.fileUrl) {
                             allFoundDocuments.push({
                                 id: doc.id,
                                 fileName: data.fileName,
                                 fileUrl: data.fileUrl,
                                 timestamp: data.timestamp,
                                 adoptionNumber: data.adoptionNumber,
                                 source: 'adoption_progress'
                             });
                         }
                     });
                 }).catch(error => {
                     console.error('Error checking adoption_progress:', error);
                 });

             documentPromises.push(adoptionProgressPromise);

             // 2. Check user_submissions_status collection (mobile app primary)
             const userSubmissionsPromise = db.collection("user_submissions_status")
                 .doc(targetUserId)
                 .collection(`step${stepNumber}_documents`)
                 .get()
                 .then(querySnapshot => {
                     console.log(`📄 user_submissions_status collection: Found ${querySnapshot.size} documents`);
                     querySnapshot.docs.forEach(doc => {
                         const data = doc.data();
                         if (data.fileName && (data.documentUrl || data.lastFileUrl)) {
                             allFoundDocuments.push({
                                 id: doc.id,
                                 fileName: data.fileName,
                                 fileUrl: data.documentUrl || data.lastFileUrl,
                                 timestamp: data.uploadedAt || data.lastSubmissionTimestamp,
                                 adoptionNumber: data.adoptionNumber,
                                 source: 'user_submissions_status'
                             });
                         }
                     });
                 }).catch(error => {
                     console.error('Error checking user_submissions_status:', error);
                 });

             documentPromises.push(userSubmissionsPromise);

             // 3. Check user_documents collection (mobile app secondary)
             const userDocumentsPromise = db.collection("user_documents")
                 .doc(targetUserId)
                 .collection(`step${stepNumber}_documents`)
                 .get()
                 .then(querySnapshot => {
                     console.log(`📄 user_documents collection: Found ${querySnapshot.size} documents`);
                     querySnapshot.docs.forEach(doc => {
                         const data = doc.data();
                         if (data.fileName && data.documentUrl) {
                             allFoundDocuments.push({
                                 id: doc.id,
                                 fileName: data.fileName,
                                 fileUrl: data.documentUrl,
                                 timestamp: data.uploadedAt,
                                 adoptionNumber: data.adoptionNumber,
                                 source: 'user_documents'
                             });
                         }
                     });
                 }).catch(error => {
                     console.error('Error checking user_documents:', error);
                 });

             documentPromises.push(userDocumentsPromise);

             // Wait for all document collections to be checked
             return Promise.all(documentPromises).then(() => {
                 console.log(`🎯 COMPREHENSIVE FETCH: Found ${allFoundDocuments.length} total documents across all collections`);

                 // Group documents by requirement (documentId) instead of deduplicating by fileName
                 const documentsByRequirement = new Map();

                 // Sort by timestamp descending to prefer newer uploads
                 allFoundDocuments.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));

                 allFoundDocuments.forEach(doc => {
                     // Extract documentId from the Firestore document ID
                     // For user_submissions_status, the doc.id IS the documentId
                     // For adoption_progress, the doc.id might be documentId_timestamp
                     let documentId = doc.id;
                     
                     // Handle documents with timestamp suffix (new format)
                     if (documentId.includes('_') && /\d{13}$/.test(documentId)) {
                         // Extract base documentId by removing timestamp suffix
                         documentId = documentId.replace(/_\d{13}$/, '');
                     }
                     
                     // For each requirement, keep only the latest upload
                     if (!documentsByRequirement.has(documentId) || 
                         (doc.timestamp > (documentsByRequirement.get(documentId).timestamp || 0))) {
                         
                         // Add documentId info to the document object
                         const enrichedDoc = {
                             ...doc,
                             requirementId: documentId,
                             requirementTitle: getRequirementTitle(stepNumber, documentId)
                         };
                         
                         documentsByRequirement.set(documentId, enrichedDoc);
                         console.log(`📄 Updated latest document for requirement ${documentId}:`, enrichedDoc);
                     }
                 });

                 // Convert map to array for display
                 const documentsToDisplay = Array.from(documentsByRequirement.values());
                 
                 console.log(`📋 COMPREHENSIVE FETCH: ${documentsToDisplay.length} documents grouped by requirement (${documentsByRequirement.size} requirements)`);

                 // Store documents data for display
                 submissionData[stepNumber].documents = documentsToDisplay;

                 // Update UI to show documents
                 updateStepDocumentsDisplay(stepNumber);

                 console.log(`✅ COMPREHENSIVE FETCH: Complete for step ${stepNumber}`);
             }).catch(error => {
                 console.error(`❌ COMPREHENSIVE FETCH: Error for step ${stepNumber}:`, error);
                 submissionData[stepNumber] = { documents: [] };
                 updateStepDocumentsDisplay(stepNumber);
                 return Promise.resolve();
             });
        }

                 // Function to update the documents display in the UI
         function updateStepDocumentsDisplay(stepNumber) {
             const documentsContainer = document.getElementById(`step${stepNumber}DocumentsContainer`);
             if (!documentsContainer) {
                 console.log(`Documents container not found for step ${stepNumber}`);
                 console.log('Available containers:', document.querySelectorAll('[id*="DocumentsContainer"]'));
                 return;
             }

            documentsContainer.innerHTML = ''; // Clear existing content

            const stepData = submissionData[stepNumber];
            if (!stepData || !stepData.documents || stepData.documents.length === 0) {
                documentsContainer.innerHTML = '<p style="color: #666; font-style: italic; padding: 10px;">No documents uploaded for this step.</p>';
                return;
            }

            console.log(`Displaying ${stepData.documents.length} documents for step ${stepNumber}`);

            stepData.documents.forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'document-item';
                docElement.style.cssText = `
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 15px;
                    margin-bottom: 10px;
                    background: #f9f9f9;
                `;

                const uploadDate = doc.timestamp ? new Date(doc.timestamp).toLocaleString() : 'Unknown date';
                const adoptionInfo = doc.adoptionNumber ? `(Adoption #${doc.adoptionNumber})` : '';
                const requirementTitle = doc.requirementTitle || 'Document';
                const sourceInfo = doc.source ? `[${doc.source}]` : '';

                docElement.innerHTML = `
                    <div style="font-weight: bold; margin-bottom: 8px; color: #2c5aa0; font-size: 14px;">
                        📋 ${requirementTitle}
                    </div>
                    <div style="font-weight: 600; margin-bottom: 5px; color: #333;">
                        📄 ${doc.fileName || 'Unknown file'}
                    </div>
                    <div style="margin-bottom: 8px;">
                        <button class="view-document-button" onclick="viewDocument('${doc.fileUrl}', '${doc.fileName || 'Document'}')">
                            👁️ View Document
                        </button>
                    </div>
                    <div style="font-size: 12px; color: #666;">
                        Uploaded: ${uploadDate} ${adoptionInfo} ${sourceInfo}
                    </div>
                `;

                                 documentsContainer.appendChild(docElement);
             });
         }

         // Admin Functions (copied from mobile app logic) - REMOVED DUPLICATE FUNCTION

         function markStepInProgress(stepNumber) {
             // Use appropriate user ID - admin viewing another user or current user
             const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
             
             if (!targetUserId || !db) {
                 alert('Cannot mark step in progress - missing user data');
                 return;
             }

             if (!confirm(`Are you sure you want to mark Step ${stepNumber} as in progress?`)) {
                 return;
             }

             console.log(`Admin marking step ${stepNumber} as in progress for user ${targetUserId}`);

             const userProgressRef = db.collection("adoption_progress").doc(targetUserId);
             const stepKey = `step${stepNumber}`;

             userProgressRef.get().then(documentSnapshot => {
                 const adoptionsMap = documentSnapshot.data().adoptions;

                 if (adoptionsMap) {
                     // Versioned structure
                     const currentAdoptionNumber = documentSnapshot.data().currentAdoption;
                     const currentAdoptionId = currentAdoptionNumber?.toString();
                     if (currentAdoptionId) {
                         const currentAdoption = adoptionsMap[currentAdoptionId];
                         if (currentAdoption) {
                             const adoptProgressMap = currentAdoption.adopt_progress || {};
                             adoptProgressMap[stepKey] = "in_progress";

                             const updatedAdoption = {
                                 ...currentAdoption,
                                 adopt_progress: adoptProgressMap
                             };
                             
                             const updatedAdoptions = {
                                 ...adoptionsMap,
                                 [currentAdoptionId]: updatedAdoption
                             };

                                                           userProgressRef.update("adoptions", updatedAdoptions)
                                  .then(() => {
                                      alert(`Step ${stepNumber} marked as in progress.`);
                                      
                                      // Send notification about step progress
                                      sendAdoptionNotification('step_started', stepNumber, {
                                          userId: targetUserId,
                                          stepNumber: stepNumber,
                                          status: 'in_progress'
                                      });
                                      
                                      refreshProgressDisplay();
                                      updateAdminStepStatus(stepNumber, 'in_progress');
                                  })
                                  .catch(error => {
                                      alert(`Error: ${error.message}`);
                                  });
                         }
                     }
                 } else {
                     // Old structure
                     const adoptProgressMap = documentSnapshot.data().adopt_progress || {};
                     adoptProgressMap[stepKey] = "in_progress";

                                           userProgressRef.update("adopt_progress", adoptProgressMap)
                          .then(() => {
                              alert(`Step ${stepNumber} marked as in progress.`);
                              
                              // Send notification about step progress
                              sendAdoptionNotification('step_started', stepNumber, {
                                  userId: targetUserId,
                                  stepNumber: stepNumber,
                                  status: 'in_progress'
                              });
                              
                              refreshProgressDisplay();
                              updateAdminStepStatus(stepNumber, 'in_progress');
                          })
                          .catch(error => {
                              alert(`Error: ${error.message}`);
                          });
                 }
             });
         }

         function saveAdminComment(stepNumber) {
             // Use appropriate user ID - admin viewing another user or current user
             const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
             
             if (!targetUserId || !db) {
                 alert('Cannot save comment - missing user data');
                 return;
             }

             const commentInput = document.getElementById(`adminComment${stepNumber}`);
             const comment = commentInput.value.trim();

             if (!comment) {
                 alert('Please enter a comment');
                 return;
             }

             console.log(`Saving admin comment for step ${stepNumber}: ${comment}`);

             const commentRef = db.collection("adoption_progress")
                 .doc(targetUserId)
                 .collection("comments")
                 .doc(`step${stepNumber}`);

             commentRef.set({ comment: comment })
                 .then(() => {
                     alert(`Comment saved for Step ${stepNumber}.`);
                     commentInput.value = ''; // Clear the input
                 })
                 .catch(error => {
                     alert(`Error saving comment: ${error.message}`);
                     console.error('Error saving admin comment:', error);
                 });
         }

         function getNextStep(currentStep) {
             const stepMap = {
                 "step1": "step2",
                 "step2": "step3", 
                 "step3": "step4",
                 "step4": "step5",
                 "step5": "step6",
                 "step6": "step7",
                 "step7": "step8",
                 "step8": null
             };
             return stepMap[currentStep] || null;
         }

         // Helper function to handle automatic adoption completion - MOBILE APP EXACT LOGIC
         function handleAutomaticAdoptionCompletion(userProgressRef, adoptionsMap, currentAdoptionNumber, currentAdoption, completingStep) {
             console.log('🎉 All steps completed! Processing automatic completion and reset - MOBILE APP LOGIC');
             
             // Mark current adoption as completed
             currentAdoption.status = 'completed';
             currentAdoption.completedAt = Date.now();
             
             // Store completed adoption in adoptions history
             const currentAdoptionKey = currentAdoptionNumber.toString();
             adoptionsMap[currentAdoptionKey] = currentAdoption;
             
             // Create new adoption for re-adoption with CLEAN RESET
             const newAdoptionNumber = currentAdoptionNumber + 1;
             const newAdoptionKey = newAdoptionNumber.toString();
             
             console.log(`🔄 Creating new adoption instance #${newAdoptionNumber} for re-adoption`);
             
             const newAdoption = {
                 adopt_progress: {
                     step1: 'complete',      // Step 1 starts complete for re-adoption
                     step2: 'in_progress',   // Step 2 is next
                     step3: 'locked',
                     step4: 'locked',
                     step5: 'locked',
                     step6: 'locked',
                     step7: 'locked',
                     step8: 'locked',
                     step9: 'locked',
                     step10: 'locked',
                     step11: 'locked'
                 },
                 status: 'in_progress',
                 startedAt: Date.now(),
                 adoptionNumber: newAdoptionNumber
             };
             
             // Add new adoption to adoptions map
             adoptionsMap[newAdoptionKey] = newAdoption;
             
             // Update document with new structure
             const updateData = {
                 adoptions: adoptionsMap,
                 currentAdoption: newAdoptionNumber,
                 lastUpdated: Date.now(),
                 totalAdoptions: Object.keys(adoptionsMap).length // Update total count
             };
             
             userProgressRef.update(updateData)
                 .then(() => {
                     alert(`🎉 Adoption completed! ${completingStep.replace('step', '')} was the final step. New adoption #${newAdoptionNumber} started automatically.`);
                     console.log(`✅ Automatic adoption completion and reset successful`);
                     console.log(`📊 Completed adoption #${currentAdoptionNumber} stored in history`);
                     console.log(`🆕 New adoption #${newAdoptionNumber} created and ready for re-adoption`);
                     refreshProgressDisplay();
                 })
                 .catch(error => {
                     alert(`Error completing adoption: ${error.message}`);
                     console.error('❌ Error in automatic adoption completion:', error);
                 });
         }

         // Helper function for old structure completion - MOBILE APP EXACT LOGIC
         function handleOldStructureCompletion(userProgressRef, adoptProgressMap, documentSnapshot, completingStep) {
             console.log('🎉 Converting old structure and completing adoption - MOBILE APP LOGIC');
             
             // Convert to versioned structure with completed first adoption
             const firstAdoption = {
                 adopt_progress: adoptProgressMap,
                 status: 'completed',
                 startedAt: documentSnapshot.data().timestamp || Date.now(),
                 completedAt: Date.now(),
                 adoptionNumber: 1
             };
             
             // Create new adoption for re-adoption with CLEAN RESET
             console.log('🔄 Creating second adoption instance for re-adoption');
             
             const secondAdoption = {
                 adopt_progress: {
                     step1: 'complete',      // Step 1 starts complete for re-adoption
                     step2: 'in_progress',   // Step 2 is next
                     step3: 'locked',
                     step4: 'locked',
                     step5: 'locked',
                     step6: 'locked',
                     step7: 'locked',
                     step8: 'locked',
                     step9: 'locked',
                     step10: 'locked',
                     step11: 'locked'
                 },
                 status: 'in_progress',
                 startedAt: Date.now(),
                 adoptionNumber: 2
             };
             
             // Create versioned structure
             const versionedData = {
                 adoptions: {
                     '1': firstAdoption,
                     '2': secondAdoption
                 },
                 currentAdoption: 2,
                 lastUpdated: Date.now(),
                 totalAdoptions: 2,
                 username: documentSnapshot.data().username || 'Unknown User',
                 timestamp: documentSnapshot.data().timestamp || Date.now()
             };
             
             userProgressRef.set(versionedData)
                 .then(() => {
                     alert(`🎉 Adoption completed! ${completingStep.replace('step', '')} process finished. New adoption #2 started automatically.`);
                     console.log('✅ Automatic adoption completion and conversion to versioned structure successful');
                     console.log('📊 First adoption completed and stored in history');
                     console.log('🆕 Second adoption created and ready for re-adoption');
                     refreshProgressDisplay();
                 })
                 .catch(error => {
                     alert(`Error completing adoption: ${error.message}`);
                     console.error('❌ Error in automatic adoption completion and conversion:', error);
                 });
         }

         // Function to refresh progress display
         function refreshProgressDisplay() {
             if (isAdminUser && currentSelectedUser) {
                 // Admin viewing a user's progress - reload that user's progress
                 loadUserProgress(currentSelectedUser.uid, currentSelectedUser.username);
             } else if (currentUser) {
                 // Regular user or admin viewing their own progress - reload current user's progress
                 checkFirebaseProgress();
             }
        }

        // Check for completed adoption and auto-reset on page load - MOBILE APP EXACT LOGIC
        function checkForCompletedAdoptionOnStartup(userId) {
            if (!userId || !db) {
                console.log('❌ STARTUP CHECK: No user ID or database for startup completion check');
                return;
            }

            console.log('🔍 STARTUP CHECK: Checking for completed adoption that needs reset for user:', userId);
            console.log('🔍 STARTUP CHECK: Database available:', !!db);
            console.log('🔍 STARTUP CHECK: User ID type:', typeof userId, 'Value:', userId);

            const userProgressRef = db.collection('adoption_progress').doc(userId);
            
            userProgressRef.get().then(documentSnapshot => {
                if (!documentSnapshot.exists) {
                    console.log('📄 STARTUP CHECK: No adoption progress document found');
                    return;
                }

                const data = documentSnapshot.data();
                console.log('📊 STARTUP CHECK: Document found, checking data structure...');
                console.log('📊 STARTUP CHECK: Has adoptions:', 'adoptions' in data);
                console.log('📊 STARTUP CHECK: Has adopt_progress:', 'adopt_progress' in data);
                
                // Check versioned structure first
                if (data.adoptions) {
                    const currentAdoptionNumber = data.currentAdoption;
                    const adoptionsMap = data.adoptions;
                    const currentAdoptionKey = currentAdoptionNumber?.toString();
                    
                    if (currentAdoptionKey && adoptionsMap[currentAdoptionKey]) {
                        const currentAdoption = adoptionsMap[currentAdoptionKey];
                        const adoptProgressMap = currentAdoption.adopt_progress || {};
                        
                        // Check if all 11 steps are complete in current adoption
                        let completedSteps = 0;
                        for (let i = 1; i <= 11; i++) {
                            if (adoptProgressMap[`step${i}`] === 'complete') {
                                completedSteps++;
                            }
                        }
                        
                        console.log('📊 STARTUP CHECK: Current adoption status:', currentAdoption.status);
                        console.log('📊 STARTUP CHECK: Completed steps count:', completedSteps);
                        console.log('📊 STARTUP CHECK: Progress details:', adoptProgressMap);
                        
                        // NO AUTOMATIC RESET - Just log progress
                        console.log(`📊 STARTUP CHECK: Current adoption progress: ${completedSteps}/11 steps complete`);
                        if (completedSteps === 11) {
                            console.log('🎉 STARTUP CHECK: All 11 steps complete! Adoption is finished (no automatic reset).');
                        }
                    }
                } else {
                    // Old structure - check direct adopt_progress
                    const adoptProgressMap = data.adopt_progress || {};
                    
                    let completedSteps = 0;
                    for (let i = 1; i <= 11; i++) {
                        if (adoptProgressMap[`step${i}`] === 'complete') {
                            completedSteps++;
                        }
                    }
                    
                    console.log('📊 STARTUP CHECK: Old structure - Completed steps count:', completedSteps);
                    console.log('📊 STARTUP CHECK: Old structure - Progress details:', adoptProgressMap);
                    
                    // NO AUTOMATIC RESET - Just log progress
                    console.log(`📊 STARTUP CHECK: Old structure progress: ${completedSteps}/11 steps complete`);
                    if (completedSteps === 11) {
                        console.log('🎉 STARTUP CHECK: All 11 steps complete in old structure! Adoption is finished (no automatic reset).');
                    }
                }
            }).catch(error => {
                console.error('❌ Error in startup completion check:', error);
            });
        }

        // SIMPLE RESET FUNCTIONS - MOBILE APP LOGIC
        function simpleResetAfterStep11(userId) {
            console.log('🔄 SIMPLE RESET: Checking if step 11 is complete for user:', userId);
            
            if (!userId || !db) {
                console.error('❌ SIMPLE RESET: Missing user ID or database');
                return;
            }
            
            const userProgressRef = db.collection('adoption_progress').doc(userId);
            
            userProgressRef.get().then(doc => {
                if (!doc.exists) {
                    console.log('📄 SIMPLE RESET: No progress document found');
                    return;
                }
                
                const data = doc.data();
                let step11Complete = false;
                let currentProgress = {};
                
                // Check if step 11 is complete
                if (data.adoptions) {
                    // Versioned structure
                    const currentAdoptionNumber = data.currentAdoption;
                    const adoptionsMap = data.adoptions;
                    const currentAdoption = adoptionsMap[currentAdoptionNumber?.toString()];
                    
                    if (currentAdoption?.adopt_progress) {
                        currentProgress = currentAdoption.adopt_progress;
                        step11Complete = currentProgress.step11 === 'complete';
                    }
                } else if (data.adopt_progress) {
                    // Old structure
                    currentProgress = data.adopt_progress;
                    step11Complete = currentProgress.step11 === 'complete';
                }
                
                console.log('📊 SIMPLE RESET: Step 11 complete?', step11Complete);
                console.log('📊 SIMPLE RESET: Current progress:', currentProgress);
                
                if (step11Complete) {
                    console.log('🎉 SIMPLE RESET: Step 11 is complete! Creating new adoption...');
                    createNewAdoptionAfterStep11(userProgressRef, data);
                } else {
                    console.log('📊 SIMPLE RESET: Step 11 not complete yet');
                }
            }).catch(error => {
                console.error('❌ SIMPLE RESET: Error checking step 11:', error);
            });
        }
        
        function createNewAdoptionAfterStep11(userProgressRef, currentData) {
            console.log('🆕 SIMPLE RESET: Creating new adoption after step 11 completion');
            
            if (currentData.adoptions) {
                // Versioned structure - add new adoption
                const adoptionsMap = { ...currentData.adoptions };
                const newAdoptionNumber = (currentData.currentAdoption || 0) + 1;
                
                // Mark current adoption as completed
                const currentAdoptionKey = currentData.currentAdoption?.toString();
                if (currentAdoptionKey && adoptionsMap[currentAdoptionKey]) {
                    adoptionsMap[currentAdoptionKey] = {
                        ...adoptionsMap[currentAdoptionKey],
                        status: 'completed',
                        completedAt: Date.now()
                    };
                }
                
                // Create new adoption
                adoptionsMap[newAdoptionNumber.toString()] = {
                    adopt_progress: {
                        step1: 'complete',      // Mobile app logic: Step 1 starts complete
                        step2: 'in_progress',   // Step 2 is next
                        step3: 'locked',
                        step4: 'locked',
                        step5: 'locked',
                        step6: 'locked',
                        step7: 'locked',
                        step8: 'locked',
                        step9: 'locked',
                        step10: 'locked',
                        step11: 'locked'
                    },
                    status: 'in_progress',
                    startedAt: Date.now(),
                    adoptionNumber: newAdoptionNumber
                };
                
                const updateData = {
                    adoptions: adoptionsMap,
                    currentAdoption: newAdoptionNumber,
                    lastUpdated: Date.now()
                };
                
                userProgressRef.update(updateData)
                    .then(() => {
                        console.log('✅ SIMPLE RESET: New adoption created successfully!');
                        
                        // Create messaging connection for new adoption (matching mobile app behavior)
                        const userName = auth.currentUser?.displayName || auth.currentUser?.email?.split('@')[0] || 'User';
                        const currentUserId = auth.currentUser?.uid || '';
                        
                        console.log('🔗 Creating messaging connection for new adoption...');
                        // Send adoption started message via Firebase bridge
                        if (window.firebaseMessagingBridge) {
                            window.firebaseMessagingBridge.sendAdoptionStarted(currentUserId, 'system', userName, 'Social Worker')
                                .then(chatId => {
                                    console.log('✅ MESSAGING CONNECTION CREATED FOR NEW ADOPTION!');
                                    console.log('Chat ID:', chatId);
                                })
                                .catch(error => {
                                    console.error('❌ Error creating messaging connection for new adoption:', error);
                                });
                        } else {
                            console.error('❌ Firebase messaging bridge not available');
                        }
                        
                        alert(`🎉 Congratulations! Adoption completed! Starting new adoption #${newAdoptionNumber}...`);
                        // Refresh page to show new adoption
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('❌ SIMPLE RESET: Error creating new adoption:', error);
                    });
                    
            } else {
                // Old structure - convert to versioned and reset
                console.log('🔄 SIMPLE RESET: Converting old structure to versioned and resetting');
                
                const versionedData = {
                    adoptions: {
                        '1': {
                            adopt_progress: currentData.adopt_progress || {},
                            status: 'completed',
                            startedAt: currentData.timestamp || Date.now(),
                            completedAt: Date.now(),
                            adoptionNumber: 1
                        },
                        '2': {
                            adopt_progress: {
                                step1: 'complete',      // Mobile app logic: Step 1 starts complete
                                step2: 'in_progress',   // Step 2 is next
                                step3: 'locked',
                                step4: 'locked',
                                step5: 'locked',
                                step6: 'locked',
                                step7: 'locked',
                                step8: 'locked',
                                step9: 'locked',
                                step10: 'locked',
                                step11: 'locked'
                            },
                            status: 'in_progress',
                            startedAt: Date.now(),
                            adoptionNumber: 2
                        }
                    },
                    currentAdoption: 2,
                    lastUpdated: Date.now(),
                    username: currentData.username || 'Unknown User',
                    timestamp: currentData.timestamp || Date.now()
                };
                
                userProgressRef.set(versionedData)
                    .then(() => {
                        console.log('✅ SIMPLE RESET: Converted to versioned structure and reset!');
                        
                        // Create messaging connection for new adoption (matching mobile app behavior)
                        const userName = auth.currentUser?.displayName || auth.currentUser?.email?.split('@')[0] || 'User';
                        const currentUserId = auth.currentUser?.uid || '';
                        
                        console.log('🔗 Creating messaging connection for new adoption #2...');
                        // Send adoption started message via Firebase bridge
                        if (window.firebaseMessagingBridge) {
                            window.firebaseMessagingBridge.sendAdoptionStarted(currentUserId, 'system', userName, 'Social Worker')
                                .then(chatId => {
                                    console.log('✅ MESSAGING CONNECTION CREATED FOR NEW ADOPTION #2!');
                                    console.log('Chat ID:', chatId);
                                })
                                .catch(error => {
                                    console.error('❌ Error creating messaging connection for new adoption #2:', error);
                                });
                        } else {
                            console.error('❌ Firebase messaging bridge not available for new adoption #2');
                        }
                        
                        alert('🎉 Congratulations! Adoption completed! Starting new adoption #2...');
                        // Refresh page to show new adoption
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('❌ SIMPLE RESET: Error converting and resetting:', error);
                    });
            }
        }
        
        function resetToNewAdoption(userProgressRef, adoptionsMap, currentAdoptionNumber) {
            console.log('🔄 RESET TO NEW: Creating new adoption');
            
            // Mark current adoption as completed and create new one
            const newAdoptionNumber = currentAdoptionNumber + 1;
            const currentAdoptionKey = currentAdoptionNumber.toString();
            
            // Update current adoption status
            if (adoptionsMap[currentAdoptionKey]) {
                adoptionsMap[currentAdoptionKey] = {
                    ...adoptionsMap[currentAdoptionKey],
                    status: 'completed',
                    completedAt: Date.now()
                };
            }
            
            // Create new adoption
            adoptionsMap[newAdoptionNumber.toString()] = {
                adopt_progress: {
                    step1: 'complete',      // Mobile app logic: Step 1 starts complete
                    step2: 'in_progress',   // Step 2 is next
                    step3: 'locked',
                    step4: 'locked',
                    step5: 'locked',
                    step6: 'locked',
                    step7: 'locked',
                    step8: 'locked',
                    step9: 'locked',
                    step10: 'locked',
                    step11: 'locked'
                },
                status: 'in_progress',
                startedAt: Date.now(),
                adoptionNumber: newAdoptionNumber
            };
            
            const updateData = {
                adoptions: adoptionsMap,
                currentAdoption: newAdoptionNumber,
                lastUpdated: Date.now()
            };
            
            userProgressRef.update(updateData)
                .then(() => {
                    console.log('✅ RESET TO NEW: New adoption created!');
                    
                    // Send adoption started message via Firebase bridge
                    const userName = auth.currentUser?.displayName || auth.currentUser?.email?.split('@')[0] || 'User';
                    const currentUserId = auth.currentUser?.uid || '';
                    
                    if (window.firebaseMessagingBridge) {
                        window.firebaseMessagingBridge.sendAdoptionStarted(currentUserId, 'system', userName, 'Social Worker')
                            .then(chatId => {
                                console.log('✅ ADOPTION STARTED MESSAGE SENT FOR NEW ADOPTION!');
                                console.log('Chat ID:', chatId);
                            })
                            .catch(error => {
                                console.error('❌ Error sending adoption started message for new adoption:', error);
                            });
                    } else {
                        console.error('❌ Firebase messaging bridge not available for new adoption');
                    }
                    
                    alert(`🎉 Congratulations! Adoption completed! Starting new adoption #${newAdoptionNumber}...`);
                    // Refresh page to show new adoption
                    window.location.reload();
                })
                .catch(error => {
                    console.error('❌ RESET TO NEW: Error creating new adoption:', error);
                });
        }

        // Manual test function for debugging - call this from browser console
        window.testStartupCheck = function() {
            const testUserId = userId || window.sessionUserId;
            console.log('🧪 MANUAL TEST: Starting manual startup check for user:', testUserId);
            if (testUserId) {
                simpleResetAfterStep11(testUserId);
            } else {
                console.error('🧪 MANUAL TEST: No user ID available for test');
            }
        };

        function loadAdminComments(stepNumber) {
             // Use appropriate user ID - admin viewing another user or current user
             const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
             
             if (!db || !targetUserId) {
                return;
            }

            const stepKey = `step${stepNumber}`;
            
            db.collection("adoption_progress")
                 .doc(targetUserId)
                .collection("comments")
                .doc(stepKey)
                .get()
                .then(doc => {
                    if (doc.exists) {
                        const data = doc.data();
                        const comment = data.comment;
                        if (comment && comment.trim()) {
                            displayAdminComment(comment, stepNumber);
                        }
                    }
                })
                .catch(error => {
                    console.error(`Failed to load admin comments for ${stepKey}:`, error);
                });
        }
        
        function displayAdminComment(comment, stepNumber) {
            console.log('📝 Displaying admin comment:', comment);
            
            // Look for admin comment section in mobile step detail view
            const adminCommentSection = document.getElementById(`adminCommentSection_${stepNumber}`);
            if (adminCommentSection) {
                // Make it visible and set the comment
                adminCommentSection.style.display = 'block';
                
                const commentTextElement = document.getElementById(`adminCommentText_${stepNumber}`);
                if (commentTextElement) {
                    commentTextElement.textContent = comment;
                    commentTextElement.style.cssText = `
                        background: #fff3cd;
                        border: 1px solid #ffeaa7;
                        border-radius: 8px;
                        padding: 16px;
                        margin: 8px 0;
                        color: #856404;
                        font-size: 14px;
                        line-height: 1.5;
                        white-space: pre-wrap;
                    `;
                }
            } else {
                // Create a floating comment popup if no dedicated section exists
                createFloatingAdminComment(comment);
            }
        }
        
        function createFloatingAdminComment(comment) {
            // Remove any existing floating comment
            const existingComment = document.getElementById('floatingAdminComment');
            if (existingComment) {
                existingComment.remove();
            }
            
            const commentDiv = document.createElement('div');
            commentDiv.id = 'floatingAdminComment';
            commentDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #fff3cd;
                border: 2px solid #ffeaa7;
                border-radius: 12px;
                padding: 16px;
                max-width: 300px;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                font-family: Arial, sans-serif;
            `;
            
            const title = document.createElement('div');
            title.style.cssText = `
                font-weight: bold;
                color: #856404;
                margin-bottom: 8px;
                font-size: 14px;
            `;
            title.textContent = '📝 Admin Comment';
            
            const text = document.createElement('div');
            text.style.cssText = `
                color: #856404;
                font-size: 13px;
                line-height: 1.4;
                white-space: pre-wrap;
                margin-bottom: 12px;
            `;
            text.textContent = comment;
            
            const closeBtn = document.createElement('button');
            closeBtn.style.cssText = `
                background: #856404;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                float: right;
            `;
            closeBtn.textContent = 'Close';
            closeBtn.onclick = () => commentDiv.remove();
            
            commentDiv.appendChild(title);
            commentDiv.appendChild(text);
            commentDiv.appendChild(closeBtn);
            document.body.appendChild(commentDiv);
            
            console.log('✅ Created floating admin comment');
        }

        function showStepDetailView(stepNumber) {
            const step = stepDefinitions[stepNumber - 1];
            const stepDetailView = document.getElementById('stepDetailView');
            
            let html = `
                <div class="step-detail-header">
                    <h3>${step.title}</h3>
                    <p class="subtitle">Step ${step.number} Requirements and Submissions</p>
                </div>
                <div class="step-detail-content">
                    <button class="back-button" onclick="goBackToStepsList()">
                        ← Back to Progress Overview
                    </button>
                    
                    <div id="stepRequirements">
                        <p style="color: #666; font-style: italic; text-align: center; margin: 40px 0;">Loading submission status...</p>
                    </div>
                    
                    <div class="submitted-documents-section" style="margin-top: 30px;">
                        <h4 style="color: #2c5aa0; margin-bottom: 15px; font-size: 16px;">📄 Previously Submitted Documents</h4>
                        <div id="step${stepNumber}DocumentsContainer" class="documents-container">
                            <p style="color: #666; font-style: italic;">Loading documents...</p>
                        </div>
                    </div>
                </div>
            `;
            
            stepDetailView.innerHTML = html;
            stepDetailView.style.display = 'block';
            
            // Load submission data to ensure submitted status is properly displayed
            // Admin comments will be loaded AFTER the HTML is generated in refreshStepDetailView
            loadStepSubmissionData(stepNumber);
            
            // Load admin comment for this step at the top
            loadTopAdminComment(stepNumber);
            
            // Scroll to top
            window.scrollTo(0, 0);
        }

        function generateRequirementCard(requirement, stepNumber, index) {
            // Get submission status from already loaded submissionData instead of calling async function
            const documentId = requirement.documentId;
            const submissionStatus = getSubmissionStatusSync(documentId, stepNumber);
            const maxAttempts = requirement.maxAttempts || 1;
            const remainingAttempts = maxAttempts - submissionStatus.attempts;
            const documentNumber = index + 1; // Convert 0-based index to 1-based document number
            
            let html = `
                <div class="requirement-card">
                    <div class="requirement-header">
                        <div class="requirement-title">${requirement.title}</div>
                        <div class="requirement-description">${requirement.description}</div>
            `;
            
            if (requirement.link) {
                html += `
                    <div>
                        <a href="${requirement.link}" target="_blank" class="requirement-link">
                            ${requirement.link}
                        </a>
                    </div>
                `;
            }
            
            // Add uploaded documents section below each requirement
            html += `
                <div id="userDocuments_${stepNumber}_${documentId}" class="requirement-documents-section" style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #6ea4ce;">
                    <div style="color: #666; font-style: italic; font-size: 14px;">📄 Loading uploaded documents...</div>
                </div>
            `;
            
            html += `
                    </div>
                    <div class="requirement-content">
            `;
            
            // Add submission status
            html += generateSubmissionStatus(submissionStatus, maxAttempts, remainingAttempts);
            
            // Add mobile app compatible UI elements
            html += generateMobileAppUIElements(requirement, stepNumber, documentNumber, submissionStatus);
            
            // Add upload form if not a status-only or form-only requirement
            // Skip upload forms for special interfaces (ethical form, matching interface)
            if (!requirement.isStatus && 
                !requirement.isForm && 
                !requirement.isEthicalForm && 
                !requirement.isMatchingInterface && 
                !requirement.isPAPView && 
                !requirement.isPAPDownload &&
                !requirement.hasAppointmentScheduling) {
                html += generateUploadForm(requirement, stepNumber, index, submissionStatus, remainingAttempts);
            } else if (requirement.isForm && requirement.link) {
                html += generateFormSection(requirement);
            } else if (requirement.isEthicalForm) {
                // Ethical form placeholder - will be replaced by loadEthicalPreferenceForm
                html += '<div style="color: #666; font-style: italic;">Loading ethical preference form...</div>';
            } else if (requirement.isMatchingInterface) {
                // Matching interface placeholder - will be replaced by loadEthicalMatchingInterface
                html += '<div style="color: #666; font-style: italic;">Loading ethical matching interface...</div>';
            } else if (requirement.hasAppointmentScheduling) {
                // Appointment scheduling placeholder - will be replaced by loadAppointmentScheduling
                html += '<div style="color: #666; font-style: italic;">Loading appointment scheduling...</div>';
            }
            
            html += `
                    </div>
                </div>
            `;
            
            return html;
        }

        // SYNCHRONOUS function to get submission status from already loaded submissionData
        function getSubmissionStatusSync(documentId, stepNumber) {
            console.log(`🔍 SYNC: Getting submission status for ${documentId} in step ${stepNumber}`);
            
            // Check if we have loaded submission data for this step
            if (!submissionData[stepNumber] || !submissionData[stepNumber][documentId]) {
                console.log(`❌ SYNC: No submission data found for ${documentId} in step ${stepNumber}`);
                return {
                    submitted: false,
                    attempts: 0,
                    status: 'not_submitted',
                    lastSubmissionTimestamp: null,
                    fileName: null,
                    documentUrl: null,
                    hasDocument: false
                };
            }
            
            const statusData = submissionData[stepNumber][documentId];
            console.log(`✅ SYNC: Found submission status for ${documentId}:`, statusData);
            
            return {
                submitted: statusData.submitted || false,
                attempts: statusData.attempts || 0,
                status: statusData.status || 'not_submitted',
                lastSubmissionTimestamp: statusData.lastSubmissionTimestamp || null,
                fileName: statusData.fileName || null,
                documentUrl: statusData.documentUrl || null,
                hasDocument: !!(statusData.fileName || statusData.documentUrl)
            };
        }

        // MOBILE APP EXACT LOGIC: Check submission status like the mobile app
        function getSubmissionStatus(documentId, stepNumber) {
            console.log(`🔍 MOBILE APP LOGIC: Checking ${documentId} in step ${stepNumber}`);
            
            // Return a promise since we need to check Firebase collections
            return new Promise((resolve) => {
                const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
                
                if (!targetUserId || !db) {
                    console.log(`❌ No userId or db available`);
                    resolve({
                        submitted: false,
                        attempts: 0,
                        status: 'not_submitted',
                        lastSubmissionTimestamp: null,
                        fileName: null,
                        documentUrl: null,
                        hasDocument: false
                    });
                    return;
                }
                
                console.log(`📋 Checking Firebase for user: ${targetUserId}`);
                
                // STEP 1: Check if documents exist in step{X}_uploads (like mobile app)
                db.collection('adoption_progress')
                    .doc(targetUserId)
                    .collection(`step${stepNumber}_uploads`)
                    .get()
                    .then(uploadsSnapshot => {
                        const hasDocuments = !uploadsSnapshot.empty;
                        console.log(`📄 Step ${stepNumber} uploads exist: ${hasDocuments}`);
                        
                        if (hasDocuments) {
                            // STEP 2: Check submission status in user_submissions_status (like mobile app)
                            db.collection('user_submissions_status')
                                .doc(targetUserId)
                                .collection(`step${stepNumber}_documents`)
                                .doc(documentId)
                                .get()
                                .then(documentSnapshot => {
                                    const submitted = documentSnapshot.exists() ? 
                                        (documentSnapshot.data().submitted || false) : false;
                                    const attempts = documentSnapshot.exists() ? 
                                        (documentSnapshot.data().attempts || 0) : 0;
                                    const fileName = documentSnapshot.exists() ? 
                                        (documentSnapshot.data().fileName || null) : null;
                                    const documentUrl = documentSnapshot.exists() ? 
                                        (documentSnapshot.data().documentUrl || null) : null;
                                    const status = documentSnapshot.exists() ? 
                                        (documentSnapshot.data().status || 'not_submitted') : 'not_submitted';
                                    
                                    console.log(`✅ MOBILE APP RESULT: submitted=${submitted}, attempts=${attempts}, fileName=${fileName}`);
                                    
                                    resolve({
                                        submitted: submitted,
                                        attempts: attempts,
                                        status: submitted ? status : 'not_submitted',
                                        lastSubmissionTimestamp: documentSnapshot.exists() ? 
                                            (documentSnapshot.data().uploadedAt || null) : null,
                                        fileName: fileName,
                                        documentUrl: documentUrl,
                                        hasDocument: !!fileName || !!documentUrl
                                    });
                                })
                                .catch(error => {
                                    console.error(`❌ Error checking submission status:`, error);
                                    resolve({
                                        submitted: false,
                                        attempts: 0,
                                        status: 'not_submitted',
                                        lastSubmissionTimestamp: null,
                                        fileName: null,
                                        documentUrl: null,
                                        hasDocument: false
                                    });
                                });
                        } else {
                            // No documents uploaded for this step
                            console.log(`❌ No documents found for step ${stepNumber}`);
                            resolve({
                                submitted: false,
                                attempts: 0,
                                status: 'not_submitted',
                                lastSubmissionTimestamp: null,
                                fileName: null,
                                documentUrl: null,
                                hasDocument: false
                            });
                        }
                    })
                    .catch(error => {
                        console.error(`❌ Error checking step uploads:`, error);
                        resolve({
                            submitted: false,
                            attempts: 0,
                            status: 'not_submitted',
                            lastSubmissionTimestamp: null,
                            fileName: null,
                            documentUrl: null,
                            hasDocument: false
                        });
                    });
            });
        }

        function generateSubmissionStatus(submissionStatus, maxAttempts, remainingAttempts) {
            let statusClass, statusText, statusIcon;
            
            // Check for submission in multiple ways to ensure persistence
            const isSubmitted = submissionStatus.submitted || 
                               submissionStatus.status === 'submitted' ||
                               submissionStatus.status === 'pending_review' ||
                               submissionStatus.status === 'approved' ||
                               submissionStatus.fileName ||
                               submissionStatus.documentUrl;
            
            if (isSubmitted && submissionStatus.status === 'approved') {
                statusClass = 'submitted';
                statusText = 'SUBMITTED & APPROVED';
                statusIcon = '✓';
            } else if (isSubmitted && submissionStatus.status === 'pending_review') {
                statusClass = 'pending';
                statusText = 'SUBMITTED - PENDING REVIEW';
                statusIcon = '⏳';
            } else if (isSubmitted) {
                statusClass = 'submitted';
                statusText = 'SUBMITTED';
                statusIcon = '✓';
            } else {
                statusClass = 'not-submitted';
                statusText = 'NOT SUBMITTED';
                statusIcon = '○';
            }
            
            let attemptsText = '';
            if (maxAttempts > 1) {
                if (submissionStatus.submitted && remainingAttempts > 0) {
                    attemptsText = `You can submit ${remainingAttempts} more time(s).`;
                } else if (remainingAttempts > 0) {
                    attemptsText = `You can submit ${remainingAttempts} more time(s).`;
                } else {
                    attemptsText = 'No more submission attempts remaining.';
                }
            }
            
            // Add View Document button if document exists
            let viewDocumentButton = '';
            if (submissionStatus.submitted && submissionStatus.documentUrl) {
                viewDocumentButton = `
                    <div class="view-document-section" style="margin-top: 10px;">
                        <button class="view-document-button" onclick="viewDocument('${submissionStatus.documentUrl}', '${submissionStatus.fileName || 'Document'}')">
                            👁️ View Document
                        </button>
                    </div>
                `;
            }

            return `
                <div class="submission-status">
                    <div class="status-icon-large ${statusClass}">
                        ${statusIcon}
                    </div>
                    <div class="status-details">
                        <div class="status-text-large ${statusClass}">${statusText}</div>
                        ${attemptsText ? `<div class="attempts-text">${attemptsText}</div>` : ''}
                        ${submissionStatus.lastSubmissionTimestamp ? 
                            `<div class="attempts-text">Last submitted: ${new Date(submissionStatus.lastSubmissionTimestamp).toLocaleDateString()}</div>` : ''}
                        ${viewDocumentButton}
                    </div>
                </div>
            `;
        }

        function generateUploadForm(requirement, stepNumber, index, submissionStatus, remainingAttempts) {
            const formId = `uploadForm_${stepNumber}_${index}`;
            const fileInputId = `fileInput_${stepNumber}_${index}`;
            const canUpload = remainingAttempts > 0;
            
            let html = `
                <div class="upload-section">
            `;
            
            if (submissionStatus.submitted && submissionStatus.fileName) {
                html += `
                    <div class="selected-file">
                        📄 Last submitted: ${submissionStatus.fileName}
                    </div>
                `;
                
                if (canUpload) {
                    html += `
                        <button class="reupload-button" onclick="showUploadForm('${formId}')">
                            Re-upload Document
                        </button>
                    `;
                }
            }
            
            const formStyle = (submissionStatus.submitted && canUpload) ? 'style="display: none;"' : '';
            
            if (canUpload || !submissionStatus.submitted) {
                html += `
                    <div id="${formId}" class="upload-form" ${formStyle}>
                        <div class="file-input-wrapper">
                            <input 
                                type="file" 
                                id="${fileInputId}" 
                                class="file-input" 
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                onchange="handleFileSelect(event, '${fileInputId}')"
                            >
                            <label for="${fileInputId}" class="file-input-label">
                                📁 Choose File
                            </label>
                        </div>
                        <div id="selectedFileName_${fileInputId}" class="selected-file" style="display: none;"></div>
                        <button 
                            type="button" 
                            class="upload-button" 
                            onclick="uploadDocument('${requirement.documentId}', '${fileInputId}', ${stepNumber})"
                            disabled
                        >
                            Upload Document
                        </button>
                    </div>
                `;
            }
            
            html += `
                </div>
            `;
            
            return html;
        }

        function generateFormSection(requirement) {
            return `
                <div class="form-link-section">
                    <div class="form-link-title">📝 Complete Online Form</div>
                    <p>Please complete the online form at the link provided above, then return here to mark as completed.</p>
                    <a href="${requirement.link}" target="_blank" class="form-submit-button">
                        Open Form
                    </a>
                </div>
            `;
        }

        function generateMobileAppUIElements(requirement, stepNumber, documentNumber, submissionStatus) {
            // Generate mobile app compatible UI elements that replace the regular status display
            const isSubmitted = submissionStatus.submitted || 
                               submissionStatus.status === 'submitted' ||
                               submissionStatus.status === 'pending_review' ||
                               submissionStatus.status === 'approved' ||
                               submissionStatus.fileName ||
                               submissionStatus.documentUrl;
            const statusText = isSubmitted ? 'Submitted' : 'Upload your document';
            const statusColor = isSubmitted ? '#4CAF50' : '#666';
            const checkIconDisplay = isSubmitted ? 'inline-block' : 'none';
            
            return `
                <div class="mobile-app-status-section" style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid ${isSubmitted ? '#4CAF50' : '#ccc'};">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <div id="checkIcon_${stepNumber}_${documentNumber}" class="mobile-check-icon" style="display: ${checkIconDisplay}; color: #4CAF50; font-size: 20px; font-weight: bold;">✓</div>
                        <div id="statusText_${stepNumber}_${documentNumber}" class="mobile-status-text" style="color: ${statusColor}; font-weight: bold; font-size: 16px;">${statusText}</div>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <button id="uploadBtn_${stepNumber}_${documentNumber}" class="mobile-upload-btn" style="
                            background: #6EC6FF; 
                            color: white; 
                            border: none; 
                            padding: 10px 20px; 
                            border-radius: 6px; 
                            cursor: pointer; 
                            font-weight: bold;
                        ">Upload Document</button>
                    </div>
                    <div id="attemptsText_${stepNumber}_${documentNumber}" class="mobile-attempts-text" style="display: none; color: #666; font-size: 14px;"></div>
                </div>
            `;
        }

        function refreshStepDetailView(stepNumber) {
            // Only refresh the requirements section, don't change the view state
            console.log(`Refreshing step detail view for step ${stepNumber}`);
            const stepDef = stepDefinitions.find(step => step.number === stepNumber);
            if (!stepDef) {
                console.error(`Step definition not found for step ${stepNumber}`);
                return;
            }
            
            const requirementsContainer = document.getElementById('stepRequirements');
            if (requirementsContainer) {
                requirementsContainer.innerHTML = generateStepRequirementsHTML(stepNumber, stepDef);
                
                // TASK FIX: Load special interfaces (ethical form, matching interface, appointment scheduling)
                console.log('🔧 SPECIAL INTERFACE DEBUG: Starting special interface loading for step', stepNumber);
                if (stepDef && stepDef.requirements) {
                    console.log('📋 Found', stepDef.requirements.length, 'requirements for step', stepNumber);
                    stepDef.requirements.forEach((requirement, index) => {
                        console.log(`🔍 Requirement ${index + 1}:`, requirement.title, 'isEthicalForm:', requirement.isEthicalForm, 'isMatchingInterface:', requirement.isMatchingInterface);
                        
                        // Add special interface loading logic that was missing
                        setTimeout(() => {
                            console.log(`⏰ Processing requirement ${index + 1} after timeout`);
                            
                            // Add matching interface for Stage 7
                            if (requirement.isMatchingInterface) {
                                console.log('🧩 Loading matching interface for requirement:', requirement.documentId);
                                const matchingArea = document.createElement('div');
                                matchingArea.id = `matchingInterface_${stepNumber}_${requirement.documentId}`;
                                matchingArea.style.cssText = 'margin-top: 15px;';
                                
                                // Find the requirement card and append the matching interface
                                const requirementCards = requirementsContainer.querySelectorAll('.requirement-card');
                                console.log('📋 Found', requirementCards.length, 'requirement cards');
                                if (requirementCards[index]) {
                                    requirementCards[index].appendChild(matchingArea);
                                    console.log('✅ Appended matching area to card', index);
                                } else {
                                    console.error('❌ Could not find requirement card at index', index);
                                }
                                
                                // Load ethical matching interface
                                setTimeout(() => {
                                    console.log('🔄 Calling loadEthicalMatchingInterface...');
                                    loadEthicalMatchingInterface(stepNumber, requirement.documentId, matchingArea);
                                }, 200);
                            }
                            
                            // Add ethical form handling for Stage 6
                            if (requirement.isEthicalForm) {
                                console.log('📝 Loading ethical form for requirement:', requirement.documentId);
                                const formArea = document.createElement('div');
                                formArea.id = `ethicalForm_${stepNumber}_${requirement.documentId}`;
                                formArea.style.cssText = 'margin-top: 15px;';
                                
                                // Find the requirement card and append the ethical form
                                const requirementCards = requirementsContainer.querySelectorAll('.requirement-card');
                                console.log('📋 Found', requirementCards.length, 'requirement cards');
                                if (requirementCards[index]) {
                                    requirementCards[index].appendChild(formArea);
                                    console.log('✅ Appended ethical form area to card', index);
                                } else {
                                    console.error('❌ Could not find requirement card at index', index);
                                }
                                
                                // Load ethical preference form
                                setTimeout(() => {
                                    console.log('🔄 Calling loadEthicalPreferenceForm...');
                                    loadEthicalPreferenceForm(stepNumber, requirement.documentId, formArea);
                                }, 200);
                            }
                            
                            // Add appointment scheduling for Stage 7 selection
                            if (requirement.hasAppointmentScheduling) {
                                console.log('📅 Loading appointment scheduling for requirement:', requirement.documentId);
                                const appointmentArea = document.createElement('div');
                                appointmentArea.id = `appointmentScheduling_${stepNumber}_${requirement.documentId}`;
                                appointmentArea.style.cssText = 'margin-top: 15px;';
                                
                                // Find the requirement card and append the appointment scheduling
                                const requirementCards = requirementsContainer.querySelectorAll('.requirement-card');
                                console.log('📋 Found', requirementCards.length, 'requirement cards');
                                if (requirementCards[index]) {
                                    requirementCards[index].appendChild(appointmentArea);
                                    console.log('✅ Appended appointment area to card', index);
                                } else {
                                    console.error('❌ Could not find requirement card at index', index);
                                }
                                
                                // Load appointment scheduling interface
                                setTimeout(() => {
                                    console.log('🔄 Calling loadAppointmentScheduling...');
                                    loadAppointmentScheduling(stepNumber, requirement.documentId, appointmentArea);
                                }, 200);
                            }
                        }, 100 + (index * 50)); // Stagger the interface loading
                    });
                } else {
                    console.error('❌ No step definition or requirements found for step', stepNumber);
                }
                
                // After generating the HTML, update mobile app UI elements with loaded data
                updateMobileAppUIElements(stepNumber);
                
                // Load documents for each requirement (like admin view)
                console.log(`🔄 Loading documents for step ${stepNumber} requirements`);
                const stepDef = stepDefinitions.find(step => step.number === stepNumber);
                if (stepDef && stepDef.requirements) {
                    stepDef.requirements.forEach((requirement, index) => {
                        const documentId = requirement.documentId;
                        const documentNumber = index + 1;
                        console.log(`📄 Loading documents for requirement ${documentNumber}: ${documentId}`);
                        
                        // Load documents for this specific requirement
                        setTimeout(() => {
                            loadUserDocumentsForRequirement(stepNumber, documentId);
                        }, 300 + (index * 100)); // Stagger the loading after interface loading
                    });
                }
                
                // Admin comments are only shown at the top (topAdminCommentSection), not per requirement
            }
        }
        
        function loadTopAdminComment(stepNumber) {
            console.log(`📝 TOP ADMIN COMMENT: Loading for step ${stepNumber}`);
            
            // Get the target user ID (the user whose progress we're viewing)
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId || !db) {
                console.log(`❌ Missing targetUserId or db for step ${stepNumber}`);
                return;
            }
            
            const topAdminCommentSection = document.getElementById('topAdminCommentSection');
            const topAdminCommentText = document.getElementById('topAdminCommentText');
            
            if (!topAdminCommentSection || !topAdminCommentText) {
                console.log(`❌ Top admin comment elements not found`);
                return;
            }
            
            // Load from Firebase: adoption_progress/{userId}/comments/step{X}
            db.collection('adoption_progress').doc(targetUserId)
                .collection('comments').doc(`step${stepNumber}`)
                .get()
                .then(doc => {
                    console.log(`📋 TOP Firebase response - document exists: ${doc.exists}`);
                    
                    if (doc.exists) {
                        const data = doc.data();
                        const comment = data.comment;
                        
                        if (comment && comment.trim()) {
                            console.log(`✅ TOP SUCCESS: Found admin comment: "${comment}"`);
                            topAdminCommentText.textContent = comment;
                            topAdminCommentSection.style.display = 'block';
                        } else {
                            console.log(`⚠️ TOP Empty comment field`);
                            topAdminCommentSection.style.display = 'none';
                        }
                    } else {
                        console.log(`⚠️ TOP No comment document exists`);
                        topAdminCommentSection.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error(`❌ TOP Firebase error loading comment for step ${stepNumber}:`, error);
                    topAdminCommentSection.style.display = 'none';
                });
        }

        function loadAdminCommentsForStep(stepNumber, targetUserId) {
            console.log(`📝 MOBILE APP STYLE: Loading admin comments for step ${stepNumber}, user ${targetUserId}`);
            
            if (!targetUserId || !db) {
                console.log(`❌ Missing targetUserId or db for step ${stepNumber}`);
                return;
            }
            
            // Load from Firebase first
            db.collection('adoption_progress').doc(targetUserId)
                .collection('comments').doc(`step${stepNumber}`)
                .get()
                .then(doc => {
                    console.log(`📋 Firebase response - document exists: ${doc.exists}`);
                    
                    let comment = '';
                    if (doc.exists) {
                        const data = doc.data();
                        comment = data.comment;
                        
                        if (comment && comment.trim()) {
                            console.log(`✅ SUCCESS: Found admin comment: "${comment}"`);
                        } else {
                            console.log(`⚠️ Empty comment field`);
                            comment = '';
                        }
                    } else {
                        console.log(`⚠️ No comment document exists`);
                        comment = '';
                    }
                    
                    // EXACTLY LIKE MOBILE APP: Find all admin comment sections for this step and update them
                    // Each requirement has its own admin comment section: adminCommentSection_{stepNumber}_{documentNumber}
                    const stepDef = stepDefinitions.find(step => step.number === stepNumber);
                    if (stepDef && stepDef.requirements) {
                        stepDef.requirements.forEach((requirement, index) => {
                            const documentNumber = index + 1;
                            const adminCommentSection = document.getElementById(`adminCommentSection_${stepNumber}_${documentNumber}`);
                            const adminCommentText = document.getElementById(`adminCommentText_${stepNumber}_${documentNumber}`);
                            
                            console.log(`🔍 Checking requirement ${documentNumber}: section=${!!adminCommentSection}, text=${!!adminCommentText}`);
                            
                            if (adminCommentSection && adminCommentText) {
                                if (comment && comment.trim()) {
                                    // Show comment - EXACTLY LIKE MOBILE APP
                                    adminCommentText.textContent = comment;
                                    adminCommentSection.style.display = 'flex';
                                    console.log(`✅ Admin comment displayed for req ${documentNumber}: "${comment}"`);
                                } else {
                                    // Hide section when no comment - EXACTLY LIKE MOBILE APP
                                    adminCommentSection.style.display = 'none';
                                    console.log(`📵 Admin comment hidden for req ${documentNumber} (no comment)`);
                                }
                            } else {
                                console.log(`❌ Admin comment elements not found for req ${documentNumber}`);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error(`❌ Firebase error loading comment for step ${stepNumber}:`, error);
                    // Hide all admin comment sections on error - like mobile app
                    const stepDef = stepDefinitions.find(step => step.number === stepNumber);
                    if (stepDef && stepDef.requirements) {
                        stepDef.requirements.forEach((requirement, index) => {
                            const documentNumber = index + 1;
                            const adminCommentSection = document.getElementById(`adminCommentSection_${stepNumber}_${documentNumber}`);
                            if (adminCommentSection) {
                                adminCommentSection.style.display = 'none';
                            }
                        });
                    }
                });
        }

        // MOBILE APP EXACT LOGIC: Update UI elements like the mobile app
        function updateMobileAppUIElements(stepNumber) {
            console.log(`🔄 MOBILE APP LOGIC: Updating UI elements for step ${stepNumber}`);
            
            const stepDef = stepDefinitions.find(step => step.number === stepNumber);
            if (!stepDef || !stepDef.requirements) {
                console.log(`❌ No step definition found for step ${stepNumber}`);
                return;
            }
            
            console.log(`📋 Step ${stepNumber} has ${stepDef.requirements.length} requirements`);
            
            // Iterate through each requirement and update UI with submission data
            stepDef.requirements.forEach((requirement, index) => {
                const documentId = requirement.documentId;
                const documentNumber = index + 1; // Convert 0-based index to 1-based document number
                
                console.log(`🔍 MOBILE APP: Checking requirement ${documentNumber}: ${documentId}`);
                
                // USE SYNCHRONOUS getSubmissionStatus function (from already loaded data)
                const submissionStatus = getSubmissionStatusSync(documentId, stepNumber);
                const isSubmitted = submissionStatus.submitted;
                
                console.log(`📊 MOBILE APP submission status:`, submissionStatus);
                
                if (isSubmitted) {
                    console.log(`✅ Document ${documentNumber} (${documentId}) is submitted - updating UI`);
                    
                    // Use the existing mobile app UI elements that are generated by generateMobileAppUIElements
                    const checkIcon = document.getElementById(`checkIcon_${stepNumber}_${documentNumber}`);
                    const statusText = document.getElementById(`statusText_${stepNumber}_${documentNumber}`);
                    const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${documentNumber}`);
                    const attemptsText = document.getElementById(`attemptsText_${stepNumber}_${documentNumber}`);
                    
                    console.log(`   UI Elements:`, {
                        checkIcon: !!checkIcon,
                        statusText: !!statusText,
                        uploadBtn: !!uploadBtn,
                        attemptsText: !!attemptsText
                    });
                    
                    if (checkIcon && statusText && uploadBtn) {
                        // EXACTLY LIKE MOBILE APP - Show submitted status
                        checkIcon.style.display = 'inline-block';
                        statusText.textContent = 'Submitted';
                        statusText.style.color = '#4CAF50';
                        
                        const maxAttempts = requirement.maxAttempts || 3;
                        const attempts = submissionStatus.attempts || 0;
                        const remainingAttempts = maxAttempts - attempts;
                        
                        if (remainingAttempts > 0) {
                            // Can still reupload
                            uploadBtn.textContent = 'RE-UPLOAD DOCUMENT';
                            uploadBtn.style.background = '#6EC6FF';
                            uploadBtn.disabled = false;
                            uploadBtn.style.opacity = '1';
                            
                            if (attemptsText) {
                                attemptsText.textContent = `You can submit ${remainingAttempts} more time(s).`;
                                attemptsText.style.display = 'block';
                                attemptsText.style.color = '#666';
                            }
                            
                            // Re-upload functionality
                            uploadBtn.onclick = () => {
                                console.log(`🔄 Initiating re-upload for document ${documentNumber}`);
                                handleMobileDocumentUpload(stepNumber, documentId, documentNumber);
                            };
                        } else {
                            // No more attempts
                            uploadBtn.textContent = 'NO MORE UPLOADS';
                            uploadBtn.style.background = '#ccc';
                            uploadBtn.disabled = true;
                            uploadBtn.style.opacity = '0.6';
                            
                            if (attemptsText) {
                                attemptsText.textContent = 'No more submissions allowed.';
                                attemptsText.style.display = 'block';
                                attemptsText.style.color = '#f44336';
                            }
                        }
                        
                        console.log(`✅ Updated UI for requirement ${documentNumber} - Submitted with ${remainingAttempts} attempts remaining`);
                    } else {
                        console.log(`❌ Could not find UI elements for requirement ${documentNumber}`);
                    }
                } else {
                    console.log(`❌ No submitted data found for ${documentId} in step ${stepNumber}`);
                    
                    // Ensure UI shows "not submitted" state
                    const checkIcon = document.getElementById(`checkIcon_${stepNumber}_${documentNumber}`);
                    const statusText = document.getElementById(`statusText_${stepNumber}_${documentNumber}`);
                    const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${documentNumber}`);
                    const attemptsText = document.getElementById(`attemptsText_${stepNumber}_${documentNumber}`);
                    
                    if (checkIcon && statusText && uploadBtn) {
                        checkIcon.style.display = 'none';
                        statusText.textContent = 'Upload your document';
                        statusText.style.color = '#666';
                        uploadBtn.textContent = 'Upload Document';
                        uploadBtn.style.background = '#6EC6FF';
                        uploadBtn.disabled = false;
                        uploadBtn.style.opacity = '1';
                        
                        if (attemptsText) {
                            attemptsText.style.display = 'none';
                        }
                        
                        uploadBtn.onclick = () => {
                            handleMobileDocumentUpload(stepNumber, documentId, documentNumber);
                        };
                    }
                }
            });
        }

        function generateStepRequirementsHTML(stepNumber, stepDef) {
            let html = '';
            
            // Generate requirement cards
            stepDef.requirements.forEach((requirement, index) => {
                html += generateRequirementCard(requirement, stepNumber, index);
            });
            
            // Admin comments are now handled per requirement in generateMobileAppUIElements
            
            return html;
        }

        // Test function to check Firebase connectivity
        function testFirebaseConnection() {
            console.log('🔥 Testing Firebase connection...');
            console.log('Auth:', auth);
            console.log('DB:', db);
            console.log('Storage:', storage);
            console.log('Current user:', auth.currentUser);
            
            if (!auth.currentUser) {
                console.error('❌ No authenticated user');
                return;
            }
            
            // Test Firestore write
            db.collection('test').doc('web_test').set({
                timestamp: Date.now(),
                message: 'Web test'
            }).then(() => {
                console.log('✅ Firestore write test successful');
            }).catch(error => {
                console.error('❌ Firestore write test failed:', error);
            });
        }

        function goBackToStepsList() {
            // Hide detail view
            const detailView = document.getElementById('stepDetailView');
            if (detailView) {
                detailView.style.display = 'none';
            }
            
            // Show the main step cards view - use appropriate container
            const contentContainerId = (isAdminUser && currentSelectedUser) ? 'adminStepsContent' : 'stepsContent';
            const contentContainer = document.getElementById(contentContainerId);
            if (contentContainer) {
                contentContainer.style.display = 'block';
            } else {
                console.warn(`Steps content container not found: ${contentContainerId}`);
            }
            
            currentViewStep = null;
        }

        function showAdminComment(comment) {
            const commentSection = document.getElementById('adminCommentSection');
            const commentText = document.getElementById('adminCommentText');
            
            if (commentSection && commentText) {
                commentText.textContent = comment;
                commentSection.classList.add('visible');
            }
        }

        function showUploadForm(formId) {
            document.getElementById(formId).style.display = 'block';
        }

        function viewDocument(documentUrl, fileName) {
            console.log('Opening document:', fileName, 'URL:', documentUrl);
            
            // Check if it's an image or document
            const isImage = fileName && (fileName.toLowerCase().includes('.jpg') || 
                                       fileName.toLowerCase().includes('.jpeg') || 
                                       fileName.toLowerCase().includes('.png') || 
                                       fileName.toLowerCase().includes('.gif'));
            
            if (isImage) {
                // For images, show in a modal/popup
                showImageModal(documentUrl, fileName);
            } else {
                // For documents (PDF, DOC, etc.), open in new tab
                window.open(documentUrl, '_blank');
            }
        }

        function showImageModal(imageUrl, fileName) {
            // Create modal overlay
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                cursor: pointer;
            `;
            
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                max-width: 90%;
                max-height: 90%;
                position: relative;
                background: white;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            `;
            
            const closeButton = document.createElement('button');
            closeButton.textContent = '✕ Close';
            closeButton.style.cssText = `
                position: absolute;
                top: 10px;
                right: 10px;
                background: #ff4444;
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            `;
            
            const image = document.createElement('img');
            image.src = imageUrl;
            image.alt = fileName;
            image.style.cssText = `
                max-width: 100%;
                max-height: 70vh;
                object-fit: contain;
                display: block;
                margin: 0 auto;
            `;
            
            const title = document.createElement('h3');
            title.textContent = fileName;
            title.style.cssText = `
                margin: 0 0 15px 0;
                text-align: center;
                color: #333;
            `;
            
            modalContent.appendChild(closeButton);
            modalContent.appendChild(title);
            modalContent.appendChild(image);
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Close modal when clicking overlay or close button
            modal.addEventListener('click', (e) => {
                if (e.target === modal || e.target === closeButton) {
                    document.body.removeChild(modal);
                }
            });
            
            // Prevent modal content clicks from closing modal
            modalContent.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        function handleFileSelect(event, fileInputId) {
            const file = event.target.files[0];
            const fileNameDiv = document.getElementById(`selectedFileName_${fileInputId}`);
            const uploadButton = event.target.closest('.upload-form').querySelector('.upload-button');
            
            if (file) {
                fileNameDiv.textContent = `📄 Selected: ${file.name}`;
                fileNameDiv.style.display = 'block';
                uploadButton.disabled = false;
            } else {
                fileNameDiv.style.display = 'none';
                uploadButton.disabled = true;
            }
        }

                // TASK 2: Enhanced upload function with mandatory step-by-step enforcement
                function uploadDocument(documentId, fileInputId, stepNumber) {
            const fileInput = document.getElementById(fileInputId);
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file to upload.');
                return;
            }
            
            // EXACT MOBILE APP LOGIC: Check authentication first
            if (!auth || !auth.currentUser) {
                showError('User not logged in. Please authenticate.');
                console.error('Upload failed: User not authenticated.');
                return;
            }
            
            const user = auth.currentUser;
            const targetUserId = user.uid;
            
            // TASK 2: Check if user can upload to this step (mandatory step-by-step)
            checkUploadPermission(targetUserId, stepNumber)
                .then(canUpload => {
                    if (!canUpload) {
                        showError(`❌ You must complete all previous steps before uploading to Step ${stepNumber}. Please complete the required documents for Step ${stepNumber - 1} first.`);
                        return;
                    }
                    
                    // Permission granted, proceed with upload
                    proceedWithDocumentUpload(documentId, fileInputId, stepNumber, targetUserId, file);
                })
                .catch(error => {
                    console.error('Error checking upload permission:', error);
                    showError('Error checking upload permission. Please try again.');
                });
        }
        
        // TASK 2: Separated upload logic after permission check
        function proceedWithDocumentUpload(documentId, fileInputId, stepNumber, targetUserId, file) {
            const fileInput = document.getElementById(fileInputId);
            
            console.log(`Uploading document ${documentId} for Step ${stepNumber}`);
            console.log(`User ID: ${targetUserId}`);
            
            // Show upload progress
            const uploadButton = fileInput.closest('.upload-form').querySelector('.upload-button');
            const originalText = uploadButton.textContent;
            uploadButton.textContent = 'Uploading...';
            uploadButton.disabled = true;
            
            // EXACT MOBILE APP LOGIC: Get username first
            db.collection("users")
                .doc(targetUserId)
                .get()
                .then(userDoc => {
                    const username = userDoc.data()?.username || targetUserId;
                    const fileName = `${username}-${documentId}-${Date.now()}`;
                    
                    // EXACT MOBILE APP STORAGE PATH: stepX_uploads/{userId}/{fileName}
                    const storageRef = firebase.storage().ref().child(`step${stepNumber}_uploads/${targetUserId}/${fileName}`);
                    console.log(`Starting upload for file: ${fileName} to path: ${storageRef.fullPath}`);
                    
                    // EXACT MOBILE APP UPLOAD: storageRef.put(file)
                    const uploadTask = storageRef.put(file);
                    
                    uploadTask.then(taskSnapshot => {
                        console.log('Document uploaded to Storage successfully. Getting download URL...');
                        return storageRef.getDownloadURL();
                    }).then(downloadURL => {
                        const fileUrl = downloadURL;
                        console.log('Download URL obtained:', fileUrl);
                        
                        // EXACT MOBILE APP FIRESTORE STRUCTURE
                        const documentData = {
                            "fileUrl": fileUrl,
                            "timestamp": Date.now(),
                            "fileName": fileName,
                            "status": "pending_review"
                        };
                        
                        // FIXED: Use unique document ID instead of overwriting same documentId
                        const uniqueDocId = `${documentId}_${Date.now()}`;
                        
                        // Save document metadata to Firestore under 'adoption_progress/{userId}/stepX_uploads/{uniqueDocId}'
                        return db.collection("adoption_progress")
                            .doc(targetUserId)
                            .collection(`step${stepNumber}_uploads`)
                            .doc(uniqueDocId)
                            .set(documentData, { merge: true })
                            .then(() => {
                                console.log(`Document details saved to Firestore for user: ${targetUserId}, document: ${uniqueDocId}`);
                                
                                // EXACT MOBILE APP SUBMISSION STATUS UPDATE - FIXED: Use same unique ID
                                const submissionStatusRef = db.collection("user_submissions_status")
                                    .doc(targetUserId)
                                    .collection(`step${stepNumber}_documents`)
                                    .doc(uniqueDocId);
                                
                                // Use a transaction to safely increment attempts
                                return db.runTransaction(transaction => {
                                    return transaction.get(submissionStatusRef).then(snapshot => {
                                        const currentAttempts = snapshot.exists ? (snapshot.data()?.attempts || 0) : 0;  // Fixed: removed ()
                                        const finalNewAttempts = currentAttempts + 1;
                                        
                                        transaction.set(submissionStatusRef, {
                                            "submitted": true,
                                            "attempts": finalNewAttempts,
                                            "lastSubmissionTimestamp": Date.now(),
                                            "lastFileUrl": fileUrl,
                                            "fileName": fileName,
                                            "status": "pending_review"
                                        });
                                        
                                        return finalNewAttempts;
                                    });
                                });
                            });
                    }).then((finalNewAttempts) => {
                        showSuccess('Document submitted! Admin will review.');
                        console.log(`Submission status updated successfully. Attempts: ${finalNewAttempts}`);
                        uploadButton.textContent = 'Upload Successful!';
                        
                        // TASK 2: Check and mark step as completed if all documents uploaded
                        setTimeout(() => {
                            checkAndMarkStepCompleted(targetUserId, stepNumber);
                        }, 1000);
                        
                        // Reset form after success
                        setTimeout(() => {
                            fileInput.value = '';
                            const selectedFileNameDiv = document.getElementById(`selectedFileName_${fileInputId}`);
                            if (selectedFileNameDiv) {
                                selectedFileNameDiv.style.display = 'none';
                            }
                            uploadButton.textContent = originalText;
                            uploadButton.disabled = true;
                            
                            // Reload submission data and refresh view
                            loadStepSubmissionData(stepNumber);
                            // Force UI update to show "Submitted" status immediately
                            setTimeout(() => {
                                // Update UI directly to show submitted state while reloading
                                const checkIcon = document.getElementById(`checkIcon_${stepNumber}_1`);
                                const statusText = document.getElementById(`statusText_${stepNumber}_1`);
                                const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_1`);
                                
                                if (checkIcon && statusText && uploadBtn) {
                                    checkIcon.style.display = 'inline-block';
                                    statusText.textContent = 'Submitted';
                                    statusText.style.color = '#4CAF50';
                                    uploadBtn.textContent = 'RE-UPLOAD DOCUMENT';
                                    uploadBtn.style.background = '#6EC6FF';
                                }
                                
                                // Then refresh the view with complete data
                                showStepDetailView(stepNumber);
                            }, 500);
                        }, 2000);
                        
                    }).catch(error => {
                        console.error('Document upload failed:', error);
                        showError(`Document upload failed: ${error.message}`);
                        uploadButton.textContent = originalText;
                        uploadButton.disabled = false;
                    });
                })
                .catch(error => {
                    console.error('Failed to fetch username:', error);
                    showError(`Failed to fetch username: ${error.message}`);
                    uploadButton.textContent = originalText;
                    uploadButton.disabled = false;
                });
        }
        

        


        function showConfirmationDialog(adoptionNumber, username = '<?php echo addslashes($currentUsername); ?>') {
            console.log('Showing confirmation dialog for adoption:', adoptionNumber);
            console.log('Current session user role:', window.sessionUserRole);
            console.log('User role type:', typeof window.sessionUserRole);
            console.log('Is admin check:', window.sessionUserRole === 'admin');
            
            // TASK 2: Hide Start Adoption Process for admin users
            if (window.sessionUserRole === 'admin') {
                console.log('Admin user - not showing Start Adoption Process dialog');
                showError('Administrators cannot start adoption processes. This feature is for users only.');
                return;
            }
            
            const dialog = document.getElementById('confirmationDialog');
            const title = document.getElementById('dialogTitle');
            const message = document.getElementById('dialogMessage');
            
            if (adoptionNumber === 1) {
                title.textContent = 'Start Adoption Process';
                message.textContent = 'Are you sure you want to begin the adoption process?';
            } else {
                title.textContent = `Start Adoption #${adoptionNumber}`;
                message.textContent = `Congratulations on completing your previous adoption!\n\nAre you ready to start Adoption #${adoptionNumber}?`;
            }
            
            dialog.style.display = 'flex';
            
            // Store adoption number for confirmation
            dialog.dataset.adoptionNumber = adoptionNumber;
            dialog.dataset.username = username;
        }

        function showNewAdoptionConfirmationDialog(userId) {
            console.log('Showing new adoption confirmation dialog for user:', userId);
            
            // Check if user is admin
            if (window.sessionUserRole === 'admin') {
                console.log('Admin user - not showing new adoption dialog');
                showError('Administrators cannot start adoption processes. This feature is for users only.');
                return;
            }
            
            // Create and show custom dialog for new adoption
            const dialog = document.getElementById('newAdoptionDialog');
            if (!dialog) {
                // Create the dialog if it doesn't exist
                createNewAdoptionDialog();
            }
            
            const newDialog = document.getElementById('newAdoptionDialog');
            const title = document.getElementById('newAdoptionDialogTitle');
            const message = document.getElementById('newAdoptionDialogMessage');
            
            title.textContent = '🎉 Adoption Process Completed!';
            message.innerHTML = `
                <p><strong>Congratulations!</strong> You have successfully completed all 11 steps of the adoption process.</p>
                <p>Your completed adoption has been saved in your history.</p>
                <br>
                <p><strong>Would you like to start a new adoption process?</strong></p>
                <p>This will begin a fresh adoption journey with all steps reset.</p>
            `;
            
            newDialog.style.display = 'flex';
            
            // Store user ID for confirmation
            newDialog.dataset.userId = userId;
        }

        function createNewAdoptionDialog() {
            console.log('Creating new adoption dialog');
            
            const dialogHTML = `
                <div id="newAdoptionDialog" class="new-adoption-dialog-overlay" style="display: none;">
                    <div class="new-adoption-dialog-content">
                        <div class="new-adoption-dialog-header">
                            <h3 id="newAdoptionDialogTitle">🎉 Adoption Process Completed!</h3>
                        </div>
                        <div class="new-adoption-dialog-body">
                            <div id="newAdoptionDialogMessage"></div>
                        </div>
                        <div class="new-adoption-dialog-footer">
                            <button class="new-adoption-btn new-adoption-btn-primary" onclick="confirmStartNewAdoption()">
                                <i class="fas fa-heart"></i> Yes, Start New Adoption
                            </button>
                            <button class="new-adoption-btn new-adoption-btn-secondary" onclick="closeNewAdoptionDialog()">
                                <i class="fas fa-times"></i> No, View Completed Adoption
                            </button>
                        </div>
                    </div>
                </div>
                <style>
                    .new-adoption-dialog-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.6);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 10000;
                        backdrop-filter: blur(5px);
                    }
                    
                    .new-adoption-dialog-content {
                        background: white;
                        border-radius: 16px;
                        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                        max-width: 500px;
                        width: 90%;
                        max-height: 80vh;
                        overflow-y: auto;
                        animation: dialogSlideIn 0.3s ease-out;
                    }
                    
                    @keyframes dialogSlideIn {
                        from {
                            opacity: 0;
                            transform: translateY(-50px) scale(0.9);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0) scale(1);
                        }
                    }
                    
                    .new-adoption-dialog-header {
                        padding: 24px 24px 16px;
                        border-bottom: 1px solid #e5e7eb;
                        text-align: center;
                    }
                    
                    .new-adoption-dialog-header h3 {
                        margin: 0;
                        font-size: 24px;
                        font-weight: 700;
                        color: #1f2937;
                        line-height: 1.2;
                    }
                    
                    .new-adoption-dialog-body {
                        padding: 24px;
                        text-align: center;
                    }
                    
                    .new-adoption-dialog-body p {
                        margin: 12px 0;
                        font-size: 16px;
                        line-height: 1.5;
                        color: #4b5563;
                    }
                    
                    .new-adoption-dialog-body p strong {
                        color: #1f2937;
                        font-weight: 600;
                    }
                    
                    .new-adoption-dialog-footer {
                        padding: 16px 24px 24px;
                        display: flex;
                        gap: 12px;
                        justify-content: center;
                        flex-wrap: wrap;
                    }
                    
                    .new-adoption-btn {
                        padding: 12px 24px;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        border: 2px solid transparent;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        min-width: 180px;
                        justify-content: center;
                    }
                    
                    .new-adoption-btn-primary {
                        background-color: #10b981;
                        color: white;
                        border-color: #10b981;
                    }
                    
                    .new-adoption-btn-primary:hover {
                        background-color: #059669;
                        border-color: #059669;
                        transform: translateY(-2px);
                        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
                    }
                    
                    .new-adoption-btn-secondary {
                        background-color: #f3f4f6;
                        color: #374151;
                        border-color: #d1d5db;
                    }
                    
                    .new-adoption-btn-secondary:hover {
                        background-color: #e5e7eb;
                        border-color: #9ca3af;
                        transform: translateY(-2px);
                        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                    }
                    
                    @media (max-width: 640px) {
                        .new-adoption-dialog-content {
                            width: 95%;
                            margin: 20px;
                        }
                        
                        .new-adoption-dialog-footer {
                            flex-direction: column;
                        }
                        
                        .new-adoption-btn {
                            width: 100%;
                            min-width: auto;
                        }
                    }
                </style>
            `;
            
            // Add to body
            document.body.insertAdjacentHTML('beforeend', dialogHTML);
        }

        function confirmStartNewAdoption() {
            const dialog = document.getElementById('newAdoptionDialog');
            const userId = dialog.dataset.userId;
            
            console.log('User confirmed starting new adoption for user:', userId);
            closeNewAdoptionDialog();
            
            // Call the existing reset function
            simpleResetAfterStep11(userId);
        }

        function closeNewAdoptionDialog() {
            const dialog = document.getElementById('newAdoptionDialog');
            if (dialog) {
                dialog.style.display = 'none';
            }
        }

        function confirmStartAdoption() {
            const dialog = document.getElementById('confirmationDialog');
            const adoptionNumber = parseInt(dialog.dataset.adoptionNumber);
            const username = dialog.dataset.username;
            
            console.log('Confirming start adoption:', adoptionNumber, username);
            closeDialog();
            saveInitialProgressToFirestore(adoptionNumber, username);
        }

        function closeDialog() {
            document.getElementById('confirmationDialog').style.display = 'none';
        }

        function saveInitialProgressToFirestore(adoptionNumber = 1, username = '<?php echo addslashes($currentUsername); ?>') {
            if (!db) {
                showError('Firebase not available. Cannot save progress.');
                return;
            }

            if (!userId) {
                showError('User is not authenticated. Cannot save initial progress.');
                return;
            }

            console.log('Saving initial progress to Firestore for adoption:', adoptionNumber);

            const initialProgressMap = getDefaultProgressStatus();

            if (adoptionNumber === 1) {
                // Create initial structure compatible with both old and new formats
                const userProgressData = {
                    adopt_progress: initialProgressMap,
                    username: username,
                    timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                    // Also create versioned structure for consistency with mobile app
                    currentAdoption: 1,
                    totalAdoptions: 1,
                    adoptions: {
                        "1": {
                            adopt_progress: initialProgressMap,
                            status: 'in_progress',
                            startedAt: firebase.firestore.FieldValue.serverTimestamp(),
                            adoptionNumber: 1
                        }
                    },
                    lastUpdated: firebase.firestore.FieldValue.serverTimestamp(),
                    currentStatus: 'in_progress' // Current adoption status for admin view
                };

                console.log('Creating initial adoption progress document with versioned structure');
                db.collection('adoption_progress').doc(userId)
                    .set(userProgressData)
                    .then(() => {
                        console.log('Initial adoption progress started and saved successfully with versioned structure!');
                        showSuccess('Adoption process started!');
                        
                        // Send adoption started notifications - FIREBASE IMPLEMENTATION
                        const userName = auth.currentUser?.displayName || auth.currentUser?.email?.split('@')[0] || 'User';
                        const currentUserId = auth.currentUser?.uid || '';
                        
                        console.log('🔔 Sending adoption started notification to Firebase for user:', currentUserId, 'name:', userName);
                        
                        // Send adoption started message via Firebase bridge
                        if (window.firebaseMessagingBridge) {
                            window.firebaseMessagingBridge.sendAdoptionStarted(currentUserId, 'system', userName, 'Social Worker')
                                .then(chatId => {
                                    console.log('✅ ADOPTION STARTED MESSAGE SENT TO CHAT!');
                                    console.log('Chat ID:', chatId);
                                })
                                .catch(error => {
                                    console.error('❌ Error sending adoption started message:', error);
                                });
                        } else {
                            console.error('❌ Firebase messaging bridge not available for adoption started message');
                        }
                        
                        // Create notifications directly in Firebase
                        const timestamp = firebase.firestore.FieldValue.serverTimestamp();
                        const timestampMs = Date.now();
                        
                        // 1. USER NOTIFICATION
                        const userNotification = {
                            userId: currentUserId,
                            title: '🎉 Adoption Started',
                            message: 'Congratulations! Your adoption process has been started successfully. Begin with Step 1 when you\'re ready!',
                            type: 'adoption',
                            status: 'process_started',
                            timestamp: timestamp,
                            timestampMs: timestampMs,
                            isRead: false,
                            processType: 'adoption',
                            notificationType: 'process_initiated',
                            icon: '👶',
                            data: {
                                action: 'adoption_started',
                                adoptionNumber: adoptionNumber
                            }
                        };
                        
                        // 2. ADMIN NOTIFICATION
                        const adminNotification = {
                            userId: 'h8qq0E8avWO74cqS2Goy1wtENJh1', // Admin user ID
                            title: '👶 New Adoption Process Started',
                            message: `${userName} has started adoption process #${adoptionNumber}. Please monitor their progress.`,
                            type: 'adoption',
                            status: 'admin_alert',
                            timestamp: timestamp,
                            timestampMs: timestampMs,
                            isRead: false,
                            processType: 'adoption',
                            notificationType: 'process_initiated',
                            isAdminNotification: true,
                            icon: '👶',
                            data: {
                                action: 'adoption_started',
                                targetUserId: currentUserId,
                                targetUserName: userName,
                                adoptionNumber: adoptionNumber
                            }
                        };
                        
                        // Send to Firebase notifications collection
                        Promise.all([
                            db.collection('notifications').add(userNotification),
                            db.collection('notifications').add(adminNotification),
                            db.collection('notification_logs').add({
                                action: 'adoption_started',
                                userId: currentUserId,
                                userName: userName,
                                adoptionNumber: adoptionNumber,
                                timestamp: timestamp,
                                userNotificationSent: true,
                                adminNotificationSent: true
                            })
                        ])
                        .then((results) => {
                            console.log('✅ ADOPTION STARTED NOTIFICATIONS SENT TO FIREBASE!');
                            console.log('User notification ID:', results[0].id);
                            console.log('Admin notification ID:', results[1].id);
                            console.log('Log entry ID:', results[2].id);
                            
                            // Create messaging connection with admin (matching mobile app behavior)
                            console.log('🔗 Creating messaging connection with admin...');
                            fetch('adoption_message_handler.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    action: 'adoption_started',
                                    userId: currentUserId,
                                    username: userName
                                })
                            })
                            .then(response => response.json())
                            .then(messageResult => {
                                if (messageResult.success) {
                                    console.log('✅ MESSAGING CONNECTION CREATED!');
                                    console.log('Message ID:', messageResult.messageId);
                                } else {
                                    console.error('❌ Failed to create messaging connection:', messageResult.error);
                                }
                            })
                            .catch(error => {
                                console.error('❌ Error creating messaging connection:', error);
                            });
                            
                            // Trigger notification reload
                            if (typeof loadNotifications === 'function') {
                                setTimeout(loadNotifications, 500); // Small delay to ensure Firebase has processed
                            }
                        })
                        .catch(error => {
                            console.error('❌ Failed to send Firebase notifications:', error);
                            console.error('Error details:', error.code, error.message);
                        });
                    })
                    .catch(error => {
                        console.error('Failed to start initial progress:', error);
                        showError(`Failed to start progress: ${error.message}`);
                    });
            } else {
                console.warn(`Attempting to manually create adoption #${adoptionNumber} - this should be automatic`);
                showError('Error: New adoptions should be created automatically after completion');
            }
        }

        function showLoading(show) {
            // Try both admin and regular loading message elements
            const adminLoadingElement = document.getElementById('adminLoadingMessage');
            const regularLoadingElement = document.getElementById('loadingMessage');
            
            if (adminLoadingElement && (isAdminUser && currentSelectedUser)) {
                // Use admin loading element when admin is viewing another user
                adminLoadingElement.style.display = show ? 'block' : 'none';
            } else if (regularLoadingElement) {
                // Use regular loading element for normal user view
                regularLoadingElement.style.display = show ? 'block' : 'none';
            } else {
                // Neither element found, just log and continue
                console.log('No loading message element found, continuing without loading indicator');
            }
        }

        function showError(message) {
            console.error('Error:', message);
            // Try both admin and regular error message elements
            const adminErrorElement = document.getElementById('adminErrorMessage');
            const regularErrorElement = document.getElementById('errorMessage');
            
            if (adminErrorElement && (isAdminUser && currentSelectedUser)) {
                // Use admin error element when admin is viewing another user
                adminErrorElement.textContent = message;
                adminErrorElement.style.display = 'block';
            } else if (regularErrorElement) {
                // Use regular error element for normal user view
                regularErrorElement.textContent = message;
                regularErrorElement.style.display = 'block';
            } else {
                // Neither element found, fallback to alert
                console.log('No error message element found, using alert fallback');
                alert('Error: ' + message);
            }
        }

        function showSuccess(message) {
            console.log('Success:', message);
            alert(message);
        }
        
        // Social Worker file selection handler
        function handleSocialWorkerFileSelect(event, fileInputId) {
            const file = event.target.files[0];
            const selectedFileNameDiv = document.getElementById(`selectedFileName_${fileInputId}`);
            const uploadButton = document.getElementById(`swUploadBtn_${fileInputId.split('_')[2]}_${fileInputId.split('_')[3]}`);
            
            if (file) {
                selectedFileNameDiv.textContent = `Selected: ${file.name}`;
                selectedFileNameDiv.style.display = 'block';
                uploadButton.disabled = false;
                uploadButton.style.opacity = '1';
            } else {
                selectedFileNameDiv.style.display = 'none';
                uploadButton.disabled = true;
                uploadButton.style.opacity = '0.6';
            }
        }
        
        // Social Worker document upload function
        function uploadSocialWorkerDocument(documentId, fileInputId, stepNumber, uploadConfig) {
            const fileInput = document.getElementById(fileInputId);
            const file = fileInput.files[0];
            const uploadButton = document.getElementById(`swUploadBtn_${stepNumber}_${documentId}`);
            
            if (!file) {
                alert('Please select a file to upload');
                return;
            }
            
            if (!currentSelectedUser) {
                alert('Please select a user first');
                return;
            }
            
            // Get status if this upload has status field
            let status = null;
            if (uploadConfig.hasStatusField) {
                const statusSelect = document.getElementById(`swStatus_${stepNumber}_${documentId}`);
                status = statusSelect.value;
                if (!status) {
                    alert('Please select a recommendation status');
                    return;
                }
            }
            
            const originalText = uploadButton.textContent;
            uploadButton.textContent = 'Uploading...';
            uploadButton.disabled = true;
            
            const targetUserId = currentSelectedUser.uid;
            const fileName = `SW-${stepNumber}-${documentId}-${Date.now()}.${file.name.split('.').pop()}`;
            
            // Upload to social worker specific storage path
            const storageRef = firebase.storage().ref().child(`social_worker_uploads/${targetUserId}/step${stepNumber}/${fileName}`);
            
            storageRef.put(file).then(snapshot => {
                return storageRef.getDownloadURL();
            }).then(downloadURL => {
                // Save to social worker uploads collection
                const documentData = {
                    fileName: fileName,
                    originalFileName: file.name,
                    fileUrl: downloadURL,
                    timestamp: Date.now(),
                    uploadedBy: userId, // Current admin/social worker ID
                    uploadType: documentId,
                    stepNumber: stepNumber,
                    targetUserId: targetUserId
                };
                
                // Add status if specified
                if (status) {
                    documentData.recommendationStatus = status;
                    documentData.status = status;
                }
                
                // Save to Firestore
                return db.collection('social_worker_uploads')
                    .doc(targetUserId)
                    .collection(`step${stepNumber}_uploads`)
                    .add(documentData);
            }).then(() => {
                alert('Document uploaded successfully!');
                uploadButton.textContent = originalText;
                uploadButton.disabled = false;
                
                // Clear file input
                fileInput.value = '';
                document.getElementById(`selectedFileName_${fileInputId}`).style.display = 'none';
                
                // Clear status if applicable
                if (uploadConfig.hasStatusField) {
                    document.getElementById(`swStatus_${stepNumber}_${documentId}`).value = '';
                }
                
                // Refresh existing uploads display
                const existingUploadsDiv = document.getElementById(`swExistingUploads_${stepNumber}_${documentId}`);
                if (existingUploadsDiv) {
                    loadExistingSocialWorkerUploads(stepNumber, documentId, existingUploadsDiv);
                }
                
                // Refresh PAP view status if applicable (for stage 5)
                if (stepNumber === 5) {
                    const statusArea = document.getElementById(`papViewStatus_${stepNumber}_home_study_report_status`);
                    if (statusArea) {
                        loadSocialWorkerDocumentStatus(stepNumber, 'home_study_report_status', statusArea, targetUserId);
                    }
                }
                
            }).catch(error => {
                console.error('Error uploading social worker document:', error);
                alert('Upload failed: ' + error.message);
                uploadButton.textContent = originalText;
                uploadButton.disabled = false;
            });
        }
        
        // Function to load existing social worker uploads
        function loadExistingSocialWorkerUploads(stepNumber, documentId, container) {
            if (!currentSelectedUser) {
                container.innerHTML = '<span style="color: #999;">No user selected</span>';
                return;
            }
            
            const targetUserId = currentSelectedUser.uid;
            
            db.collection('social_worker_uploads')
                .doc(targetUserId)
                .collection(`step${stepNumber}_uploads`)
                .where('uploadType', '==', documentId)
                .orderBy('timestamp', 'desc')
                .get()
                .then(querySnapshot => {
                    if (querySnapshot.empty) {
                        container.innerHTML = '<div style="color: #666; font-style: italic; font-size: 12px;">No previous uploads</div>';
                        return;
                    }
                    
                    let uploadsHTML = '<div style="font-weight: 600; margin-bottom: 10px; color: #333;">Previous Uploads:</div>';
                    
                    querySnapshot.forEach(doc => {
                        const data = doc.data();
                        const uploadDate = new Date(data.timestamp).toLocaleString();
                        const statusDisplay = data.recommendationStatus ? 
                            `<span style="color: ${data.recommendationStatus === 'Approved' ? '#4CAF50' : '#FF9800'}; font-weight: 600;">[${data.recommendationStatus}]</span>` : '';
                        
                        uploadsHTML += `
                            <div style="margin-bottom: 8px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                                <div style="font-weight: 600; margin-bottom: 2px;">${data.originalFileName} ${statusDisplay}</div>
                                <div style="color: #666;">Uploaded: ${uploadDate}</div>
                                <button 
                                    onclick="window.open('${data.fileUrl}', '_blank')"
                                    style="
                                        background: #2196F3;
                                        color: white;
                                        border: none;
                                        padding: 4px 8px;
                                        border-radius: 3px;
                                        cursor: pointer;
                                        font-size: 11px;
                                        margin-top: 4px;
                                    "
                                >
                                    View
                                </button>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = uploadsHTML;
                })
                .catch(error => {
                    console.error('Error loading existing social worker uploads:', error);
                    container.innerHTML = '<span style="color: #f44336;">Error loading uploads</span>';
                });
        }
        
        // Function to download document (for PAP downloads)
        function downloadDocument(fileUrl, fileName) {
            const link = document.createElement('a');
            link.href = fileUrl;
            link.download = fileName;
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Helper function to get requirement title from documentId
        function getRequirementTitle(stepNumber, documentId) {
            const stepDef = stepDefinitions.find(step => step.number === stepNumber);
            if (!stepDef || !stepDef.requirements) {
                return documentId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }
            
            const requirement = stepDef.requirements.find(req => req.documentId === documentId);
            if (requirement) {
                return requirement.title;
            }
            
            // Fallback to formatted documentId
            return documentId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }
        
        // TASK 1: Ethical Preference Form Implementation
        function loadEthicalPreferenceForm(stepNumber, documentId, container) {
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId) {
                container.innerHTML = '<span style="color: #999;">Please select a user to view preferences</span>';
                return;
            }
            
            // Check if preferences already exist
            db.collection('ethical_preferences').doc(targetUserId).get()
                .then(doc => {
                    const existingPrefs = doc.exists ? doc.data() : {};
                    
                    container.innerHTML = `
                        <div style="background: #f8fbff; border: 2px solid #e3f2fd; border-radius: 8px; padding: 20px;">
                            <h4 style="color: #1976d2; margin-bottom: 15px;">🌟 Ethical Child Preferences</h4>
                            <p style="color: #666; margin-bottom: 20px; font-style: italic;">
                                We use only meaningful and ethical criteria for matching. No discrimination based on appearance.
                            </p>
                            
                            <form id="ethicalPrefsForm" style="display: grid; gap: 15px;">
                                <div>
                                    <label style="font-weight: 600; color: #333;">Age Range Preference:</label>
                                    <select name="ageRange" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                        <option value="">Select Age Range</option>
                                        <option value="Infant (0-2)" ${existingPrefs.ageRange === 'Infant (0-2)' ? 'selected' : ''}>Infant (0-2)</option>
                                        <option value="Toddler (3-4)" ${existingPrefs.ageRange === 'Toddler (3-4)' ? 'selected' : ''}>Toddler (3-4)</option>
                                        <option value="Child (5-10)" ${existingPrefs.ageRange === 'Child (5-10)' ? 'selected' : ''}>Child (5-10)</option>
                                        <option value="Pre-teen (11-12)" ${existingPrefs.ageRange === 'Pre-teen (11-12)' ? 'selected' : ''}>Pre-teen (11-12)</option>
                                        <option value="Any" ${existingPrefs.ageRange === 'Any' ? 'selected' : ''}>Any Age</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="font-weight: 600; color: #333;">Gender Preference:</label>
                                    <select name="genderPreference" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                        <option value="">Select Gender</option>
                                        <option value="Male" ${existingPrefs.genderPreference === 'Male' ? 'selected' : ''}>Male</option>
                                        <option value="Female" ${existingPrefs.genderPreference === 'Female' ? 'selected' : ''}>Female</option>
                                        <option value="Any" ${existingPrefs.genderPreference === 'Any' ? 'selected' : ''}>Any Gender</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="font-weight: 600; color: #333;">Open to Siblings:</label>
                                    <select name="siblingsOpen" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                        <option value="">Select Option</option>
                                        <option value="Yes" ${existingPrefs.siblingsOpen === 'Yes' ? 'selected' : ''}>Yes, open to siblings</option>
                                        <option value="No" ${existingPrefs.siblingsOpen === 'No' ? 'selected' : ''}>No, single child only</option>
                                        <option value="Either" ${existingPrefs.siblingsOpen === 'Either' ? 'selected' : ''}>Either is fine</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="font-weight: 600; color: #333;">Open to Special Needs:</label>
                                    <select name="specialNeedsOpen" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                        <option value="">Select Option</option>
                                        <option value="Yes" ${existingPrefs.specialNeedsOpen === 'Yes' ? 'selected' : ''}>Yes, willing to support special needs</option>
                                        <option value="No" ${existingPrefs.specialNeedsOpen === 'No' ? 'selected' : ''}>No special needs preference</option>
                                        <option value="Mild" ${existingPrefs.specialNeedsOpen === 'Mild' ? 'selected' : ''}>Mild special needs only</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="font-weight: 600; color: #333;">Activity Interests:</label>
                                    <select name="activityInterests" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                        <option value="">Select Preference</option>
                                        <option value="Sports" ${existingPrefs.activityInterests === 'Sports' ? 'selected' : ''}>Sports & Physical Activities</option>
                                        <option value="Arts" ${existingPrefs.activityInterests === 'Arts' ? 'selected' : ''}>Arts & Crafts</option>
                                        <option value="Music" ${existingPrefs.activityInterests === 'Music' ? 'selected' : ''}>Music & Performance</option>
                                        <option value="Reading" ${existingPrefs.activityInterests === 'Reading' ? 'selected' : ''}>Reading & Learning</option>
                                        <option value="Any" ${existingPrefs.activityInterests === 'Any' ? 'selected' : ''}>Any Activities</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="font-weight: 600; color: #333;">Educational Background:</label>
                                    <select name="educationalBackground" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                        <option value="">Select Preference</option>
                                        <option value="Early learner" ${existingPrefs.educationalBackground === 'Early learner' ? 'selected' : ''}>Early learner (advanced for age)</option>
                                        <option value="Average" ${existingPrefs.educationalBackground === 'Average' ? 'selected' : ''}>Average development</option>
                                        <option value="Needs support" ${existingPrefs.educationalBackground === 'Needs support' ? 'selected' : ''}>Needs educational support</option>
                                        <option value="Any" ${existingPrefs.educationalBackground === 'Any' ? 'selected' : ''}>Any background</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="font-weight: 600; color: #333;">Additional Meaningful Preferences:</label>
                                    <textarea 
                                        name="additionalPreferences" 
                                        rows="3" 
                                        placeholder="Describe personality traits, hobbies, or other meaningful preferences..."
                                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px; resize: vertical;"
                                    >${existingPrefs.additionalPreferences || ''}</textarea>
                                </div>
                                
                                <button 
                                    type="button" 
                                    onclick="saveEthicalPreferences('${targetUserId}')"
                                    style="
                                        background: #4CAF50;
                                        color: white;
                                        border: none;
                                        padding: 12px 20px;
                                        border-radius: 6px;
                                        cursor: pointer;
                                        font-size: 16px;
                                        font-weight: 600;
                                        margin-top: 10px;
                                    "
                                >
                                    💾 Save Ethical Preferences
                                </button>
                            </form>
                            
                            ${existingPrefs.savedAt ? `
                                <div style="margin-top: 15px; padding: 10px; background: #e8f5e8; border-radius: 4px; color: #2e7d32;">
                                    ✅ Preferences saved on ${new Date(existingPrefs.savedAt).toLocaleString()}
                                </div>
                            ` : ''}
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error loading ethical preferences:', error);
                    container.innerHTML = '<span style="color: #f44336;">Error loading preferences form</span>';
                });
        }
        
        // Function to save ethical preferences
        function saveEthicalPreferences(targetUserId) {
            const form = document.getElementById('ethicalPrefsForm');
            const formData = new FormData(form);
            
            const preferences = {
                ageRange: formData.get('ageRange'),
                genderPreference: formData.get('genderPreference'),
                siblingsOpen: formData.get('siblingsOpen'),
                specialNeedsOpen: formData.get('specialNeedsOpen'),
                activityInterests: formData.get('activityInterests'),
                educationalBackground: formData.get('educationalBackground'),
                additionalPreferences: formData.get('additionalPreferences'),
                savedAt: Date.now(),
                savedBy: userId
            };
            
            // Validate required fields
            if (!preferences.ageRange || !preferences.genderPreference) {
                alert('Please fill in at least age range and gender preferences');
                return;
            }
            
            db.collection('ethical_preferences').doc(targetUserId).set(preferences)
                .then(() => {
                    alert('✅ Ethical preferences saved successfully!');
                    
                    // TASK 2: Mark Stage 6 as completed when preferences are saved
                    db.collection('adoption_progress').doc(targetUserId).update({
                        step6_completed: true,
                        step6_completion_date: Date.now(),
                        step6_ethical_preferences_saved: true
                    }).then(() => {
                        console.log('✅ Stage 6 marked as completed - ethical preferences saved');
                        
                        // Show completion notification
                        showStepCompletionNotification(6);
                        
                        // TASK 2: Send notification to admin about Stage 6 completion
                        // Get user information for notification
                        db.collection('users').doc(targetUserId).get()
                            .then(userDoc => {
                                let userName = 'User';
                                let userEmail = '';
                                
                                if (userDoc.exists) {
                                    const userData = userDoc.data();
                                    userName = userData.displayName || userData.name || userData.username || 'User';
                                    userEmail = userData.email || '';
                                    console.log('✅ Got user info for Stage 6 completion notification:', userName, userEmail);
                                } else {
                                    console.log('⚠️ No user data found for Stage 6 completion, using defaults');
                                }
                                
                                // Send step completion notification to admin
                                sendAdoptionNotification('step_completed', 6, {
                                    userId: targetUserId,
                                    stepNumber: 6,
                                    status: 'complete',
                                    userName: userName,
                                    userEmail: userEmail,
                                    stepType: 'ethical_preferences'
                                });
                                
                                console.log('📧 Stage 6 completion notification sent to admin');
                            })
                            .catch(error => {
                                console.log('❌ Error getting user info for Stage 6 notification:', error);
                                // Fallback: send notification without user info
                                sendAdoptionNotification('step_completed', 6, {
                                    userId: targetUserId,
                                    stepNumber: 6,
                                    status: 'complete',
                                    userName: 'User',
                                    userEmail: '',
                                    stepType: 'ethical_preferences'
                                });
                            });
                    }).catch(error => {
                        console.error('Error marking Stage 6 as completed:', error);
                    });
                    
                    // Trigger matching if this is a complete preference set
                    if (preferences.ageRange && preferences.genderPreference && preferences.siblingsOpen && preferences.specialNeedsOpen) {
                        console.log('Triggering ethical matching for user:', targetUserId);
                        triggerEthicalMatching(targetUserId, preferences);
                    }
                    
                    // Reload the form to show updated save time
                    setTimeout(() => {
                        loadEthicalPreferenceForm(6, 'child_preference_form', document.getElementById('ethicalForm_6_child_preference_form'));
                    }, 1000);
                })
                .catch(error => {
                    console.error('Error saving ethical preferences:', error);
                    alert('Error saving preferences: ' + error.message);
                });
        }

        // TASK 1: Ethical Matching Interface Implementation
        function loadEthicalMatchingInterface(stepNumber, documentId, container) {
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId) {
                container.innerHTML = '<span style="color: #999;">Please select a user to view matches</span>';
                return;
            }
            
            container.innerHTML = '<div style="text-align: center; padding: 20px;">🔍 Loading ethical matches...</div>';
            
            // First get the current adoption number
            db.collection('adoption_progress').doc(targetUserId).get()
                .then(adoptionDoc => {
                    const currentAdoptionNumber = adoptionDoc.exists && adoptionDoc.data().adoptions 
                        ? (adoptionDoc.data().currentAdoption || 1) 
                        : 1;
                    
                    console.log(`🔍 Checking selections for current adoption #${currentAdoptionNumber}`);
                    
                    // Check if user has already selected a child and scheduled an appointment for CURRENT adoption
                    db.collection('child_selections')
                        .where('userId', '==', targetUserId)
                        .where('adoptionNumber', '==', currentAdoptionNumber)
                        .get()
                        .then(selectionsSnapshot => {
                            if (!selectionsSnapshot.empty) {
                                // User has already selected a child in current adoption
                                const latestSelection = selectionsSnapshot.docs[selectionsSnapshot.docs.length - 1].data();
                                
                                // Check if they also have scheduled appointments for current adoption
                                db.collection('appointment_requests')
                                    .where('userId', '==', targetUserId)
                                    .where('adoptionNumber', '==', currentAdoptionNumber)
                                    .get()
                                    .then(appointmentsSnapshot => {
                                        if (!appointmentsSnapshot.empty) {
                                            // User has selected a child AND scheduled an appointment in current adoption - lock interface
                                            container.innerHTML = `
                                                <div style="background: #d4edda; border: 2px solid #c3e6cb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                                                    <h4 style="color: #155724; margin-bottom: 10px;">✅ Selection Complete</h4>
                                                    <p style="color: #155724; margin-bottom: 15px;">
                                                        You have already selected <strong>${latestSelection.childName}</strong> and scheduled a meeting for your current adoption process. 
                                                        The interface is now locked to prevent changes.
                                                    </p>
                                                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; margin-top: 15px;">
                                                        <p style="color: #6c757d; margin: 0; font-size: 14px;">
                                                            <strong>Note:</strong> If you need to reschedule your appointment, please use the "Schedule New Appointment" button in Stage 7 - Child Selection Confirmation below.
                                                        </p>
                                                    </div>
                                                </div>
                                            `;
                                            return;
                                        }
                                        
                                        // User has selected but not scheduled in current adoption - show selection info but allow scheduling
                                        loadMatchingInterfaceWithSelection(targetUserId, latestSelection, container);
                                    })
                                    .catch(error => {
                                        console.error('Error checking appointments:', error);
                                        container.innerHTML = '<span style="color: #f44336;">Error checking appointments</span>';
                                    });
                            } else {
                                // No selection made in current adoption - load normal interface
                                loadNormalMatchingInterface(targetUserId, container);
                            }
                        })
                        .catch(error => {
                            console.error('Error checking child selections:', error);
                            container.innerHTML = '<span style="color: #f44336;">Error checking selections</span>';
                        });
                })
                .catch(error => {
                    console.error('Error getting adoption progress:', error);
                    container.innerHTML = '<span style="color: #f44336;">Error loading adoption progress</span>';
                });
        }
        
        // Helper function to load normal matching interface
        function loadNormalMatchingInterface(targetUserId, container) {
            // Load user's ethical preferences first
            db.collection('ethical_preferences').doc(targetUserId).get()
                .then(prefsDoc => {
                    if (!prefsDoc.exists) {
                        container.innerHTML = `
                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px;">
                                ⚠️ Please complete your ethical preferences in Stage 6 first before viewing matches.
                            </div>
                        `;
                        return;
                    }
                    
                    const preferences = prefsDoc.data();
                    
                    // Load all available children and perform ethical matching
                    db.collection('children').where('status', '==', 'Available').get()
                        .then(childrenSnapshot => {
                            const matches = performEthicalMatching(preferences, childrenSnapshot);
                            
                            if (matches.length === 0) {
                                container.innerHTML = `
                                    <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 6px;">
                                        😔 No children currently match your ethical preferences. We'll notify you when new matches become available.
                                    </div>
                                `;
                                return;
                            }
                            
                            // Send match found notification
                            sendMatchFoundNotification(targetUserId, matches.length);
                            
                            // Display matches with ethical criteria
                            let matchesHtml = `
                                <div style="background: #e8f5e8; border: 2px solid #c8e6c9; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                                    <h4 style="color: #2e7d32; margin-bottom: 10px;">🌟 Your Ethical Matches</h4>
                                    <p style="color: #555; margin-bottom: 15px;">
                                        Based on meaningful criteria only - no discrimination based on appearance.
                                        Found ${matches.length} compatible ${matches.length === 1 ? 'child' : 'children'}.
                                    </p>
                                </div>
                                
                                <div style="display: grid; gap: 20px;">
                            `;
                            
                            matches.forEach((match, index) => {
                                const child = match.child;
                                const score = match.score;
                                const criteria = match.matchedCriteria;
                                
                                matchesHtml += `
                                    <div style="
                                        background: #ffffff;
                                        border: 2px solid #e3f2fd;
                                        border-radius: 12px;
                                        padding: 20px;
                                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                                        position: relative;
                                    ">
                                        <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 15px;">
                                            <div style="flex: 1;">
                                                <h4 style="color: #1976d2; margin: 0 0 5px 0;">👶 ${child.name || 'Child ' + (index + 1)}</h4>
                                                <div style="color: #666; font-size: 14px;">
                                                    Match Score: ${score}/${criteria.length + 2} criteria
                                                </div>
                                            </div>
                                            <div style="
                                                background: ${score >= 4 ? '#4caf50' : score >= 3 ? '#ff9800' : '#f44336'};
                                                color: white;
                                                padding: 4px 12px;
                                                border-radius: 20px;
                                                font-size: 12px;
                                                font-weight: 600;
                                            ">
                                                ${score >= 4 ? '🌟 Excellent' : score >= 3 ? '👍 Good' : '⚠️ Fair'} Match
                                            </div>
                                        </div>
                                        
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                            <div>
                                                <strong>Age:</strong> ${child.age || 'Not specified'}
                                            </div>
                                            <div>
                                                <strong>Gender:</strong> ${child.gender || 'Not specified'}
                                            </div>
                                            <div>
                                                <strong>Interests:</strong> ${child.interests || child.activityInterests || 'Various activities'}
                                            </div>
                                            <div>
                                                <strong>Education:</strong> ${child.educationalLevel || child.educationalBackground || 'Age appropriate'}
                                            </div>
                                            <div>
                                                <strong>Special Needs:</strong> ${child.specialNeeds ? 'Yes' : 'None'}
                                            </div>
                                            <div>
                                                <strong>Siblings:</strong> ${child.hasSiblings ? 'Yes' : 'None'}
                                            </div>
                                        </div>
                                        
                                        <div style="margin-bottom: 15px;">
                                            <strong>Background:</strong> 
                                            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px; line-height: 1.4;">
                                                ${child.background || child.description || 'A wonderful child looking for a loving family.'}
                                            </p>
                                        </div>
                                        
                                        <div style="margin-bottom: 15px;">
                                            <strong>Why this is an ethical match:</strong>
                                            <ul style="margin: 5px 0 0 0; color: #2e7d32; font-size: 14px;">
                                                ${criteria.map(c => `<li>${c}</li>`).join('')}
                                            </ul>
                                        </div>
                                        
                                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <button 
                                                onclick="selectChildForAdoption('${child.id}', '${child.name || 'Child ' + (index + 1)}')"
                                                style="
                                                    background: #4caf50;
                                                    color: white;
                                                    border: none;
                                                    padding: 10px 20px;
                                                    border-radius: 6px;
                                                    cursor: pointer;
                                                    font-weight: 600;
                                                    flex: 1;
                                                    min-width: 150px;
                                                "
                                            >
                                                💕 Select This Child
                                            </button>
                                            <button 
                                                onclick="scheduleAppointmentWithChild('${child.id}', '${child.name || 'Child ' + (index + 1)}')"
                                                style="
                                                    background: #2196f3;
                                                    color: white;
                                                    border: none;
                                                    padding: 10px 20px;
                                                    border-radius: 6px;
                                                    cursor: pointer;
                                                    font-weight: 600;
                                                    flex: 1;
                                                    min-width: 150px;
                                                "
                                            >
                                                📅 Schedule Meeting
                                            </button>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            matchesHtml += '</div>';
                            container.innerHTML = matchesHtml;
                        })
                        .catch(error => {
                            console.error('Error loading children for matching:', error);
                            container.innerHTML = '<span style="color: #f44336;">Error loading matches</span>';
                        });
                })
                .catch(error => {
                    console.error('Error loading ethical preferences:', error);
                    container.innerHTML = '<span style="color: #f44336;">Error loading preferences</span>';
                });
        }
        
        // Helper function to show interface when user has selected but not scheduled
        function loadMatchingInterfaceWithSelection(targetUserId, latestSelection, container) {
            container.innerHTML = `
                <div style="background: #fff3cd; border: 2px solid #ffeaa7; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="color: #856404; margin-bottom: 10px;">⚠️ Selection Made</h4>
                    <p style="color: #856404; margin-bottom: 15px;">
                        You have selected <strong>${latestSelection.childName}</strong> for adoption, but haven't scheduled a meeting yet.
                        Please schedule an appointment to complete the process.
                    </p>
                    <button 
                        onclick="scheduleAppointmentWithChild('${latestSelection.childId}', '${latestSelection.childName}')"
                        style="
                            background: #2196f3;
                            color: white;
                            border: none;
                            padding: 12px 24px;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: 600;
                            width: 100%;
                        "
                    >
                        📅 Schedule Meeting with ${latestSelection.childName}
                    </button>
                </div>
            `;
        }
        
        // MATCHING AND SCHEDULING NOTIFICATION FUNCTIONS
        
        function sendMatchFoundNotification(userId, matchCount) {
            try {
                console.log('🔍 Sending match found notification to user:', userId);
                
                // Send to super_simple_notifications.php for system notifications
                const notificationData = {
                    action: 'send_adoption_notification',
                    userId: userId,
                    status: 'matches_found',
                    stepNumber: 6,
                    data: {
                        matchCount: matchCount,
                        activityType: 'matches_found',
                        activityDetails: `System found ${matchCount} ethical ${matchCount === 1 ? 'match' : 'matches'} based on your preferences`
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
                        console.log('✅ Match found notification sent successfully');
                    } else {
                        console.error('❌ Failed to send match found notification:', result.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Error sending match found notification:', error);
                });
                
                // ALSO send to chat via Firebase bridge (like step completion messages)
                if (window.firebaseMessagingBridge) {
                    const chatMessage = `🔍 Great news! We found ${matchCount} ethical ${matchCount === 1 ? 'match' : 'matches'} based on your preferences. View them in Stage 6 to continue your adoption journey.`;
                    window.firebaseMessagingBridge.sendCustomMessage(userId, chatMessage, 'matches_found')
                        .then(() => {
                            console.log('✅ Match found message sent to chat via Firebase bridge');
                        })
                        .catch(error => {
                            console.error('❌ Error sending match found message to chat:', error);
                        });
                } else {
                    console.error('❌ Firebase messaging bridge not available for match found message');
                }
            } catch (error) {
                console.error('❌ Error in sendMatchFoundNotification:', error);
            }
        }
        
        function sendChildSelectionNotification(userId, childName) {
            try {
                console.log('👶 Sending child selection notification to user:', userId);
                
                // Send to super_simple_notifications.php for system notifications
                const notificationData = {
                    action: 'send_adoption_notification',
                    userId: userId,
                    status: 'child_selected',
                    stepNumber: 7,
                    data: {
                        childName: childName,
                        activityType: 'child_selected',
                        activityDetails: `Selected ${childName} for adoption - awaiting social worker review`
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
                        console.log('✅ Child selection notification sent successfully');
                    } else {
                        console.error('❌ Failed to send child selection notification:', result.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Error sending child selection notification:', error);
                });
                
                // ALSO send to chat via Firebase bridge (like step completion messages)
                if (window.firebaseMessagingBridge) {
                    const chatMessage = `👶 Wonderful! You have selected ${childName} for adoption. A social worker will review your selection and contact you soon to proceed with the next steps.`;
                    window.firebaseMessagingBridge.sendCustomMessage(userId, chatMessage, 'child_selected')
                        .then(() => {
                            console.log('✅ Child selection message sent to chat via Firebase bridge');
                        })
                        .catch(error => {
                            console.error('❌ Error sending child selection message to chat:', error);
                        });
                } else {
                    console.error('❌ Firebase messaging bridge not available for child selection message');
                }
            } catch (error) {
                console.error('❌ Error in sendChildSelectionNotification:', error);
            }
        }
        
        function sendAppointmentRequestNotification(userId, childName, appointmentDate, appointmentTime) {
            try {
                console.log('📅 Sending appointment request notification to user:', userId);
                
                // Send to super_simple_notifications.php for system notifications
                const notificationData = {
                    action: 'send_adoption_notification',
                    userId: userId,
                    status: 'appointment_requested',
                    stepNumber: 7,
                    data: {
                        childName: childName,
                        appointmentDate: appointmentDate,
                        appointmentTime: appointmentTime,
                        activityType: 'appointment_requested',
                        activityDetails: `Requested meeting with ${childName} on ${appointmentDate} at ${appointmentTime} - awaiting approval`
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
                        console.log('✅ Appointment request notification sent successfully');
                    } else {
                        console.error('❌ Failed to send appointment request notification:', result.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Error sending appointment request notification:', error);
                });
                
                // ALSO send to chat via Firebase bridge (like step completion messages)
                if (window.firebaseMessagingBridge) {
                    const chatMessage = `📅 Your appointment request has been submitted! You've requested to meet ${childName} on ${appointmentDate} at ${appointmentTime}. A social worker will review and approve your request shortly.`;
                    window.firebaseMessagingBridge.sendCustomMessage(userId, chatMessage, 'appointment_requested')
                        .then(() => {
                            console.log('✅ Appointment request message sent to chat via Firebase bridge');
                        })
                        .catch(error => {
                            console.error('❌ Error sending appointment request message to chat:', error);
                        });
                } else {
                    console.error('❌ Firebase messaging bridge not available for appointment request message');
                }
            } catch (error) {
                console.error('❌ Error in sendAppointmentRequestNotification:', error);
            }
        }
        
        // TASK 1: Ethical Matching Algorithm - NO DISCRIMINATION
        function performEthicalMatching(preferences, childrenSnapshot) {
            const matches = [];
            
            childrenSnapshot.forEach(doc => {
                const child = { id: doc.id, ...doc.data() };
                let score = 0;
                const matchedCriteria = [];
                
                // Age compatibility (developmental stage matching)
                if (preferences.ageRange && preferences.ageRange !== 'Any') {
                    if (child.age === preferences.ageRange) {
                        score += 2; // High weight for age compatibility
                        matchedCriteria.push(`✅ Age compatibility: ${child.age}`);
                    }
                } else if (preferences.ageRange === 'Any') {
                    score += 1;
                    matchedCriteria.push(`✅ Open to any age: ${child.age || 'Age compatible'}`);
                }
                
                // Gender preference (if specified)
                if (preferences.genderPreference && preferences.genderPreference !== 'Any') {
                    if (child.gender === preferences.genderPreference) {
                        score += 1;
                        matchedCriteria.push(`✅ Gender preference: ${child.gender}`);
                    }
                } else if (preferences.genderPreference === 'Any') {
                    score += 1;
                    matchedCriteria.push(`✅ Open to any gender: ${child.gender || 'Gender compatible'}`);
                }
                
                // Sibling compatibility
                if (preferences.siblingsOpen) {
                    if (preferences.siblingsOpen === 'Yes' && child.hasSiblings) {
                        score += 2;
                        matchedCriteria.push(`✅ Sibling compatibility: Child has siblings, you're open to siblings`);
                    } else if (preferences.siblingsOpen === 'No' && !child.hasSiblings) {
                        score += 2;
                        matchedCriteria.push(`✅ Sibling compatibility: Single child, preference for single child`);
                    } else if (preferences.siblingsOpen === 'Either') {
                        score += 1;
                        matchedCriteria.push(`✅ Sibling flexibility: Open to either option`);
                    }
                }
                
                // Special needs compatibility
                if (preferences.specialNeedsOpen) {
                    if (preferences.specialNeedsOpen === 'Yes' && child.specialNeeds) {
                        score += 2;
                        matchedCriteria.push(`✅ Special needs compatibility: Willing to support special needs`);
                    } else if (preferences.specialNeedsOpen === 'No' && !child.specialNeeds) {
                        score += 1;
                        matchedCriteria.push(`✅ Special needs compatibility: No special needs required`);
                    } else if (preferences.specialNeedsOpen === 'Mild' && child.specialNeeds && child.specialNeedsLevel === 'Mild') {
                        score += 2;
                        matchedCriteria.push(`✅ Special needs compatibility: Mild special needs match`);
                    }
                }
                
                // Activity interests alignment
                if (preferences.activityInterests && preferences.activityInterests !== 'Any') {
                    const childInterests = child.interests || child.activityInterests || '';
                    if (childInterests.toLowerCase().includes(preferences.activityInterests.toLowerCase())) {
                        score += 1;
                        matchedCriteria.push(`✅ Activity interests: Shared interest in ${preferences.activityInterests}`);
                    }
                } else if (preferences.activityInterests === 'Any') {
                    score += 1;
                    matchedCriteria.push(`✅ Activity openness: Open to child's interests`);
                }
                
                // Educational compatibility
                if (preferences.educationalBackground && preferences.educationalBackground !== 'Any') {
                    const childEducation = child.educationalLevel || child.educationalBackground || 'Average';
                    if (childEducation === preferences.educationalBackground) {
                        score += 1;
                        matchedCriteria.push(`✅ Educational compatibility: ${childEducation} level`);
                    }
                } else if (preferences.educationalBackground === 'Any') {
                    score += 1;
                    matchedCriteria.push(`✅ Educational openness: Supportive of child's learning level`);
                }
                
                // Only include children with meaningful matches (score >= 3)
                if (score >= 3) {
                    matches.push({
                        child: child,
                        score: score,
                        matchedCriteria: matchedCriteria
                    });
                }
            });
            
            // Sort by score (highest first) and ensure we return at least 3 if available
            matches.sort((a, b) => b.score - a.score);
            
            // Ensure at least 3 children are matched if available
            const minMatches = Math.min(3, matches.length);
            if (matches.length < 3 && childrenSnapshot.size >= 3) {
                // If we don't have 3 matches, lower the threshold to include more children
                const additionalMatches = [];
                childrenSnapshot.forEach(doc => {
                    const child = { id: doc.id, ...doc.data() };
                    const existingMatch = matches.find(m => m.child.id === child.id);
                    if (!existingMatch) {
                        // Add with basic compatibility score
                        additionalMatches.push({
                            child: child,
                            score: 2, // Minimum score for basic compatibility
                            matchedCriteria: ['✅ Basic compatibility assessed']
                        });
                    }
                });
                
                // Add additional matches to reach 3 total
                const needed = 3 - matches.length;
                matches.push(...additionalMatches.slice(0, needed));
            }
            
            return matches.slice(0, 3); // Return top 3 matches maximum
        }
        
        // Function to trigger ethical matching process
        function triggerEthicalMatching(targetUserId, preferences) {
            console.log('🤖 Starting ethical matching process for user:', targetUserId);
            
            // Store matching trigger in database for tracking
            db.collection('matching_history').add({
                userId: targetUserId,
                preferences: preferences,
                triggeredAt: Date.now(),
                type: 'ethical_matching',
                status: 'triggered'
            }).then(() => {
                console.log('✅ Ethical matching triggered and logged');
            }).catch(error => {
                console.error('❌ Error logging matching trigger:', error);
            });
        }
        
        // TASK 1 & 2: Child Selection and Appointment Scheduling
        function selectChildForAdoption(childId, childName) {
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (confirm(`Are you sure you want to select ${childName} for adoption? This will notify the social worker for review.`)) {
                // First get the current adoption number
                db.collection('adoption_progress').doc(targetUserId).get()
                    .then(adoptionDoc => {
                        const currentAdoptionNumber = adoptionDoc.exists && adoptionDoc.data().adoptions 
                            ? (adoptionDoc.data().currentAdoption || 1) 
                            : 1;
                        
                        console.log(`👶 Selecting child for adoption #${currentAdoptionNumber}`);
                        
                        const selectionData = {
                            userId: targetUserId,
                            childId: childId,
                            childName: childName,
                            selectedAt: Date.now(),
                            status: 'pending_social_worker_review',
                            selectionStep: 7,
                            adoptionNumber: currentAdoptionNumber  // Add adoption number
                        };
                        
                        return db.collection('child_selections').add(selectionData);
                    })
                    .then(() => {
                        alert(`✅ Child selection confirmed! ${childName} has been selected for adoption. A social worker will review your selection and contact you soon.`);
                        
                        // Send child selection notification
                        sendChildSelectionNotification(targetUserId, childName);
                        
                        // Update progress to indicate selection made
                        db.collection('adoption_progress').doc(targetUserId).update({
                            step7_selection_made: true,
                            step7_selected_child: childName,
                            step7_selection_date: Date.now(),
                            step7_completed: true,
                            step7_completion_date: Date.now()
                        }).then(() => {
                            // TASK 2: Send notification to admin about Stage 7 completion (child selection)
                            // Get user information for notification
                            db.collection('users').doc(targetUserId).get()
                                .then(userDoc => {
                                    let userName = 'User';
                                    let userEmail = '';
                                    
                                    if (userDoc.exists) {
                                        const userData = userDoc.data();
                                        userName = userData.displayName || userData.name || userData.username || 'User';
                                        userEmail = userData.email || '';
                                        console.log('✅ Got user info for Stage 7 completion notification:', userName, userEmail);
                                    } else {
                                        console.log('⚠️ No user data found for Stage 7 completion, using defaults');
                                    }
                                    
                                    // Send step completion notification to admin
                                    sendAdoptionNotification('step_completed', 7, {
                                        userId: targetUserId,
                                        stepNumber: 7,
                                        status: 'complete',
                                        userName: userName,
                                        userEmail: userEmail,
                                        stepType: 'child_selection',
                                        selectedChild: childName
                                    });
                                    
                                    console.log('📧 Stage 7 completion notification sent to admin');
                                })
                                .catch(error => {
                                    console.log('❌ Error getting user info for Stage 7 notification:', error);
                                    // Fallback: send notification without user info
                                    sendAdoptionNotification('step_completed', 7, {
                                        userId: targetUserId,
                                        stepNumber: 7,
                                        status: 'complete',
                                        userName: 'User',
                                        userEmail: '',
                                        stepType: 'child_selection',
                                        selectedChild: childName
                                    });
                                });
                        }).catch(error => {
                            console.error('Error updating Stage 7 progress:', error);
                        });
                        
                        // Refresh the page to show updated status
                        setTimeout(() => window.location.reload(), 2000);
                    })
                    .catch(error => {
                        console.error('Error saving child selection:', error);
                        alert('Error confirming selection: ' + error.message);
                    })
                    .catch(error => {
                        console.error('Error getting adoption progress:', error);
                        alert('Error getting adoption progress: ' + error.message);
                    });
            }
        }
        
        function scheduleAppointmentWithChild(childId, childName) {
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            // Open appointment scheduling modal
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            modal.innerHTML = `
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 80vh;
                    overflow-y: auto;
                ">
                    <h3 style="color: #1976d2; margin-bottom: 20px;">📅 Schedule Appointment with ${childName}</h3>
                    
                    <form id="appointmentForm">
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; color: #333;">Preferred Date:</label>
                            <input 
                                type="date" 
                                name="appointmentDate" 
                                required 
                                min="${new Date().toISOString().split('T')[0]}"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;"
                            >
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; color: #333;">Preferred Time:</label>
                            <select name="appointmentTime" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                <option value="">Select Time</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 600; color: #333;">Meeting Type:</label>
                            <select name="meetingType" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                                <option value="">Select Type</option>
                                <option value="first_meeting">First Meeting</option>
                                <option value="follow_up">Follow-up Meeting</option>
                                <option value="placement_visit">Placement Visit</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="font-weight: 600; color: #333;">Additional Notes:</label>
                            <textarea 
                                name="appointmentNotes" 
                                rows="3" 
                                placeholder="Any special requirements or notes for the appointment..."
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px; resize: vertical;"
                            ></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button 
                                type="button" 
                                onclick="this.closest('.modal').remove()"
                                style="
                                    background: #6c757d;
                                    color: white;
                                    border: none;
                                    padding: 10px 20px;
                                    border-radius: 6px;
                                    cursor: pointer;
                                    flex: 1;
                                "
                            >
                                Cancel
                            </button>
                            <button 
                                type="button" 
                                onclick="submitAppointmentRequest('${childId}', '${childName}', '${targetUserId}')"
                                style="
                                    background: #4caf50;
                                    color: white;
                                    border: none;
                                    padding: 10px 20px;
                                    border-radius: 6px;
                                    cursor: pointer;
                                    flex: 1;
                                "
                            >
                                📅 Schedule Appointment
                            </button>
                        </div>
                    </form>
                </div>
            `;
            
            modal.className = 'modal';
            document.body.appendChild(modal);
        }
        
        function submitAppointmentRequest(childId, childName, targetUserId) {
            const form = document.getElementById('appointmentForm');
            const formData = new FormData(form);
            
            // Get user information and current adoption number for appointment display
            Promise.all([
                db.collection('users').doc(targetUserId).get(),
                db.collection('adoption_progress').doc(targetUserId).get()
            ])
                .then(([userDoc, adoptionDoc]) => {
                    let userName = 'User';
                    let userEmail = '';
                    
                    if (userDoc.exists) {
                        const userData = userDoc.data();
                        userName = userData.displayName || userData.name || userData.username || 'User';
                        userEmail = userData.email || '';
                    }
                    
                    const currentAdoptionNumber = adoptionDoc.exists && adoptionDoc.data().adoptions 
                        ? (adoptionDoc.data().currentAdoption || 1) 
                        : 1;
                    
                    console.log(`📅 Scheduling appointment for adoption #${currentAdoptionNumber}`);
                    
                    const appointmentData = {
                        userId: targetUserId,
                        username: userName, // Add username for Appointments.php display
                        userEmail: userEmail, // Add userEmail for Appointments.php display
                        childId: childId,
                        childName: childName,
                        appointmentDate: formData.get('appointmentDate'),
                        appointmentTime: formData.get('appointmentTime'),
                        meetingType: formData.get('meetingType'),
                        appointmentNotes: formData.get('appointmentNotes'),
                        requestedAt: Date.now(),
                        status: 'pending_approval',
                        type: 'child_meeting',
                        adoptionNumber: currentAdoptionNumber  // Add adoption number
                    };
                    
                    if (!appointmentData.appointmentDate || !appointmentData.appointmentTime || !appointmentData.meetingType) {
                        alert('Please fill in all required fields');
                        return;
                    }
                    
                    return db.collection('appointment_requests').add(appointmentData);
                })
                .then((docRef) => {
                    alert(`✅ Appointment request submitted! Your request to meet ${childName} is pending approval.`);
                    document.querySelector('.modal').remove();
                    
                    // Send appointment request notification
                    sendAppointmentRequestNotification(targetUserId, childName, formData.get('appointmentDate'), formData.get('appointmentTime'));
                    
                    // Update progress to indicate appointment requested
                    return db.collection('adoption_progress').doc(targetUserId).update({
                        step7_appointment_requested: true,
                        step7_appointment_child: childName,
                        step7_appointment_date: Date.now()
                    });
                })
                .catch(error => {
                    console.error('Error submitting appointment request:', error);
                    alert('Error submitting appointment request: ' + error.message);
                });
        }
        
        // TASK 2: Appointment Scheduling Interface for Stage 7
        function loadAppointmentScheduling(stepNumber, documentId, container) {
            const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
            
            if (!targetUserId) {
                container.innerHTML = '<span style="color: #999;">Please select a user to view appointments</span>';
                return;
            }
            
            // First get the current adoption number
            db.collection('adoption_progress').doc(targetUserId).get()
                .then(adoptionDoc => {
                    const currentAdoptionNumber = adoptionDoc.exists && adoptionDoc.data().adoptions 
                        ? (adoptionDoc.data().currentAdoption || 1) 
                        : 1;
                    
                    console.log(`📅 Loading appointment scheduling for adoption #${currentAdoptionNumber}`);
                    
                    // Check if user has made a child selection for current adoption first
                    db.collection('child_selections')
                        .where('userId', '==', targetUserId)
                        .where('adoptionNumber', '==', currentAdoptionNumber)
                        .get()
                        .then(selectionsSnapshot => {
                            if (selectionsSnapshot.empty) {
                                container.innerHTML = `
                                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px;">
                                        ⚠️ Please view and select a child from the matching interface above before scheduling appointments.
                                    </div>
                                `;
                                return;
                            }
                            
                            const latestSelection = selectionsSnapshot.docs[selectionsSnapshot.docs.length - 1].data();
                            
                            // Load existing appointment requests for current adoption
                            db.collection('appointment_requests')
                                .where('userId', '==', targetUserId)
                                .where('adoptionNumber', '==', currentAdoptionNumber)
                                .get()
                        .then(appointmentsSnapshot => {
                            let appointmentsHtml = `
                                <div style="background: #e3f2fd; border: 2px solid #bbdefb; border-radius: 8px; padding: 20px;">
                                    <h4 style="color: #1976d2; margin-bottom: 15px;">📅 Appointment Scheduling</h4>
                                    <p style="color: #555; margin-bottom: 15px;">
                                        Selected Child: <strong>${latestSelection.childName}</strong>
                                    </p>
                            `;
                            
                            if (!appointmentsSnapshot.empty) {
                                appointmentsHtml += '<h5 style="color: #333; margin: 15px 0 10px 0;">Your Appointment Requests:</h5>';
                                
                                appointmentsSnapshot.forEach(doc => {
                                    const appointment = doc.data();
                                    const statusColor = appointment.status === 'approved' ? '#4caf50' : 
                                                       appointment.status === 'rejected' ? '#f44336' : '#ff9800';
                                    
                                    appointmentsHtml += `
                                        <div style="
                                            background: white;
                                            border-left: 4px solid ${statusColor};
                                            padding: 12px;
                                            margin: 8px 0;
                                            border-radius: 4px;
                                        ">
                                            <strong>${appointment.meetingType.replace('_', ' ').toUpperCase()}</strong> - 
                                            ${appointment.appointmentDate} at ${appointment.appointmentTime}
                                            <br>
                                            <span style="color: ${statusColor}; font-weight: 600;">
                                                Status: ${appointment.status.replace('_', ' ').toUpperCase()}
                                            </span>
                                            ${appointment.appointmentNotes ? `<br><small>Notes: ${appointment.appointmentNotes}</small>` : ''}
                                        </div>
                                    `;
                                });
                            }
                            
                            appointmentsHtml += `
                                    <button 
                                        onclick="scheduleAppointmentWithChild('${latestSelection.childId}', '${latestSelection.childName}')"
                                        style="
                                            background: #2196f3;
                                            color: white;
                                            border: none;
                                            padding: 12px 24px;
                                            border-radius: 6px;
                                            cursor: pointer;
                                            font-weight: 600;
                                            margin-top: 15px;
                                            width: 100%;
                                        "
                                    >
                                        📅 Schedule New Appointment
                                    </button>
                                </div>
                            `;
                            
                            container.innerHTML = appointmentsHtml;
                            })
                            .catch(error => {
                                console.error('Error loading appointments:', error);
                                container.innerHTML = '<span style="color: #f44336;">Error loading appointments</span>';
                            });
                        })
                        .catch(error => {
                            console.error('Error checking child selections:', error);
                            container.innerHTML = '<span style="color: #f44336;">Error checking selections</span>';
                        });
                })
                .catch(error => {
                    console.error('Error getting adoption progress:', error);
                    container.innerHTML = '<span style="color: #f44336;">Error loading adoption progress</span>';
                });
        }
        

        
        // TASK 2: Function to check if a user can upload to a specific step
        function checkUploadPermission(userId, stepNumber) {
            return new Promise((resolve, reject) => {
                // Step 1 is always allowed
                if (stepNumber === 1) {
                    resolve(true);
                    return;
                }
                
                // Check if previous steps are completed
                db.collection('adoption_progress').doc(userId).get()
                    .then(doc => {
                        if (!doc.exists) {
                            // No progress yet, only allow step 1
                            resolve(stepNumber === 1);
                            return;
                        }
                        
                        const progress = doc.data();
                        
                        // Check each previous step for completion
                        for (let i = 1; i < stepNumber; i++) {
                            const stepKey = `step${i}_completed`;
                            if (!progress[stepKey]) {
                                console.log(`Step ${i} not completed, cannot proceed to step ${stepNumber}`);
                                resolve(false);
                                return;
                            }
                        }
                        
                        // All previous steps completed
                        console.log(`All previous steps completed, allowing upload to step ${stepNumber}`);
                        resolve(true);
                    })
                    .catch(error => {
                        console.error('Error checking upload permission:', error);
                        reject(error);
                    });
            });
        }
        
        // TASK 2: Function to mark a step as completed when all requirements are uploaded
        function checkAndMarkStepCompleted(userId, stepNumber) {
            // Get step requirements
            const stepDef = stepDefinitions.find(step => step.number === stepNumber);
            if (!stepDef || !stepDef.requirements) {
                return;
            }
            
            // Special handling for Stage 7 - check if already completed by child selection
            if (stepNumber === 7) {
                db.collection('adoption_progress').doc(userId).get()
                    .then(doc => {
                        if (doc.exists) {
                            const data = doc.data();
                            if (data.step7_completed || data.step7_selection_made) {
                                console.log('✅ Stage 7 already completed by child selection');
                                return;
                            }
                        }
                        
                        // Continue with normal completion check for Stage 7
                        checkStepUploadsCompleted(userId, stepNumber, stepDef);
                    })
                    .catch(error => {
                        console.error('Error checking Stage 7 completion status:', error);
                        // Fallback to normal check
                        checkStepUploadsCompleted(userId, stepNumber, stepDef);
                    });
                return;
            }
            
            // For all other steps, use normal completion logic
            checkStepUploadsCompleted(userId, stepNumber, stepDef);
        }
        
        // Helper function to check step uploads completion
        function checkStepUploadsCompleted(userId, stepNumber, stepDef) {
            // Count required uploads (exclude view-only, forms without uploads, etc.)
            const requiredUploads = stepDef.requirements.filter(req => 
                !req.viewOnly && 
                !req.isPAPView && 
                !req.isPAPDownload && 
                !req.isMatchingInterface &&
                !(req.isForm && !req.isPAPUpload) &&
                !req.isEthicalForm // Exclude ethical form from Step 6
            );
            
            console.log(`Step ${stepNumber} has ${requiredUploads.length} required uploads:`, requiredUploads.map(r => r.documentId));
            
            if (requiredUploads.length === 0) {
                // No uploads required, mark as completed
                db.collection('adoption_progress').doc(userId).update({
                    [`step${stepNumber}_completed`]: true,
                    [`step${stepNumber}_completion_date`]: Date.now()
                });
                console.log(`Step ${stepNumber} marked as completed (no uploads required)`);
                return;
            }
            
            // Check if all required documents are uploaded
            const checkPromises = requiredUploads.map(requirement => {
                return checkDocumentUploaded(userId, stepNumber, requirement.documentId);
            });
            
            Promise.all(checkPromises)
                .then(results => {
                    console.log(`Step ${stepNumber} upload results:`, results);
                    const allUploaded = results.every(uploaded => uploaded);
                    
                    if (allUploaded) {
                        // Mark step as completed
                        db.collection('adoption_progress').doc(userId).update({
                            [`step${stepNumber}_completed`]: true,
                            [`step${stepNumber}_completion_date`]: Date.now()
                        }).then(() => {
                            console.log(`✅ Step ${stepNumber} marked as completed`);
                            
                            // Show completion notification
                            showStepCompletionNotification(stepNumber);
                        });
                    } else {
                        console.log(`Step ${stepNumber} not yet completed - missing uploads`);
                    }
                })
                .catch(error => {
                    console.error('Error checking step completion:', error);
                });
        }
        
        // Helper function to check if a specific document is uploaded
        function checkDocumentUploaded(userId, stepNumber, documentId) {
            return new Promise((resolve) => {
                // Check in adoption_progress collection first
                db.collection('adoption_progress').doc(userId)
                    .collection(`step${stepNumber}_uploads`)
                    .where('fileName', '>=', `${documentId}`)
                    .where('fileName', '<', `${documentId}\uf8ff`)
                    .get()
                    .then(snapshot => {
                        if (!snapshot.empty) {
                            resolve(true);
                            return;
                        }
                        
                        // Check in user_submissions_status
                        return db.collection('user_submissions_status').doc(userId)
                            .collection(`step${stepNumber}_documents`)
                            .get();
                    })
                    .then(snapshot => {
                        if (snapshot && !snapshot.empty) {
                            // Check if any document matches this requirement
                            let found = false;
                            snapshot.forEach(doc => {
                                const fileName = doc.data().fileName || '';
                                if (fileName.includes(documentId)) {
                                    found = true;
                                }
                            });
                            resolve(found);
                        } else {
                            resolve(false);
                        }
                    })
                    .catch(error => {
                        console.error(`Error checking document ${documentId}:`, error);
                        resolve(false);
                    });
            });
        }
        
        // Function to show step completion notification
        function showStepCompletionNotification(stepNumber) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
                color: white;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
                z-index: 1000;
                font-weight: 600;
                max-width: 350px;
                animation: slideInRight 0.5s ease-out;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 24px; margin-right: 10px;">🎉</span>
                    <span style="font-size: 18px;">Step ${stepNumber} Completed!</span>
                </div>
                <div style="font-size: 14px; opacity: 0.9;">
                    All required documents uploaded successfully. You can now proceed to Step ${stepNumber + 1}.
                </div>
            `;
            
            // Add animation styles
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        opacity: 0;
                        transform: translateX(100px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(notification);
            
            // Remove notification after 7 seconds
            setTimeout(() => {
                notification.style.animation = 'slideInRight 0.5s ease-out reverse';
                setTimeout(() => {
                    notification.remove();
                    style.remove();
                }, 500);
            }, 7000);
            
            // Refresh the page after 3 seconds to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (progressListener) {
                progressListener();
            }
        });

        // Debug function to manually check Firebase data structure
        function debugFirebaseData() {
            if (!db || !userId) {
                console.log('❌ Cannot debug Firebase data - missing db or userId');
                return;
            }
            
            console.log('=== MANUAL FIREBASE DATA DEBUG ===');
            console.log('Attempting to read adoption_progress document for user:', userId);
            
            db.collection('adoption_progress').doc(userId).get()
                .then(doc => {
                    if (doc.exists) {
                        const data = doc.data();
                        console.log('✅ Manual Firebase read successful');
                        console.log('Document data:', JSON.stringify(data, null, 2));
                        
                        // Analyze structure
                        if (data.adoptions) {
                            console.log('📋 Document has versioned structure');
                            console.log('Current adoption:', data.currentAdoption);
                            console.log('Total adoptions:', data.totalAdoptions);
                            Object.keys(data.adoptions).forEach(key => {
                                const adoption = data.adoptions[key];
                                console.log(`Adoption ${key}:`, JSON.stringify(adoption, null, 2));
                            });
                        } else if (data.adopt_progress) {
                            console.log('📋 Document has old structure');
                            console.log('Progress data:', data.adopt_progress);
                        } else {
                            console.log('❌ Document has no recognizable progress structure');
                        }
                    } else {
                        console.log('❌ No document found for user:', userId);
                    }
                })
                .catch(error => {
                    console.error('❌ Manual Firebase read failed:', error);
                    console.log('Error details:', error.code, error.message);
                });
            console.log('=== END MANUAL DEBUG ===');
        }

        // Enhanced debugging for user identification
        function debugUserIdentity() {
            console.log('=== USER IDENTITY DEBUG ===');
            console.log('PHP Session User ID:', userId);
            console.log('PHP Session Username:', username);
            console.log('PHP Session Email:', currentUserEmail);
            console.log('Firebase Current User:', currentUser ? currentUser.uid : 'None');
            console.log('Firebase User Email:', currentUser ? currentUser.email : 'None');
            
            if (currentUser) {
                console.log('User IDs match:', currentUser.uid === userId);
                console.log('Emails match:', currentUser.email === currentUserEmail);
            }
            console.log('=== END USER IDENTITY DEBUG ===');
        }

        // Attempt to authenticate with Firebase using session email (if available)
        function attemptFirebaseEmailAuth() {
            if (!auth || !currentUserEmail) {
                console.log('❌ Cannot attempt Firebase email auth - missing auth or email');
                return Promise.reject('Missing auth or email');
            }
            
            console.log('=== ATTEMPTING FIREBASE EMAIL AUTH ===');
            console.log('Using email from PHP session:', currentUserEmail);
            
            // Since we don't have the password, we'll check if user is already authenticated
            return new Promise((resolve, reject) => {
                // Check current auth state first
                const unsubscribe = auth.onAuthStateChanged((user) => {
                    unsubscribe(); // Remove listener after first check
                    
                    if (user && user.email === currentUserEmail) {
                        console.log('✅ User already authenticated with matching email');
                        resolve(user);
                    } else if (user && user.email !== currentUserEmail) {
                        console.warn('⚠️ User authenticated but email mismatch');
                        console.log('Firebase email:', user.email, 'Session email:', currentUserEmail);
                        // Sign out the mismatched user
                        auth.signOut().then(() => {
                            reject('Email mismatch - signed out');
                        });
                    } else {
                        console.log('❌ No authenticated user - using session data only');
                        reject('No authenticated user');
                    }
                });
            });
        }

        // Force check adoption progress using PHP session user ID
        function forceCheckProgress() {
            if (!db || !userId) {
                console.log('❌ Cannot force check progress - missing db or userId');
                checkProgressWithFallback();
                return;
            }
            
            console.log('=== FORCE CHECKING PROGRESS WITH PHP SESSION ===');
            console.log('Using PHP Session User ID:', userId);
            
            // Try to read the document directly using PHP session user ID
            db.collection('adoption_progress').doc(userId).get()
                .then(doc => {
                    showLoading(false);
                    
                    if (doc.exists) {
                        const data = doc.data();
                        console.log('✅ Successfully read adoption progress with PHP session');
                        console.log('Document data:', JSON.stringify(data, null, 2));
                        
                        // Process the data using the same logic as authenticated users
                        if (data.adoptions && typeof data.adoptions === 'object') {
                            console.log('✅ Using versioned structure (force check)');
                            handleVersionedStructure(data);
                        } else if (data.adopt_progress && typeof data.adopt_progress === 'object') {
                            console.log('⚠️ Using old structure (force check)');
                            updateProgressTracking(data.adopt_progress);
                        } else {
                            console.log('❌ No valid progress data found (force check)');
                            showConfirmationDialog(1, username);
                        }
                    } else {
                        console.log('❌ No document found for PHP session user ID');
                        showConfirmationDialog(1, username);
                    }
                })
                .catch(error => {
                    console.error('❌ Force check failed:', error);
                    console.log('Error details:', error.code, error.message);
                    console.log('Falling back to default progress');
                    checkProgressWithFallback();
                });
        }

        // Auto-authenticate with Firebase using session data
        function autoAuthenticateFirebase() {
            if (!auth) {
                console.log('❌ Cannot auto-authenticate - Firebase auth not available');
                return Promise.reject('Firebase auth not available');
            }
            
            console.log('=== AUTO FIREBASE AUTHENTICATION ===');
            console.log('Session Email:', currentUserEmail);
            console.log('Session User ID:', userId);
            console.log('Firebase Token Available:', firebaseTokenValid);
            console.log('Token Value:', firebaseIdToken ? 'Present' : 'None');
            
            return new Promise((resolve, reject) => {
                // First, check if we have a valid stored Firebase token
                if (firebaseTokenValid && firebaseIdToken) {
                    console.log('✅ Using stored Firebase ID token for authentication');
                    
                    // Set up auth state listener first
                    const unsubscribe = auth.onAuthStateChanged((user) => {
                        if (user && user.uid === userId && user.email === currentUserEmail) {
                            console.log('✅ User authenticated via stored token');
                            unsubscribe();
                            resolve(user);
                        }
                    });
                    
                    // We can't directly sign in with an ID token in Firebase v9+
                    // So we'll check if the user is already signed in, and if not, redirect
                    setTimeout(() => {
                        const currentFirebaseUser = auth.currentUser;
                        if (currentFirebaseUser && currentFirebaseUser.uid === userId) {
                            console.log('✅ User already authenticated');
                            unsubscribe();
                            resolve(currentFirebaseUser);
                        } else {
                            console.log('⚠️ Stored token exists but user not authenticated - may need refresh');
                            unsubscribe();
                            reject(new Error('Stored token invalid - re-authenticate required'));
                        }
                    }, 1000);
                    
                } else {
                    console.log('❌ No valid Firebase token in session');
                    
                    // Check if user is already authenticated
                    const unsubscribe = auth.onAuthStateChanged((user) => {
                        if (user && user.email === currentUserEmail && user.uid === userId) {
                            console.log('✅ User already authenticated with correct credentials');
                            unsubscribe();
                            resolve(user);
                            return;
                        }
                        
                        // If not authenticated, redirect for fresh authentication
                        console.log('🔄 No valid token - need fresh authentication');
                        unsubscribe();
                        reject(new Error('No valid token - re-authenticate required'));
                    });
                    
                    // Set timeout for authentication attempt
                    setTimeout(() => {
                        unsubscribe();
                        reject(new Error('Authentication timeout'));
                    }, 3000);
                }
            });
        }

        function useOfflineMode() {
            console.log('Using offline mode - showing default progress');
            // Try to hide error messages from both admin and regular views
            const adminErrorElement = document.getElementById('adminErrorMessage');
            const regularErrorElement = document.getElementById('errorMessage');
            
            if (adminErrorElement && (isAdminUser && currentSelectedUser)) {
                adminErrorElement.style.display = 'none';
            } else if (regularErrorElement) {
                regularErrorElement.style.display = 'none';
            }
            
            checkProgressWithFallback();
        }

        // Detect mobile app changes and send notifications
        function detectMobileAppChanges(previousStatus, currentStatus, userId) {
            console.log('🔍 Detecting mobile app changes...');
            console.log('Previous status:', previousStatus);
            console.log('Current status:', currentStatus);
            
            if (!previousStatus || !currentStatus) {
                console.log('⚠️ Missing status data for comparison');
                return;
            }
            
            // Check each step for status changes
            for (let i = 1; i <= 10; i++) {
                const stepKey = `step${i}`;
                const previousStepStatus = previousStatus[stepKey];
                const currentStepStatus = currentStatus[stepKey];
                
                // Skip if no change
                if (previousStepStatus === currentStepStatus) {
                    continue;
                }
                
                console.log(`📱 Step ${i} changed: ${previousStepStatus} → ${currentStepStatus}`);
                
                // Detect mobile app approval patterns
                if (previousStepStatus === 'in_progress' && currentStepStatus === 'complete') {
                    console.log(`🎉 Mobile app approved Step ${i}!`);
                    sendMobileAppApprovalNotification(userId, i);
                } else if (previousStepStatus === 'locked' && currentStepStatus === 'in_progress') {
                    console.log(`🔄 Mobile app started Step ${i}!`);
                    sendMobileAppStepStartedNotification(userId, i);
                } else if (currentStepStatus === 'complete' && previousStepStatus !== 'complete') {
                    console.log(`✅ Mobile app completed Step ${i}!`);
                    sendMobileAppApprovalNotification(userId, i);
                }
            }
        }
        
        // Send notification when mobile app approves a step
        function sendMobileAppApprovalNotification(userId, stepNumber) {
            console.log(`📱 Sending mobile app approval notification for Step ${stepNumber}`);
            
            try {
                if (typeof window.notificationClient !== 'undefined') {
                    // Send adoption notification with specific mobile app approval status
                    window.notificationClient.sendAdoptionNotification('mobile_approved', stepNumber, {
                        userId: userId,
                        stepNumber: stepNumber,
                        source: 'mobile_app',
                        approvedAt: new Date().toISOString()
                    })
                    .then(success => {
                        if (success) {
                            console.log(`✅ Mobile app approval notification sent for Step ${stepNumber}`);
                        } else {
                            console.log(`❌ Failed to send mobile app approval notification for Step ${stepNumber}`);
                        }
                    })
                    .catch(error => {
                        console.log('❌ Mobile app notification error:', error);
                    });
                } else {
                    console.log('⚠️ Notification client not available for mobile app approval');
                }
            } catch (error) {
                console.log('❌ Send mobile app approval notification error:', error);
            }
        }
        
        // Send notification when mobile app starts a step
        function sendMobileAppStepStartedNotification(userId, stepNumber) {
            console.log(`📱 Sending mobile app step started notification for Step ${stepNumber}`);
            
            try {
                if (typeof window.notificationClient !== 'undefined') {
                    window.notificationClient.sendAdoptionNotification('mobile_step_started', stepNumber, {
                        userId: userId,
                        stepNumber: stepNumber,
                        source: 'mobile_app',
                        startedAt: new Date().toISOString()
                    })
                    .then(success => {
                        if (success) {
                            console.log(`✅ Mobile app step started notification sent for Step ${stepNumber}`);
                        } else {
                            console.log(`❌ Failed to send mobile app step started notification for Step ${stepNumber}`);
                        }
                    })
                    .catch(error => {
                        console.log('❌ Mobile app step started notification error:', error);
                    });
                } else {
                    console.log('⚠️ Notification client not available for mobile app step started');
                }
            } catch (error) {
                console.log('❌ Send mobile app step started notification error:', error);
            }
        }

        // Send adoption notification - EXACT MOBILE APP LOGIC
        function sendAdoptionNotification(status, stepNumber, additionalData = {}) {
            try {
                console.log('📱 MOBILE APP LOGIC: Sending adoption notification:', status, 'Step:', stepNumber);
                
                const userId = additionalData.userId || window.sessionUserId || 'EcWvBKf3zvQsgEE5Tl99eErnblD3';
                
                // Use the EXACT SAME notification system as mobile app (super_simple_notifications.php)
                const notificationData = {
                    action: 'send_adoption_notification',
                    userId: userId,
                    status: status,
                    stepNumber: stepNumber,
                    data: additionalData
                };
                
                console.log('📤 MOBILE APP FORMAT: Sending notification to super_simple_notifications.php:', notificationData);
                
                // Send via super_simple_notifications.php using the EXACT mobile app format
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
                        console.log('✅ MOBILE APP LOGIC: Adoption notification sent successfully:', result.message);
                        
                        // Force refresh navbar notifications after success
                        setTimeout(() => {
                            if (typeof loadNotifications === 'function') {
                                console.log('🔄 MOBILE APP LOGIC: Forcing notification reload...');
                                loadNotifications();
                            }
                        }, 500);
                    } else {
                        console.log('❌ MOBILE APP LOGIC: Failed to send adoption notification:', result.error);
                    }
                })
                .catch(error => {
                    console.error('❌ MOBILE APP LOGIC: Notification send error:', error);
                });
                
            } catch (error) {
                console.log('❌ MOBILE APP LOGIC: Send adoption notification error:', error);
            }
        }

        // MOBILE USER VIEW INITIALIZATION - EXACT MOBILE APP MATCH
        function initializeMobileUserView() {
            console.log('🏠 Initializing Mobile User View - Exact Mobile App Match');
            
            // Check if we're in user mode (not admin)
            const userRole = window.sessionUserRole || 'user';
            if (userRole === 'admin') {
                console.log('Admin role detected, skipping mobile user view');
                return;
            }
            
            // Hide loading message
            const loadingMessage = document.getElementById('loadingMessage');
            if (loadingMessage) {
                loadingMessage.style.display = 'none';
            }
            
            // Show mobile steps container
            const mobileStepsContainer = document.getElementById('mobileStepsContainer');
            if (mobileStepsContainer) {
                mobileStepsContainer.style.display = 'block';
            }
            
            // Initialize real Firebase data loading
            setupAdoptionProgressListener(userId, username);
            
            // DISABLED: Auto-reset on page load - only reset when admin marks step 10 complete
            // console.log('⏰ SCHEDULING step 10 completion check for user:', userId);
            // setTimeout(() => {
            //     console.log('⏰ EXECUTING step 10 completion check on page load...');
            //     simpleResetAfterStep10(userId);
            // }, 2000); // Delay to ensure Firebase is ready
        }

        function getStepStatus(stepNumber) {
            // Use real progress data if available, otherwise default to locked
            if (progressStatus && progressStatus[`step${stepNumber}`]) {
                return progressStatus[`step${stepNumber}`];
            }
            
            // Fallback to default demo status if no real data
            const defaultStatuses = {
                1: 'complete',
                2: 'in_progress',
                3: 'locked',
                4: 'locked',
                5: 'locked',
                6: 'locked',
                7: 'locked',
                8: 'locked',
                9: 'locked',
                10: 'locked'
            };
            
            return defaultStatuses[stepNumber] || 'locked';
        }

        function generateProgressCircles() {
            const container = document.querySelector('.mobile-progress-container');
            if (!container) return;
            
            container.innerHTML = '';
            
            for (let i = 1; i <= 10; i++) {
                const circle = document.createElement('div');
                circle.className = 'mobile-progress-circle';
                circle.textContent = i;
                
                // Get status from existing data or default to locked
                const status = getStepStatus(i);
                if (status === 'complete') {
                    circle.style.backgroundColor = '#4CAF50';
                    circle.style.color = 'white';
                } else if (status === 'in_progress') {
                    circle.style.backgroundColor = '#FFC107';
                    circle.style.color = 'black';
                } else {
                    circle.style.backgroundColor = '#9E9E9E';
                    circle.style.color = 'white';
                }
                
                container.appendChild(circle);
            }
        }

        function generateMobileLargeImageCards() {
            console.log('📱 Generating Mobile Large Image Cards with real progress data');
            
            const container = document.getElementById('mobileStepsContainer');
            if (!container) {
                console.error('Mobile steps container not found');
                return;
            }
            
            container.innerHTML = '';
            
            // Generate large image cards for all 10 steps using real progress data
            stepDefinitions.forEach((step, index) => {
                const stepCard = createMobileLargeImageCard(step, progressStatus || {});
                container.appendChild(stepCard);
            });
        }

        function createMobileLargeImageCard(step, progressData) {
            const stepStatus = progressData[`step${step.number}`] || 'locked';
            
            const card = document.createElement('div');
            card.className = 'mobile-large-image-card';
            
            // Check if step is accessible (locked steps 9 and 10 should not be clickable)
            if (stepStatus === 'locked' && (step.number === 9 || step.number === 10)) {
                card.style.opacity = '0.6';
                card.style.cursor = 'not-allowed';
                card.onclick = () => {
                    alert(`Step ${step.number} is locked and must be manually opened by an administrator.`);
                };
            } else if (stepStatus === 'locked') {
                card.style.opacity = '0.6';
                card.style.cursor = 'not-allowed';
                card.onclick = () => {
                    alert(`Step ${step.number} is locked. Complete the previous steps first.`);
                };
            } else {
                card.onclick = () => openMobileStepDetail(step.number);
            }
            
            const imageDiv = document.createElement('div');
            imageDiv.className = 'mobile-card-image';
            
            const img = document.createElement('img');
            img.src = `images/${step.image}`;
            img.alt = `Step ${step.number}: ${step.title}`;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            
            // Add error handling for images
            img.onerror = function() {
                console.error('Failed to load image:', `images/${step.image}`);
                this.src = 'images/step1_image.png'; // Fallback image
                this.alt = 'Image not available';
            };
            
            img.onload = function() {
                console.log('Successfully loaded image:', `images/${step.image}`);
            };
            
            imageDiv.appendChild(img);
            
            const titleDiv = document.createElement('div');
            titleDiv.className = 'mobile-card-title';
            titleDiv.textContent = `Step ${step.number}: ${step.title}`;
            
            const statusDiv = document.createElement('div');
            statusDiv.className = 'mobile-card-status';
            
            const statusIcon = document.createElement('img');
            const statusText = document.createElement('span');
            
            // Set status icon and text with proper image paths
            if (stepStatus === 'complete') {
                statusIcon.src = 'images/ic_status_complete.png';
                statusIcon.alt = 'Complete';
                statusText.textContent = 'Complete';
                statusText.style.color = '#FFFF00'; // Yellow as per mobile app
            } else if (stepStatus === 'in_progress') {
                statusIcon.src = 'images/ic_status_in_progress.png';
                statusIcon.alt = 'In Progress';
                statusText.textContent = 'In Progress';
                statusText.style.color = '#000000'; // Black as per mobile app
            } else {
                statusIcon.src = 'images/ic_status_locked.png';
                statusIcon.alt = 'Locked';
                statusText.textContent = 'Locked';
                statusText.style.color = '#FF0000'; // Red as per mobile app
            }
            
            // Add error handling for status icons
            statusIcon.onerror = function() {
                console.error('Failed to load status icon:', this.src);
                this.style.display = 'none';
            };
            
            statusIcon.onload = function() {
                console.log('Successfully loaded status icon:', this.src);
            };
            
            statusIcon.style.width = '24px';
            statusIcon.style.height = '24px';
            statusIcon.style.marginRight = '8px';
            
            statusDiv.appendChild(statusIcon);
            statusDiv.appendChild(statusText);
            
            card.appendChild(imageDiv);
            card.appendChild(titleDiv);
            card.appendChild(statusDiv);
            
            return card;
        }

        function openMobileStepDetail(stepNumber) {
            console.log(`📱 Opening Mobile Step Detail for Step ${stepNumber}`);
            
            // Find the step definition
            const step = stepDefinitions.find(s => s.number === stepNumber);
            if (!step) {
                console.error('Step not found:', stepNumber);
                return;
            }
            
            // Update detail page title
            const titleElement = document.getElementById('stepDetailTitle');
            if (titleElement) {
                titleElement.textContent = `Step ${stepNumber} Details`;
            }
            
            // Update user name
            const userNameElement = document.getElementById('stepDetailUserName');
            if (userNameElement) {
                const userName = window.sessionUserEmail || username || 'User';
                userNameElement.textContent = `User: ${userName}`;
            }
            
            // Load admin comment for this step RIGHT BELOW USERNAME
            loadTopAdminComment(stepNumber);
            
            // Generate step detail content
            generateMobileStepDetailContent(step);
            
            // Show detail page
            const detailPage = document.getElementById('stepDetailPage');
            if (detailPage) {
                detailPage.classList.add('visible');
                document.body.style.overflow = 'hidden'; // Prevent background scroll
            }
        }

        function closeMobileStepDetail() {
            console.log('📱 Closing Mobile Step Detail');
            
            const detailPage = document.getElementById('stepDetailPage');
            if (detailPage) {
                detailPage.classList.remove('visible');
                document.body.style.overflow = 'auto'; // Restore scroll
            }
        }

        function generateMobileStepDetailContent(step) {
            console.log('📱 Generating Mobile Step Detail Content for Step', step.number);
            
            const container = document.getElementById('stepDetailContent');
            if (!container) {
                console.error('Step detail content container not found');
                return;
            }
            
            container.innerHTML = '';
            
            // Create step header like mobile app
            const stepHeader = document.createElement('div');
            stepHeader.className = 'step-detail-header-info';
            stepHeader.style.cssText = `
                padding: 16px;
                background: #F5F5F5;
                border-radius: 8px;
                margin-bottom: 24px;
                text-align: center;
            `;
            
            const stepTitle = document.createElement('div');
            stepTitle.style.cssText = `
                font-size: 18px;
                font-weight: bold;
                color: #000000;
                margin-bottom: 16px;
            `;
            stepTitle.textContent = `Step ${step.number}: ${step.title}`;
            
            stepHeader.appendChild(stepTitle);
            container.appendChild(stepHeader);
            
            // Generate individual requirement sections (like mobile app)
            if (step.requirements && step.requirements.length > 0) {
                console.log(`📝 Generating ${step.requirements.length} requirements for step ${step.number}`);
                step.requirements.forEach((req, index) => {
                    const requirementSection = createMobileRequirementSection(req, step.number, index + 1);
                    container.appendChild(requirementSection);
                });
            }
            
            // Load uploaded documents status for this step after a short delay to ensure UI is ready
            console.log(`🔍 Starting document loading for step ${step.number}`);
            setTimeout(() => {
                loadUploadedDocumentsStatus(step.number);
                
                // Load documents for each requirement (like admin view)
                console.log(`🔄 Loading documents for step ${step.number} requirements in mobile detail view`);
                console.log(`🔄 Step requirements:`, step.requirements);
                if (step.requirements) {
                    step.requirements.forEach((requirement, index) => {
                        const documentId = requirement.documentId;
                        const documentNumber = index + 1;
                        console.log(`📄 Loading documents for requirement ${documentNumber}: ${documentId}`);
                        console.log(`📄 Requirement object:`, requirement);
                        
                        // Load documents for this specific requirement
                        setTimeout(() => {
                            console.log(`🚀 TRIGGERING loadUserDocumentsForRequirement(${step.number}, ${documentId})`);
                            loadUserDocumentsForRequirement(step.number, documentId);
                        }, 100 + (index * 50)); // Stagger the loading
                    });
                } else {
                    console.log(`❌ No requirements found for step ${step.number}`);
                }
                
                // Admin comments are only shown at the top (topAdminCommentSection)
            }, 200); // Slightly longer delay to ensure everything is ready
        }
        
        function createMobileRequirementSection(requirement, stepNumber, reqNumber) {
            const section = document.createElement('div');
            section.className = 'mobile-requirement-section';
            section.style.cssText = `
                margin-bottom: 24px;
                padding: 16px;
                border: 1px solid #E0E0E0;
                border-radius: 8px;
                background: #FFFFFF;
            `;
            
            // Requirement title
            const title = document.createElement('div');
            title.style.cssText = `
                font-size: 16px;
                font-weight: bold;
                color: #000000;
                margin-bottom: 8px;
            `;
            title.textContent = `${reqNumber}. ${requirement.title}`;
            
            // Description
            const description = document.createElement('div');
            description.style.cssText = `
                font-size: 14px;
                color: #555555;
                margin-bottom: 8px;
                line-height: 1.4;
            `;
            description.textContent = requirement.description;
            
            // Link if available (like mobile app)
            if (requirement.link) {
                const linkElement = document.createElement('a');
                linkElement.href = requirement.link;
                linkElement.target = '_blank';
                linkElement.style.cssText = `
                    color: #2196F3;
                    text-decoration: underline;
                    font-size: 14px;
                    display: block;
                    margin-bottom: 8px;
                    word-break: break-all;
                `;
                linkElement.textContent = requirement.link;
                section.appendChild(linkElement);
            }
            
            // Status container (check icon + status text)
            const statusContainer = document.createElement('div');
            statusContainer.style.cssText = `
                display: flex;
                flex-direction: column;
                align-items: center;
                margin: 16px 0;
            `;
            
            // Check icon (hidden initially, shown when submitted)
            const checkIcon = document.createElement('img');
            checkIcon.id = `checkIcon_${stepNumber}_${reqNumber}`;
            checkIcon.src = 'images/ic_check_circle.png';
            checkIcon.alt = 'Submitted';
            checkIcon.style.cssText = `
                width: 48px;
                height: 48px;
                display: none;
                margin-bottom: 8px;
            `;
            
            // Status text
            const statusText = document.createElement('div');
            statusText.id = `statusText_${stepNumber}_${reqNumber}`;
            statusText.style.cssText = `
                font-size: 18px;
                font-weight: bold;
                color: #000000;
                margin-bottom: 8px;
                text-align: center;
            `;
            statusText.textContent = 'Upload your document';
            
            statusContainer.appendChild(checkIcon);
            statusContainer.appendChild(statusText);
            
            // Upload button (like mobile app)
            const uploadButton = document.createElement('button');
            uploadButton.id = `uploadBtn_${stepNumber}_${reqNumber}`;
            uploadButton.style.cssText = `
                background: #6EC6FF;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                margin: 8px auto;
                display: block;
            `;
            uploadButton.textContent = 'Upload Document';
            uploadButton.onclick = () => handleMobileDocumentUpload(stepNumber, requirement.documentId, reqNumber);
            
            // Attempts remaining text (like mobile app)
            const attemptsText = document.createElement('div');
            attemptsText.id = `attemptsText_${stepNumber}_${reqNumber}`;
            attemptsText.style.cssText = `
                font-size: 14px;
                color: #000000;
                text-align: center;
                margin-top: 8px;
                display: none;
            `;
            attemptsText.textContent = `You can submit ${requirement.maxAttempts || 3} more time(s).`;
            
            // Add document container for this requirement
            const documentsContainer = document.createElement('div');
            documentsContainer.id = `userDocuments_${stepNumber}_${requirement.documentId}`;
            documentsContainer.className = 'requirement-documents-section';
            documentsContainer.style.cssText = `
                margin: 10px 0; 
                padding: 10px; 
                background: #f8f9fa; 
                border-radius: 6px; 
                border-left: 3px solid #6ea4ce;
            `;
            documentsContainer.innerHTML = '<div style="color: #666; font-style: italic; font-size: 14px;">📄 Loading uploaded documents...</div>';
            
            section.appendChild(title);
            section.appendChild(description);
            section.appendChild(documentsContainer); // Add documents container after description
            
            // TASK FIX: Add special interface support for ethical form and matching interface
            if (requirement.isEthicalForm) {
                console.log('📝 MOBILE: Creating ethical form for requirement:', requirement.documentId);
                const ethicalFormContainer = document.createElement('div');
                ethicalFormContainer.id = `ethicalForm_${stepNumber}_${requirement.documentId}`;
                ethicalFormContainer.style.cssText = 'margin: 15px 0;';
                section.appendChild(ethicalFormContainer);
                
                // Load ethical preference form
                setTimeout(() => {
                    console.log('🔄 MOBILE: Loading ethical preference form...');
                    loadEthicalPreferenceForm(stepNumber, requirement.documentId, ethicalFormContainer);
                }, 100);
            } else if (requirement.isMatchingInterface) {
                console.log('🧩 MOBILE: Creating matching interface for requirement:', requirement.documentId);
                const matchingContainer = document.createElement('div');
                matchingContainer.id = `matchingInterface_${stepNumber}_${requirement.documentId}`;
                matchingContainer.style.cssText = 'margin: 15px 0;';
                section.appendChild(matchingContainer);
                
                // Load ethical matching interface
                setTimeout(() => {
                    console.log('🔄 MOBILE: Loading ethical matching interface...');
                    loadEthicalMatchingInterface(stepNumber, requirement.documentId, matchingContainer);
                }, 100);
            } else if (requirement.hasAppointmentScheduling) {
                console.log('📅 MOBILE: Creating appointment scheduling for requirement:', requirement.documentId);
                const appointmentContainer = document.createElement('div');
                appointmentContainer.id = `appointmentScheduling_${stepNumber}_${requirement.documentId}`;
                appointmentContainer.style.cssText = 'margin: 15px 0;';
                section.appendChild(appointmentContainer);
                
                // Load appointment scheduling interface
                setTimeout(() => {
                    console.log('🔄 MOBILE: Loading appointment scheduling...');
                    loadAppointmentScheduling(stepNumber, requirement.documentId, appointmentContainer);
                }, 100);
            } else {
                // Only show upload button for regular requirements
                section.appendChild(statusContainer);
                section.appendChild(uploadButton);
                section.appendChild(attemptsText);
            }
            
            return section;
        }
        
        function loadUploadedDocumentsStatus(stepNumber) {
            console.log('🔍 MOBILE APP EXACT SEARCH - Loading documents for step', stepNumber);
            
            // Use the userId from URL parameter if available (for admin viewing user's progress)
            // Otherwise use the current user's ID
            const targetUserId = userId || window.sessionUserId;
            console.log('Target User ID:', targetUserId);
            console.log('Current User ID:', window.sessionUserId);
            console.log('Admin Status:', isAdminUser);
            
            if (!db || !targetUserId) {
                console.error('❌ Database or user ID not available');
                return;
            }
            
            // EXACTLY LIKE MOBILE APP: Check user_submissions_status first (primary location)
            console.log(`🔎 PRIMARY: user_submissions_status/${targetUserId}/step${stepNumber}_documents`);
            
            db.collection('user_submissions_status').doc(targetUserId)
                .collection(`step${stepNumber}_documents`)
                .get()
                .then(snapshot => {
                    console.log(`📱 MOBILE APP PRIMARY: Found ${snapshot.size} documents in user_submissions_status`);
                    
                    if (snapshot.size > 0) {
                        snapshot.forEach(doc => {
                            const data = doc.data();
                            console.log(`📄 MOBILE PRIMARY DOC: ${doc.id}`, data);
                            
                            // Add document ID to data for processing
                            data.documentId = doc.id;
                            updateAnyDocumentUI(stepNumber, data, null);
                        });
                    }
                    
                    // ALSO check user_documents (secondary location)
                    console.log(`🔎 SECONDARY: user_documents/${targetUserId}/step${stepNumber}_documents`);
                    return db.collection('user_documents').doc(targetUserId)
                        .collection(`step${stepNumber}_documents`)
                        .get();
                })
                .then(snapshot => {
                    console.log(`📱 MOBILE APP SECONDARY: Found ${snapshot.size} documents in user_documents`);
                    
                    if (snapshot.size > 0) {
                        snapshot.forEach(doc => {
                            const data = doc.data();
                            console.log(`📄 MOBILE SECONDARY DOC: ${doc.id}`, data);
                            
                            // Add document ID to data for processing
                            data.documentId = doc.id;
                            updateAnyDocumentUI(stepNumber, data, null);
                        });
                    } else {
                        console.log('No documents found in either location. Checking for files in Firebase Storage directly...');
                        
                        // Check Firebase Storage directly for files
                        if (storage) {
                            const storageRef = storage.ref(`user_documents/${targetUserId}/step${stepNumber}`);
                            storageRef.listAll()
                                .then(result => {
                                    console.log(`📦 STORAGE CHECK: Found ${result.items.length} files in storage for step ${stepNumber}`);
                                    result.items.forEach(itemRef => {
                                        console.log(`📄 Storage file: ${itemRef.name}`);
                                        
                                        // Get download URL for each file
                                        itemRef.getDownloadURL()
                                            .then(url => {
                                                console.log(`🔗 File URL: ${url}`);
                                                // Create a simple document object with the URL
                                                const docData = {
                                                    documentId: `document_1_${itemRef.name}`,
                                                    documentNumber: 1,
                                                    fileName: itemRef.name,
                                                    documentUrl: url,
                                                    submitted: true,
                                                    attempts: 1
                                                };
                                                updateAnyDocumentUI(stepNumber, docData, null);
                                            })
                                            .catch(error => {
                                                console.error(`❌ Error getting download URL: ${error.message}`);
                                            });
                                    });
                                })
                                .catch(error => {
                                    console.error(`❌ Error listing files in storage: ${error.message}`);
                                });
                        }
                    }
                })
                .catch(error => {
                    console.error('❌ Error loading mobile app documents:', error);
                });
        }
        
        function loadAdminComments(stepNumber, currentUserId) {
            console.log(`🗨️ MOBILE APP COMMENTS - Loading for step ${stepNumber}, user ${currentUserId}`);
            
            const stepKey = `step${stepNumber}`;
            const commentPath = `adoption_progress/${currentUserId}/comments/${stepKey}`;
            
            console.log(`📍 Comment path: ${commentPath}`);
            
            // EXACTLY LIKE MOBILE APP: adoption_progress/{userId}/comments/{stepKey}
            db.collection('adoption_progress').doc(currentUserId)
                .collection('comments').doc(stepKey)
                .get()
                .then(doc => {
                    console.log(`📋 Comment document exists: ${doc.exists}`);
                    
                    if (doc.exists) {
                        const data = doc.data();
                        console.log(`📄 Comment document data:`, data);
                        
                        const comment = data.comment;
                        if (comment && comment.trim()) {
                            console.log(`💬 ✅ FOUND Admin comment for ${stepKey}:`, comment);
                            displayAdminComment(comment, stepNumber);
                        } else {
                            console.log(`💬 ❌ Empty comment field for ${stepKey}`);
                        }
                    } else {
                        console.log(`💬 ❌ No comment document exists for ${stepKey}`);
                        
                        // DEBUG: Check if user has any comments at all
                        db.collection('adoption_progress').doc(currentUserId)
                            .collection('comments')
                            .get()
                            .then(snapshot => {
                                console.log(`🔍 User has ${snapshot.size} total comment documents:`);
                                snapshot.forEach(commentDoc => {
                                    console.log(`  - ${commentDoc.id}:`, commentDoc.data());
                                });
                            });
                    }
                })
                .catch(error => {
                    console.error('❌ Error loading admin comments:', error);
                });
        }
        
        function displayAdminComment(comment, stepNumber) {
            console.log('📝 Displaying admin comment:', comment);
            
            // Find the existing admin comment section in the step detail view
            const adminCommentSection = document.getElementById(`adminCommentSection_${stepNumber}`);
            const adminCommentText = document.getElementById(`adminCommentText_${stepNumber}`);
            
            if (adminCommentSection && adminCommentText) {
                // Use the existing admin comment section
                adminCommentText.textContent = comment;
                adminCommentSection.style.display = 'block';
                adminCommentSection.style.background = '#f0f7ff';
                adminCommentSection.style.border = '1px solid #007bff';
                adminCommentSection.style.borderRadius = '8px';
                adminCommentSection.style.padding = '15px';
                adminCommentSection.style.margin = '15px 0';
                
                console.log('✅ Admin comment displayed in existing section');
                return;
            }
            
            // Fallback: Create new admin comment section if the existing one is not found
            const container = document.getElementById('stepRequirements') || document.querySelector('.step-detail-content');
            if (!container) {
                console.error('❌ No container found for admin comment');
                return;
            }
            
            // Create admin comment section
            const adminSection = document.createElement('div');
            adminSection.className = 'admin-comment-section';
            adminSection.style.cssText = `
                background: #f0f7ff;
                border: 1px solid #007bff;
                border-radius: 8px;
                padding: 15px;
                margin: 15px 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            `;
            
            // ADMIN COMMENT STRUCTURE
            adminSection.innerHTML = `
                <div style="
                    font-size: 14px;
                    font-weight: bold;
                    color: #333333;
                    margin-bottom: 4px;
                ">Admin Comment:</div>
                <div style="
                    font-size: 14px;
                    color: #666666;
                    line-height: 1.4;
                ">${comment}</div>
            `;
            
            // Insert at the BOTTOM of the container (append, not prepend)
            container.appendChild(adminSection);
            
            console.log('✅ MOBILE APP EXACT admin comment displayed');
            console.log('📍 Admin comment element added to container:', container.id);
            console.log('📊 Container children count:', container.children.length);
            console.log('🎯 Admin section element:', adminSection);
            console.log('📏 Admin section position:', adminSection.getBoundingClientRect());
        }
        
        // MOBILE APP EXACT LOGIC - Update UI based on submission status and attempts
        function updateAnyDocumentUI(stepNumber, docData, docUrl) {
            console.log('📱 MOBILE APP UI UPDATE:', docData);
            
            // EXTRACT DOCUMENT NUMBER from mobile app document ID patterns
            let documentNumber = docData.documentNumber || docData.docNumber;
            
            // If not found, extract from documentId (mobile app pattern: "document_X_name")
            if (!documentNumber && docData.documentId) {
                const match = docData.documentId.match(/document_(\d+)_/);
                if (match) {
                    documentNumber = parseInt(match[1]);
                    console.log(`🔍 EXTRACTED document number ${documentNumber} from ID: ${docData.documentId}`);
                } else {
                    console.log(`⚠️ Could not extract number from documentId: ${docData.documentId}`);
                }
            }
            
            // Default to 1 if still not found
            if (!documentNumber) {
                documentNumber = 1;
                console.log(`⚠️ No document number found, defaulting to 1`);
            }
            
            console.log(`🎯 PROCESSING: Document ${documentNumber} from ${docData.documentId}`);
            
            // IMPORTANT: Also update the documents container to show the document
            const documentsContainer = document.getElementById(`step${stepNumber}DocumentsContainer`);
            if (documentsContainer) {
                console.log(`📋 Found documents container for step ${stepNumber}`);
                
                // Clear "No documents" message if it exists
                if (documentsContainer.textContent.includes('No documents uploaded')) {
                    documentsContainer.innerHTML = '';
                }
                
                // Create document entry
                const docElement = document.createElement('div');
                docElement.className = 'document-entry';
                docElement.style.cssText = `
                    display: flex;
                    align-items: center;
                    margin: 8px 0;
                    padding: 8px;
                    background: #f9f9f9;
                    border-radius: 4px;
                    border: 1px solid #ddd;
                `;
                
                const fileName = docData.fileName || 'Document ' + documentNumber;
                const url = docData.documentUrl || docUrl;
                
                docElement.innerHTML = `
                    <div style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <span style="font-weight: bold;">${fileName}</span>
                    </div>
                    <button class="view-doc-btn" style="
                        background: #4CAF50;
                        color: white;
                        border: none;
                        padding: 4px 8px;
                        border-radius: 4px;
                        cursor: pointer;
                    ">View</button>
                `;
                
                if (url) {
                    docElement.querySelector('.view-doc-btn').onclick = () => {
                        console.log('Opening document URL:', url);
                        window.open(url, '_blank');
                    };
                } else {
                    docElement.querySelector('.view-doc-btn').disabled = true;
                    docElement.querySelector('.view-doc-btn').style.background = '#ccc';
                }
                
                documentsContainer.appendChild(docElement);
                console.log(`✅ Added document ${fileName} to container`);
            } else {
                console.log(`❌ Could not find documents container for step ${stepNumber}`);
            }
            
            // MOBILE APP FIELDS - exactly like mobile app
            const submitted = docData.submitted === true; // Mobile app uses boolean 'submitted' field
            const attempts = docData.attempts || 0;
            const MAX_SUBMISSION_ATTEMPTS = 3; // Mobile app constant
            const remainingAttempts = MAX_SUBMISSION_ATTEMPTS - attempts;
            
            console.log(`📋 Mobile App Status: docNumber=${documentNumber}, submitted=${submitted}, attempts=${attempts}, remaining=${remainingAttempts}`);
            
            // Find UI elements
            const checkIcon = document.getElementById(`checkIcon_${stepNumber}_${documentNumber}`);
            const statusText = document.getElementById(`statusText_${stepNumber}_${documentNumber}`);
            const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${documentNumber}`);
            const attemptsText = document.getElementById(`attemptsText_${stepNumber}_${documentNumber}`);
            
            if (checkIcon && statusText && uploadBtn) {
                console.log(`📱 Updating mobile app UI for step ${stepNumber}, doc ${documentNumber}`);
                
                if (submitted) {
                    // EXACTLY LIKE MOBILE APP - Document is submitted
                    checkIcon.style.display = 'block';
                    statusText.textContent = 'Submitted';
                    statusText.style.color = '#4CAF50';
                    uploadBtn.textContent = 'RE-UPLOAD DOCUMENT'; // Mobile app text
                    uploadBtn.style.background = '#6EC6FF'; // Mobile app color
                    
                    if (remainingAttempts <= 0) {
                        // No more attempts allowed
                        if (attemptsText) {
                            attemptsText.textContent = 'No more submissions allowed.';
                            attemptsText.style.display = 'block';
                        }
                        uploadBtn.disabled = true;
                        uploadBtn.style.opacity = '0.6';
                        console.log(`🚫 Document ${documentNumber}: Submitted, no attempts remaining`);
                    } else {
                        // Can still reupload
                        if (attemptsText) {
                            attemptsText.textContent = `You can submit ${remainingAttempts} more time(s).`;
                            attemptsText.style.display = 'block';
                            attemptsText.style.color = '#666';
                        }
                        uploadBtn.disabled = false;
                        uploadBtn.style.opacity = '1';
                        console.log(`✅ Document ${documentNumber}: Submitted, ${remainingAttempts} attempts left`);
                    }
                    
                    // RE-UPLOAD functionality (not view document)
                    uploadBtn.onclick = () => {
                        if (remainingAttempts > 0) {
                            console.log(`🔄 Initiating re-upload for document ${documentNumber}`);
                            handleMobileDocumentUpload(stepNumber, docData.documentId, documentNumber);
                        } else {
                            alert('No more submissions allowed for this document.');
                        }
                    };
                    
                } else {
                    // EXACTLY LIKE MOBILE APP - Document not submitted
                    checkIcon.style.display = 'none';
                    statusText.textContent = 'Upload your document';
                    statusText.style.color = '#666';
                    uploadBtn.textContent = 'Upload Document';
                    uploadBtn.style.background = '#6EC6FF';
                    uploadBtn.disabled = false;
                    uploadBtn.style.opacity = '1';
                    
                    if (attempts >= MAX_SUBMISSION_ATTEMPTS) {
                        // Max attempts reached without successful submission
                        uploadBtn.disabled = true;
                        if (attemptsText) {
                            attemptsText.textContent = 'No more submissions allowed.';
                            attemptsText.style.display = 'block';
                        }
                        console.log(`🚫 Document ${documentNumber}: Not submitted, no attempts remaining`);
                    } else {
                        // Can still upload
                        if (attemptsText) {
                            attemptsText.style.display = 'none';
                        }
                        console.log(`📤 Document ${documentNumber}: Not submitted, attempts left`);
                    }
                    
                    // Upload functionality
                    uploadBtn.onclick = () => {
                        console.log(`📤 Initiating upload for document ${documentNumber}`);
                        handleMobileDocumentUpload(stepNumber, docData.documentId, documentNumber);
                    };
                }
                
                console.log(`🎉 MOBILE APP UI: Successfully updated step ${stepNumber}, doc ${documentNumber}`);
            } else {
                console.log(`❌ UI elements not found for step ${stepNumber}, doc ${documentNumber}`);
                
                // Try document 1 as fallback
                if (documentNumber !== 1) {
                    const alt1CheckIcon = document.getElementById(`checkIcon_${stepNumber}_1`);
                    const alt1StatusText = document.getElementById(`statusText_${stepNumber}_1`);
                    const alt1UploadBtn = document.getElementById(`uploadBtn_${stepNumber}_1`);
                    
                    if (alt1CheckIcon && alt1StatusText && alt1UploadBtn) {
                        console.log(`🔄 Using fallback UI for step ${stepNumber}, doc 1`);
                        
                        if (submitted) {
                            alt1CheckIcon.style.display = 'block';
                            alt1StatusText.textContent = 'Submitted';
                            alt1StatusText.style.color = '#4CAF50';
                            alt1UploadBtn.textContent = 'RE-UPLOAD DOCUMENT';
                            alt1UploadBtn.style.background = '#6EC6FF';
                            alt1UploadBtn.onclick = () => handleMobileDocumentUpload(stepNumber, docData.documentId, 1);
                        }
                    }
                }
            }
        }
        
        function updateDocumentStatus(stepNumber, docData) {
            console.log(`🔄 MOBILE APP DATA - Updating document status for step ${stepNumber}:`, docData);
            
            // Use MOBILE APP field names exactly
            const documentId = docData.documentId;
            const documentNumber = docData.documentNumber || 1;
            const fileName = docData.fileName;
            const documentUrl = docData.documentUrl; // Mobile app uses 'documentUrl' not 'downloadUrl'
            const status = docData.status;
            const isSubmitted = status === 'submitted';
            const attempts = docData.attempts || 0;
            
            console.log(`📋 Mobile App Document Details:`);
            console.log(`   - Document ID: ${documentId}`);
            console.log(`   - Document Number: ${documentNumber}`);
            console.log(`   - File Name: ${fileName}`);
            console.log(`   - Document URL: ${documentUrl}`);
            console.log(`   - Status: ${status}`);
            console.log(`   - Submitted: ${isSubmitted}`);
            console.log(`   - Attempts: ${attempts}`);
            
            // Find which requirement this document belongs to using documentNumber
            const step = stepDefinitions.find(s => s.number === stepNumber);
            if (!step) {
                console.log(`❌ Step ${stepNumber} not found in stepDefinitions`);
                return;
            }
            
            // Use documentNumber to find the correct requirement (mobile app uses 1-based indexing)
            const reqNumber = documentNumber;
            const reqIndex = reqNumber - 1; // Convert to 0-based for array access
            
            if (reqIndex < 0 || reqIndex >= step.requirements.length) {
                console.log(`❌ Document number ${documentNumber} out of range for step ${stepNumber}`);
                return;
            }
            
            console.log(`📍 Updating requirement ${reqNumber} for step ${stepNumber} (mobile app structure)`);
            
            // Update the UI elements using mobile app requirement numbering
            const checkIcon = document.getElementById(`checkIcon_${stepNumber}_${reqNumber}`);
            const statusText = document.getElementById(`statusText_${stepNumber}_${reqNumber}`);
            const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${reqNumber}`);
            const attemptsText = document.getElementById(`attemptsText_${stepNumber}_${reqNumber}`);
            
            console.log(`UI Elements found:`, {
                checkIcon: !!checkIcon,
                statusText: !!statusText,
                uploadBtn: !!uploadBtn,
                attemptsText: !!attemptsText
            });
            
            if (checkIcon && statusText && uploadBtn) {
                if (isSubmitted) {
                    // Document is submitted - exactly like mobile app
                    checkIcon.style.display = 'block';
                    statusText.textContent = 'Submitted';
                    statusText.style.color = '#4CAF50';
                    
                    if (documentUrl) {
                        // Has URL - can view document
                        uploadBtn.textContent = 'View Document';
                        uploadBtn.style.background = '#4CAF50';
                        uploadBtn.disabled = false;
                        uploadBtn.onclick = () => {
                            console.log('🔗 Opening mobile app document:', documentUrl);
                            window.open(documentUrl, '_blank');
                        };
                    } else {
                        // No URL available but submitted
                        uploadBtn.textContent = 'Document Uploaded';
                        uploadBtn.style.background = '#6c757d';
                        uploadBtn.disabled = true;
                        uploadBtn.onclick = () => {
                            alert('Document URL not available');
                        };
                    }
                } else {
                    // Not submitted - keep upload functionality
                    checkIcon.style.display = 'none';
                    statusText.textContent = 'Upload your document';
                    statusText.style.color = '#666';
                    uploadBtn.textContent = 'Upload Document';
                    uploadBtn.style.background = '#6EC6FF';
                    uploadBtn.disabled = false;
                }
                
                if (attemptsText) {
                    if (attempts > 0) {
                        attemptsText.textContent = `Attempts: ${attempts}`;
                        attemptsText.style.display = 'block';
                    } else {
                        attemptsText.style.display = 'none';
                    }
                }
                
                console.log(`✅ Successfully updated UI for step ${stepNumber}, requirement ${reqNumber} (mobile app format)`);
            } else {
                console.log(`❌ Failed to find UI elements for step ${stepNumber}, requirement ${reqNumber}`);
            }
        }
        
        function handleMobileDocumentUpload(stepNumber, documentId, reqNumber) {
            console.log(`📤 Handling upload for step ${stepNumber}, document ${documentId}`);
            
            // Create file input
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
            
            fileInput.onchange = function(e) {
                const file = e.target.files[0];
                if (!file) return;
                
                // Show upload progress
                const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${reqNumber}`);
                if (uploadBtn) {
                    uploadBtn.textContent = 'Uploading...';
                    uploadBtn.disabled = true;
                }
                
                // Call existing upload function (this should be connected to your existing upload system)
                uploadDocumentToFirebase(file, stepNumber, documentId, reqNumber);
            };
            
            fileInput.click();
        }
        
        function uploadDocumentToFirebase(file, stepNumber, documentId, reqNumber) {
            console.log('📁 MOBILE APP UPLOAD - Uploading file to Firebase:', file.name);
            
            if (!storage || !userId) {
                alert('Upload system not available');
                return;
            }
            
            // Use MOBILE APP storage structure: user_documents/{userId}/step{X}/{fileName}
            const fileName = `step${stepNumber}_${documentId}_${Date.now()}.pdf`;
            const storageRef = storage.ref(`user_documents/${userId}/step${stepNumber}/${fileName}`);
            
            console.log('📂 Mobile app upload path:', `user_documents/${userId}/step${stepNumber}/${fileName}`);
            
            // Upload file
            const uploadTask = storageRef.put(file);
            
            uploadTask.on('state_changed',
                (snapshot) => {
                    // Progress
                    const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                    console.log('Upload progress:', progress + '%');
                    
                    // Update button with progress
                    const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${reqNumber}`);
                    if (uploadBtn) {
                        uploadBtn.textContent = `Uploading... ${Math.round(progress)}%`;
                    }
                },
                (error) => {
                    // Error
                    console.error('Upload failed:', error);
                    alert('Upload failed: ' + error.message);
                    
                    // Reset button
                    const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${reqNumber}`);
                    if (uploadBtn) {
                        uploadBtn.textContent = 'Upload Document';
                        uploadBtn.disabled = false;
                    }
                },
                () => {
                    // Success
                    uploadTask.snapshot.ref.getDownloadURL().then((downloadURL) => {
                        console.log('📥 File uploaded successfully, URL:', downloadURL);
                        
                        // Get current attempts
                        db.collection('user_submissions_status').doc(userId)
                            .collection(`step${stepNumber}_documents`)
                            .doc(documentId)
                            .get()
                            .then(doc => {
                                const currentAttempts = doc.exists ? (doc.data().attempts || 0) : 0;
                                const newAttempts = currentAttempts + 1;
                                
                                // MOBILE APP data structure exactly
                                const documentData = {
                                    documentUrl: downloadURL,     // Mobile app uses 'documentUrl'
                                    fileName: file.name,
                                    uploadedAt: Date.now(),      // Mobile app uses timestamp
                                    submitted: true,             // Mobile app uses boolean 'submitted' field
                                    status: 'submitted',         // Keep for compatibility
                                    attempts: newAttempts,
                                    documentNumber: reqNumber,   // Mobile app field
                                    documentId: documentId,      // Mobile app field
                                    stepNumber: stepNumber       // Mobile app field
                                };
                                
                                console.log('💾 Saving mobile app data structure:', documentData);
                                
                                // FIXED: Generate unique document ID for multiple uploads per step
                                const uniqueDocId = `${documentId}_${documentData.uploadedAt}`;
                                
                                // Save to user_submissions_status (mobile app primary location)
                                const saveToSubmissionStatus = db.collection('user_submissions_status')
                                    .doc(userId)
                                    .collection(`step${stepNumber}_documents`)
                                    .doc(uniqueDocId)
                                    .set(documentData);
                                
                                // Save to user_documents (mobile app secondary location for admin)
                                const saveToUserDocuments = db.collection('user_documents')
                                    .doc(userId)
                                    .collection(`step${stepNumber}_documents`)
                                    .doc(uniqueDocId)
                                    .set(documentData);
                                
                                // Also save to adoption_progress for backward compatibility
                                const saveToAdoptionProgress = db.collection('adoption_progress')
                                    .doc(userId)
                                    .collection(`step${stepNumber}_uploads`)
                                    .doc(uniqueDocId)
                                    .set({
                                        ...documentData,
                                        fileUrl: documentData.documentUrl,  // Legacy field name
                                        timestamp: documentData.uploadedAt  // Legacy field name
                                    });
                                
                                // Wait for all saves to complete
                                Promise.all([saveToSubmissionStatus, saveToUserDocuments, saveToAdoptionProgress])
                                    .then(() => {
                                        console.log('✅ Document saved to all required locations (user_submissions_status, user_documents, adoption_progress)');
                                        
                                        // Update UI to show success using mobile app data
                                        updateDocumentStatus(stepNumber, documentData);
                                        
                                        alert('Document uploaded successfully!');
                                        
                                        // Send notification about document upload
                                        sendAdoptionNotification('document_uploaded', stepNumber, {
                                            userId: userId,
                                            stepNumber: stepNumber,
                                            documentId: documentId,
                                            fileName: file.name,
                                            userName: window.sessionUserEmail || username || 'User',
                                            userEmail: window.sessionUserEmail || ''
                                        });
                                    })
                                    .catch(error => {
                                        console.error('❌ Error saving to required locations:', error);
                                        alert('Upload completed but failed to save metadata');
                                        
                                        // Reset button
                                        const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${reqNumber}`);
                                        if (uploadBtn) {
                                            uploadBtn.textContent = 'Upload Document';
                                            uploadBtn.disabled = false;
                                        }
                                    });
                            })
                            .catch(error => {
                                console.error('❌ Error getting current attempts:', error);
                                alert('Upload failed to save metadata');
                            });
                    });
                }
            );
        }



        function getStatusText(status) {
            switch (status) {
                case 'complete': return 'Complete';
                case 'in_progress': return 'In Progress';
                case 'locked': return 'Locked';
                default: return 'Unknown';
            }
        }

        // COMPREHENSIVE test function to find ALL documents in the database
        function testDocumentLoading() {
            const currentUserId = userId || window.sessionUserId;
            console.log('🧪 COMPREHENSIVE SEARCH for user:', currentUserId);
            
            if (!db || !currentUserId) {
                console.error('Cannot test - database or user ID not available');
                return;
            }
            
            // Check ALL possible collections and subcollections
            const allLocations = [
                'adoption_progress',
                'user_submissions_status', 
                'user_documents',
                'documents',
                'uploads'
            ];
            
            allLocations.forEach(collection => {
                console.log(`🔍 Searching collection: ${collection}`);
                
                // First check if user document exists
                db.collection(collection).doc(currentUserId).get()
                    .then(doc => {
                        if (doc.exists) {
                            console.log(`✅ User found in ${collection}:`, doc.data());
                            
                            // Check all possible subcollections
                            for (let step = 1; step <= 10; step++) {
                                const subcollections = [
                                    `step${step}_uploads`,
                                    `step${step}_documents`,
                                    `step_${step}_uploads`,
                                    `step_${step}_documents`,
                                    `documents`,
                                    `uploads`
                                ];
                                
                                subcollections.forEach(subcol => {
                                    db.collection(collection).doc(currentUserId)
                                        .collection(subcol)
                                        .get()
                                        .then(snapshot => {
                                            if (snapshot.size > 0) {
                                                console.log(`🎯 FOUND ${snapshot.size} documents in ${collection}/${subcol}:`);
                                                snapshot.forEach(subdoc => {
                                                    const data = subdoc.data();
                                                    console.log(`   📄 ${subdoc.id}:`, data);
                                                    
                                                    // Check for URL fields
                                                    const url = data.documentUrl || data.downloadUrl || data.url || data.downloadURL;
                                                    if (url) {
                                                        console.log(`   🔗 URL FOUND: ${url}`);
                                                    }
                                                });
                                            }
                                        })
                                        .catch(() => {}); // Silent fail for non-existent subcollections
                                });
                            }
                        } else {
                            console.log(`❌ No user document in ${collection}`);
                        }
                    })
                    .catch(() => {}); // Silent fail
            });
            
            // Also check top-level collections that might contain user data
            console.log('🔍 Checking top-level collections...');
            ['submissions', 'user_files', 'step_documents'].forEach(collection => {
                db.collection(collection).where('userId', '==', currentUserId).get()
                    .then(snapshot => {
                        if (snapshot.size > 0) {
                            console.log(`🎯 FOUND ${snapshot.size} user documents in top-level ${collection}:`);
                            snapshot.forEach(doc => {
                                console.log(`   📄 ${doc.id}:`, doc.data());
                            });
                        }
                    })
                    .catch(() => {});
            });
        }
        
        // Make test function globally available
        window.testDocumentLoading = testDocumentLoading;

        // Initialize mobile view when page loads (for users only)
        document.addEventListener('DOMContentLoaded', function() {
            const userRole = window.sessionUserRole || 'user';
            if (userRole !== 'admin') {
                console.log('🏠 DOM loaded - initializing mobile user view');
                setTimeout(initializeMobileUserView, 100);
                
                // Add test button for debugging (remove in production)
                setTimeout(() => {
                    if (window.location.search.includes('debug=1')) {
                        const testBtn = document.createElement('button');
                        testBtn.textContent = 'Debug Documents';
                        testBtn.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 9999; background: red; color: white; padding: 10px;';
                        testBtn.onclick = () => {
                            testDocumentLoading();
                            debugUIElements();
                            testAdminComments();
                            testManualAdminComment();
                        };
                        document.body.appendChild(testBtn);
                        
                        // Add a second button to create test admin comment
                        const createBtn = document.createElement('button');
                        createBtn.textContent = 'Create Test Comment';
                        createBtn.style.cssText = 'position: fixed; top: 60px; right: 10px; z-index: 9999; background: green; color: white; padding: 10px;';
                        createBtn.onclick = createTestAdminComment;
                        document.body.appendChild(createBtn);
                    }
                }, 1000);
        
        function debugUIElements() {
            console.log('🔍 DEBUGGING UI ELEMENTS:');
            for (let step = 1; step <= 10; step++) {
                for (let doc = 1; doc <= 8; doc++) {
                    const checkIcon = document.getElementById(`checkIcon_${step}_${doc}`);
                    const statusText = document.getElementById(`statusText_${step}_${doc}`);
                    const uploadBtn = document.getElementById(`uploadBtn_${step}_${doc}`);
                    
                    if (checkIcon || statusText || uploadBtn) {
                        console.log(`✅ UI Elements exist for Step ${step}, Doc ${doc}:`, {
                            checkIcon: !!checkIcon,
                            statusText: !!statusText,
                            uploadBtn: !!uploadBtn
                        });
                    }
                }
            }
        }
        
        function testAdminComments() {
            const currentUserId = userId || window.sessionUserId;
            console.log('🧪 TESTING ADMIN COMMENTS for user:', currentUserId);
            
            if (!db || !currentUserId) {
                console.error('Cannot test admin comments - database or user ID not available');
                return;
            }
            
            // Test all possible admin comment locations
            const testPaths = [
                'adoption_progress',
                'admin_comments', 
                'comments',
                'step_comments'
            ];
            
            testPaths.forEach(collection => {
                console.log(`🔍 Checking admin comments in: ${collection}`);
                
                // Check if user document exists
                db.collection(collection).doc(currentUserId).get()
                    .then(doc => {
                        if (doc.exists) {
                            console.log(`✅ User document found in ${collection}:`, doc.data());
                            
                            // Check for comments subcollection
                            return db.collection(collection).doc(currentUserId)
                                .collection('comments')
                                .get();
                        } else {
                            console.log(`❌ No user document in ${collection}`);
                            return null;
                        }
                    })
                    .then(snapshot => {
                        if (snapshot && snapshot.size > 0) {
                            console.log(`🗨️ Found ${snapshot.size} comment documents in ${collection}/comments:`);
                            snapshot.forEach(commentDoc => {
                                console.log(`  📝 ${commentDoc.id}:`, commentDoc.data());
                            });
                        } else if (snapshot) {
                            console.log(`❌ No comments in ${collection}/comments`);
                        }
                    })
                    .catch(() => {}); // Silent fail
            });
            
            // Also check current step specifically
            const currentStep = window.location.pathname.includes('step') ? 
                parseInt(window.location.pathname.match(/step(\d+)/)?.[1] || '1') : 1;
            
            console.log(`🎯 Testing current step ${currentStep} specifically`);
            loadAdminComments(currentStep, currentUserId);
        }
        
        function testManualAdminComment() {
            console.log('🧪 MANUAL ADMIN COMMENT TEST');
            
            // First, let's test if the display function works
            console.log('Testing display function with sample comment...');
            displayAdminComment('TEST: This is a sample admin comment to verify display works');
            
            // Get current user and step
            const currentUserId = userId || window.sessionUserId;
            const currentStepElement = document.querySelector('.mobile-step-detail-container');
            
            if (currentStepElement) {
                // Try to extract step number from current context
                const stepTitle = document.querySelector('h2')?.textContent || '';
                const stepMatch = stepTitle.match(/Step (\d+)/);
                const stepNumber = stepMatch ? parseInt(stepMatch[1]) : 3; // Default to 3
                
                console.log(`📍 Detected step: ${stepNumber}, user: ${currentUserId}`);
                
                // Manual direct Firebase query
                if (db && currentUserId) {
                    const commentPath = `adoption_progress/${currentUserId}/comments/step${stepNumber}`;
                    console.log(`🔍 DIRECT QUERY: ${commentPath}`);
                    
                    db.collection('adoption_progress').doc(currentUserId)
                        .collection('comments').doc(`step${stepNumber}`)
                        .get()
                        .then(doc => {
                            console.log(`📋 Direct query result - exists: ${doc.exists}`);
                            if (doc.exists) {
                                const data = doc.data();
                                console.log(`📄 Direct query data:`, data);
                                if (data.comment) {
                                    console.log(`✅ REAL COMMENT FOUND: ${data.comment}`);
                                    displayAdminComment(data.comment);
                                }
                            } else {
                                console.log(`❌ No comment document found at ${commentPath}`);
                                
                                // Try alternative paths
                                const alternatives = [
                                    `step_${stepNumber}`,
                                    `Step${stepNumber}`,
                                    `Step_${stepNumber}`,
                                    stepNumber.toString()
                                ];
                                
                                alternatives.forEach(altStep => {
                                    db.collection('adoption_progress').doc(currentUserId)
                                        .collection('comments').doc(altStep)
                                        .get()
                                        .then(altDoc => {
                                            if (altDoc.exists) {
                                                console.log(`✅ FOUND comment with alternative key ${altStep}:`, altDoc.data());
                                            }
                                        });
                                });
                            }
                        })
                        .catch(error => {
                            console.error('❌ Direct query error:', error);
                        });
                }
                         }
         }
         
         function createTestAdminComment() {
             const currentUserId = userId || window.sessionUserId;
             console.log('🆕 CREATING TEST ADMIN COMMENT for user:', currentUserId);
             
             if (!db || !currentUserId) {
                 alert('Cannot create test comment - database or user ID not available');
                 return;
             }
             
             // Get current step
             const stepTitle = document.querySelector('h2')?.textContent || '';
             const stepMatch = stepTitle.match(/Step (\d+)/);
             const stepNumber = stepMatch ? parseInt(stepMatch[1]) : 3;
             
             const testComment = `Test admin comment for step ${stepNumber} - ${new Date().toLocaleString()}`;
             
             // Create comment in EXACT mobile app format
             db.collection('adoption_progress').doc(currentUserId)
                 .collection('comments').doc(`step${stepNumber}`)
                 .set({
                     comment: testComment,
                     timestamp: Date.now(),
                     createdBy: 'web-test'
                 })
                 .then(() => {
                     console.log('✅ Test admin comment created successfully');
                     alert('Test admin comment created! Now refresh and check if it appears.');
                     
                     // Immediately try to load and display it
                     loadAdminComments(stepNumber, currentUserId);
                 })
                 .catch(error => {
                     console.error('❌ Error creating test comment:', error);
                     alert('Error creating test comment: ' + error.message);
                 });
         }
              }
          });
          
          // LOAD COMPREHENSIVE FINAL FIXES INLINE
          setTimeout(() => {
              console.log('🔧 Loading comprehensive final fixes...');
              
              // COMPREHENSIVE FINAL FIXES FOR ALL THREE ISSUES
              console.log('🔧 COMPREHENSIVE FINAL FIXES STARTING...');
              
              // ISSUE 1 FIX: Admin completion notifications
              if (typeof sendAdoptionNotification === 'function') {
                  console.log('✅ sendAdoptionNotification function exists');
                  
                  // Enhance the function with more debugging
                  const originalSendAdoptionNotification = sendAdoptionNotification;
                  window.sendAdoptionNotification = function(status, stepNumber, additionalData = {}) {
                      console.log('🔔 ENHANCED sendAdoptionNotification called:', {
                          status,
                          stepNumber,
                          additionalData,
                          userId: additionalData.userId || window.sessionUserId,
                          currentUser: window.sessionUserId
                      });
                      
                      return originalSendAdoptionNotification(status, stepNumber, additionalData);
                  };
                  
                  console.log('✅ Enhanced sendAdoptionNotification with debugging');
              } else {
                  console.error('❌ sendAdoptionNotification function not found!');
              }
              
              // ISSUE 2 FIX: Document submission persistence
              if (typeof generateSubmissionStatus === 'function') {
                  console.log('✅ generateSubmissionStatus function exists');
                  
                  // Enhance the function to be more aggressive about detecting submissions
                  const originalGenerateSubmissionStatus = generateSubmissionStatus;
                  window.generateSubmissionStatus = function(submissionStatus, maxAttempts, remainingAttempts) {
                      console.log('📄 ENHANCED generateSubmissionStatus called:', {
                          submissionStatus,
                          isSubmittedFlag: submissionStatus.submitted,
                          statusField: submissionStatus.status,
                          hasFileName: !!submissionStatus.fileName,
                          hasDocumentUrl: !!submissionStatus.documentUrl
                      });
                      
                      // ENHANCED SUBMISSION DETECTION - check ALL possible indicators
                      const isSubmitted = !!(
                          submissionStatus.submitted || 
                          submissionStatus.status === 'submitted' ||
                          submissionStatus.status === 'pending_review' ||
                          submissionStatus.status === 'approved' ||
                          submissionStatus.status === 'complete' ||
                          submissionStatus.fileName ||
                          submissionStatus.documentUrl ||
                          submissionStatus.lastSubmissionTimestamp
                      );
                      
                      if (isSubmitted && !submissionStatus.submitted) {
                          console.log('🔧 FIXING: Detected submission but submitted flag was false');
                          submissionStatus.submitted = true;
                          if (!submissionStatus.status || submissionStatus.status === 'not_submitted') {
                              submissionStatus.status = 'submitted';
                          }
                      }
                      
                      console.log('📊 Final submission status:', {
                          isSubmitted,
                          finalStatus: submissionStatus.status,
                          submitted: submissionStatus.submitted
                      });
                      
                      return originalGenerateSubmissionStatus(submissionStatus, maxAttempts, remainingAttempts);
                  };
                  
                  console.log('✅ Enhanced generateSubmissionStatus with persistence detection');
              } else {
                  console.error('❌ generateSubmissionStatus function not found!');
              }
              
              // ISSUE 3 FIX: Admin comments display
              if (typeof loadAdminComments === 'function') {
                  console.log('✅ loadAdminComments function exists');
                  
                  // Enhance the function with more debugging and better display
                  const originalLoadAdminComments = loadAdminComments;
                  window.loadAdminComments = function(stepNumber) {
                      console.log('💬 ENHANCED loadAdminComments called for step:', stepNumber);
                      
                      // First call the original function
                      originalLoadAdminComments(stepNumber);
                      
                      // Then add additional checks and fallbacks
                      setTimeout(() => {
                          console.log('💬 Checking admin comment section after load...');
                          
                          const adminCommentSection = document.getElementById(`adminCommentSection_${stepNumber}`);
                          const adminCommentText = document.getElementById(`adminCommentText_${stepNumber}`);
                          
                          if (adminCommentSection) {
                              console.log('✅ Admin comment section found');
                              
                              // Make sure it's visible
                              adminCommentSection.style.display = 'block';
                              adminCommentSection.style.visibility = 'visible';
                              
                              // Add enhanced styling
                              adminCommentSection.style.cssText = `
                                  display: block !important;
                                  visibility: visible !important;
                                  background: #fff3cd;
                                  border: 2px solid #ffeaa7;
                                  border-radius: 8px;
                                  padding: 16px;
                                  margin: 16px 0;
                                  min-height: 40px;
                              `;
                              
                              if (adminCommentText) {
                                  if (!adminCommentText.textContent || adminCommentText.textContent.trim() === '') {
                                      console.log('⚠️ No admin comment text found, adding placeholder');
                                      adminCommentText.innerHTML = '<em style="color: #999;">No admin comments yet for this step.</em>';
                                  } else {
                                      console.log('✅ Admin comment text found:', adminCommentText.textContent);
                                  }
                              }
                          } else {
                              console.error('❌ Admin comment section not found in DOM!');
                              
                              // Create the section if it doesn't exist
                              const stepDetailContent = document.querySelector('.step-detail-content');
                              if (stepDetailContent) {
                                  console.log('🔧 Creating missing admin comment section...');
                                  
                                  const commentSection = document.createElement('div');
                                  commentSection.id = `adminCommentSection_${stepNumber}`;
                                  commentSection.className = 'admin-comment-section';
                                  commentSection.style.cssText = `
                                      display: block !important;
                                      background: #fff3cd;
                                      border: 2px solid #ffeaa7;
                                      border-radius: 8px;
                                      padding: 16px;
                                      margin: 16px 0;
                                  `;
                                  
                                  // Admin comment section removed as requested
                                  
                                  // Insert after the back button
                                  const backButton = stepDetailContent.querySelector('.back-button');
                                  if (backButton) {
                                      backButton.parentNode.insertBefore(commentSection, backButton.nextSibling);
                                  } else {
                                      stepDetailContent.insertBefore(commentSection, stepDetailContent.firstChild);
                                  }
                                  
                                  console.log('✅ Created admin comment section');
                              }
                          }
                      }, 500);
                      
                      return originalLoadAdminComments(stepNumber);
                  };
                  
                  console.log('✅ Enhanced loadAdminComments with better display logic');
              } else {
                  console.error('❌ loadAdminComments function not found!');
              }
              
              // ISSUE FIX: Enhanced showStepDetailView to ensure all components load properly
              if (typeof showStepDetailView === 'function') {
                  console.log('✅ showStepDetailView function exists');
                  
                  const originalShowStepDetailView = showStepDetailView;
                  window.showStepDetailView = function(stepNumber) {
                      console.log('📋 ENHANCED showStepDetailView called for step:', stepNumber);
                      
                      // Call original function first
                      const result = originalShowStepDetailView(stepNumber);
                      
                      // Add enhanced loading with multiple retries
                      setTimeout(() => {
                          console.log('🔄 Post-load enhancements for step:', stepNumber);
                          
                          // Force reload submission data
                          if (typeof loadStepSubmissionData === 'function') {
                              console.log('📄 Force reloading submission data...');
                              loadStepSubmissionData(stepNumber);
                          }
                          
                          // Force reload admin comments
                          if (typeof loadAdminComments === 'function') {
                              console.log('💬 Force reloading admin comments...');
                              loadAdminComments(stepNumber);
                          }
                          
                          // Force reload TOP admin comment
                          if (typeof loadTopAdminComment === 'function') {
                              console.log('🔝 Force reloading TOP admin comment...');
                              loadTopAdminComment(stepNumber);
                          }
                          
                          // Force refresh UI after data loads
                          setTimeout(() => {
                              if (typeof refreshStepDetailView === 'function') {
                                  console.log('🔄 Force refreshing step detail view...');
                                  refreshStepDetailView(stepNumber);
                              }
                          }, 1000);
                          
                      }, 100);
                      
                      return result;
                  };
                  
                  console.log('✅ Enhanced showStepDetailView with forced reloading');
              } else {
                  console.error('❌ showStepDetailView function not found!');
              }
              
          }, 1000);
          
      </script>

      <!-- Separate Debug Functions Script Block -->
      <script>
          setTimeout(() => {
              // ADD GLOBAL DEBUG FUNCTIONS
              window.debugNotifications = function() {
                  console.log('🔔 DEBUG: Testing notification system...');
                  if (typeof sendAdoptionNotification === 'function') {
                      sendAdoptionNotification('step_completed', 999, {
                          userId: window.sessionUserId || 'test_user',
                          debug: true
                      });
                  }
              };
              
              window.debugSubmissions = function(stepNumber = 3) {
                  console.log('📄 DEBUG: Testing submission status for step:', stepNumber);
                  if (typeof loadStepSubmissionData === 'function') {
                      loadStepSubmissionData(stepNumber);
                  }
                  
                  setTimeout(() => {
                      if (window.submissionData && window.submissionData[stepNumber]) {
                          console.log('📊 Submission data for step', stepNumber, ':', window.submissionData[stepNumber]);
                      }
                  }, 2000);
              };
              
              window.debugAdminComments = function(stepNumber = 3) {
                  console.log('💬 DEBUG: Testing admin comments for step:', stepNumber);
                  if (typeof loadAdminComments === 'function') {
                      loadAdminComments(stepNumber);
                  }
                  
                  // Test creating a fake comment
                  setTimeout(() => {
                      if (typeof displayAdminComment === 'function') {
                          displayAdminComment('TEST DEBUG COMMENT: This is a test admin comment to verify the display system works properly.');
                      }
                  }, 1000);
              };
              
              // NEW DEBUG FUNCTION: Test submission status persistence
              window.debugSubmissionPersistence = function(stepNumber = 3) {
                  console.log('🔄 DEBUG: Testing submission status persistence for step:', stepNumber);
                  
                  // Show current submission data
                  console.log('📊 Current submissionData:', submissionData[stepNumber]);
                  
                  // Test mobile app getSubmissionStatus for each requirement
                  if (stepDefinitions[stepNumber - 1] && stepDefinitions[stepNumber - 1].requirements) {
                      stepDefinitions[stepNumber - 1].requirements.forEach((req, index) => {
                          const docNumber = index + 1;
                          getSubmissionStatus(req.documentId, stepNumber).then(status => {
                              console.log(`📄 Doc ${docNumber} (${req.documentId}):`, status);
                              
                              // Check UI elements
                              const checkIcon = document.getElementById(`checkIcon_${stepNumber}_${docNumber}`);
                              const statusText = document.getElementById(`statusText_${stepNumber}_${docNumber}`);
                              const uploadBtn = document.getElementById(`uploadBtn_${stepNumber}_${docNumber}`);
                              
                              console.log(`   UI Elements: checkIcon=${!!checkIcon}, statusText=${!!statusText}, uploadBtn=${!!uploadBtn}`);
                              if (statusText) console.log(`   Current UI Text: "${statusText.textContent}"`);
                              if (checkIcon) console.log(`   Check Icon Display: "${checkIcon.style.display}"`);
                          });
                      });
                  }
                  
                  // Force refresh submission data from Firebase
                  const targetUserId = (isAdminUser && currentSelectedUser) ? currentSelectedUser.uid : userId;
                  if (targetUserId && db) {
                      console.log('🔄 Force refreshing from Firebase...');
                      
                      Promise.all([
                          db.collection('user_submissions_status').doc(targetUserId).get(),
                          db.collection('user_documents').where('userId', '==', targetUserId).where('stepNumber', '==', stepNumber).get()
                      ]).then(([submissionsDoc, documentsSnapshot]) => {
                          console.log('📋 Firebase refresh results:');
                          console.log('  - user_submissions_status exists:', submissionsDoc.exists);
                          console.log('  - user_documents count:', documentsSnapshot.size);
                          
                          if (submissionsDoc.exists) {
                              const data = submissionsDoc.data();
                              console.log('  - submissions data for step:', data[stepNumber]);
                              
                              // Update local submissionData
                              if (data[stepNumber]) {
                                  submissionData[stepNumber] = {
                                      ...submissionData[stepNumber],
                                      ...data[stepNumber]
                                  };
                                  console.log('✅ Updated local submissionData');
                              }
                          }
                          
                          if (!documentsSnapshot.empty) {
                              console.log('📄 Documents found:');
                              documentsSnapshot.forEach(doc => {
                                  const data = doc.data();
                                  console.log(`  - ${data.documentId}: ${data.fileName}`);
                                  
                                  // Ensure submission is marked in local data
                                  if (!submissionData[stepNumber]) submissionData[stepNumber] = {};
                                  if (!submissionData[stepNumber][data.documentId]) {
                                      submissionData[stepNumber][data.documentId] = {};
                                  }
                                  submissionData[stepNumber][data.documentId] = {
                                      ...submissionData[stepNumber][data.documentId],
                                      submitted: true,
                                      fileName: data.fileName,
                                      documentUrl: data.downloadURL,
                                      status: 'submitted'
                                  };
                              });
                          }
                          
                          // Force update UI with fresh data
                          console.log('🔄 Forcing UI update...');
                          setTimeout(() => {
                              updateMobileAppUIElements(stepNumber);
                          }, 500);
                      }).catch(error => {
                          console.error('❌ Error refreshing from Firebase:', error);
                      });
                  }
              };
              
              // NEW DEBUG FUNCTION: Test TOP admin comment specifically
              window.debugTopAdminComment = function(stepNumber = 3) {
                  console.log('🔝 DEBUG: Testing TOP admin comment for step:', stepNumber);
                  
                  // First check if elements exist
                  const topSection = document.getElementById('topAdminCommentSection');
                  const topText = document.getElementById('topAdminCommentText');
                  
                  console.log('🔍 TOP ELEMENTS CHECK:');
                  console.log('  - topAdminCommentSection exists:', !!topSection);
                  console.log('  - topAdminCommentText exists:', !!topText);
                  
                  if (topSection && topText) {
                      // Show a test comment
                      topText.textContent = `TEST ADMIN COMMENT FOR STEP ${stepNumber}: This is working correctly!`;
                      topSection.style.display = 'block';
                      console.log('✅ TOP ADMIN COMMENT TEST DISPLAYED');
                  } else {
                      console.error('❌ TOP ADMIN COMMENT ELEMENTS NOT FOUND');
                  }
                  
                  // Also try to load real comment
                  if (typeof loadTopAdminComment === 'function') {
                      console.log('🔄 Calling loadTopAdminComment...');
                      loadTopAdminComment(stepNumber);
                  }
              };
              
              console.log('✅ COMPREHENSIVE FINAL FIXES COMPLETE!');
              console.log('🧪 Debug functions available:');
              console.log('  - debugNotifications() - Test notification system');
              console.log('  - debugSubmissions(stepNumber) - Test submission persistence');
              console.log('  - debugSubmissionPersistence(stepNumber) - ENHANCED submission persistence test');
console.log('  - debugDocumentContainers(stepNumber) - Check if document containers exist');
console.log('  - testLoadDocuments(stepNumber) - Manually trigger document loading');
              console.log('  - debugAdminComments(stepNumber) - Test admin comment display');
              console.log('  - debugTopAdminComment(stepNumber) - Test TOP admin comment display');
              
          }, 1500);
          
      </script>
    <?php endif; ?>

    <!-- Mobile Menu JavaScript -->
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
                    
                    // Force enable pointer events and visibility
                    dropdownMenu.style.display = 'block';
                    dropdownMenu.style.pointerEvents = 'auto';
                    dropdownMenu.style.visibility = 'visible';
                    
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
                // Force disable pointer events and hide completely
                dropdownMenu.style.pointerEvents = 'none';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.display = 'none';
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
        function detectMobile() {
            if (window.innerWidth <= 768) {
                document.body.classList.add('mobile-view');
                
                // COMPLETE SIDEBAR REMOVAL on mobile
                const sidebar = document.querySelector('.sidebar');
                const contentWrapper = document.querySelector('.content-wrapper');
                
                if (sidebar) {
                    // Completely remove the sidebar from DOM on mobile
                    sidebar.remove();
                    console.log('✅ Sidebar completely removed from DOM on mobile');
                }
                
                if (contentWrapper) {
                    // Reset content wrapper to simple block layout
                    contentWrapper.style.display = 'block';
                    contentWrapper.style.width = '100%';
                    contentWrapper.style.padding = '0';
                }
                
                // Extra safety: remove any remaining sidebar elements
                const remainingSidebarElements = document.querySelectorAll('.sidebar, .sidebar *, .sidebar a, .sidebar li, aside');
                remainingSidebarElements.forEach(el => {
                    if (el.classList.contains('sidebar') || el.closest('.sidebar')) {
                        el.remove();
                    }
                });
                
                console.log('✅ Mobile mode activated - all sidebar elements removed');
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

        // Close menu when clicking on a link
        document.addEventListener('DOMContentLoaded', function() {
            // Add a small delay to ensure all elements are loaded
            setTimeout(() => {
                detectMobile();
            }, 100);
            
            document.querySelectorAll('.mobile-dropdown-menu a').forEach(link => {
                link.addEventListener('click', closeMobileMenu);
            });

            // Close menu on window resize if screen becomes larger
            window.addEventListener('resize', function() {
                detectMobile(); // Re-run mobile detection on resize
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
                    e.target.closest('.mobile-action-link') ||
                    e.target.closest('.mobile-upload-btn') ||
                    e.target.closest('.step-card') ||
                    e.target.closest('.mobile-large-image-card')) {
                    return;
                }
                
                if (dropdownMenu && hamburger && dropdownMenu.classList.contains('active') && 
                    !dropdownMenu.contains(e.target) && 
                    !hamburger.contains(e.target)) {
                    closeMobileMenu();
                }
            });
        });
    </script>
</body>

</html>