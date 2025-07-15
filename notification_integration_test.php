<?php
require_once 'session_check.php';

// Get session info
$currentUserId = $_SESSION['user_id'] ?? '';
$currentUsername = $_SESSION['username'] ?? '';
$currentUserEmail = $_SESSION['user_email'] ?? '';
$currentUserRole = $_SESSION['user_role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Integration Test - Ally Foundation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e8f0;
        }

        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }

        .notification-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .notification-card {
            background: #ffffff;
            border: 1px solid #e1e8f0;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .notification-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notification-card p {
            color: #7f8c8d;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .test-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 10px;
        }

        .test-btn:hover {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .test-btn:active {
            transform: translateY(0);
        }

        .test-btn.donation { background: linear-gradient(135deg, #27ae60, #229954); }
        .test-btn.donation:hover { background: linear-gradient(135deg, #229954, #1e8449); }

        .test-btn.adoption { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .test-btn.adoption:hover { background: linear-gradient(135deg, #c0392b, #a93226); }

        .test-btn.appointment { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .test-btn.appointment:hover { background: linear-gradient(135deg, #e67e22, #d35400); }

        .test-btn.matching { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        .test-btn.matching:hover { background: linear-gradient(135deg, #8e44ad, #7d3c98); }

        .test-btn.system { background: linear-gradient(135deg, #34495e, #2c3e50); }
        .test-btn.system:hover { background: linear-gradient(135deg, #2c3e50, #283747); }

        .results {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            border-left: 4px solid #28a745;
        }

        .results h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .result-item {
            background: white;
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 6px;
            border-left: 3px solid #28a745;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .result-item.error {
            border-left-color: #dc3545;
            color: #dc3545;
        }

        .result-item.success {
            border-left-color: #28a745;
            color: #155724;
        }

        .emoji {
            font-size: 1.2em;
        }

        .integration-status {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #d4edda;
        }

        .integration-status h3 {
            color: #155724;
            margin-bottom: 10px;
        }

        .integration-list {
            list-style: none;
            padding-left: 0;
        }

        .integration-list li {
            padding: 5px 0;
            color: #155724;
        }

        .integration-list li:before {
            content: "✅ ";
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Notification Integration Test</h1>
            <p>Test all notification types across the Ally Foundation website</p>
        </div>

        <div class="user-info">
            <strong>Current User:</strong> <?php echo htmlspecialchars($currentUsername); ?> 
            (<?php echo htmlspecialchars($currentUserEmail); ?>) - 
            Role: <?php echo htmlspecialchars($currentUserRole); ?>
            <br>
            <strong>User ID:</strong> <?php echo htmlspecialchars($currentUserId); ?>
        </div>

        <div class="integration-status">
            <h3>🚀 Notification System Integration Status</h3>
            <p>The following pages have been integrated with comprehensive notification support:</p>
            <ul class="integration-list">
                <li><strong>Donation.php</strong> - Sends notifications when donations are approved/rejected by admin</li>
                <li><strong>ProgTracking.php</strong> - Sends notifications when adoption steps are completed or process finishes</li>
                <li><strong>Schedule.php</strong> - Sends notifications when appointments are scheduled</li>
                <li><strong>Appointments.php</strong> - Sends notifications when appointments are approved by admin</li>
                <li><strong>matching.php</strong> - Sends notifications for matching requests, matches found, and auto-acceptance</li>
                <li><strong>navbar.php</strong> - Real-time notification bell with badge and dropdown preview</li>
                <li><strong>notifications.php</strong> - Complete notification management center</li>
            </ul>
        </div>

        <div class="notification-grid">
            <!-- Donation Notifications -->
            <div class="notification-card">
                <h3><span class="emoji">💰</span> Donation Notifications</h3>
                <p>Test donation-related notifications including submissions, approvals, and rejections.</p>
                <button class="test-btn donation" onclick="testDonationNotification('submitted')">Test Donation Submitted</button>
                <button class="test-btn donation" onclick="testDonationNotification('approved')">Test Donation Approved</button>
                <button class="test-btn donation" onclick="testDonationNotification('rejected')">Test Donation Rejected</button>
                <button class="test-btn donation" onclick="testDonationNotification('completed')">Test Donation Completed</button>
            </div>

            <!-- Adoption Notifications -->
            <div class="notification-card">
                <h3><span class="emoji">👨‍👩‍👧‍👦</span> Adoption Notifications</h3>
                <p>Test adoption process notifications including step completions and process milestones.</p>
                <button class="test-btn adoption" onclick="testAdoptionNotification('step_completed', 3)">Test Step 3 Completed</button>
                <button class="test-btn adoption" onclick="testAdoptionNotification('step_completed', 7)">Test Step 7 Completed</button>
                <button class="test-btn adoption" onclick="testAdoptionNotification('process_completed', null)">Test Process Completed</button>
                <button class="test-btn adoption" onclick="testAdoptionNotification('document_uploaded', 5)">Test Document Uploaded</button>
            </div>

            <!-- Appointment Notifications -->
            <div class="notification-card">
                <h3><span class="emoji">📅</span> Appointment Notifications</h3>
                <p>Test appointment-related notifications including scheduling and approvals.</p>
                <button class="test-btn appointment" onclick="testAppointmentNotification('scheduled')">Test Appointment Scheduled</button>
                <button class="test-btn appointment" onclick="testAppointmentNotification('approved')">Test Appointment Approved</button>
                <button class="test-btn appointment" onclick="testAppointmentNotification('completed')">Test Appointment Completed</button>
                <button class="test-btn appointment" onclick="testAppointmentNotification('cancelled')">Test Appointment Cancelled</button>
            </div>

            <!-- Matching Notifications -->
            <div class="notification-card">
                <h3><span class="emoji">💕</span> Matching Notifications</h3>
                <p>Test child matching notifications including requests and successful matches.</p>
                <button class="test-btn matching" onclick="testMatchingNotification('request_submitted')">Test Request Submitted</button>
                <button class="test-btn matching" onclick="testMatchingNotification('match_found')">Test Match Found</button>
                <button class="test-btn matching" onclick="testMatchingNotification('match_accepted')">Test Match Accepted</button>
                <button class="test-btn matching" onclick="testMatchingNotification('process_completed')">Test Process Completed</button>
            </div>

            <!-- Chat Notifications -->
            <div class="notification-card">
                <h3><span class="emoji">💬</span> Chat Notifications</h3>
                <p>Test chat and communication notifications.</p>
                <button class="test-btn" onclick="testChatNotification()">Test New Message</button>
                <button class="test-btn" onclick="testChatNotification('admin')">Test Admin Message</button>
            </div>

            <!-- System Notifications -->
            <div class="notification-card">
                <h3><span class="emoji">⚙️</span> System Notifications</h3>
                <p>Test system-level notifications including security alerts and maintenance.</p>
                <button class="test-btn system" onclick="testSystemNotification('maintenance')">Test Maintenance Notice</button>
                <button class="test-btn system" onclick="testSecurityNotification()">Test Security Alert</button>
                <button class="test-btn system" onclick="testSystemNotification('welcome')">Test Welcome Message</button>
            </div>
        </div>

        <div class="results" id="results" style="display: none;">
            <h3>📊 Test Results</h3>
            <div id="result-list"></div>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    <script src="notification_client.js"></script>

    <script>
        // Initialize Firebase
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            projectId: "ally-user",
            storageBucket: "ally-user.appspot.com",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        function showResult(message, type = 'success') {
            const resultsDiv = document.getElementById('results');
            const resultList = document.getElementById('result-list');
            
            resultsDiv.style.display = 'block';
            
            const resultItem = document.createElement('div');
            resultItem.className = `result-item ${type}`;
            resultItem.textContent = `${new Date().toLocaleTimeString()}: ${message}`;
            
            resultList.appendChild(resultItem);
            resultItem.scrollIntoView({ behavior: 'smooth' });
        }

        async function testDonationNotification(status) {
            try {
                const result = await sendDonationNotification('food', status, {
                    amount: status === 'completed' ? 'PHP 5,000' : undefined,
                    donationId: 'TEST_' + Date.now()
                });
                
                if (result) {
                    showResult(`✅ Donation notification sent: ${status.toUpperCase()}`, 'success');
                } else {
                    showResult(`❌ Failed to send donation notification: ${status}`, 'error');
                }
            } catch (error) {
                showResult(`❌ Error sending donation notification: ${error.message}`, 'error');
            }
        }

        async function testAdoptionNotification(status, stepNumber) {
            try {
                const result = await sendAdoptionNotification(status, stepNumber, {
                    stepName: stepNumber ? `Step ${stepNumber}` : undefined,
                    totalSteps: status === 'process_completed' ? 10 : undefined
                });
                
                if (result) {
                    const message = stepNumber ? 
                        `✅ Adoption notification sent: ${status.toUpperCase()} - Step ${stepNumber}` :
                        `✅ Adoption notification sent: ${status.toUpperCase()}`;
                    showResult(message, 'success');
                } else {
                    showResult(`❌ Failed to send adoption notification: ${status}`, 'error');
                }
            } catch (error) {
                showResult(`❌ Error sending adoption notification: ${error.message}`, 'error');
            }
        }

        async function testAppointmentNotification(status) {
            try {
                const appointmentDate = new Date();
                appointmentDate.setDate(appointmentDate.getDate() + 7);
                
                const result = await sendAppointmentNotification(status, appointmentDate.toLocaleDateString(), {
                    appointmentType: 'Initial Consultation',
                    appointmentCode: 'APT_' + Date.now()
                });
                
                if (result) {
                    showResult(`✅ Appointment notification sent: ${status.toUpperCase()}`, 'success');
                } else {
                    showResult(`❌ Failed to send appointment notification: ${status}`, 'error');
                }
            } catch (error) {
                showResult(`❌ Error sending appointment notification: ${error.message}`, 'error');
            }
        }

        async function testMatchingNotification(status) {
            try {
                const childName = status === 'request_submitted' ? null : 'Maria Santos';
                const result = await sendMatchingNotification(status, childName, {
                    totalMatches: status === 'match_found' ? 4 : undefined,
                    autoAccepted: status === 'match_accepted' ? true : undefined
                });
                
                if (result) {
                    showResult(`✅ Matching notification sent: ${status.toUpperCase()}`, 'success');
                } else {
                    showResult(`❌ Failed to send matching notification: ${status}`, 'error');
                }
            } catch (error) {
                showResult(`❌ Error sending matching notification: ${error.message}`, 'error');
            }
        }

        async function testChatNotification(type = 'user') {
            try {
                const senderName = type === 'admin' ? 'Admin Support' : 'John Doe';
                const message = type === 'admin' ? 
                    'Your adoption application requires additional documentation.' :
                    'Hello! I have a question about the adoption process.';
                
                const result = await sendChatNotification(senderName, message, 'chat_' + Date.now(), {
                    messageType: type
                });
                
                if (result) {
                    showResult(`✅ Chat notification sent from: ${senderName}`, 'success');
                } else {
                    showResult(`❌ Failed to send chat notification`, 'error');
                }
            } catch (error) {
                showResult(`❌ Error sending chat notification: ${error.message}`, 'error');
            }
        }

        async function testSystemNotification(type) {
            try {
                let title, message;
                
                switch(type) {
                    case 'maintenance':
                        title = '🔧 System Maintenance';
                        message = 'The system will undergo scheduled maintenance on Sunday 2AM-4AM.';
                        break;
                    case 'welcome':
                        title = '🎉 Welcome to Ally Foundation';
                        message = 'Thank you for joining our community. Start your adoption journey today!';
                        break;
                    default:
                        title = '📢 System Update';
                        message = 'A new feature has been added to improve your experience.';
                }
                
                const result = await sendSystemNotification(title, message, {
                    notificationType: type
                });
                
                if (result) {
                    showResult(`✅ System notification sent: ${title}`, 'success');
                } else {
                    showResult(`❌ Failed to send system notification`, 'error');
                }
            } catch (error) {
                showResult(`❌ Error sending system notification: ${error.message}`, 'error');
            }
        }

        async function testSecurityNotification() {
            try {
                const result = await sendSecurityNotification('login_attempt', 'New login from unknown device detected', true);
                
                if (result) {
                    showResult(`✅ Security notification sent: Login Alert`, 'success');
                } else {
                    showResult(`❌ Failed to send security notification`, 'error');
                }
            } catch (error) {
                showResult(`❌ Error sending security notification: ${error.message}`, 'error');
            }
        }

        // Show initial instructions
        window.addEventListener('load', function() {
            showResult('🚀 Notification test system loaded. Click any button above to test notifications!', 'success');
        });
    </script>
</body>
</html> 