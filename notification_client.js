/**
 * Client-side notification service for the Ally Foundation website
 * This handles sending notifications from JavaScript to the PHP notification service
 */

class NotificationClient {
    constructor() {
        this.baseUrl = window.location.origin;
    }

    /**
     * Send notification via AJAX to PHP backend
     */
    async sendNotification(type, data) {
        try {
            const response = await fetch(`${this.baseUrl}/notification_handler.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: type,
                    data: data
                })
            });

            const result = await response.json();
            if (result.success) {
                console.log('Notification sent successfully:', type);
                return true;
            } else {
                console.error('Failed to send notification:', result.error);
                return false;
            }
        } catch (error) {
            console.error('Error sending notification:', error);
            return false;
        }
    }

    /**
     * Get current user ID from Firebase Auth or session fallback
     */
    getCurrentUserId() {
        try {
            // Try Firebase auth first
            if (typeof firebase !== 'undefined' && firebase.auth) {
                const user = firebase.auth().currentUser;
                if (user) {
                    console.log('Using Firebase user ID:', user.uid);
                    return user.uid;
                }
            }
            
            // Fallback: try to get from PHP session data in global variables
            if (typeof sessionUserId !== 'undefined' && sessionUserId) {
                console.log('Using session user ID:', sessionUserId);
                return sessionUserId;
            }
            
            // Another fallback: try to get from meta tags or other sources
            const metaUserId = document.querySelector('meta[name="user-id"]');
            if (metaUserId && metaUserId.content) {
                console.log('Using meta tag user ID:', metaUserId.content);
                return metaUserId.content;
            }
            
            console.warn('No user ID found - Firebase auth, session, or meta tag');
            return null;
        } catch (error) {
            console.error('Error getting user ID:', error);
            return null;
        }
    }

    /**
     * Send donation notification
     */
    async sendDonationNotification(donationType, status, additionalData = {}) {
        const userId = this.getCurrentUserId();
        if (!userId) return false;

        return await this.sendNotification('donation', {
            userId: userId,
            donationType: donationType,
            status: status,
            ...additionalData
        });
    }

    /**
     * Send adoption notification
     */
    async sendAdoptionNotification(status, stepNumber = null, additionalData = {}) {
        const userId = this.getCurrentUserId();
        if (!userId) return false;

        return await this.sendNotification('adoption', {
            userId: userId,
            status: status,
            stepNumber: stepNumber,
            ...additionalData
        });
    }

    /**
     * Send appointment notification
     */
    async sendAppointmentNotification(status, appointmentDate = null, additionalData = {}) {
        const userId = this.getCurrentUserId();
        if (!userId) return false;

        return await this.sendNotification('appointment', {
            userId: userId,
            status: status,
            appointmentDate: appointmentDate,
            ...additionalData
        });
    }

    /**
     * Send matching notification
     */
    async sendMatchingNotification(status, childName = null, additionalData = {}) {
        const userId = this.getCurrentUserId();
        if (!userId) return false;

        return await this.sendNotification('matching', {
            userId: userId,
            status: status,
            childName: childName,
            ...additionalData
        });
    }

    /**
     * Send chat notification
     */
    async sendChatNotification(senderName, message, chatUserId, additionalData = {}) {
        const userId = this.getCurrentUserId();
        if (!userId) return false;

        return await this.sendNotification('chat', {
            userId: userId,
            senderName: senderName,
            message: message,
            chatUserId: chatUserId,
            ...additionalData
        });
    }

    /**
     * Send security notification
     */
    async sendSecurityNotification(securityEvent, details, actionRequired = false) {
        const userId = this.getCurrentUserId();
        if (!userId) return false;

        return await this.sendNotification('security', {
            userId: userId,
            securityEvent: securityEvent,
            details: details,
            actionRequired: actionRequired
        });
    }

    /**
     * Send system notification
     */
    async sendSystemNotification(title, message, additionalData = {}) {
        const userId = this.getCurrentUserId();
        if (!userId) return false;

        return await this.sendNotification('system', {
            userId: userId,
            title: title,
            message: message,
            ...additionalData
        });
    }
}

// Create global instance
window.notificationClient = new NotificationClient();

// Helper functions for backward compatibility
window.sendDonationNotification = function(donationType, status, additionalData = {}) {
    return window.notificationClient.sendDonationNotification(donationType, status, additionalData);
};

window.sendAdoptionNotification = function(status, stepNumber = null, additionalData = {}) {
    return window.notificationClient.sendAdoptionNotification(status, stepNumber, additionalData);
};

window.sendAppointmentNotification = function(status, appointmentDate = null, additionalData = {}) {
    return window.notificationClient.sendAppointmentNotification(status, appointmentDate, additionalData);
};

window.sendMatchingNotification = function(status, childName = null, additionalData = {}) {
    return window.notificationClient.sendMatchingNotification(status, childName, additionalData);
};

window.sendChatNotification = function(senderName, message, chatUserId, additionalData = {}) {
    return window.notificationClient.sendChatNotification(senderName, message, chatUserId, additionalData);
};

window.sendSecurityNotification = function(securityEvent, details, actionRequired = false) {
    return window.notificationClient.sendSecurityNotification(securityEvent, details, actionRequired);
};

window.sendSystemNotification = function(title, message, additionalData = {}) {
    return window.notificationClient.sendSystemNotification(title, message, additionalData);
};

console.log('Notification client loaded successfully'); 