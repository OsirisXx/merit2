<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'session_check.php';

// Load notifications directly from file - SIMPLE AND WORKING
$currentUserId = $_SESSION['uid'] ?? 'h8qq0E8avWO74cqS2Goy1wtENJh1';
$userNotifications = [];
$unreadCount = 0;
$phpWorking = true;

try {
    if (file_exists('notifications.json')) {
        $allNotifications = json_decode(file_get_contents('notifications.json'), true) ?? [];
        // Filter notifications for current user
        $userNotifications = array_filter($allNotifications, function($notif) use ($currentUserId) {
            return isset($notif['userId']) && $notif['userId'] === $currentUserId;
        });
        // Sort by timestamp (newest first)
        usort($userNotifications, function($a, $b) {
            return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
        });
        // Count unread
        $unreadCount = count(array_filter($userNotifications, function($n) { return !($n['isRead'] ?? false); }));
    }
} catch (Exception $e) {
    $phpWorking = false;
}
?>
<!-- Font Awesome CSS for navbar icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<nav style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: #7CB9E8; color: white; height: 80px; position: relative;">
    
    <div style="display: flex; align-items: center;">
        <a href="Index.php"><img src="https://www.meritxellchildrensfoundation.org/images/logo-with-words-3.png" alt="Logo" style="height: 60px;"></a>
    </div>

    <div style="display: flex; align-items: center; gap: 20px; margin-right: 40px; position: relative;">
        <!-- MESSAGES ICON -->
        <div id="messages-icon" class="clickable-icon" style="font-size: 22px; color: white; position: relative; cursor: pointer;" onclick="window.location.href='chat_messages.php';" title="Messages">
            <i class="fas fa-comments"></i>
            <span id="messages-badge" class="notification-badge" style="position: absolute; top: -8px; right: -8px; background: #ff4444; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; display: none; align-items: center; justify-content: center; font-weight: bold;">0</span>
        </div>
        
        <!-- NOTIFICATION BELL - Works with both PHP and Python servers -->
        <div id="notif-icon" class="clickable-icon" style="font-size: 22px; color: white; position: relative; cursor: pointer;">🔔
            <span id="notif-badge" class="notification-badge" style="position: absolute; top: -8px; right: -8px; background: #ff4444; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; display: none; align-items: center; justify-content: center; font-weight: bold;">0</span>
            
            <div id="notif-popup" class="popup-menu" style="display: none; position: absolute; top: 40px; right: -5px; background: white; color: #333; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); width: 300px; max-height: 400px; overflow-y: auto; z-index: 1000;">
                <div style="padding: 10px 15px; border-bottom: 1px solid #eee; font-weight: bold; background: #f8f9fa;">
                    <span id="notif-header">Notifications</span>
                </div>
                
                <div id="notif-list" style="min-height: 50px;">
                    <div style="padding: 20px; text-align: center; color: #666;" id="loading-text">Loading...</div>
                </div>
            </div>
        </div>

        <!-- PROFILE -->
        <div id="profile-icon" class="clickable-icon" style="cursor: pointer;">
            <img id="profile-picture" src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541" alt="Profile" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;">
            <div id="profile-popup" class="popup-menu" style="display: none; position: absolute; top: 40px; right: 0; background: white; color: #333; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); width: 150px; z-index: 1000;">
                <a href="Profile.php" style="padding: 10px 15px; display: block; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">My Profile</a>
                <a href="logout.php" style="padding: 10px 15px; display: block; color: #333; text-decoration: none;">Sign Out</a>
            </div>
        </div>
    </div>
</nav>

<style>
.popup-menu {
    position: absolute;
    background: white;
    color: #333;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 1000;
    font-size: 1rem;
}
.popup-menu::before {
    content: "";
    position: absolute;
    top: -8px;
    right: 14px;
    border-width: 0 8px 8px 8px;
    border-style: solid;
    border-color: transparent transparent white transparent;
}
.popup-menu a:hover, .notification-item-mini:hover {
    background-color: #f0f0f0;
}
.clickable-icon {
    cursor: pointer;
    position: relative;
}
</style>

