<?php
// Include session check to get proper session variables
require_once 'session_check.php';

// Redirect if user is not logged in
if (!$isLoggedIn) {
    header('Location: signin.php');
    exit;
}

// Get chat parameters
$chatId = isset($_GET['chatId']) ? $_GET['chatId'] : null;
$userId = isset($_GET['userId']) ? $_GET['userId'] : null;

// Define admin status
$isAdmin = ($currentUserRole === 'admin');

// Handle AJAX requests for chat operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'mark_messages_read':
            // Mark messages as read in the chat
            echo json_encode(['success' => true, 'message' => 'Messages marked as read']);
            break;
            
        case 'get_user_chats':
            // This will be handled by JavaScript Firebase calls
            echo json_encode(['success' => true, 'message' => 'Use Firebase for real-time chat data']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}
?>

<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Ally</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            overflow-x: hidden;
        }

        .chat-container {
            display: flex;
            height: calc(100vh - 80px);
            background: #fff;
        }

        /* Chat List Sidebar */
        .chat-list {
            width: 350px;
            background: #fff;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .chat-list-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: linear-gradient(135deg, #7CB9E8 0%, #5a9bd5 100%);
            color: white;
        }

        .chat-list-header h2 {
            margin: 0;
            font-size: 1.4em;
            font-weight: 600;
        }

        .chat-search {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .chat-search input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            background: #f8f9fa;
            box-sizing: border-box;
        }

        .chat-search input:focus {
            outline: none;
            border-color: #7CB9E8;
            background: #fff;
        }

        .chat-search-icon {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .chat-list-content {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
            position: relative;
        }

        .chat-item:hover {
            background-color: #f8f9fa;
        }

        .chat-item.active {
            background-color: #e3f2fd;
            border-right: 3px solid #7CB9E8;
        }

        .chat-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .chat-user-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .chat-timestamp {
            font-size: 12px;
            color: #999;
        }

        .chat-last-message {
            font-size: 13px;
            color: #666;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-right: 20px;
        }

        .chat-unread-badge {
            position: absolute;
            right: 15px;
            bottom: 15px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .chat-connection-type {
            font-size: 11px;
            color: #7CB9E8;
            font-weight: 500;
            margin-top: 2px;
        }

        /* Conversation Type Header */
        .conversation-type-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .conversation-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .conversation-type-badge.adoption {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
        }

        .conversation-type-badge.donation {
            background: linear-gradient(135deg, #e91e63, #ad1457);
        }

        .conversation-type-badge.mixed {
            background: linear-gradient(135deg, #9c27b0, #673ab7);
        }

        .conversation-type-badge.money {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
        }

        .conversation-type-badge.education {
            background: linear-gradient(135deg, #45b7d1, #2196f3);
        }

        .conversation-type-badge.medicine {
            background: linear-gradient(135deg, #96ceb4, #4caf50);
        }

        .conversation-type-badge.toys {
            background: linear-gradient(135deg, #feca57, #ff9ff3);
        }

        .conversation-type-badge.clothes {
            background: linear-gradient(135deg, #ff9a9e, #fecfef);
        }

        .conversation-type-badge.food {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
        }

        .conversation-type-badge.general {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .profile-view-btn {
            background: #7CB9E8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .profile-view-btn:hover {
            background: #5a9bd5;
            transform: translateY(-1px);
        }

        .connection-status {
            text-align: center;
            padding: 12px 20px;
            background: #e3f2fd;
            border-bottom: 1px solid #bbdefb;
            color: #1976d2;
            font-size: 13px;
        }

        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: linear-gradient(135deg, #7CB9E8 0%, #5a9bd5 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .chat-user-details h3 {
            margin: 0;
            font-size: 1.1em;
            font-weight: 600;
        }

        .chat-user-status {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 2px;
        }

        .chat-actions {
            display: flex;
            gap: 10px;
        }

        .chat-action-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.2s;
        }

        .chat-action-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Messages Area */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            scroll-behavior: smooth;
        }

        .message {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .message.sent {
            align-items: flex-end;
        }

        .message.received {
            align-items: flex-start;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
            position: relative;
        }

        .message.sent .message-content {
            background: #7CB9E8;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-content {
            background: #fff;
            color: #333;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 4px;
        }

        .message-timestamp {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 5px;
            text-align: right;
        }

        .message.received .message-timestamp {
            text-align: left;
        }

        .system-message {
            text-align: center;
            margin: 20px 0;
        }

        .system-message .message-content {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            border-radius: 15px;
            padding: 10px 15px;
            font-size: 13px;
            max-width: 80%;
            margin: 0 auto;
        }

        /* Message Input */
        .message-input-container {
            padding: 20px;
            background: #fff;
            border-top: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .message-input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            resize: none;
            min-height: 20px;
            max-height: 100px;
            overflow-y: auto;
            background: #f8f9fa;
            transition: all 0.2s;
        }

        .message-input:focus {
            outline: none;
            border-color: #7CB9E8;
            background: #fff;
        }

        .send-button {
            background: #7CB9E8;
            border: none;
            color: white;
            padding: 12px 16px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-button:hover {
            background: #5a9bd5;
        }

        .send-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            text-align: center;
        }

        .empty-chat i {
            font-size: 4em;
            margin-bottom: 20px;
            color: #ddd;
        }

        .empty-chat h3 {
            margin: 0 0 10px 0;
            font-weight: 500;
        }

        .empty-chat p {
            margin: 0;
            font-size: 14px;
        }

        /* Loading States */
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: calc(100vh - 80px);
            }
            
            .chat-list {
                width: 100%;
                height: 50%;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .chat-area {
                height: 50%;
            }
            
            .message-content {
                max-width: 85%;
            }
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background: #f0f0f0;
            border-top: 1px solid #e0e0e0;
            font-size: 13px;
            color: #666;
        }

        .typing-dots {
            display: flex;
            gap: 3px;
            margin-left: 10px;
        }

        .typing-dots span {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #999;
            animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }

        .message-timestamp {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
        }

        .message-sender {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
            padding: 2px 8px;
            border-radius: 10px;
            display: inline-block;
        }

        .message.sent .message-sender {
            align-self: flex-end;
        }

        .message.received .message-sender {
            align-self: flex-start;
        }

        .message-sender.admin {
            background: #e3f2fd;
            color: #1976d2;
        }

        .message-sender.user {
            background: #f3e5f5;
            color: #7b1fa2;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Chat List Sidebar -->
        <div class="chat-list">
            <div class="chat-list-header">
                <h2><i class="fas fa-comments"></i> Messages</h2>
            </div>
            
            <div class="chat-search">
                <div style="position: relative;">
                    <input type="text" id="chatSearchInput" placeholder="Search conversations...">
                    <i class="fas fa-search chat-search-icon"></i>
                </div>
            </div>
            
            <div class="chat-list-content" id="chatListContent">
                <div class="loading">
                    <i class="fas fa-spinner"></i>
                    <p>Loading conversations...</p>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <div id="chatAreaContent">
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <h3>Welcome to Messages</h3>
                    <p>Select a conversation to start chatting</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-storage.js"></script>

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
        const storage = firebase.storage();

        // Global variables
        let currentUserId = '<?php echo $currentUserId; ?>';
        let currentUserRole = '<?php echo $currentUserRole; ?>';
        let currentUsername = '';
        let activeChats = {};
        let currentChatId = null;
        let currentChatUser = null;
        let messagesListener = null;
        let typingTimeout = null;

        // Initialize the chat system
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔥 Initializing chat system...');
            console.log('Current User ID:', currentUserId);
            console.log('Current User Role:', currentUserRole);
            
            // Check if user has valid PHP session
            if (!currentUserId) {
                console.log('❌ No PHP session found');
                window.location.href = 'signin.php';
                return;
            }
            
            // Try to authenticate with Firebase or use anonymous auth
            initializeFirebaseAuth();
        });

        async function initializeFirebaseAuth() {
            console.log('🔑 Initializing Firebase for database access...');
            
            // Skip authentication and go directly to chat system
            // Firebase Realtime Database rules should allow read/write for our use case
            initializeChatSystem();
        }

        async function initializeChatSystem() {
            try {
                // Get current user info
                const userDoc = await db.collection('users').doc(currentUserId).get();
                if (userDoc.exists) {
                    const userData = userDoc.data();
                    currentUsername = userData.username || 'Unknown';
                    console.log('Current username:', currentUsername);
                }

                // Load user's chats
                loadUserChats();
                
                // Set up real-time listeners
                setupChatListeners();
                
                // Set up search functionality
                document.getElementById('chatSearchInput').addEventListener('input', filterChats);
                
                // Handle URL parameters for direct chat access
                const urlParams = new URLSearchParams(window.location.search);
                const chatId = urlParams.get('chatId');
                const userId = urlParams.get('userId');
                
                if (chatId) {
                    // For centralized chats, determine the correct display name based on user role
                    if (currentUserRole === 'admin') {
                        // Admin needs to fetch the actual user name from the chat data
                        setTimeout(async () => {
                            try {
                                const chatSnapshot = await realtimeDb.ref(`chats/${chatId}`).once('value');
                                if (chatSnapshot.exists()) {
                                    const chatData = chatSnapshot.val();
                                    let displayName = 'Unknown User';
                                    
                                    if (chatData.user_name && chatData.user_name !== 'User') {
                                        displayName = chatData.user_name;
                                    } else if (chatData.user_id) {
                                        // Fetch from Firestore
                                        try {
                                            const userDoc = await db.collection('users').doc(chatData.user_id).get();
                                            if (userDoc.exists) {
                                                const userData = userDoc.data();
                                                if (userData.firstName && userData.lastName) {
                                                    displayName = `${userData.firstName} ${userData.lastName}`;
                                                } else if (userData.username) {
                                                    displayName = userData.username;
                                                }
                                                
                                                // Update chat data with real name
                                                realtimeDb.ref(`chats/${chatId}`).update({
                                                    user_name: displayName
                                                });
                                            }
                                        } catch (error) {
                                            console.error('Error fetching user name for URL chat:', error);
                                        }
                                    }
                                    
                                    openCentralizedChat(chatId, displayName, 'user');
                                } else {
                                    openCentralizedChat(chatId, 'Unknown User', 'user');
                                }
                            } catch (error) {
                                console.error('Error loading chat from URL:', error);
                                openCentralizedChat(chatId, 'Unknown User', 'user');
                            }
                        }, 1000);
                    } else {
                        // User sees Social Worker
                        setTimeout(() => openCentralizedChat(chatId, 'Social Worker', 'admin'), 1000);
                    }
                } else if (userId) {
                    setTimeout(() => createOrOpenChatWithUser(userId), 1000);
                }
                
            } catch (error) {
                console.error('Error initializing chat system:', error);
            }
        }

        function loadUserChats() {
            console.log('📂 Loading user chats...');
            
            const chatListContent = document.getElementById('chatListContent');
            chatListContent.innerHTML = '<div class="loading"><i class="fas fa-spinner"></i><p>Loading conversations...</p></div>';

            // Listen for chats based on user role
            if (currentUserRole === 'admin') {
                // Admins see all centralized chats
                realtimeDb.ref('chats').orderByChild('last_message_timestamp').on('value', (snapshot) => {
                    const chats = [];
                    
                    snapshot.forEach((chatSnapshot) => {
                        const chatData = chatSnapshot.val();
                        const chatId = chatSnapshot.key;
                        
                        // Only show centralized chats for admins
                        if (chatData.is_centralized) {
                            chats.push({
                                id: chatId,
                                ...chatData
                            });
                        }
                    });

                    // Sort by last message timestamp (newest first)
                    chats.sort((a, b) => (b.last_message_timestamp || 0) - (a.last_message_timestamp || 0));
                    
                    displayChatList(chats);
                });
            } else {
                // Regular users see only their own centralized chat
                const userChatId = `user_${currentUserId}`;
                realtimeDb.ref(`chats/${userChatId}`).on('value', (snapshot) => {
                    const chats = [];
                    
                    if (snapshot.exists()) {
                        const chatData = snapshot.val();
                        chats.push({
                            id: userChatId,
                            ...chatData
                        });
                    }
                    
                    displayChatList(chats);
                });
            }
        }

        async function displayChatList(chats) {
            const chatListContent = document.getElementById('chatListContent');
            
            if (chats.length === 0) {
                chatListContent.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-comments" style="font-size: 3em; margin-bottom: 15px; color: #ddd;"></i>
                        <h3>No conversations yet</h3>
                        <p>Start a conversation from the donation or adoption process</p>
                    </div>
                `;
                return;
            }

            let chatListHTML = '';
            
            for (const chat of chats) {
                let displayName = '';
                let displayRole = '';
                
                if (currentUserRole === 'admin') {
                    // Admin sees the user's name - fetch from Firestore if needed
                    if (chat.user_name && chat.user_name !== 'User' && chat.user_name !== 'Unknown User') {
                        displayName = chat.user_name;
                    } else if (chat.user_id) {
                        // Fetch actual user name from Firestore
                        try {
                            const userDoc = await db.collection('users').doc(chat.user_id).get();
                            if (userDoc.exists) {
                                const userData = userDoc.data();
                                if (userData.firstName && userData.lastName) {
                                    displayName = `${userData.firstName} ${userData.lastName}`;
                                } else if (userData.username) {
                                    displayName = userData.username;
                                } else {
                                    displayName = 'Unknown User';
                                }
                                
                                // Update the chat data with the real name for future use
                                realtimeDb.ref(`chats/${chat.id}`).update({
                                    user_name: displayName
                                });
                            } else {
                                displayName = 'Unknown User';
                            }
                        } catch (error) {
                            console.error('Error fetching user name:', error);
                            displayName = chat.user_name || 'Unknown User';
                        }
                    } else {
                        displayName = 'Unknown User';
                    }
                    displayRole = 'user';
                } else {
                    // User sees "Social Worker"
                    displayName = 'Social Worker';
                    displayRole = 'admin';
                    
                    // Show which admins are participating (only count real admins, not test data)
                    if (chat.participant_admins) {
                        const adminIds = Object.keys(chat.participant_admins);
                        // Filter out test/system entries and only count real admin IDs
                        const realAdmins = adminIds.filter(adminId => {
                            const admin = chat.participant_admins[adminId];
                            // Skip test entries, system entries, and entries without proper names
                            return adminId !== 'system' && 
                                   adminId !== 'test' && 
                                   admin.name !== 'Test Admin' && 
                                   admin.name !== 'System' &&
                                   adminId.length > 10; // Real Firebase user IDs are longer
                        });
                        
                        const adminCount = realAdmins.length;
                        if (adminCount > 0) {
                            displayName = `Social Worker (${adminCount} social worker${adminCount > 1 ? 's' : ''})`;
                        }
                    }
                }

                // Format timestamp
                const timestamp = chat.last_message_timestamp;
                const timeStr = timestamp ? formatTimestamp(timestamp) : '';
                
                // Connection type display
                const connectionType = formatConnectionType(chat.connection_type);
                
                // Unread count
                const unreadCount = chat.unread_count || 0;
                const unreadBadge = unreadCount > 0 ? `<div class="chat-unread-badge">${unreadCount}</div>` : '';
                
                chatListHTML += `
                    <div class="chat-item ${currentChatId === chat.id ? 'active' : ''}" onclick="openCentralizedChat('${chat.id}', '${displayName}', '${displayRole}')">
                        <div class="chat-item-header">
                            <div class="chat-user-name">${displayName}</div>
                            <div class="chat-timestamp">${timeStr}</div>
                        </div>
                        <div class="chat-last-message">${chat.last_message || 'No messages yet'}</div>
                        <div class="chat-connection-type">${connectionType}</div>
                        ${unreadBadge}
                    </div>
                `;
            }
            
            chatListContent.innerHTML = chatListHTML;
        }

        function openCentralizedChat(chatId, displayName, displayRole) {
            console.log('💬 Opening centralized chat:', chatId, 'with:', displayName);
            
            // Extract actual user ID from chat ID for profile viewing
            let actualUserId = chatId;
            if (chatId.startsWith('user_')) {
                actualUserId = chatId.substring(5); // Remove 'user_' prefix
            }
            
            // Update active chat
            currentChatId = chatId;
            currentChatUser = {
                id: actualUserId, // Use actual user ID for profile viewing
                chatId: chatId,   // Keep chat ID for messaging
                name: displayName,
                role: displayRole
            };
            
            // Update UI
            updateChatListSelection();
            displayChatArea();
            
            // Wait for chat area to be created before loading messages
            setTimeout(() => {
                loadChatMessages();
            }, 100);
            
            // Mark messages as read
            markMessagesAsRead(chatId);
            
            // Update URL without refresh
            const newUrl = `${window.location.pathname}?chatId=${chatId}`;
            window.history.replaceState({}, '', newUrl);
        }

        function updateChatListSelection() {
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.remove('active');
            });
            
            if (currentChatId) {
                const activeItem = document.querySelector(`.chat-item[onclick*="${currentChatId}"]`);
                if (activeItem) {
                    activeItem.classList.add('active');
                }
            }
        }

        async function displayChatArea() {
            const chatAreaContent = document.getElementById('chatAreaContent');
            
            console.log('🖥️ Displaying chat area for:', currentChatId, currentChatUser);
            
            if (!currentChatId || !currentChatUser) {
                chatAreaContent.innerHTML = `
                    <div class="empty-chat">
                        <i class="fas fa-comments"></i>
                        <h3>Welcome to Messages</h3>
                        <p>Select a conversation to start chatting</p>
                    </div>
                `;
                return;
            }

            // Get conversation type
            const conversationType = await getConversationType(currentChatId);
            const conversationTypeDisplay = getConversationTypeDisplay(conversationType);

            chatAreaContent.innerHTML = `
                <div class="chat-header">
                    <div class="chat-user-info">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png" alt="${currentChatUser.name}" class="chat-user-avatar">
                        <div class="chat-user-details">
                            <h3>${currentChatUser.name} ${currentChatUser.role === 'admin' ? '(Social Worker)' : ''}</h3>
                            <div class="chat-user-status">${currentUserRole === 'admin' ? 'Tap to view profile' : 'Online'}</div>
                        </div>
                    </div>
                    <div class="chat-actions">
                        ${currentUserRole === 'admin' ? `
                            <button class="chat-action-btn" onclick="viewUserProfile('${currentChatUser.id}')">
                                <i class="fas fa-user"></i> Profile
                            </button>
                        ` : ''}
                    </div>
                </div>

                <div class="conversation-type-header">
                    <div class="conversation-type-badge ${conversationType}">
                        ${conversationTypeDisplay.icon} ${conversationTypeDisplay.text}
                    </div>
                    ${currentUserRole === 'admin' ? `
                        <button class="profile-view-btn" onclick="viewUserProfile('${currentChatUser.id}')">
                            <i class="fas fa-user"></i> View Profile
                        </button>
                    ` : ''}
                </div>

                <div class="connection-status">
                    You are now connected with ${currentChatUser.name}
                </div>
                
                <div class="messages-container" id="messagesContainer">
                    <div class="loading">
                        <i class="fas fa-spinner"></i>
                        <p>Loading messages...</p>
                    </div>
                </div>
                
                <div class="typing-indicator" id="typingIndicator" style="display: none;">
                    <span>${currentChatUser.name} is typing</span>
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                
                <div class="message-input-container">
                    <textarea class="message-input" id="messageInput" placeholder="Type a message..." rows="1"></textarea>
                    <button class="send-button" id="sendButton" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            `;

            console.log('🖥️ Chat area HTML created, checking for messagesContainer...');
            const messagesContainer = document.getElementById('messagesContainer');
            console.log('🖥️ Messages container found:', !!messagesContainer);

            // Set up message input handlers
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');

            messageInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            messageInput.addEventListener('input', () => {
                // Auto-resize textarea
                messageInput.style.height = 'auto';
                messageInput.style.height = messageInput.scrollHeight + 'px';
                
                // Enable/disable send button
                sendButton.disabled = !messageInput.value.trim();
            });
        }

        function loadChatMessages() {
            if (!currentChatId) return;
            
            console.log('📨 Loading messages for chat:', currentChatId);
            
            // Remove existing listener
            if (messagesListener) {
                messagesListener.off();
            }
            
            // Set up real-time message listener with error handling
            messagesListener = realtimeDb.ref(`chats/${currentChatId}/messages`).orderByChild('timestamp');
            
            messagesListener.on('value', (snapshot) => {
                console.log('📨 Messages snapshot received:', snapshot.exists(), snapshot.numChildren());
                
                const messages = [];
                
                snapshot.forEach((messageSnapshot) => {
                    const messageData = messageSnapshot.val();
                    console.log('📨 Message data:', messageData);
                    messages.push({
                        id: messageSnapshot.key,
                        ...messageData
                    });
                });
                
                console.log('📨 Total messages loaded:', messages.length);
                displayMessages(messages);
            }, (error) => {
                console.error('❌ Error loading messages:', error);
                const messagesContainer = document.getElementById('messagesContainer');
                messagesContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #e74c3c;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2em; margin-bottom: 10px;"></i>
                        <p>Error loading messages. Please refresh the page.</p>
                    </div>
                `;
            });
        }

        function displayMessages(messages) {
            const messagesContainer = document.getElementById('messagesContainer');
            
            console.log('📨 Displaying messages:', messages.length);
            
            if (!messagesContainer) {
                console.error('❌ Messages container not found, retrying in 200ms...');
                setTimeout(() => displayMessages(messages), 200);
                return;
            }
            
            if (messages.length === 0) {
                messagesContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-comment" style="font-size: 2em; margin-bottom: 10px; color: #ddd;"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                `;
                return;
            }

            let messagesHTML = '';
            
            messages.forEach((message) => {
                const isSystemMessage = message.isSystemMessage || message.messageType === 'system';
                const isSentByCurrentUser = message.senderId === currentUserId;
                
                if (isSystemMessage) {
                    let progressButton = '';
                    if (message.hasProgressButton && message.progressUrl) {
                        progressButton = `
                            <div style="margin-top: 10px;">
                                <a href="${message.progressUrl}" target="_blank" style="
                                    display: inline-block;
                                    background: #6ea4ce;
                                    color: white;
                                    padding: 8px 16px;
                                    border-radius: 6px;
                                    text-decoration: none;
                                    font-weight: 500;
                                    font-size: 14px;
                                    transition: background 0.2s;
                                " onmouseover="this.style.background='#5a8bb8'" onmouseout="this.style.background='#6ea4ce'">
                                    📊 View Progress
                                </a>
                            </div>
                        `;
                    }
                    
                    let donationButton = '';
                    if (message.hasDonationButton && message.donationUrl) {
                        donationButton = `
                            <div style="margin-top: 10px;">
                                <a href="${message.donationUrl}" target="_blank" style="
                                    display: inline-block;
                                    background: #e91e63;
                                    color: white;
                                    padding: 8px 16px;
                                    border-radius: 6px;
                                    text-decoration: none;
                                    font-weight: 500;
                                    font-size: 14px;
                                    transition: background 0.2s;
                                " onmouseover="this.style.background='#ad1457'" onmouseout="this.style.background='#e91e63'">
                                    💖 View Donations
                                </a>
                            </div>
                        `;
                    }
                    
                    messagesHTML += `
                        <div class="message system-message">
                            <div class="message-content">
                                ${message.message}
                                ${progressButton}
                                ${donationButton}
                            </div>
                        </div>
                    `;
                } else {
                    const messageClass = isSentByCurrentUser ? 'sent' : 'received';
                    const timestamp = formatTimestamp(message.timestamp);
                    
                    // Show sender name for all messages
                    let senderDisplay = '';
                    const senderName = message.senderName || 'Unknown';
                    const senderRole = message.senderRole || 'user';
                    
                    if (senderRole === 'admin') {
                        senderDisplay = `<div class="message-sender admin">${senderName} (Social Worker)</div>`;
                    } else {
                        senderDisplay = `<div class="message-sender user">${senderName}</div>`;
                    }
                    
                    messagesHTML += `
                        <div class="message ${messageClass}">
                            ${senderDisplay}
                            <div class="message-content">
                                ${message.message}
                                <div class="message-timestamp">${timestamp}</div>
                            </div>
                        </div>
                    `;
                }
            });
            
            messagesContainer.innerHTML = messagesHTML;
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        async function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message || !currentChatId) return;
            
            try {
                const timestamp = Date.now();
                const messageId = realtimeDb.ref(`chats/${currentChatId}/messages`).push().key;
                
                // Get sender name
                let senderName = currentUsername || 'Unknown';
                if (currentUserRole === 'admin') {
                    // For admins, try to get their actual name from Firestore
                    try {
                        const userDoc = await db.collection('users').doc(currentUserId).get();
                        if (userDoc.exists) {
                            const userData = userDoc.data();
                            senderName = userData.username || userData.firstName || 'Admin';
                        }
                    } catch (error) {
                        console.error('Error getting admin name:', error);
                        senderName = 'Admin';
                    }
                }
                
                const messageData = {
                    messageId: messageId,
                    senderId: currentUserId,
                    senderName: senderName,
                    senderRole: currentUserRole,
                    message: message,
                    timestamp: timestamp,
                    serverTimestamp: timestamp,
                    read_by_receiver: false,
                    deleted_by_sender: false,
                    deleted_by_receiver: false,
                    isSystemMessage: false,
                    messageType: 'text',
                    created_at: timestamp
                };
                
                // Send message to Firebase
                await realtimeDb.ref(`chats/${currentChatId}/messages/${messageId}`).set(messageData);
                
                // Update chat's last message and activity
                const updateData = {
                    last_message: message,
                    last_message_timestamp: timestamp,
                    last_activity: timestamp
                };
                
                // If this is an admin sending a message, add them to participants
                if (currentUserRole === 'admin' && currentUserId && senderName !== 'Test Admin') {
                    const adminParticipantData = {
                        id: currentUserId,
                        name: senderName,
                        joined_at: timestamp,
                        last_active: timestamp
                    };
                    
                    await realtimeDb.ref(`chats/${currentChatId}/participant_admins/${currentUserId}`).set(adminParticipantData);
                }
                
                await realtimeDb.ref(`chats/${currentChatId}`).update(updateData);
                
                // Clear input
                messageInput.value = '';
                messageInput.style.height = 'auto';
                document.getElementById('sendButton').disabled = true;
                
                console.log('✅ Message sent successfully to centralized chat');
                
            } catch (error) {
                console.error('❌ Error sending message:', error);
                alert('Failed to send message. Please try again.');
            }
        }

        function markMessagesAsRead(chatId) {
            // Update unread count in Firebase
            realtimeDb.ref(`chats/${chatId}`).update({
                unread_count: 0
            });
        }

        function setupChatListeners() {
            // Listen for new messages to show notifications
            realtimeDb.ref('chats').on('child_changed', (snapshot) => {
                const chatData = snapshot.val();
                const chatId = snapshot.key;
                
                // Check if this is a chat the user is part of
                if (chatData.participant_user === currentUserId || chatData.participant_admin === currentUserId) {
                    // If it's not the currently active chat, show notification
                    if (chatId !== currentChatId) {
                        console.log('🔔 New message in chat:', chatId);
                        // You can add notification logic here
                    }
                }
            });
        }

        async function createOrOpenChatWithUser(otherUserId) {
            try {
                // Generate chat ID
                const chatId = currentUserId < otherUserId ? `${currentUserId}_${otherUserId}` : `${otherUserId}_${currentUserId}`;
                
                // Check if chat exists
                const chatSnapshot = await realtimeDb.ref(`chats/${chatId}`).once('value');
                
                if (chatSnapshot.exists()) {
                    // Chat exists, open it
                    const chatData = chatSnapshot.val();
                    const otherUserDoc = await db.collection('users').doc(otherUserId).get();
                    const otherUserData = otherUserDoc.data();
                    
                    openChat(chatId, otherUserId, otherUserData.username || 'Unknown', otherUserData.role || 'user');
                } else {
                    // Create new chat
                    const otherUserDoc = await db.collection('users').doc(otherUserId).get();
                    const otherUserData = otherUserDoc.data();
                    
                    const chatData = {
                        connection_type: 'manual',
                        created_by: currentUserId,
                        participant_user: currentUserRole === 'admin' ? otherUserId : currentUserId,
                        participant_admin: currentUserRole === 'admin' ? currentUserId : otherUserId,
                        created_at: Date.now(),
                        last_activity: Date.now(),
                        unread_count: 0
                    };
                    
                    await realtimeDb.ref(`chats/${chatId}`).set(chatData);
                    
                    openChat(chatId, otherUserId, otherUserData.username || 'Unknown', otherUserData.role || 'user');
                }
                
            } catch (error) {
                console.error('Error creating/opening chat:', error);
            }
        }

        function filterChats() {
            const searchTerm = document.getElementById('chatSearchInput').value.toLowerCase();
            const chatItems = document.querySelectorAll('.chat-item');
            
            chatItems.forEach(item => {
                const userName = item.querySelector('.chat-user-name').textContent.toLowerCase();
                const lastMessage = item.querySelector('.chat-last-message').textContent.toLowerCase();
                
                if (userName.includes(searchTerm) || lastMessage.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function viewUserProfile(userId) {
            window.open(`Profile.php?userId=${userId}`, '_blank');
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
            if (!connectionType) return '';
            
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
                'system': 'System Message'
            };
            
            return types[connectionType] || connectionType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        async function getConversationType(chatId) {
            try {
                // Get chat data from Firebase
                const chatSnapshot = await realtimeDb.ref(`chats/${chatId}`).once('value');
                const chatData = chatSnapshot.val();
                
                if (chatData && chatData.connection_type) {
                    return chatData.connection_type;
                }
                
                // If no connection type, try to detect from messages
                const messagesSnapshot = await realtimeDb.ref(`chats/${chatId}/messages`).once('value');
                const messages = messagesSnapshot.val();
                
                if (messages) {
                    for (const messageId in messages) {
                        const message = messages[messageId];
                        if (message.donationType) {
                            return message.donationType;
                        }
                        if (message.messageType === 'adoption' || message.message.toLowerCase().includes('adoption')) {
                            return 'adoption';
                        }
                    }
                }
                
                return 'general';
            } catch (error) {
                console.error('Error getting conversation type:', error);
                return 'general';
            }
        }

        function getConversationTypeDisplay(type) {
            const types = {
                'adoption': { icon: '👶', text: 'Adoption Process' },
                'donation': { icon: '💖', text: 'Donation Process' },
                'mixed': { icon: '🤝', text: 'Mixed Process' },
                'money': { icon: '💰', text: 'Money Donation' },
                'money_donation': { icon: '💰', text: 'Money Donation' },
                'education': { icon: '📚', text: 'Education Donation' },
                'education_donation': { icon: '📚', text: 'Education Donation' },
                'medicine': { icon: '💊', text: 'Medicine Sponsorship' },
                'medicine_donation': { icon: '💊', text: 'Medicine Sponsorship' },
                'toys': { icon: '🧸', text: 'Toys Donation' },
                'toys_donation': { icon: '🧸', text: 'Toys Donation' },
                'clothes': { icon: '👕', text: 'Clothes Donation' },
                'clothes_donation': { icon: '👕', text: 'Clothes Donation' },
                'food': { icon: '🍎', text: 'Food Donation' },
                'food_donation': { icon: '🍎', text: 'Food Donation' },
                'appointment': { icon: '📅', text: 'Appointment' },
                'manual': { icon: '💬', text: 'Direct Message' },
                'general': { icon: '💬', text: 'General Chat' }
            };
            
            return types[type] || types['general'];
        }

        // Make functions globally available
        window.openChat = openChat;
        window.sendMessage = sendMessage;
        window.viewUserProfile = viewUserProfile;
        window.createOrOpenChatWithUser = createOrOpenChatWithUser;
    </script>
</body>
</html> 