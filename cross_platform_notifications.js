/**
 * Cross-Platform Notification System (JavaScript)
 * Sends notifications from web to both Firestore (web) and FCM (mobile)
 */

class CrossPlatformNotifications {
    constructor() {
        this.projectId = 'ally-user';
        this.firestoreUrl = `https://firestore.googleapis.com/v1/projects/${this.projectId}/databases/(default)/documents`;
        this.fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        
        // You'll need to get these from Firebase Console
        this.serverKey = 'YOUR_FCM_SERVER_KEY'; // Replace with actual FCM server key
        this.apiKey = 'YOUR_FIREBASE_API_KEY'; // Replace with actual Firebase API key
    }

    /**
     * Send notification to both web and mobile
     */
    async sendCrossPlatformNotification(userId, title, message, data = {}) {
        console.log('📱 Sending cross-platform notification to:', userId);
        
        const results = {
            web: false,
            mobile: false
        };

        try {
            // 1. Store in Firestore for web display
            results.web = await this.storeWebNotification(userId, title, message, data);
            
            // 2. Send FCM notification for mobile
            results.mobile = await this.sendMobileNotification(userId, title, message, data);
            
            const success = results.web || results.mobile;
            console.log(`Cross-platform notification sent - Web: ${results.web ? '✅' : '❌'}, Mobile: ${results.mobile ? '✅' : '❌'}`);
            
            return success;
        } catch (error) {
            console.error('❌ Cross-platform notification error:', error);
            return false;
        }
    }

    /**
     * Send user notification (excludes admins)
     */
    async sendUserNotification(userId, title, message, data = {}) {
        const userData = {
            ...data,
            isAdminNotification: false,
            targetRole: 'user',
            notificationSource: 'user_system'
        };
        
        return await this.sendCrossPlatformNotification(userId, title, message, userData);
    }

    /**
     * Send admin notification to all admins
     */
    async sendAdminNotification(title, message, data = {}) {
        try {
            const adminIds = await this.getAdminUserIds();
            if (adminIds.length === 0) {
                console.warn('⚠️ No admin users found for notification:', title);
                return false;
            }

            const adminData = {
                ...data,
                isAdminNotification: true,
                targetRole: 'admin',
                notificationSource: 'admin_system'
            };

            let success = true;
            const promises = adminIds.map(adminId => 
                this.sendCrossPlatformNotification(adminId, title, message, adminData)
            );

            const results = await Promise.all(promises);
            success = results.some(result => result);

            console.log(`📧 Admin notification sent to ${adminIds.length} admins:`, title);
            return success;

        } catch (error) {
            console.error('❌ Admin notification error:', error);
            return false;
        }
    }

