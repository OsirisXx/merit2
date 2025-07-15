/**
 * Firebase Messaging Bridge
 * Handles messaging operations directly from the frontend using Firebase SDK
 * Assumes Firebase is already initialized by the parent page
 */

// Firebase instances will be accessed when needed, not at top level

/**
 * Firebase Messaging Bridge Class
 */
class FirebaseMessagingBridge {
    constructor() {
        // Firebase should already be initialized
        this.realtimeDb = firebase.database();
        this.firestore = firebase.firestore();
        this.auth = firebase.auth();
    }

    /**
     * Generate centralized chat ID for a user
     * Format: user_{userId} - all admins share this conversation
     */
    generateChatId(userId) {
        return `user_${userId}`;
    }

    /**
     * Create or get existing centralized chat for a user
     */
    async createOrGetUserChat(userId, userName = null, connectionType = 'adoption') {
        const chatId = this.generateChatId(userId);
        
        try {
            // Get user's actual name from Firestore if not provided
            if (!userName) {
                try {
                    const userDoc = await this.firestore.collection('users').doc(userId).get();
                    if (userDoc.exists) {
                        const userData = userDoc.data();
                        // Use firstName + lastName, or fallback to username
                        if (userData.firstName && userData.lastName) {
                            userName = `${userData.firstName} ${userData.lastName}`;
                        } else if (userData.username) {
                            userName = userData.username;
                        }
                    }
                } catch (error) {
                    console.error('Error fetching user name:', error);
                }
            }
            
            // Check if chat already exists
            const chatSnapshot = await this.realtimeDb.ref(`chats/${chatId}`).once('value');
            
            if (chatSnapshot.exists()) {
                console.log(`✅ Using existing centralized chat for user: ${userId}`);
                
                // Update user name if we have a better one
                const existingData = chatSnapshot.val();
                if (userName && userName !== 'User' && existingData.user_name !== userName) {
                    await this.realtimeDb.ref(`chats/${chatId}`).update({
                        user_name: userName,
                        last_activity: Date.now()
                    });
                    console.log(`✅ Updated user name to ${userName} for user: ${userId}`);
                }
                
                // Update connection type based on user's service preference
                const newConnectionType = await this.determineConnectionType(userId, connectionType);
                if (existingData.connection_type !== newConnectionType) {
                    await this.realtimeDb.ref(`chats/${chatId}`).update({
                        connection_type: newConnectionType,
                        last_activity: Date.now()
                    });
                    console.log(`✅ Updated chat connection type to ${newConnectionType} for user: ${userId}`);
                }
                
                return chatId;
            }
            
            // Determine connection type based on user's service preference
            const finalConnectionType = await this.determineConnectionType(userId, connectionType);
            
            // Create new centralized chat
            const chatData = {
                chat_type: 'user_admin_centralized',
                user_id: userId,
                user_name: userName || 'User',
                connection_type: finalConnectionType,
                created_at: Date.now(),
                created_by: 'system',
                last_activity: Date.now(),
                last_message: 'Conversation started',
                last_message_timestamp: Date.now(),
                unread_count: 0,
                participant_admins: [], // Will be populated as admins join
                is_centralized: true
            };

            await this.realtimeDb.ref(`chats/${chatId}`).set(chatData);
            console.log(`✅ Created centralized chat for user: ${userId} with connection type: ${finalConnectionType}`);
            
            return chatId;
            
        } catch (error) {
            console.error('❌ Error creating/getting user chat:', error);
            throw error;
        }
    }

    /**
     * Determine connection type based on user's service preference
     */
    async determineConnectionType(userId, requestedType) {
        try {
            const userDoc = await this.firestore.collection('users').doc(userId).get();
            if (userDoc.exists) {
                const userData = userDoc.data();
                const servicePreference = userData.servicePreference;
                
                // Map service preference to connection type
                switch (servicePreference) {
                    case 'adopt_only':
                        return 'adoption';
                    case 'donate_only':
                        return 'donation';
                    case 'both':
                        return 'mixed';
                    default:
                        // Fallback to requested type if no preference found
                        return requestedType;
                }
            }
            
            // Fallback to requested type if user not found
            return requestedType;
        } catch (error) {
            console.error('Error determining connection type:', error);
            return requestedType;
        }
    }

