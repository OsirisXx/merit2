<?php
require_once 'session_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Ally Foundation</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-title {
            color: #333;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .notification-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            background: #f0f0f0;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .filter-btn.active {
            background: #7CB9E8;
            color: white;
        }
        
        .notification-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item.unread {
            background-color: #f8f9ff;
            border-left: 4px solid #7CB9E8;
        }
        
        .notification-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .icon-adoption { background: #e8f5e8; color: #4caf50; }
        .icon-donation { background: #fff3e0; color: #ff9800; }
        .icon-appointment { background: #e3f2fd; color: #2196f3; }
        .icon-matching { background: #fce4ec; color: #e91e63; }
        .icon-chat { background: #f3e5f5; color: #9c27b0; }
        .icon-system { background: #f5f5f5; color: #607d8b; }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        
        .notification-message {
            color: #666;
            margin: 0 0 8px 0;
            line-height: 1.4;
            font-size: 14px;
        }
        
        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 12px;
            color: #999;
        }
        
        .notification-time {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .notification-type {
            background: #e0e0e0;
            color: #666;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .mark-read-btn {
            padding: 4px 8px;
            background: #7CB9E8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state img {
            width: 100px;
            height: 100px;
            opacity: 0.5;
            margin-bottom: 20px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #7CB9E8;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #7CB9E8;
            color: white;
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #666;
        }
        
        .btn-primary:hover {
            background: #6ba8d1;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .notification-filters {
                gap: 5px;
            }
            
            .filter-btn {
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .notification-item {
                padding: 15px;
            }
            
            .notification-icon {
                width: 35px;
                height: 35px;
                font-size: 16px;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">🔔 Notifications</h1>
        </div>
        
        <div class="notification-filters">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="adoption">Adoption</button>
            <button class="filter-btn" data-filter="donation">Donation</button>
            <button class="filter-btn" data-filter="appointment">Appointment</button>
            <button class="filter-btn" data-filter="matching">Matching</button>
            <button class="filter-btn" data-filter="chat">Chat</button>
            <button class="filter-btn" data-filter="system">System</button>
            <button class="filter-btn" data-filter="unread">Unread</button>
        </div>
        
        <div class="notification-list">
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>Loading notifications...</p>
            </div>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.11.0/firebase-firestore-compat.js"></script>
    
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

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const db = firebase.firestore();
        let currentUser = null;
        let notifications = [];
        let currentFilter = 'all';

        // Authentication state listener
        firebase.auth().onAuthStateChanged(user => {
            if (user) {
                currentUser = user;
                loadNotifications();
                setupRealtimeUpdates();
            } else {
                window.location.href = 'signin.php';
            }
        });

        // Load notifications from Firebase
        async function loadNotifications() {
            try {
                const notificationList = document.querySelector('.notification-list');
                
                // Query notification_logs for this user
                // Temporarily simplified query to work without indexes
                const query = db.collection('notification_logs')
                    .where('userId', '==', currentUser.uid)
                    .limit(50);
                
                const snapshot = await query.get();
                notifications = [];
                
                snapshot.forEach(doc => {
                    const data = doc.data();
                    notifications.push({
                        id: doc.id,
                        ...data,
                        isRead: data.isRead || false
                    });
                });
                
                // Also load user_notifications collection if exists
                try {
                    const userNotificationsQuery = db.collection('users')
                        .doc(currentUser.uid)
                        .collection('notifications')
                        .limit(50);
                    
                    const userNotificationsSnapshot = await userNotificationsQuery.get();
                    
                    userNotificationsSnapshot.forEach(doc => {
                        const data = doc.data();
                        notifications.push({
                            id: doc.id,
                            ...data,
                            isRead: data.isRead || false
                        });
                    });
                } catch (e) {
                    console.log('User notifications collection not found or accessible');
                }
                
                // Sort by timestamp descending
                notifications.sort((a, b) => b.timestamp - a.timestamp);
                
                renderNotifications();
                
            } catch (error) {
                console.error('Error loading notifications:', error);
                showError('Failed to load notifications');
            }
        }

        // Setup real-time updates
        function setupRealtimeUpdates() {
            // Listen for new notifications in notification_logs
            db.collection('notification_logs')
                .where('userId', '==', currentUser.uid)
                .onSnapshot(snapshot => {
                    snapshot.docChanges().forEach(change => {
                        if (change.type === 'added') {
                            const data = change.doc.data();
                            const notification = {
                                id: change.doc.id,
                                ...data,
                                isRead: false
                            };
                            
                            // Add to beginning of array if it's not already there
                            if (!notifications.find(n => n.id === notification.id)) {
                                notifications.unshift(notification);
                                renderNotifications();
                                showNewNotificationIndicator();
                            }
                        }
                    });
                });
        }

        // Render notifications
        function renderNotifications() {
            const notificationList = document.querySelector('.notification-list');
            const filteredNotifications = filterNotifications(notifications);
            
            if (filteredNotifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="empty-state">
                        <div style="font-size: 48px; opacity: 0.5; margin-bottom: 20px;">🔔</div>
                        <h3>No notifications</h3>
                        <p>You're all caught up!</p>
                    </div>
                `;
                return;
            }
            
            const notificationHTML = filteredNotifications.map(notification => 
                createNotificationHTML(notification)
            ).join('');
            
            notificationList.innerHTML = notificationHTML;
            
            // Add click handlers
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', () => {
                    const notificationId = item.dataset.notificationId;
                    handleNotificationClick(notificationId);
                });
            });
            
            // Add mark as read handlers
            document.querySelectorAll('.mark-read-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const notificationId = btn.dataset.notificationId;
                    markAsRead(notificationId);
                });
            });
        }

        // Create notification HTML
        function createNotificationHTML(notification) {
            const processType = notification.processType || 'system';
            const notificationType = notification.notificationType || '';
            const timeAgo = getTimeAgo(notification.timestamp);
            const icon = getNotificationIcon(processType);
            const iconClass = `icon-${processType.toLowerCase()}`;
            
            return `
                <div class="notification-item ${!notification.isRead ? 'unread' : ''}" 
                     data-notification-id="${notification.id}">
                    <div class="notification-icon ${iconClass}">
                        ${icon}
                    </div>
                    <div class="notification-content">
                        <h4 class="notification-title">${notification.title}</h4>
                        <p class="notification-message">${notification.message}</p>
                        <div class="notification-meta">
                            <span class="notification-time">
                                🕒 ${timeAgo}
                            </span>
                            <span class="notification-type">${processType}</span>
                            ${!notification.isRead ? `
                                <button class="mark-read-btn" data-notification-id="${notification.id}">
                                    Mark as read
                                </button>
                            ` : ''}
                        </div>
                        ${createNotificationActions(notification)}
                    </div>
                </div>
            `;
        }

        // Get notification icon based on process type
        function getNotificationIcon(processType) {
            const icons = {
                'ADOPTION': '👶',
                'DONATION': '📦',
                'APPOINTMENT': '📅',
                'MATCHING': '💕',
                'CHAT': '💬',
                'PROFILE': '👤',
                'SYSTEM': '🔔'
            };
            return icons[processType.toUpperCase()] || '🔔';
        }

        // Create notification actions based on type
        function createNotificationActions(notification) {
            const processType = notification.processType;
            const notificationType = notification.notificationType;
            
            let actions = [];
            
            switch (processType) {
                case 'ADOPTION':
                    actions.push(`<button class="action-btn btn-primary" onclick="goToAdoption()">View Adoption</button>`);
                    break;
                case 'DONATION':
                    actions.push(`<button class="action-btn btn-primary" onclick="goToDonation()">View Donations</button>`);
                    break;
                case 'APPOINTMENT':
                    actions.push(`<button class="action-btn btn-primary" onclick="goToAppointments()">View Appointments</button>`);
                    break;
                case 'MATCHING':
                    actions.push(`<button class="action-btn btn-primary" onclick="goToMatching()">View Matching</button>`);
                    break;
                case 'CHAT':
                    if (notification.data && notification.data.chatUserId) {
                        actions.push(`<button class="action-btn btn-primary" onclick="goToChat('${notification.data.chatUserId}')">Open Chat</button>`);
                    }
                    break;
            }
            
            if (actions.length > 0) {
                return `<div class="notification-actions">${actions.join('')}</div>`;
            }
            
            return '';
        }

        // Filter notifications
        function filterNotifications(notifications) {
            switch (currentFilter) {
                case 'unread':
                    return notifications.filter(n => !n.isRead);
                case 'all':
                    return notifications;
                default:
                    return notifications.filter(n => 
                        n.processType && n.processType.toLowerCase() === currentFilter.toLowerCase()
                    );
            }
        }

        // Get time ago string
        function getTimeAgo(timestamp) {
            const now = Date.now();
            const diff = now - timestamp;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            return new Date(timestamp).toLocaleDateString();
        }

        // Handle notification click
        function handleNotificationClick(notificationId) {
            const notification = notifications.find(n => n.id === notificationId);
            if (!notification) return;
            
            // Mark as read
            if (!notification.isRead) {
                markAsRead(notificationId);
            }
            
            // Navigate based on process type
            const processType = notification.processType;
            switch (processType) {
                case 'ADOPTION':
                    goToAdoption();
                    break;
                case 'DONATION':
                    goToDonation();
                    break;
                case 'APPOINTMENT':
                    goToAppointments();
                    break;
                case 'MATCHING':
                    goToMatching();
                    break;
                case 'CHAT':
                    if (notification.data && notification.data.chatUserId) {
                        goToChat(notification.data.chatUserId);
                    }
                    break;
            }
        }

        // Mark notification as read
        async function markAsRead(notificationId) {
            try {
                const notification = notifications.find(n => n.id === notificationId);
                if (!notification) return;
                
                // Update in Firebase
                await db.collection('notification_logs').doc(notificationId).update({
                    isRead: true,
                    readAt: firebase.firestore.FieldValue.serverTimestamp()
                });
                
                // Update local state
                notification.isRead = true;
                renderNotifications();
                
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        // Navigation functions
        function goToAdoption() {
            window.location.href = 'ProgTracking.php';
        }
        
        function goToDonation() {
            window.location.href = 'Donation.php';
        }
        
        function goToAppointments() {
            window.location.href = 'Appointments.php';
        }
        
        function goToMatching() {
            window.location.href = 'matching.php';
        }
        
        function goToChat(chatUserId) {
            window.location.href = `chat.php?chatUserId=${chatUserId}`;
        }

        // Show error message
        function showError(message) {
            const notificationList = document.querySelector('.notification-list');
            notificationList.innerHTML = `
                <div class="empty-state">
                    <div style="font-size: 48px; color: #f44336; margin-bottom: 20px;">⚠️</div>
                    <h3>Error</h3>
                    <p>${message}</p>
                    <button class="action-btn btn-primary" onclick="loadNotifications()">Retry</button>
                </div>
            `;
        }

        // Show new notification indicator
        function showNewNotificationIndicator() {
            // This could show a toast or update the navbar badge
            console.log('New notification received');
        }

        // Filter button handlers
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update active state
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    // Update filter
                    currentFilter = btn.dataset.filter;
                    renderNotifications();
                });
            });
        });
    </script>
</body>
</html>