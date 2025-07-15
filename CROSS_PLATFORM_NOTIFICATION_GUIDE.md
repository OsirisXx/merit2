# Cross-Platform Notification System Guide

## Overview

The Cross-Platform Notification System ensures that notifications sent from the web application also reach mobile devices via Firebase Cloud Messaging (FCM), and vice versa. It includes proper role-based filtering so that admins only receive admin notifications and users only receive user notifications.

## How It Works

### Architecture

```
Web Application (PHP)                    Mobile Application (Android)
      |                                           |
      |                                           |
  [Notification                               [Notification
   Triggered]                                 Triggered]
      |                                           |
      |                                           |
  Cross-Platform                              NotificationOrchestrator
  Notification System                         (Enhanced with Filtering)
      |                                           |
      |                                           |
  +-----------+                               +----------------+
  |           |                               |                |
Web Storage   FCM Push                    FCM Push        Firestore
(Firestore)   Notification                Notification    Storage
  |           |                               |                |
  |           |                               |                |
Web UI        Mobile Device                Mobile Device    Web UI
Display       Push Notification            Push Notification Display
```

### Storage Collections

The notification system uses multiple Firestore collections for redundancy and organization:

1. **`notification_logs`** - Main collection for all notifications
2. **`notifications`** - Backup collection 
3. **`users/{userId}/notifications`** - User-specific subcollection
4. **`notification_analytics`** - Analytics and logging data

### Notification Flow

1. **Web → Mobile**: When a notification is sent from web:
   - Stored in Firestore collections (for web UI)
   - Sent to Firebase Cloud Function
   - Cloud Function sends FCM push notification to mobile devices

2. **Mobile → Web**: When a notification is sent from mobile:
   - Sent via Firebase Cloud Function
   - Cloud Function stores in Firestore collections
   - Available immediately in web UI

## Role-Based Filtering

### Admin Notifications
- **Who receives**: Only users with `role: "admin"` in Firestore
- **Characteristics**: 
  - `isAdminNotification: true`
  - `targetRole: "admin"`
  - `notificationSource: "admin_system"`
- **Examples**: Process submissions, appointment requests, donation submissions

### User Notifications  
- **Who receives**: Only users with `role: "user"` (or no role specified)
- **Characteristics**:
  - `isAdminNotification: false`
  - `targetRole: "user"`
  - `notificationSource: "user_system"`
- **Examples**: Process approvals, appointment confirmations, donation status updates

### General Notifications
- **Who receives**: All users (both admin and regular users)
- **Examples**: System announcements, maintenance notifications

## Implementation

### Web Application (PHP)

#### Basic Usage

```php
// Include the cross-platform system
require_once 'cross_platform_notification_system.php';

// Send user notification (goes to web + mobile)
sendCrossPlatformUserNotification(
    $userId,
    'DONATION',
    'PROCESS_APPROVED', 
    'Donation Approved',
    'Your food donation has been approved!',
    ['donationType' => 'food', 'status' => 'approved']
);

// Send admin notification (goes to all admins on web + mobile)
sendCrossPlatformAdminNotification(
    'APPOINTMENT',
    'PROCESS_INITIATED',
    'New Appointment Request',
    'A user has requested an appointment.',
    ['userName' => 'John Doe', 'appointmentType' => 'consultation']
);
```

#### Integration with Existing Code

The system automatically enhances existing notification methods:

```php
// This now sends to both web and mobile!
$notificationSystem = new SuperSimpleNotifications();
$notificationSystem->sendUserNotification($userId, 'donation', 'Approved', 'Your donation was approved');
$notificationSystem->sendAdminNotification('appointment', 'New Request', 'User requested appointment');
```

### Mobile Application (Android)

#### Enhanced Filtering

The mobile app now filters notifications based on user role:

```kotlin
// In MyFirebaseMessagingService.kt
private fun checkUserRoleAndFilterNotification(data: Map<String, String>, callback: (Boolean) -> Unit) {
    val isAdminNotification = data["isAdminNotification"] == "true"
    val targetRole = data["targetRole"]
    val userRole = getCurrentUserRole() // "admin" or "user"
    
    val shouldShow = when {
        isAdminNotification && userRole == "admin" -> true
        targetRole == "user" && userRole != "admin" -> true
        targetRole == "admin" && userRole == "admin" -> true
        else -> false
    }
    
    callback(shouldShow)
}
```