<script>
// Universal notification system - works with both PHP and Python servers
const CURRENT_USER_ID = '<?php echo htmlspecialchars($currentUserId); ?>';
let notifications = [];
let unreadCount = 0;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 Starting notification system...');
    
    // Set up click handlers
    const notifIcon = document.getElementById('notif-icon');
    const notifPopup = document.getElementById('notif-popup');
    const profileIcon = document.getElementById('profile-icon');
    const profilePopup = document.getElementById('profile-popup');

    if (notifIcon && notifPopup) {
        notifIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            if (profilePopup) profilePopup.style.display = 'none';
            
            const isOpening = notifPopup.style.display !== 'block';
            notifPopup.style.display = isOpening ? 'block' : 'none';
            
            // If opening the popup and there are unread notifications, mark them all as read
            if (isOpening && unreadCount > 0) {
                console.log('🔔 NOTIFICATION BELL OPENED: Marking', unreadCount, 'notifications as read');
                markAllNotificationsAsRead();
            }
        });
    }

    if (profileIcon && profilePopup) {
        profileIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            if (notifPopup) notifPopup.style.display = 'none';
            profilePopup.style.display = profilePopup.style.display === 'block' ? 'none' : 'block';
        });
    }

    document.addEventListener('click', function() {
        if (notifPopup) notifPopup.style.display = 'none';
        if (profilePopup) profilePopup.style.display = 'none';
    });
    
    // Wait for Firebase to be available before loading notifications
    function waitForFirebaseAndLoadNotifications() {
        // Check for Firebase in multiple ways (global window.firebase or just firebase)
        const firebaseAvailable = window.firebase || (typeof firebase !== 'undefined' && firebase);
        const dbAvailable = window.db || (typeof db !== 'undefined' && db);
        
        if (firebaseAvailable && dbAvailable) {
            console.log('🔥 Firebase available, loading notifications and profile picture...');
            loadNotifications();
            loadUserProfilePicture();
            loadUnreadMessageCount();
            
            // Auto-refresh every 2 minutes (reduced from 30 seconds to prevent excessive requests)
            setInterval(loadNotifications, 120000);
            setInterval(loadUnreadMessageCount, 120000);
        } else {
            console.log('⏳ Waiting for Firebase to initialize... (firebase:', !!firebaseAvailable, 'db:', !!dbAvailable, ')');
            
            // Hide loading text after 3 seconds if Firebase is not available
            setTimeout(() => {
                const loadingText = document.getElementById('loading-text');
                if (loadingText) {
                    loadingText.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">No notifications available</div>';
                }
            }, 3000);
            
            setTimeout(waitForFirebaseAndLoadNotifications, 1000);
        }
    }
    
    waitForFirebaseAndLoadNotifications();
});

function loadNotifications() {
    console.log('📂 MOBILE APP LOGIC: Loading notifications from file system (primary) and Firebase (backup)...');
    
    // Method 1: Load from file system (EXACT MOBILE APP LOGIC)
    fetch('super_simple_notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'get_notifications',
            userId: CURRENT_USER_ID,
            limit: 50
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notifications) {
            console.log(`✅ MOBILE APP LOGIC: Loaded ${data.notifications.length} notifications from file system`);
            
            // Convert to the format expected by the display
            notifications = data.notifications.map(n => ({
                id: n.id,
                userId: n.userId,
                title: n.title,
                message: n.message,
                type: n.type,
                timestamp: n.timestamp,
                isRead: n.isRead || false,
                icon: n.icon || '📋',
                data: n.data || {},
                source: 'file_system'
            }));
            
            // Count unread
            unreadCount = notifications.filter(n => !n.isRead).length;
            
            updateNotificationDisplay();
            loadUserProfilePicture();
            
            // Hide loading text
            const loadingText = document.getElementById('loading-text');
            if (loadingText) {
                loadingText.style.display = 'none';
            }
        } else {
            console.log('⚠️ MOBILE APP LOGIC: File system notifications failed, trying Firebase backup...');
            loadNotificationsFromFirebase();
        }
    })
    .catch(error => {
        console.error('❌ MOBILE APP LOGIC: File system notification loading failed:', error);
        console.log('🔄 MOBILE APP LOGIC: Falling back to Firebase...');
        loadNotificationsFromFirebase();
    });
}

