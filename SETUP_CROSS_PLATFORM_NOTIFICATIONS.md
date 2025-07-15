# Cross-Platform Notification Setup Guide

## 🎯 Overview

This guide will help you set up true cross-platform notifications where:
- **Web → Mobile**: Notifications sent from web appear as push notifications on mobile devices
- **Mobile → Web**: Notifications sent from mobile appear in web notification systems
- **Role-based filtering**: Admins only see admin notifications, users only see user notifications

## 📋 Prerequisites

1. Firebase project with Firestore and Cloud Messaging enabled
2. Mobile app already integrated with Firebase
3. Web application with notification system

## 🔧 Step 1: Get Firebase Credentials

### 1.1 Get FCM Server Key
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (`ally-user`)
3. Go to **Project Settings** (gear icon)
4. Click on **Cloud Messaging** tab
5. Copy the **Server key** (starts with `AAAA...`)

### 1.2 Get Firebase API Key
1. In Firebase Console → **Project Settings**
2. Go to **General** tab
3. Scroll down to **Your apps** section
4. Find your web app configuration
5. Copy the **apiKey** (starts with `AIza...`)

## 🌐 Step 2: Configure Web Application

### 2.1 Update Firebase Configuration
Open `Ally/config.json` and update the Firebase section:

```json
{
  "firebase": {
    "projectId": "ally-user",
    "serverKey": "YOUR_FCM_SERVER_KEY_HERE",
    "apiKey": "YOUR_FIREBASE_API_KEY_HERE",
    "authDomain": "ally-user.firebaseapp.com",
    "databaseURL": "https://ally-user-default-rtdb.firebaseio.com",
    "storageBucket": "ally-user.appspot.com"
  }
}
```

Replace:
- `YOUR_FCM_SERVER_KEY_HERE` with the Server key from step 1.1
- `YOUR_FIREBASE_API_KEY_HERE` with the API key from step 1.2

### 2.2 Test Web → Mobile Notifications

1. Open `Ally/test_cross_platform_notifications.html` in your browser
2. Enter your FCM Server Key and API Key
3. Click "Update Configuration"
4. Test sending notifications to mobile devices

## 📱 Step 3: Mobile App Configuration

### 3.1 Ensure FCM Token Storage
The mobile app has been updated to automatically store FCM tokens in Firestore when they're generated. This enables web-to-mobile notifications.

**File**: `MyFirebaseMessagingService.kt`
- Added `storeFCMTokenInFirestore()` method
- Stores user's FCM token in Firestore user document
- Enables web application to send push notifications to mobile

### 3.2 Ensure Notification Storage
The mobile app now stores all notifications in Firestore collections when sending them. This enables mobile-to-web notifications.

**File**: `NotificationOrchestrator.kt`
- Added `storeNotificationInFirestore()` method
- Stores notifications in multiple collections for redundancy
- Enables web application to display mobile-sent notifications

## 🧪 Step 4: Testing

### 4.1 Test Web → Mobile
1. Open the test page: `Ally/test_cross_platform_notifications.html`
2. Configure Firebase credentials
3. Send test notifications
4. Check that they appear as push notifications on mobile devices

### 4.2 Test Mobile → Web
1. Use the mobile app to send notifications (e.g., complete an adoption step)
2. Check that they appear in the web notification system
3. Verify notifications are stored in Firestore collections

### 4.3 Test Role-Based Filtering
1. **Admin Account Test**:
   - Login as admin user (`h8qq0E8avWO74cqS2Goy1wtENJh1`)
   - Should only receive admin notifications
   - Should not receive user-specific notifications

2. **User Account Test**:
   - Login as regular user
   - Should only receive user notifications
   - Should not receive admin-specific notifications

## 🔍 Troubleshooting

### Web → Mobile Not Working

**Issue**: Notifications sent from web don't appear on mobile

**Solutions**:
1. **Check FCM Server Key**: Ensure correct server key in config.json
2. **Check FCM Tokens**: Verify mobile app is storing FCM tokens in Firestore
3. **Check User Document**: In Firestore Console, verify user has `fcmToken` field
4. **Check Network**: Ensure web application can reach FCM servers
5. **Check Logs**: Open browser console for error messages

### Mobile → Web Not Working

**Issue**: Notifications sent from mobile don't appear in web

**Solutions**:
1. **Check Firestore Permissions**: Ensure mobile app can write to notification collections
2. **Check Collections**: Verify notifications appear in Firestore console under:
   - `notification_logs`
   - `notifications` 
   - `users/{userId}/notifications`
3. **Check Web Polling**: Ensure web app is checking Firestore for new notifications
4. **Check Logs**: Check mobile app logs (Logcat) for errors

### Role Filtering Not Working

**Issue**: Admins receiving user notifications or vice versa

**Solutions**:
1. **Check User Roles**: Verify user role is correctly set in Firestore
2. **Check Admin Users**: Verify admin users are listed in `admin_users.json`
3. **Check Notification Metadata**: Ensure notifications include:
   - `isAdminNotification: true/false`
   - `targetRole: "admin"/"user"`
   - `notificationSource: "admin_system"/"user_system"`

## 📊 Monitoring

### Firestore Collections to Monitor

1. **`notification_logs`** - All notifications with metadata
2. **`notifications`** - Backup notification storage
3. **`users/{userId}/notifications`** - User-specific notifications
4. **`users/{userId}`** - Check for `fcmToken` field

### Key Fields to Check

```json
{
  "userId": "user_id",
  "title": "notification_title", 
  "message": "notification_message",
  "timestamp": 1234567890000,
  "isRead": false,
  "source": "web_js" | "mobile_app",
  "data": {
    "isAdminNotification": true/false,
    "targetRole": "admin" | "user",
    "notificationSource": "admin_system" | "user_system"
  }
}
```

## 🚀 Production Deployment

### Security Considerations

1. **Secure API Keys**: Store Firebase credentials in environment variables
2. **CORS Configuration**: Configure Firebase for your domain
3. **Rate Limiting**: Implement rate limiting for notification endpoints
4. **User Authentication**: Ensure only authenticated users can send notifications

### Performance Optimization

1. **Batch Notifications**: Use batch operations for multiple notifications
2. **Caching**: Cache admin user lists and FCM tokens
3. **Retry Logic**: Implement retry mechanism for failed notifications
4. **Cleanup**: Regularly clean up old notifications

## ✅ Success Criteria

Your cross-platform notification system is working correctly when:

- ✅ Web notifications appear on mobile as push notifications
- ✅ Mobile notifications appear in web notification bell
- ✅ Admin users only receive admin notifications
- ✅ Regular users only receive user notifications
- ✅ Notifications are stored in all required Firestore collections
- ✅ FCM tokens are properly stored in user documents
- ✅ Role-based filtering works on both platforms

## 📞 Support

If you encounter issues:

1. Check this troubleshooting guide first
2. Verify all Firebase credentials are correct
3. Test with the provided HTML test page
4. Check browser console and mobile logs for errors
5. Verify Firestore rules allow read/write access

---

**Files Modified for Cross-Platform Support:**

**Web Application:**
- `cross_platform_notifications.js` - Main notification system
- `config.json` - Firebase configuration
- `test_cross_platform_notifications.html` - Testing interface

**Mobile Application:**
- `MyFirebaseMessagingService.kt` - FCM token storage
- `NotificationOrchestrator.kt` - Notification storage in Firestore

The system is now fully functional for true cross-platform notifications! 🎉 