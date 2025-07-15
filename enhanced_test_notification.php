<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: signin.php');
    exit;
}

require_once 'notification_system_v2.php';

$message = '';
$messageType = '';

if ($_POST) {
    $notificationSystem = new NotificationSystemV2();
    
    if (isset($_POST['send_test'])) {
        $result = $notificationSystem->sendTestNotification($_SESSION['uid'], [
            'source' => 'enhanced_test_page',
            'testNote' => 'Enhanced test notification sent at ' . date('Y-m-d H:i:s'),
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        
        $message = $result ? 
            "✅ Enhanced test notification sent successfully! Check the bell icon." : 
            "❌ Failed to send test notification.";
        $messageType = $result ? 'success' : 'error';
        
    } elseif (isset($_POST['send_donation'])) {
        $donationType = $_POST['donation_type'] ?? 'general';
        $status = $_POST['donation_status'] ?? 'submitted';
        
        $result = $notificationSystem->sendDonationNotification($_SESSION['uid'], $donationType, $status, [
            'source' => 'enhanced_test_page',
            'testMode' => true
        ]);
        
        $message = $result ? 
            "✅ Donation notification sent successfully!" : 
            "❌ Failed to send donation notification.";
        $messageType = $result ? 'success' : 'error';
        
    } elseif (isset($_POST['send_adoption'])) {
        $status = $_POST['adoption_status'] ?? 'initiated';
        $stepNumber = $_POST['step_number'] ?? null;
        
        $result = $notificationSystem->sendAdoptionNotification($_SESSION['uid'], $status, $stepNumber, [
            'source' => 'enhanced_test_page',
            'testMode' => true
        ]);
        
        $message = $result ? 
            "✅ Adoption notification sent successfully!" : 
            "❌ Failed to send adoption notification.";
        $messageType = $result ? 'success' : 'error';
        
    } elseif (isset($_POST['send_appointment'])) {
        $status = $_POST['appointment_status'] ?? 'scheduled';
        $appointmentDate = $_POST['appointment_date'] ?? null;
        
        $result = $notificationSystem->sendAppointmentNotification($_SESSION['uid'], $status, $appointmentDate, [
            'source' => 'enhanced_test_page',
            'testMode' => true
        ]);
        
        $message = $result ? 
            "✅ Appointment notification sent successfully!" : 
            "❌ Failed to send appointment notification.";
        $messageType = $result ? 'success' : 'error';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enhanced Notification Test</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196F3;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-box h3 {
            color: #1976D2;
            margin-top: 0;
            font-size: 18px;
        }
        
        .test-section {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }
        
        .test-section h4 {
            color: #333;
            margin-top: 0;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        
        select, input[type="date"] {
            width: 100%;
            max-width: 200px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn { 
            background: #2196F3; 
            color: white; 
            border: none; 
            padding: 12px 20px; 
            margin: 8px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .btn:hover { 
            background: #1976D2; 
        }
        
        .btn-test { background: #4CAF50; }
        .btn-test:hover { background: #45a049; }
        
        .btn-donation { background: #FF9800; }
        .btn-donation:hover { background: #F57C00; }
        
        .btn-adoption { background: #E91E63; }
        .btn-adoption:hover { background: #C2185B; }
        
        .btn-appointment { background: #9C27B0; }
        .btn-appointment:hover { background: #7B1FA2; }
        
        .result { 
            margin: 20px 0; 
            padding: 15px; 
            border-radius: 8px; 
            font-weight: 600;
        }
        
        .success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        .links {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .links a {
            color: #2196F3;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 600;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .user-info {
            background: #fff3e0;
            border: 1px solid #FF9800;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .debug-btn {
            background: #607D8B;
            font-size: 12px;
            padding: 8px 15px;
        }
        
        .debug-btn:hover {
            background: #455A64;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>🔔 Enhanced Notification System Test</h1>
        
        <div class="user-info">
            <strong>Current User:</strong> <?php echo $_SESSION['uid']; ?><br>
            <strong>Test Mode:</strong> Enhanced Notification System V2<br>
            <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
        </div>
        
        <?php if ($message): ?>
            <div class="result <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h3>🎯 What This Tests</h3>
            <ul>
                <li>✓ Enhanced notification creation via new system</li>
                <li>✓ Multiple storage methods (Firebase REST API)</li>
                <li>✓ Real-time Firebase listeners</li>
                <li>✓ Navbar bell icon updates</li>
                <li>✓ Mobile app compatible format</li>
                <li>✓ Fallback API polling</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h4>🧪 Basic Test Notification</h4>
            <p>Sends a simple test notification to verify the system is working.</p>
            <form method="POST" style="display: inline;">
                <button type="submit" name="send_test" class="btn btn-test">📦 Send Test Notification</button>
            </form>
        </div>
        
        <div class="test-section">
            <h4>📦 Donation Notification Test</h4>
            <form method="POST">
                <div class="form-group">
                    <label>Donation Type:</label>
                    <select name="donation_type">
                        <option value="food">Food Donation</option>
                        <option value="clothes">Clothes Donation</option>
                        <option value="toys">Toys Donation</option>
                        <option value="education">Education Donation</option>
                        <option value="money">Money Donation</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select name="donation_status">
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <button type="submit" name="send_donation" class="btn btn-donation">Send Donation Notification</button>
            </form>
        </div>
        
        <div class="test-section">
            <h4>👶 Adoption Notification Test</h4>
            <form method="POST">
                <div class="form-group">
                    <label>Status:</label>
                    <select name="adoption_status">
                        <option value="initiated">Process Initiated</option>
                        <option value="step_completed">Step Completed</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Step Number (optional):</label>
                    <select name="step_number">
                        <option value="">None</option>
                        <option value="1">Step 1</option>
                        <option value="2">Step 2</option>
                        <option value="3">Step 3</option>
                        <option value="4">Step 4</option>
                        <option value="5">Step 5</option>
                    </select>
                </div>
                <button type="submit" name="send_adoption" class="btn btn-adoption">Send Adoption Notification</button>
            </form>
        </div>
        
        <div class="test-section">
            <h4>📅 Appointment Notification Test</h4>
            <form method="POST">
                <div class="form-group">
                    <label>Status:</label>
                    <select name="appointment_status">
                        <option value="scheduled">Scheduled</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="reminder">Reminder</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Appointment Date (optional):</label>
                    <input type="date" name="appointment_date">
                </div>
                <button type="submit" name="send_appointment" class="btn btn-appointment">Send Appointment Notification</button>
            </form>
        </div>
        
        <div class="info-box">
            <h3>📋 Instructions</h3>
            <ol>
                <li>Click any "Send" button above to create a notification</li>
                <li>Look at the notification bell (🔔) in the navbar</li>
                <li>You should see a red badge with the number of unread notifications</li>
                <li>Click the bell to see the notification popup</li>
                <li>The notification should appear in the list with the correct icon and format</li>
                <li>Notifications should also appear in real-time without page refresh</li>
            </ol>
        </div>
        
        <div class="links">
            <a href="notifications.php">📄 View All Notifications</a>
            <a href="send_test_notification.php">🔄 Old Test Page</a>
            <button onclick="testNotificationSystem('<?php echo $_SESSION['uid']; ?>')" class="btn debug-btn">🧪 JS Test</button>
        </div>
    </div>
    
    <script>
        // Auto-check for notification updates
        setInterval(() => {
            const badge = document.getElementById('notif-badge');
            if (badge && badge.style.display !== 'none') {
                console.log('🔔 Notification badge visible with count:', badge.textContent);
            }
        }, 3000);
        
        // Log when page loads
        console.log('Enhanced notification test page loaded');
        console.log('User ID:', '<?php echo $_SESSION['uid']; ?>');
        console.log('Session data:', <?php echo json_encode($_SESSION); ?>);
    </script>
</body>
</html> 