function loadNotificationsFromFirebase() {
    console.log('📂 Loading notifications from Firebase backup...');
    
    // Check for Firebase and db availability flexibly
    const firebaseAvailable = window.firebase || (typeof firebase !== 'undefined' && firebase);
    const dbInstance = window.db || (typeof db !== 'undefined' && db);
    
    if (!firebaseAvailable || !dbInstance) {
        console.error('❌ Firebase not available, using PHP file fallback');
        return;
    }
    
    // Load notifications from BOTH Firebase collections (notifications AND notification_logs)
    Promise.all([
        // Load from notification_logs collection
        dbInstance.collection('notification_logs')
            .where('userId', '==', CURRENT_USER_ID)
            .limit(50)
            .get(),
        // Load from notifications collection
        dbInstance.collection('notifications')
            .where('userId', '==', CURRENT_USER_ID)
            .limit(50)
            .get()
    ]).then(([notificationLogsSnapshot, notificationsSnapshot]) => {
        const firebaseNotifications = [];
        
        // Process notification_logs collection
        notificationLogsSnapshot.forEach(doc => {
            const data = doc.data();
            const notification = {
                id: doc.id,
                userId: data.userId,
                title: data.title,
                message: data.message,
                type: data.type,
                status: data.status,
                timestamp: data.timestampMs || (data.timestamp && data.timestamp.toMillis ? data.timestamp.toMillis() : Date.now()),
                isRead: data.isRead || false,
                icon: data.icon || '📋',
                processType: data.processType,
                notificationType: data.notificationType,
                data: data.data || {},
                source: 'notification_logs'
            };
            firebaseNotifications.push(notification);
        });
        
        // Process notifications collection
        notificationsSnapshot.forEach(doc => {
            const data = doc.data();
            const notification = {
                id: doc.id,
                userId: data.userId,
                title: data.title,
                message: data.message,
                type: data.type,
                status: data.status,
                timestamp: data.timestampMs || (data.timestamp && data.timestamp.toMillis ? data.timestamp.toMillis() : Date.now()),
                isRead: data.isRead || false,
                icon: data.icon || '📋',
                processType: data.processType,
                notificationType: data.notificationType,
                data: data.data || {},
                source: 'notifications'
            };
            firebaseNotifications.push(notification);
        });
        
        // Remove duplicates based on ID (in case notification exists in both collections)
        const uniqueNotifications = firebaseNotifications.filter((notification, index, self) =>
            index === self.findIndex(n => n.id === notification.id)
        );
        
        // Sort by timestamp (newest first)
        uniqueNotifications.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));
        
        notifications = uniqueNotifications;
        
        // Count unread
        unreadCount = notifications.filter(n => !n.isRead).length;
        
        console.log(`✅ Loaded ${notifications.length} notifications from Firebase backup (${unreadCount} unread)`);
        console.log(`📊 Sources: ${firebaseNotifications.filter(n => n.source === 'notification_logs').length} from notification_logs, ${firebaseNotifications.filter(n => n.source === 'notifications').length} from notifications`);
        
        updateNotificationDisplay();
        loadUserProfilePicture();
        
        // Hide loading text
        const loadingText = document.getElementById('loading-text');
        if (loadingText) {
            loadingText.style.display = 'none';
        }
    })
        .catch(error => {
            console.error('❌ Failed to load notifications from Firebase backup:', error);
            console.error('Error details:', error.code, error.message);
            
            // Fallback to empty notifications
            notifications = [];
            unreadCount = 0;
            updateNotificationDisplay();
        });
}