    /**
     * Send adoption started message to centralized chat
     */
    async sendAdoptionStarted(userId, adminId, userName, adminName) {
        try {
            console.log(`📨 Sending adoption started message to centralized chat for user: ${userId}`);
            
            const chatId = await this.createOrGetUserChat(userId, userName);
            
            // Add admin to participants list
            await this.addAdminToChat(chatId, adminId, adminName);
            
            // Create adoption started message
            const messageId = this.realtimeDb.ref(`chats/${chatId}/messages`).push().key;
            const messageData = {
                messageId: messageId,
                senderId: 'system',
                receiverId: userId,
                senderName: 'Social Worker',
                senderRole: 'admin',
                message: `🎉 Welcome to your adoption journey! Your adoption process has officially started. You are now connected with our social worker team who will guide you through each step. We're here to support you every step of the way!`,
                timestamp: Date.now(),
                serverTimestamp: Date.now(),
                read_by_receiver: false,
                deleted_by_sender: false,
                deleted_by_receiver: false,
                isSystemMessage: true,
                messageType: 'adoption_started',
                priority: 'high',
                created_at: Date.now()
            };

            await this.realtimeDb.ref(`chats/${chatId}/messages/${messageId}`).set(messageData);
            
            // Update chat metadata
            await this.realtimeDb.ref(`chats/${chatId}`).update({
                last_message: messageData.message,
                last_message_timestamp: messageData.timestamp,
                last_activity: messageData.timestamp
            });
            
            console.log(`✅ Adoption started message sent to centralized chat: ${chatId}`);
            
        } catch (error) {
            console.error('❌ Error sending adoption started message:', error);
            throw error;
        }
    }

    /**
     * Send adoption step completion notification to centralized chat
     */
    async sendAdoptionNotification(userId, stepNumber, stepName, status) {
        try {
            console.log(`📨 Sending step completion notification to centralized chat for user: ${userId}`);
            
            const chatId = await this.createOrGetUserChat(userId, 'User');
            
            // Create step completion message with progress tracking link
            const messageId = this.realtimeDb.ref(`chats/${chatId}/messages`).push().key;
            const completionMessage = `✅ Step ${stepNumber} completed: ${stepName}`;
            
            const messageData = {
                messageId: messageId,
                senderId: 'system',
                receiverId: userId,
                senderName: 'Social Worker',
                senderRole: 'admin',
                message: completionMessage,
                timestamp: Date.now(),
                serverTimestamp: Date.now(),
                read_by_receiver: false,
                deleted_by_sender: false,
                deleted_by_receiver: false,
                isSystemMessage: true,
                messageType: 'step_completion',
                stepNumber: stepNumber,
                stepName: stepName,
                priority: 'normal',
                created_at: Date.now(),
                hasProgressButton: true,
                progressUrl: 'https://ally-user.hostingerapp.com/ProgTracking.php'
            };

            await this.realtimeDb.ref(`chats/${chatId}/messages/${messageId}`).set(messageData);
            
            // Update chat metadata
            await this.realtimeDb.ref(`chats/${chatId}`).update({
                last_message: `Step ${stepNumber} completed: ${stepName}`,
                last_message_timestamp: messageData.timestamp,
                last_activity: messageData.timestamp
            });
            
            console.log(`✅ Step completion notification sent to centralized chat: ${chatId}`);
            
        } catch (error) {
            console.error('❌ Error sending step completion notification:', error);
            throw error;
        }
    }

    /**
     * Add admin to centralized chat participants
     */
    async addAdminToChat(chatId, adminId, adminName) {
        try {
            // Skip adding system or test admins
            if (adminId === 'system' || adminId === 'test' || adminName === 'Test Admin') {
                console.log('⏭️ Skipping system/test admin addition');
                return;
            }
            
            const chatRef = this.realtimeDb.ref(`chats/${chatId}/participant_admins`);
            const snapshot = await chatRef.once('value');
            const currentAdmins = snapshot.val() || {};
            
            // Add admin if not already in the list
            if (!currentAdmins[adminId]) {
                currentAdmins[adminId] = {
                    id: adminId,
                    name: adminName,
                    joined_at: Date.now(),
                    last_active: Date.now()
                };
                
                await chatRef.set(currentAdmins);
                console.log(`✅ Added admin ${adminName} to centralized chat: ${chatId}`);
            } else {
                // Update last active time
                await chatRef.child(adminId).update({
                    last_active: Date.now()
                });
            }
            
        } catch (error) {
            console.error('❌ Error adding admin to chat:', error);
            throw error;
        }
    }