#### Sending Notifications

```kotlin
// Send to users only
NotificationOrchestrator.sendUserNotification(
    context = this,
    processType = ProcessType.DONATION,
    notificationType = NotificationType.PROCESS_APPROVED,
    userId = userId,
    title = "Donation Approved",
    message = "Your donation has been approved!"
)

// Send to admins only  
NotificationOrchestrator.sendAdminNotification(
    context = this,
    processType = ProcessType.APPOINTMENT,
    notificationType = NotificationType.ADMIN_REVIEW_REQUIRED,
    title = "New Appointment Request",
    message = "A user has requested an appointment."
)
```

## Firebase Cloud Functions

The system uses Firebase Cloud Functions to handle cross-platform notifications:

### Key Functions

1. **`sendEnhancedNotification`** - Main notification sender with filtering
2. **`sendPushNotification`** - Basic FCM notification sender
3. **`sendBatchNotifications`** - For sending to multiple users

### Function Enhancement

The Cloud Functions automatically include filtering metadata:

```javascript
// Enhanced notification payload
const notificationData = {
    userId: userId,
    title: title,
    message: message,
    processType: processType,
    notificationType: notificationType,
    messageType: messageType,
    targetRole: targetRole,
    notificationSource: notificationSource,
    isAdminNotification: isAdminNotification.toString()
}
```

## Testing

### Test Files

1. **`test_cross_platform_notifications.php`** - Web testing interface
2. **`test_notification_filtering.php`** - Role-based filtering tests
3. **`NotificationFilterTestActivity.kt`** - Mobile testing activity

### Testing Process

1. **Role Verification**:
   - Test with admin account - should only receive admin notifications
   - Test with user account - should only receive user notifications

2. **Cross-Platform Verification**:
   - Send notification from web - check mobile device receives push notification
   - Send notification from mobile - check web notification bell updates

3. **Filtering Verification**:
   - Send admin notification - verify users don't receive it
   - Send user notification - verify admins don't receive it

## Configuration

### Firebase Setup

Ensure your Firebase project has:

1. **Firestore Database** with proper rules
2. **Cloud Functions** deployed with notification functions
3. **Cloud Messaging** enabled for push notifications

### Admin Users

Admin users are defined in `admin_users.json`:

```json
{
  "admin_users": {
    "h8qq0E8avWO74cqS2Goy1wtENJh1": {
      "username": "admin_user",
      "role": "admin"
    }
  }
}
```

## Troubleshooting

### Common Issues

1. **Notifications not reaching mobile**:
   - Check Firebase Cloud Functions are deployed
   - Verify FCM tokens are valid
   - Check internet connectivity

2. **Role filtering not working**:
   - Verify user roles in Firestore
   - Check `admin_users.json` file
   - Ensure filtering metadata is included

3. **Web notifications not appearing**:
   - Check Firestore write permissions
   - Verify notification collections exist
   - Check browser console for errors

### Debug Logs

Enable debug logging to troubleshoot:

```php
// PHP (check error logs)
error_log("📱 Cross-platform notification sent to user: $userId");

// Android (check Logcat)
Log.d("NotificationFilter", "Filtering notification - UserRole: $userRole");
```

## Best Practices

1. **Always use helper functions** for consistency:
   - `sendCrossPlatformUserNotification()`
   - `sendCrossPlatformAdminNotification()`

2. **Include relevant metadata** in notification data:
   - Process types, user information, action required flags

3. **Test thoroughly** with both admin and user accounts

4. **Monitor logs** for notification delivery status

5. **Handle failures gracefully** - notifications should degrade gracefully if one platform fails

## Migration Guide

### From Old System

If you're migrating from the old notification system:

1. **Replace direct calls**:
   ```php
   // Old
   $notificationSystem->sendNotification($userId, $type, $title, $message);
   
   // New  
   sendCrossPlatformNotification($userId, $processType, $notificationType, $title, $message);
   ```

2. **Update role handling**:
   - Ensure admin users are properly defined
   - Use `sendUserNotification()` and `sendAdminNotification()` appropriately

3. **Test cross-platform delivery**:
   - Verify both web and mobile receive notifications
   - Confirm role-based filtering works correctly

This cross-platform notification system ensures that your users stay informed regardless of whether they're using the web application or mobile app, while maintaining proper security through role-based filtering. 