function updateNotificationDisplay() {
    // Update badge
    const badge = document.getElementById('notif-badge');
    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
    
    // Update header
    const header = document.getElementById('notif-header');
    if (header) {
        header.textContent = `Notifications (${notifications.length})`;
        if (unreadCount > 0) {
            header.innerHTML = `Notifications (${notifications.length}) <span style="float: right; background: #ff4444; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px;">${unreadCount} new</span>`;
        }
    }
    
    // Update list
    const list = document.getElementById('notif-list');
    if (!list) return;
    
    // Hide loading text first
    const loadingText = document.getElementById('loading-text');
    if (loadingText) {
        loadingText.style.display = 'none';
    }
    
    if (notifications.length === 0) {
        list.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">No notifications yet</div>';
        return;
    }
    
    let html = '';
    notifications.slice(0, 50).forEach(notification => {
        const time = formatTime(notification.timestamp);
        const unreadStyle = notification.isRead ? '' : 'background-color: #f8f9ff; border-left: 3px solid #007bff;';
        
        html += `
            <div class="notification-item-mini" style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer; ${unreadStyle}" 
                 onclick="markNotificationAsRead('${notification.id}')">
                <div style="font-weight: 600; font-size: 13px; margin-bottom: 3px; color: #333;">
                    ${notification.icon || '📋'} ${escapeHtml(notification.title)}
                </div>
                <div style="font-size: 12px; color: #666; line-height: 1.3; margin-bottom: 3px;">
                    ${escapeHtml(notification.message)}
                </div>
                <div style="font-size: 11px; color: #999;">
                    ${time}
                </div>
            </div>
        `;
    });
    
    list.innerHTML = html;
}

