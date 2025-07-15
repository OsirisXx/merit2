/**
 * JavaScript-only notification system for Python server
 * Works without PHP by loading notifications.json directly
 */

// Known user ID for testing (replace with actual session logic later)
const CURRENT_USER_ID = 'h8qq0E8avWO74cqS2Goy1wtENJh1';

let notifications = [];
let unreadCount = 0;

// Initialize notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 Initializing JavaScript notifications...');
    loadNotificationsFromFile();
    
    // Refresh every 30 seconds
    setInterval(loadNotificationsFromFile, 30000);
});

// Load notifications directly from JSON file
function loadNotificationsFromFile() {
    console.log('📂 Loading notifications from file...');
    
    fetch('notifications.json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (!Array.isArray(data)) {
                throw new Error('Invalid notifications data format');
            }
            
            // Filter notifications for current user
            notifications = data.filter(notif => notif.userId === CURRENT_USER_ID);
            
            // Sort by timestamp (newest first)
            notifications.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));
            
            // Count unread notifications
            unreadCount = notifications.filter(n => !n.isRead).length;
            
            console.log(`✅ Loaded ${notifications.length} notifications (${unreadCount} unread)`);
            
            // Update the notification bell
            updateNotificationBell();
            updateNotificationDropdown();
            
        })
        .catch(error => {
            console.error('❌ Failed to load notifications:', error);
            notifications = [];
            unreadCount = 0;
            updateNotificationBell();
        });
}

// Update the notification bell badge
function updateNotificationBell() {
    const badge = document.querySelector('.notification-badge');
    if (!badge) return;
    
    if (unreadCount > 0) {
        badge.textContent = unreadCount;
        badge.style.display = 'flex';
        console.log(`🔔 Bell updated: ${unreadCount} unread notifications`);
    } else {
        badge.style.display = 'none';
        console.log('🔔 Bell updated: no unread notifications');
    }
}

// Update the notification dropdown content
function updateNotificationDropdown() {
    const notifList = document.getElementById('notif-list');
    if (!notifList) return;
    
    if (notifications.length === 0) {
        notifList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">No notifications yet</div>';
        return;
    }
    
    let html = '';
    notifications.slice(0, 10).forEach(notification => {
        const time = formatTimestamp(notification.timestamp);
        const unreadClass = notification.isRead ? '' : ' unread';
        const unreadStyle = notification.isRead ? '' : 'background-color: #f8f9ff; border-left: 3px solid #007bff;';
        
        html += `
            <div class="notification-item-mini${unreadClass}" style="padding: 10px 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer; ${unreadStyle}">
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
    
    notifList.innerHTML = html;
    console.log(`📋 Dropdown updated with ${notifications.length} notifications`);
}

// Format timestamp to human readable format
function formatTimestamp(timestamp) {
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
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
        hour: 'numeric',
        minute: '2-digit'
    });
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Mark all notifications as read (for click handler)
async function markAllNotificationsAsRead() {
    console.log('📋 COLLECTION-BASED: Marking all notifications as read...');
    
    try {
        // Get unread notifications
        const unreadNotifications = notifications.filter(n => !n.isRead);
        
        if (unreadNotifications.length === 0) {
            console.log('✅ No unread notifications to mark as read');
            return;
        }
        
        // Update UI immediately for better user experience
    notifications.forEach(notification => {
        notification.isRead = true;
    });
    unreadCount = 0;
    updateNotificationBell();
    updateNotificationDropdown();
    
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
            console.log('✅ COLLECTION-BASED: All notifications marked as read in backend');
        } else {
            console.error('❌ COLLECTION-BASED: Failed to mark notifications as read in backend:', result.error);
            // Revert UI changes if backend failed
            loadNotificationsFromFile();
        }
        
    } catch (error) {
        console.error('❌ COLLECTION-BASED: Error marking notifications as read:', error);
        // Revert UI changes if request failed
        loadNotificationsFromFile();
    }
}

// Export functions for global access
window.loadNotificationsFromFile = loadNotificationsFromFile;
window.markAllNotificationsAsRead = markAllNotificationsAsRead; 