    /**
     * Store notification in Firestore for web display
     */
    async storeWebNotification(userId, title, message, data) {
        try {
            const notification = {
                fields: {
                    userId: { stringValue: userId },
                    title: { stringValue: title },
                    message: { stringValue: message },
                    timestamp: { integerValue: String(Date.now()) },
                    isRead: { booleanValue: false },
                    id: { stringValue: `notif_${Date.now()}_${Math.random().toString(36).substr(2, 9)}` },
                    source: { stringValue: 'web_js' },
                    data: { mapValue: { fields: this.convertToFirestoreFields(data) } }
                }
            };

            // Store in multiple collections for redundancy
            const collections = [
                'notification_logs',
                'notifications',
                `users/${userId}/notifications`
            ];

            let success = false;
            for (const collection of collections) {
                try {
                    const response = await fetch(`${this.firestoreUrl}/${collection}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(notification)
                    });

                    if (response.ok) {
                        success = true;
                        console.log('✅ Stored in Firestore collection:', collection);
                    }
                } catch (collectionError) {
                    console.warn('⚠️ Failed to store in collection:', collection, collectionError);
                }
            }

            // Fallback to existing notification system
            if (!success && window.SuperSimpleNotifications) {
                try {
                    success = await window.SuperSimpleNotifications.sendNotification(userId, 'system', title, message, data);
                } catch (fallbackError) {
                    console.warn('Fallback notification failed:', fallbackError);
                }
            }

            return success;

        } catch (error) {
            console.error('❌ Web notification storage error:', error);
            return false;
        }
    }

    /**
     * Send FCM push notification to mobile devices
     */
    async sendMobileNotification(userId, title, message, data) {
        try {
            // Get user's FCM tokens from Firestore
            const tokens = await this.getUserFCMTokens(userId);
            if (tokens.length === 0) {
                console.warn('⚠️ No FCM tokens found for user:', userId);
                return false;
            }

            const payload = {
                registration_ids: tokens,
                notification: {
                    title: title,
                    body: message,
                    sound: 'default',
                    click_action: 'FLUTTER_NOTIFICATION_CLICK'
                },
                data: {
                    ...data,
                    userId: userId,
                    title: title,
                    message: message,
                    timestamp: String(Date.now()),
                    click_action: 'FLUTTER_NOTIFICATION_CLICK'
                }
            };

            return await this.sendFCMRequest(payload);

        } catch (error) {
            console.error('❌ Mobile notification error:', error);
            return false;
        }
    }

    /**
     * Get user's FCM tokens from Firestore
     */
    async getUserFCMTokens(userId) {
        try {
            const response = await fetch(`${this.firestoreUrl}/users/${userId}`);
            if (!response.ok) {
                return [];
            }

            const userData = await response.json();
            const tokens = [];

            // Check various token field names
            const tokenFields = ['fcmToken', 'deviceToken', 'registrationToken', 'token'];

            for (const field of tokenFields) {
                if (userData.fields?.[field]?.stringValue) {
                    tokens.push(userData.fields[field].stringValue);
                }
                if (userData.fields?.[field]?.arrayValue?.values) {
                    for (const tokenValue of userData.fields[field].arrayValue.values) {
                        if (tokenValue.stringValue) {
                            tokens.push(tokenValue.stringValue);
                        }
                    }
                }
            }

            return [...new Set(tokens)]; // Remove duplicates

        } catch (error) {
            console.error('Error getting FCM tokens:', error);
            return [];
        }
    }

    /**
     * Send FCM request
     */
    async sendFCMRequest(payload) {
        if (!this.serverKey || this.serverKey === 'YOUR_FCM_SERVER_KEY') {
            console.error('❌ FCM Server Key not configured');
            return false;
        }

        try {
            const response = await fetch(this.fcmUrl, {
                method: 'POST',
                headers: {
                    'Authorization': `key=${this.serverKey}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success > 0) {
                    console.log('✅ FCM notification sent successfully');
                    return true;
                } else {
                    console.error('❌ FCM notification failed:', result);
                    return false;
                }
            } else {
                console.error('❌ FCM request failed:', response.status, response.statusText);
                return false;
            }

        } catch (error) {
            console.error('❌ FCM request error:', error);
            return false;
        }
    }

    /**
     * Convert data to Firestore field format
     */
    convertToFirestoreFields(data) {
        const fields = {};

        for (const [key, value] of Object.entries(data)) {
            if (typeof value === 'string') {
                fields[key] = { stringValue: value };
            } else if (typeof value === 'number') {
                if (Number.isInteger(value)) {
                    fields[key] = { integerValue: String(value) };
                } else {
                    fields[key] = { doubleValue: value };
                }
            } else if (typeof value === 'boolean') {
                fields[key] = { booleanValue: value };
            } else if (Array.isArray(value)) {
                fields[key] = { arrayValue: { values: value.map(v => ({ stringValue: String(v) })) } };
            } else if (typeof value === 'object' && value !== null) {
                fields[key] = { mapValue: { fields: this.convertToFirestoreFields(value) } };
            } else {
                fields[key] = { stringValue: String(value) };
            }
        }

        return fields;
    }

    /**
     * Get admin user IDs
     */
    async getAdminUserIds() {
        try {
            // Try to fetch from a known admin users endpoint or file
            const response = await fetch('./admin_users.json');
            if (response.ok) {
                const adminData = await response.json();
                if (adminData.admin_users) {
                    return Object.keys(adminData.admin_users);
                }
            }

            // Fallback to hardcoded admin (from previous logs)
            return ['h8qq0E8avWO74cqS2Goy1wtENJh1'];

        } catch (error) {
            console.error('Error getting admin users:', error);
            return ['h8qq0E8avWO74cqS2Goy1wtENJh1']; // Fallback admin
        }
    }
}

// Global instance
window.CrossPlatformNotifications = new CrossPlatformNotifications();

/**
 * Helper functions for easy integration
 */
window.sendCrossPlatformUserNotification = async function(userId, title, message, data = {}) {
    return await window.CrossPlatformNotifications.sendUserNotification(userId, title, message, data);
};

window.sendCrossPlatformAdminNotification = async function(title, message, data = {}) {
    return await window.CrossPlatformNotifications.sendAdminNotification(title, message, data);
};

window.sendCrossPlatformNotification = async function(userId, title, message, data = {}) {
    return await window.CrossPlatformNotifications.sendCrossPlatformNotification(userId, title, message, data);
};

console.log('✅ Cross-Platform Notification System loaded'); 