<?php
session_start();
require_once 'session_check.php';
require_once 'notification_service.php';

// Get current user ID (you'll need to implement this based on your authentication system)
function getCurrentUserId() {
    // This is a placeholder - implement based on your auth system
    // You might get this from Firebase Auth or your session
    return 'user123'; // Replace with actual user ID
}

$message = '';
$error = '';

if ($_POST) {
    $userId = getCurrentUserId();
    $notificationService = new NotificationService();
    
    try {
        switch ($_POST['action']) {
            case 'donation_submitted':
                $result = $notificationService->sendDonationNotification(
                    $userId,
                    $_POST['donation_type'] ?? 'food',
                    'submitted'
                );
                $message = 'Donation submission notification sent!';
                break;
                
            case 'adoption_step':
                $result = $notificationService->sendAdoptionNotification(
                    $userId,
                    'step_completed',
                    $_POST['step_number'] ?? 1
                );
                $message = 'Adoption step completion notification sent!';
                break;
                
            case 'appointment_scheduled':
                $result = $notificationService->sendAppointmentNotification(
                    $userId,
                    'scheduled',
                    $_POST['appointment_date'] ?? date('Y-m-d')
                );
                $message = 'Appointment scheduled notification sent!';
                break;
                
            case 'matching_found':
                $result = $notificationService->sendMatchingNotification(
                    $userId,
                    'match_found',
                    $_POST['child_name'] ?? 'Maria'
                );
                $message = 'Matching found notification sent!';
                break;
                
            case 'chat_message':
                $result = $notificationService->sendChatNotification(
                    $userId,
                    $_POST['sender_name'] ?? 'Admin',
                    $_POST['message'] ?? 'Hello! How can we help you today?',
                    'admin_id'
                );
                $message = 'Chat message notification sent!';
                break;
                
            case 'security_alert':
                $result = $notificationService->sendSecurityNotification(
                    $userId,
                    'login_attempt',
                    'New login attempt detected from an unrecognized device.',
                    true
                );
                $message = 'Security alert notification sent!';
                break;
                
            default:
                $error = 'Unknown action';
        }
        
        if (!$result && !$error) {
            $error = 'Failed to send notification';
        }
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Demo - Ally Foundation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .demo-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
        }
        
        .demo-section h3 {
            margin-top: 0;
            color: #555;
            font-size: 18px;
        }
        
        .demo-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        label {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }
        
        input, select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            background: #7CB9E8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #6ba8d1;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #7CB9E8;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #1976d2;
        }
        
        .info-box p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="notifications.php" class="back-link">← Back to Notifications</a>
        
        <h1>🔔 Notification System Demo</h1>
        
        <div class="info-box">
            <h4>📋 Demo Instructions</h4>
            <p>This demo allows you to test the notification system by sending different types of notifications. Click the buttons below to send notifications that will appear in your notification bell and the notifications page.</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="demo-section">
            <h3>📦 Donation Notifications</h3>
            <form method="post" class="demo-form">
                <input type="hidden" name="action" value="donation_submitted">
                <div class="form-group">
                    <label>Donation Type</label>
                    <select name="donation_type">
                        <option value="food">Food</option>
                        <option value="clothes">Clothes</option>
                        <option value="toys">Toys</option>
                        <option value="money">Money</option>
                        <option value="education">Education</option>
                        <option value="medicine">Medicine</option>
                    </select>
                </div>
                <button type="submit" class="btn">Send Donation Notification</button>
            </form>
        </div>
        
        <div class="demo-section">
            <h3>👶 Adoption Notifications</h3>
            <form method="post" class="demo-form">
                <input type="hidden" name="action" value="adoption_step">
                <div class="form-group">
                    <label>Step Number</label>
                    <select name="step_number">
                        <option value="1">Step 1</option>
                        <option value="2">Step 2</option>
                        <option value="3">Step 3</option>
                        <option value="4">Step 4</option>
                        <option value="5">Step 5</option>
                    </select>
                </div>
                <button type="submit" class="btn">Send Adoption Step Notification</button>
            </form>
        </div>
        
        <div class="demo-section">
            <h3>📅 Appointment Notifications</h3>
            <form method="post" class="demo-form">
                <input type="hidden" name="action" value="appointment_scheduled">
                <div class="form-group">
                    <label>Appointment Date</label>
                    <input type="date" name="appointment_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" class="btn">Send Appointment Notification</button>
            </form>
        </div>
        
        <div class="demo-section">
            <h3>💕 Matching Notifications</h3>
            <form method="post" class="demo-form">
                <input type="hidden" name="action" value="matching_found">
                <div class="form-group">
                    <label>Child Name</label>
                    <input type="text" name="child_name" placeholder="e.g., Maria" value="Maria">
                </div>
                <button type="submit" class="btn">Send Matching Notification</button>
            </form>
        </div>
        
        <div class="demo-section">
            <h3>💬 Chat Notifications</h3>
            <form method="post" class="demo-form">
                <input type="hidden" name="action" value="chat_message">
                <div class="form-group">
                    <label>Sender Name</label>
                    <input type="text" name="sender_name" placeholder="e.g., Admin" value="Admin">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <input type="text" name="message" placeholder="Message content" value="Hello! How can we help you today?">
                </div>
                <button type="submit" class="btn">Send Chat Notification</button>
            </form>
        </div>
        
        <div class="demo-section">
            <h3>🔒 Security Notifications</h3>
            <form method="post" class="demo-form">
                <input type="hidden" name="action" value="security_alert">
                <button type="submit" class="btn">Send Security Alert</button>
            </form>
        </div>
        
        <div class="info-box">
            <h4>🔧 Integration Guide</h4>
            <p>To integrate notifications into your existing code, simply include <code>notification_service.php</code> and use the helper functions like <code>sendDonationNotification()</code>, <code>sendAdoptionNotification()</code>, etc. in your existing PHP files when relevant events occur.</p>
        </div>
    </div>
</body>
</html> 