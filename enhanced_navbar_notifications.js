/**
 * ENHANCED NAVBAR NOTIFICATIONS V2 - FIXED FOR JSON SYSTEM
 * Designed to work with notifications.json file system
 */

// Global notification state
let navbarNotifications = [];
let notificationListener = null;
let isNotificationSystemInitialized = false;

/**
 * Initialize notification system with JSON-based approach
 */
function initializeEnhancedNotifications(userId) {
    console.log('🔔 Initializing Enhanced Notification System V2 (JSON-based)');
    console.log('👤 User ID:', userId);
    
    if (isNotificationSystemInitialized) {
        console.log('⚠️ Notification system already initialized');
        return;
    }
    
    if (!userId) {
        console.error('❌ Cannot initialize notifications: No user ID provided');
        return;
    }
    
    // Use JSON-based API polling for reliable notifications
    startApiPolling(userId);
    isNotificationSystemInitialized = true;
}

/**
 * API polling for JSON-based notifications
 */
function startApiPolling(userId) {
    console.log('🔄 Starting JSON-based API polling for notifications');
    
    // Load notifications via API
    function pollNotifications() {
        fetch('enhanced_notification_api.php?action=get_notifications&userId=' + encodeURIComponent(userId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('📡 JSON notifications loaded:', data.notifications.length);
                    
                    // Check for new notifications
                    const newNotifications = data.notifications.filter(newNotif => 
                        !navbarNotifications.find(existing => existing.id === newNotif.id)
                    );
                    
                    if (newNotifications.length > 0) {
                        console.log('🆕 Found', newNotifications.length, 'new notifications');
                        
                        // Show toast for very recent notifications (within 10 seconds)
                        const now = Date.now();
                        newNotifications.forEach(notification => {
                            const notificationTime = notification.timestamp || 0;
                            if (now - notificationTime < 10000) {
                                showNotificationToast(notification);
                            }
                        });
                    }
                    
                    navbarNotifications = data.notifications || [];
                    updateNotificationBadge();
                    renderNavbarNotifications();
                } else {
                    console.error('❌ API error:', data.error || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('❌ API request failed:', error);
            });
    }
    
    // Initial load
    pollNotifications();
    
    // Poll every 3 seconds for real-time updates
    setInterval(pollNotifications, 3000);
}

/**
 * Update notification badge count
 */
function updateNotificationBadge() {
    const unreadCount = navbarNotifications.filter(n => !n.read && !n.isRead).length;
    
    console.log('🔢 Updating badge count:', unreadCount);
    
    const badge = document.getElementById('notification-count');
    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
    
    // Also update any other notification count elements
    document.querySelectorAll('.notification-count').forEach(element => {
        if (unreadCount > 0) {
            element.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
            element.style.display = 'inline-block';
        } else {
            element.style.display = 'none';
        }
    });
}

/**
 * Render notifications in the popup
 */
function renderNavbarNotifications() {
    const container = document.getElementById('notifications-container');
    if (!container) return;
    
    console.log('🎨 Rendering', navbarNotifications.length, 'notifications');
    
    if (navbarNotifications.length === 0) {
        container.innerHTML = '<div class="no-notifications">No notifications yet</div>';
        return;
    }
    
    const html = navbarNotifications.map(notification => {
        const isUnread = !notification.read && !notification.isRead;
        const icon = getNotificationIcon(notification.processType || notification.type || 'SYSTEM');
        const timeAgo = getTimeAgo(notification.timestamp);
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : 'read'}" 
                 onclick="handleNavbarNotificationClick('${notification.id}')"
                 data-notification-id="${notification.id}">
                <div class="notification-icon">${icon}</div>
                <div class="notification-content">
                    <div class="notification-title">${notification.title || 'Notification'}</div>
                    <div class="notification-message">${notification.message || ''}</div>
                    <div class="notification-time">${timeAgo}</div>
                </div>
                ${isUnread ? '<div class="unread-indicator"></div>' : ''}
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

/**
 * Get notification icon based on type
 */
function getNotificationIcon(processType) {
    const icons = {
        'ADOPTION': '👶',
        'DONATION': '💝',
        'APPOINTMENT': '📅',
        'MATCHING': '💕',
        'CHAT': '💬',
        'SYSTEM': '🔔',
        'adoption': '👶',
        'donation': '💝',
        'appointment': '📅',
        'appointment_scheduled': '📅',
        'matching': '💕',
        'admin_review_needed': '🔍',
        'general': '🔔'
    };
    return icons[processType] || '🔔';
}

/**
 * Get time ago string
 */
function getTimeAgo(timestamp) {
    if (!timestamp) return 'Just now';
    
    const now = Date.now();
    const diff = now - timestamp;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
    if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    return 'Just now';
}

/**
 * Handle notification click
 */
async function handleNavbarNotificationClick(notificationId) {
    console.log('🖱️ Notification clicked:', notificationId);
    
    const notification = navbarNotifications.find(n => n.id === notificationId);
    if (!notification) {
        console.log('⚠️ Notification not found:', notificationId);
        return;
    }
    
    // Mark as read if unread
    if (!notification.read && !notification.isRead) {
        await markNotificationAsRead(notificationId);
    }
    
    // Close popup
    const popup = document.getElementById('notif-popup');
    if (popup) {
        popup.style.display = 'none';
    }
    
    // Navigate to notifications page or relevant page
    if (notification.data && notification.data.redirectUrl) {
        window.location.href = notification.data.redirectUrl;
    } else {
        window.location.href = 'notifications.php';
    }
}

/**
 * Mark notification as read using JSON API
 */
async function markNotificationAsRead(notificationId) {
    console.log('✅ Marking notification as read:', notificationId);
    
    try {
        const response = await fetch('enhanced_notification_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_as_read&notificationId=' + encodeURIComponent(notificationId)
        });
        
        const data = await response.json();
        if (data.success) {
            console.log('✅ Notification marked as read via JSON API');
            
            // Update local state immediately
            const notification = navbarNotifications.find(n => n.id === notificationId);
            if (notification) {
                notification.read = true;
                notification.isRead = true;
                notification.readAt = Date.now();
                updateNotificationBadge();
                renderNavbarNotifications();
            }
        } else {
            console.error('❌ API error marking as read:', data.error);
        }
        
    } catch (error) {
        console.error('❌ Error marking notification as read:', error);
    }
}

/**
 * Mark all notifications as read
 */
async function markAllNotificationsAsRead() {
    console.log('✅ Marking all notifications as read');
    
    const unreadNotifications = navbarNotifications.filter(n => !n.read && !n.isRead);
    if (unreadNotifications.length === 0) {
        console.log('ℹ️ No unread notifications to mark');
        return;
    }
    
    for (const notification of unreadNotifications) {
        await markNotificationAsRead(notification.id);
    }
}

/**
 * Show notification toast for new notifications
 */
function showNotificationToast(notification) {
    console.log('🍞 Showing notification toast:', notification.title);
    
    // Create toast element
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #333;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        max-width: 300px;
        font-family: Arial, sans-serif;
        animation: slideInRight 0.3s ease-out;
    `;
    
    const icon = getNotificationIcon(notification.processType || notification.type || 'SYSTEM');
    toast.innerHTML = `
        <div style="font-weight: bold; margin-bottom: 5px;">
            ${icon} ${notification.title || 'New Notification'}
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            ${notification.message || 'You have a new notification'}
        </div>
    `;
    
    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
    
    // Click to dismiss
    toast.addEventListener('click', () => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
        handleNavbarNotificationClick(notification.id);
    });
}

/**
 * Test notification system
 */
function testNotificationSystem(userId) {
    console.log('🧪 Testing JSON notification system for user:', userId);
    
    fetch('enhanced_notification_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=send_test_notification&userId=' + encodeURIComponent(userId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Test notification sent successfully');
            alert('Test notification sent! Check the bell icon.');
        } else {
            console.error('❌ Test notification failed:', data.error);
            alert('Test notification failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('❌ Test notification request failed:', error);
        alert('Test notification request failed: ' + error.message);
    });
}

// Export functions for global use
window.initializeEnhancedNotifications = initializeEnhancedNotifications;
window.markAllNotificationsAsRead = markAllNotificationsAsRead;
window.testNotificationSystem = testNotificationSystem;
window.handleNavbarNotificationClick = handleNavbarNotificationClick; 