    /**
     * Send regular message to centralized chat
     */
    async sendMessage(chatId, senderId, senderName, message, senderRole = 'user') {
        try {
            const messageId = this.realtimeDb.ref(`chats/${chatId}/messages`).push().key;
            const messageData = {
                messageId: messageId,
                senderId: senderId,
                senderName: senderName,
                senderRole: senderRole,
                message: message,
                timestamp: Date.now(),
                serverTimestamp: Date.now(),
                read_by_receiver: false,
                deleted_by_sender: false,
                deleted_by_receiver: false,
                isSystemMessage: false,
                messageType: 'text',
                created_at: Date.now()
            };

            await this.realtimeDb.ref(`chats/${chatId}/messages/${messageId}`).set(messageData);
            
            // Update chat metadata
            await this.realtimeDb.ref(`chats/${chatId}`).update({
                last_message: message,
                last_message_timestamp: messageData.timestamp,
                last_activity: messageData.timestamp
            });
            
            console.log(`✅ Message sent to centralized chat: ${chatId}`);
            
        } catch (error) {
            console.error('❌ Error sending message:', error);
            throw error;
        }
    }

    /**
     * Send custom system message to user's chat (for matching, scheduling, and donation notifications)
     */
    async sendCustomMessage(userId, message, messageType = 'system', options = {}) {
        try {
            console.log(`📨 Sending custom message to user chat: ${userId}`);
            
            // Determine connection type based on message type
            let connectionType = 'adoption';
            if (messageType === 'donation') {
                connectionType = 'donation';
            }
            
            const chatId = await this.createOrGetUserChat(userId, 'User', connectionType);
            
            // Create custom message
            const messageId = this.realtimeDb.ref(`chats/${chatId}/messages`).push().key;
            const messageData = {
                messageId: messageId,
                senderId: 'system',
                receiverId: userId,
                senderName: 'Social Worker',
                senderRole: 'admin',
                message: message,
                timestamp: Date.now(),
                serverTimestamp: Date.now(),
                read_by_receiver: false,
                deleted_by_sender: false,
                deleted_by_receiver: false,
                isSystemMessage: true,
                messageType: messageType,
                priority: 'normal',
                created_at: Date.now()
            };

            // Add donation button if specified
            if (options.hasDonationButton) {
                messageData.hasDonationButton = true;
                messageData.donationUrl = options.donationUrl || 'Donation.php';
            }

            await this.realtimeDb.ref(`chats/${chatId}/messages/${messageId}`).set(messageData);
            
            // Update chat metadata
            await this.realtimeDb.ref(`chats/${chatId}`).update({
                last_message: message,
                last_message_timestamp: messageData.timestamp,
                last_activity: messageData.timestamp
            });
            
            console.log(`✅ Custom message sent to user chat: ${chatId}`);
            return chatId;
            
        } catch (error) {
            console.error('❌ Error sending custom message:', error);
            throw error;
        }
    }

    /**
     * Get all chats for a user (regular user sees only their chat, admin sees all chats)
     */
    async getUserChats(userId, userRole = 'user') {
        try {
            const chatsRef = this.realtimeDb.ref('chats');
            const snapshot = await chatsRef.once('value');
            const allChats = snapshot.val() || {};
            
            const userChats = [];
            
            if (userRole === 'admin') {
                // Admins see all centralized chats
                Object.keys(allChats).forEach(chatId => {
                    const chat = allChats[chatId];
                    if (chat.is_centralized) {
                        userChats.push({
                            id: chatId,
                            ...chat
                        });
                    }
                });
            } else {
                // Regular users see only their own chat
                const userChatId = this.generateChatId(userId);
                if (allChats[userChatId]) {
                    userChats.push({
                        id: userChatId,
                        ...allChats[userChatId]
                    });
                }
            }
            
            return userChats;
            
        } catch (error) {
            console.error('❌ Error getting user chats:', error);
            throw error;
        }
    }
}

// Create global instance
window.firebaseMessagingBridge = new FirebaseMessagingBridge();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FirebaseMessagingBridge;
} 