/**
 * Firebase Collection-Based Notification System
 * Simple, cross-platform notifications using Firestore collections
 * Works in browser without PHP server
 */

class FirebaseNotifications {
    constructor() {
        this.db = null;
        this.currentUserId = null;
        this.isAdmin = false;
        this.init();
    }

    async init() {
        try {
            // Get Firebase config
            const configResponse = await fetch('config.json');
            const config = await configResponse.json();
            
            // Initialize Firebase
            if (!firebase.apps.length) {
                firebase.initializeApp(config.firebase);
            }
            this.db = firebase.firestore();
            
            // Get current user from session or localStorage
            this.currentUserId = localStorage.getItem('userId') || 'h8qq0E8avWO74cqS2Goy1wtENJh1';
            this.isAdmin = localStorage.getItem('userRole') === 'admin';
            
            console.log('✅ Firebase Notifications initialized');
            
        } catch (error) {
            console.error('❌ Firebase init error:', error);
        }
    }

    /**
     * Send notification to user(s)
     */
    async sendNotification(userId, type, title, message, data = {}) {
        try {
            const notification = {
                id: 'notif_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                userId: userId,
                type: type,
                title: title,
                message: message,
                data: data,
                timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                isRead: false,
                icon: this.getIcon(type),
                createdAt: new Date().toISOString()
            };

            // Store in multiple collections for redundancy
            const batch = this.db.batch();
            
            // Main notifications collection
            batch.set(this.db.collection('notifications').doc(notification.id), notification);
            
            // User-specific subcollection
            batch.set(this.db.collection('users').doc(userId).collection('notifications').doc(notification.id), notification);
            
            // Global notification log
            batch.set(this.db.collection('notification_logs').doc(notification.id), notification);

            await batch.commit();
            
            console.log('✅ Notification sent:', title);
            return true;
            
        } catch (error) {
            console.error('❌ Send notification error:', error);
            return false;
        }
    }

    /**
     * Send notification to all admins
     */
    async sendAdminNotification(type, title, message, data = {}) {
        try {
            // Get admin user IDs
            const adminIds = await this.getAdminUserIds();
            
            if (adminIds.length === 0) {
                console.warn('⚠️ No admin users found');
                return false;
            }

            const promises = adminIds.map(adminId => {
                const adminData = {
                    ...data,
                    isAdminNotification: true,
                    targetRole: 'admin',
                    notificationSource: 'admin_system'
                };
                return this.sendNotification(adminId, type, title, message, adminData);
            });

            const results = await Promise.all(promises);
            const success = results.every(r => r);
            
            console.log(`📧 Admin notification sent to ${adminIds.length} admins:`, title);
            return success;
            
        } catch (error) {
            console.error('❌ Admin notification error:', error);
            return false;
        }
    }

    /**
     * Get notifications for current user
     */
    async getNotifications(limit = 20) {
        try {
            if (!this.currentUserId) {
                console.warn('⚠️ No current user ID');
                return [];
            }

            // Get from user-specific collection first (fastest)
            let query = this.db.collection('users')
                .doc(this.currentUserId)
                .collection('notifications')
                .orderBy('timestamp', 'desc')
                .limit(limit);

            const snapshot = await query.get();
            const notifications = [];

            snapshot.forEach(doc => {
                const notif = doc.data();
                
                // Filter based on user role
                if (this.isAdmin) {
                    // Admins only see admin notifications
                    if (notif.data && notif.data.isAdminNotification === true) {
                        notifications.push(notif);
                    }
                } else {
                    // Regular users see non-admin notifications
                    if (!notif.data || notif.data.isAdminNotification !== true) {
                        notifications.push(notif);
                    }
                }
            });

            return notifications;
            
        } catch (error) {
            console.error('❌ Get notifications error:', error);
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const batch = this.db.batch();
            
            // Update in all collections
            batch.update(this.db.collection('notifications').doc(notificationId), {
                isRead: true,
                readAt: firebase.firestore.FieldValue.serverTimestamp()
            });
            
            batch.update(
                this.db.collection('users').doc(this.currentUserId).collection('notifications').doc(notificationId), 
                {
                    isRead: true,
                    readAt: firebase.firestore.FieldValue.serverTimestamp()
                }
            );

            await batch.commit();
            return true;
            
        } catch (error) {
            console.error('❌ Mark as read error:', error);
            return false;
        }
    }

    /**
     * Get admin user IDs from Firestore
     */
    async getAdminUserIds() {
        try {
            // Try to get from admin_users collection
            const adminSnapshot = await this.db.collection('admin_users').get();
            if (!adminSnapshot.empty) {
                return adminSnapshot.docs.map(doc => doc.id);
            }

            // Fallback: get from users collection where role = admin
            const usersSnapshot = await this.db.collection('users').where('role', '==', 'admin').get();
            return usersSnapshot.docs.map(doc => doc.id);
            
        } catch (error) {
            console.error('❌ Get admin IDs error:', error);
            return ['h8qq0E8avWO74cqS2Goy1wtENJh1']; // Fallback to your admin ID
        }
    }

    /**
     * Get notification icon
     */
    getIcon(type) {
        const icons = {
            donation: '💝',
            appointment: '📅',
            adoption: '👶',
            matching: '🤝',
            chat: '💬',
            system: '🔔',
            test: '🧪'
        };
        return icons[type] || '📋';
    }

    /**
     * Listen for real-time notifications
     */
    listenForNotifications(callback) {
        if (!this.currentUserId) return;

        return this.db.collection('users')
            .doc(this.currentUserId)
            .collection('notifications')
            .orderBy('timestamp', 'desc')
            .limit(10)
            .onSnapshot(snapshot => {
                const notifications = [];
                snapshot.forEach(doc => {
                    const notif = doc.data();
                    
                    // Apply role filtering
                    if (this.isAdmin && notif.data && notif.data.isAdminNotification === true) {
                        notifications.push(notif);
                    } else if (!this.isAdmin && (!notif.data || notif.data.isAdminNotification !== true)) {
                        notifications.push(notif);
                    }
                });
                
                callback(notifications);
            });
    }
}

// Convenience functions for common notifications
const firebaseNotifications = new FirebaseNotifications();

// Test functions
async function sendTestNotification() {
    return await firebaseNotifications.sendNotification(
        firebaseNotifications.currentUserId,
        'test',
        'Test Notification',
        'This is a test notification from Firebase'
    );
}

async function sendTestAdminNotification() {
    return await firebaseNotifications.sendAdminNotification(
        'test',
        'Admin Test',
        'This is a test admin notification'
    );
}

// Export for use in other scripts
window.FirebaseNotifications = FirebaseNotifications;
window.firebaseNotifications = firebaseNotifications;
window.sendTestNotification = sendTestNotification;
window.sendTestAdminNotification = sendTestAdminNotification; 