function formatTime(timestamp) {
    if (!timestamp) return 'Just now';
    
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMinutes = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Function to mark individual notification as read
function markNotificationAsRead(notificationId) {
    console.log('📖 Marking notification as read:', notificationId);
    
    // Check for Firebase and db availability flexibly
    const firebaseAvailable = window.firebase || (typeof firebase !== 'undefined' && firebase);
    const dbInstance = window.db || (typeof db !== 'undefined' && db);
    
    if (!firebaseAvailable || !dbInstance) {
        console.error('❌ Firebase not available, cannot mark notification as read');
        return;
    }
    
    // Find the notification to determine which collection it's from
    const notification = notifications.find(n => n.id === notificationId);
    if (!notification) {
        console.error('❌ Notification not found:', notificationId);
        return;
    }
    
    const collection = notification.source || 'notification_logs';
    
    // Update in the appropriate Firebase collection
    dbInstance.collection(collection).doc(notificationId).update({
        isRead: true,
        readAt: firebaseAvailable.firestore.FieldValue.serverTimestamp()
    })
    .then(() => {
        console.log(`✅ Notification marked as read in ${collection}`);
        
        // Also update local array for immediate UI feedback
        notification.isRead = true;
        
        // Update unread count
        unreadCount = notifications.filter(n => !n.isRead).length;
        
        // Update display
        updateNotificationDisplay();
    })
    .catch(error => {
        console.error(`❌ Failed to mark notification as read in ${collection}:`, error);
    });
}

// Function to mark all notifications as read (only those currently loaded in dropdown)
async function markAllNotificationsAsRead() {
    try {
        console.log('📖 NOTIFICATION BELL: Marking notifications as read for user:', CURRENT_USER_ID);
        
        // Only mark as read the notifications that are currently loaded in the dropdown
        const unreadNotifications = notifications.filter(n => !n.isRead);
        
        if (unreadNotifications.length === 0) {
            console.log('✅ No unread notifications in dropdown to mark as read');
            return;
        }
        
        console.log('📊 Marking', unreadNotifications.length, 'unread notifications as read (from dropdown)');
        
        // Update local notifications state immediately for UI feedback
        notifications.forEach(notification => {
            if (!notification.isRead) {
            notification.isRead = true;
                console.log('📝 Marked as read:', notification.title);
            }
        });
        unreadCount = 0;
        updateNotificationDisplay();
        
        // Save to backend using collection-based system
        const response = await fetch('super_simple_notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'mark_all_as_read',
                userId: CURRENT_USER_ID
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ NOTIFICATION BELL: All notifications marked as read in backend');
        } else {
            console.error('❌ NOTIFICATION BELL: Failed to mark notifications as read:', result.message);
            // Revert UI changes since backend failed
            console.log('🔄 Reverting UI changes due to backend failure');
            loadNotifications();
        }
        
    } catch (error) {
        console.error('❌ NOTIFICATION BELL: Error marking notifications as read:', error);
        // Revert UI changes since request failed
        console.log('🔄 Reverting UI changes due to error');
        loadNotifications();
    }
}

// Load user profile picture
function loadUserProfilePicture() {
    console.log('🖼️ Loading user profile picture for:', CURRENT_USER_ID);
    
    const firebaseAvailable = window.firebase || (typeof firebase !== 'undefined' && firebase);
    const dbInstance = window.db || (typeof db !== 'undefined' && db);
    
    if (!firebaseAvailable || !dbInstance) {
        console.log('⚠️ Firebase not available for profile picture, keeping default');
        return;
    }
    
    // Load user's profile picture from Firestore
    dbInstance.collection('users').doc(CURRENT_USER_ID).get()
        .then(doc => {
            if (doc.exists) {
                const userData = doc.data();
                // Check multiple possible field names for profile picture
                const profilePictureUrl = userData.profilePictureURL || userData.profilePicture || userData.photoURL;
                
                console.log('👤 User data found:', {
                    profilePictureURL: userData.profilePictureURL,
                    profilePicture: userData.profilePicture,
                    photoURL: userData.photoURL,
                    selectedUrl: profilePictureUrl
                });
                
                if (profilePictureUrl) {
                    const profileImg = document.getElementById('profile-picture');
                    if (profileImg) {
                        console.log('✅ Updating profile picture to:', profilePictureUrl);
                        profileImg.src = profilePictureUrl;
                        profileImg.onerror = function() {
                            console.log('❌ Profile picture failed to load, using default');
                            // If image fails to load, keep the default placeholder
                            this.src = 'https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541';
                        };
                    } else {
                        console.log('❌ Profile picture element not found');
                    }
                } else {
                    console.log('ℹ️ No profile picture URL found in user data');
                }
            } else {
                console.log('❌ User document not found in Firestore');
            }
        })
        .catch(error => {
            console.log('❌ Error loading profile picture:', error);
        });
}

// Load unread message count and update messages badge
function loadUnreadMessageCount() {
    console.log('💬 Loading unread message count for user:', CURRENT_USER_ID);
    
    const firebaseAvailable = window.firebase || (typeof firebase !== 'undefined' && firebase);
    const dbInstance = window.db || (typeof db !== 'undefined' && db);
    
    if (!firebaseAvailable || !dbInstance) {
        console.log('⚠️ Firebase not available for message count');
        return;
    }
    
    // Get unread message count using the same logic as the mobile app
    fetch('messaging_helper.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'get_unread_count',
            userId: CURRENT_USER_ID
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const unreadCount = data.unreadCount || 0;
            console.log('✅ Unread message count:', unreadCount);
            
            const messagesBadge = document.getElementById('messages-badge');
            if (messagesBadge) {
                if (unreadCount > 0) {
                    messagesBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    messagesBadge.style.display = 'flex';
                } else {
                    messagesBadge.style.display = 'none';
                }
            }
        } else {
            console.error('❌ Failed to load unread message count:', data.message);
        }
    })
    .catch(error => {
        console.error('❌ Error loading unread message count:', error);
    });
}
</script>
