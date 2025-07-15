<?php
// Include session check to get proper session variables
require_once 'session_check.php';

// Redirect if user is not logged in
if (!$isLoggedIn) {
    header('Location: signin.php');
    exit;
}

// Define admin status
$isAdmin = ($currentUserRole === 'admin');
?>

<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Inbox - Ally</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 240px;
            background-color: #ffffff;
            border-right: 1px solid #e0e0e0;
            padding: 30px 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .inbox-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .inbox-header h1 {
            margin: 0;
            font-size: 2.2em;
            color: #333;
            font-weight: 600;
        }

        .inbox-actions {
            display: flex;
            gap: 15px;
        }

        .action-btn {
            background: #7CB9E8;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn:hover {
            background: #5a9bd5;
        }

        .action-btn.secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        .action-btn.secondary:hover {
            background: #e9ecef;
        }

        .search-bar {
            margin-bottom: 20px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            background: white;
            box-sizing: border-box;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #7CB9E8;
            box-shadow: 0 0 0 3px rgba(124, 185, 232, 0.1);
        }

        .search-bar i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .chat-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            color: #666;
        }

        .filter-btn:hover {
            background: #e9ecef;
        }

        .filter-btn.active {
            background: #7CB9E8;
            color: white;
            border-color: #7CB9E8;
        }

        .chat-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .chat-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
            position: relative;
        }

        .chat-item:hover {
            background-color: #f8f9fa;
        }

        .chat-item:last-child {
            border-bottom: none;
        }

        .chat-item.unread {
            background-color: #f8fbff;
            border-left: 4px solid #7CB9E8;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .chat-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .chat-time {
            font-size: 12px;
            color: #999;
        }

        .chat-preview {
            color: #666;
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 5px;
        }

        .chat-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-type {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .chat-type.donation {
            background: #fff3e0;
            color: #f57c00;
        }

        .chat-type.adoption {
            background: #e8f5e8;
            color: #388e3c;
        }

        .chat-type.appointment {
            background: #fce4ec;
            color: #c2185b;
        }

        .chat-type.admin {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .unread-badge {
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            color: #ddd;
        }

        .empty-state h3 {
            margin: 0 0 10px 0;
            font-weight: 500;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .loading i {
            font-size: 2em;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
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
            
            .inbox-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .inbox-actions {
                width: 100%;
                justify-content: center;
            }
            
            .chat-filters {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="Dashboard.php">🏠 Home</a></li>
                <li><a href="chat_messages.php">💬 Messages</a></li>
                <li><a href="chat_inbox.php" style="color: #6ea4ce;">📬 Inbox</a></li>
                
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
        </aside>

        <main class="main-content">
            <div class="inbox-header">
                <h1><i class="fas fa-inbox"></i> Chat Inbox</h1>
                <div class="inbox-actions">
                    <a href="chat_messages.php" class="action-btn">
                        <i class="fas fa-comments"></i> Open Chat
                    </a>
                    <button class="action-btn secondary" onclick="refreshInbox()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search conversations...">
                <i class="fas fa-search"></i>
            </div>

            <div class="chat-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="unread">Unread</button>
                <button class="filter-btn" data-filter="adoption">Adoption</button>
                <button class="filter-btn" data-filter="donation">Donation</button>
                <button class="filter-btn" data-filter="appointment">Appointment</button>
                <button class="filter-btn" data-filter="admin">Admin</button>
            </div>

            <div class="chat-list" id="chatList">
                <div class="loading">
                    <i class="fas fa-spinner"></i>
                    <p>Loading conversations...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>

    <script>
        // Firebase Configuration
        const firebaseConfig = {
            apiKey: "AIzaSyCH6Joz4RZPyR0v5NTECJ_A0NJZUiaZMRk",
            authDomain: "ally-user.firebaseapp.com",
            databaseURL: "https://ally-user-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "ally-user",
            storageBucket: "ally-user.firebasestorage.app",
            messagingSenderId: "567088674192",
            appId: "1:567088674192:web:76b5ef895c1181fa4aaf15"
        };

        // Initialize Firebase
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const auth = firebase.auth();
        const db = firebase.firestore();
        const realtimeDb = firebase.database();

        // Global variables
        let currentUserId = '<?php echo $currentUserId; ?>';
        let currentUserRole = '<?php echo $currentUserRole; ?>';
        let allChats = [];
        let filteredChats = [];
        let currentFilter = 'all';

        // Initialize the inbox
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔥 Initializing chat inbox...');
            
            // Wait for Firebase auth
            auth.onAuthStateChanged((user) => {
                if (user) {
                    console.log('✅ User authenticated:', user.uid);
                    loadInbox();
                } else {
                    console.log('❌ User not authenticated');
                    window.location.href = 'signin.php';
                }
            });

            // Set up event listeners
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search functionality
            document.getElementById('searchInput').addEventListener('input', handleSearch);

            // Filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.filter;
                    filterChats();
                });
            });
        }

        async function loadInbox() {
            console.log('📂 Loading chat inbox...');
            
            const chatListElement = document.getElementById('chatList');
            chatListElement.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading conversations...</p></div>';

            try {
                // Listen for chats where user is a participant
                realtimeDb.ref('chats').orderByChild('last_message_timestamp').on('value', async (snapshot) => {
                    const chats = [];
                    
                    for (const chatSnapshot of snapshot.val() ? Object.entries(snapshot.val()) : []) {
                        const [chatId, chatData] = chatSnapshot;
                        
                        // Check if current user is a participant
                        if (chatData.participant_user === currentUserId || chatData.participant_admin === currentUserId) {
                            // Get other user's info
                            const otherUserId = chatData.participant_user === currentUserId ? chatData.participant_admin : chatData.participant_user;
                            
                            try {
                                const userDoc = await db.collection('users').doc(otherUserId).get();
                                const userData = userDoc.exists() ? userDoc.data() : {};
                                
                                chats.push({
                                    id: chatId,
                                    ...chatData,
                                    otherUserId: otherUserId,
                                    otherUserName: userData.username || userData.firstName || 'Unknown User',
                                    otherUserRole: userData.role || 'user',
                                    otherUserAvatar: userData.profilePictureURL || 'https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png'
                                });
                            } catch (error) {
                                console.error('Error getting user info:', error);
                                chats.push({
                                    id: chatId,
                                    ...chatData,
                                    otherUserId: otherUserId,
                                    otherUserName: 'Unknown User',
                                    otherUserRole: 'user',
                                    otherUserAvatar: 'https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png'
                                });
                            }
                        }
                    }

                    // Sort by last message timestamp (newest first)
                    chats.sort((a, b) => (b.last_message_timestamp || 0) - (a.last_message_timestamp || 0));
                    
                    allChats = chats;
                    filterChats();
                });
                
            } catch (error) {
                console.error('Error loading inbox:', error);
                chatListElement.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Error Loading Conversations</h3>
                        <p>Please try refreshing the page</p>
                    </div>
                `;
            }
        }

        function filterChats() {
            let filtered = [...allChats];

            // Apply filter
            if (currentFilter !== 'all') {
                filtered = filtered.filter(chat => {
                    switch (currentFilter) {
                        case 'unread':
                            return (chat.unread_count || 0) > 0;
                        case 'adoption':
                            return chat.connection_type === 'adoption';
                        case 'donation':
                            return chat.connection_type && chat.connection_type.includes('donation');
                        case 'appointment':
                            return chat.connection_type === 'appointment';
                        case 'admin':
                            return chat.connection_type === 'admin_notification' || chat.connection_type === 'manual';
                        default:
                            return true;
                    }
                });
            }

            filteredChats = filtered;
            displayChats(filteredChats);
        }

        function handleSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            if (!searchTerm) {
                filterChats();
                return;
            }

            const searchResults = filteredChats.filter(chat => {
                return chat.otherUserName.toLowerCase().includes(searchTerm) ||
                       (chat.last_message || '').toLowerCase().includes(searchTerm) ||
                       (chat.connection_type || '').toLowerCase().includes(searchTerm);
            });

            displayChats(searchResults);
        }

        function displayChats(chats) {
            const chatListElement = document.getElementById('chatList');
            
            if (chats.length === 0) {
                chatListElement.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No conversations found</h3>
                        <p>Start a conversation from the donation or adoption process</p>
                    </div>
                `;
                return;
            }

            let chatListHTML = '';
            
            chats.forEach(chat => {
                const timestamp = formatTimestamp(chat.last_message_timestamp);
                const connectionType = formatConnectionType(chat.connection_type);
                const unreadCount = chat.unread_count || 0;
                const isUnread = unreadCount > 0;
                
                chatListHTML += `
                    <div class="chat-item ${isUnread ? 'unread' : ''}" onclick="openChat('${chat.id}', '${chat.otherUserId}')">
                        <img src="${chat.otherUserAvatar}" alt="${chat.otherUserName}" class="chat-avatar">
                        <div class="chat-info">
                            <div class="chat-header">
                                <div class="chat-name">${chat.otherUserName} ${chat.otherUserRole === 'admin' ? '(Admin)' : ''}</div>
                                <div class="chat-time">${timestamp}</div>
                            </div>
                            <div class="chat-preview">${chat.last_message || 'No messages yet'}</div>
                            <div class="chat-meta">
                                <div class="chat-type ${getChatTypeClass(chat.connection_type)}">${connectionType}</div>
                                ${unreadCount > 0 ? `<div class="unread-badge">${unreadCount}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            chatListElement.innerHTML = chatListHTML;
        }

        function openChat(chatId, otherUserId) {
            window.location.href = `chat_messages.php?chatId=${chatId}&userId=${otherUserId}`;
        }

        function refreshInbox() {
            loadInbox();
        }

        function formatTimestamp(timestamp) {
            if (!timestamp) return '';
            
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) { // Less than 1 minute
                return 'Just now';
            } else if (diff < 3600000) { // Less than 1 hour
                return Math.floor(diff / 60000) + 'm ago';
            } else if (diff < 86400000) { // Less than 1 day
                return Math.floor(diff / 3600000) + 'h ago';
            } else if (diff < 604800000) { // Less than 1 week
                return Math.floor(diff / 86400000) + 'd ago';
            } else {
                return date.toLocaleDateString();
            }
        }

        function formatConnectionType(connectionType) {
            if (!connectionType) return 'Direct Message';
            
            const types = {
                'manual': 'Direct Message',
                'adoption': 'Adoption Process',
                'donation': 'Donation Process',
                'mixed': 'Mixed Process',
                'money_donation': 'Money Donation',
                'education_donation': 'Education Donation',
                'medicine_donation': 'Medicine Donation',
                'toys_donation': 'Toys Donation',
                'clothes_donation': 'Clothes Donation',
                'food_donation': 'Food Donation',
                'appointment': 'Appointment',
                'admin_notification': 'Admin Message',
                'system': 'System Message'
            };
            
            return types[connectionType] || connectionType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function getChatTypeClass(connectionType) {
            if (!connectionType) return '';
            
            if (connectionType.includes('donation')) return 'donation';
            if (connectionType === 'adoption') return 'adoption';
            if (connectionType === 'appointment') return 'appointment';
            if (connectionType === 'admin_notification' || connectionType === 'manual') return 'admin';
            
            return '';
        }

        // Make functions globally available
        window.openChat = openChat;
        window.refreshInbox = refreshInbox;
    </script>
</body>
</html> 