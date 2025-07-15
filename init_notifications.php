<?php
/**
 * Notification Collection Initialization Script
 * This script creates the proper notification collection structure in Firestore
 */

require_once 'simple_notification_system.php';

function initializeNotificationCollection() {
    $notification = new SimpleNotificationSystem();
    
    // Sample notification structure to establish the collection
    $sampleNotification = [
        'userId' => 'sample_user',
        'processType' => 'SYSTEM',
        'notificationType' => 'SYSTEM_INITIALIZE',
        'title' => 'Notification System Initialized',
        'message' => 'The notification collection has been properly initialized.',
        'data' => [
            'isSystemMessage' => true,
            'initializedAt' => date('Y-m-d H:i:s')
        ],
        'timestamp' => time() * 1000,
        'status' => 'sent',
        'isRead' => false,
        'icon' => '🔔',
        'id' => 'system_init_' . uniqid()
    ];
    
    echo "Initializing notification collection...\n";
    
    $result = $notification->sendNotification(
        'system',
        'SYSTEM',
        'SYSTEM_INITIALIZE',
        'Notification System Initialized',
        'The notification collection has been properly initialized with the correct structure.',
        ['initializedAt' => date('Y-m-d H:i:s')]
    );
    
    if ($result) {
        echo "✅ Notification collection initialized successfully!\n";
        echo "Collection: notification_logs\n";
        echo "Structure: userId, processType, notificationType, title, message, data, timestamp, status, isRead, icon, id\n";
    } else {
        echo "❌ Failed to initialize notification collection.\n";
    }
    
    return $result;
}

// Run initialization if this script is called directly
if (php_sapi_name() === 'cli' || !empty($_GET['init'])) {
    initializeNotificationCollection();
}

/**
 * API endpoint for web initialization
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $result = initializeNotificationCollection();
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 
            'Notification collection initialized successfully' : 
            'Failed to initialize notification collection',
        'collection' => 'notification_logs',
        'structure' => [
            'userId' => 'string - User ID who receives the notification',
            'processType' => 'string - ADOPTION, DONATION, APPOINTMENT, MATCHING, SYSTEM',
            'notificationType' => 'string - PROCESS_INITIATED, PROCESS_APPROVED, etc.',
            'title' => 'string - Notification title',
            'message' => 'string - Notification message',
            'data' => 'object - Additional data',
            'timestamp' => 'number - Unix timestamp in milliseconds',
            'status' => 'string - sent, delivered, read',
            'isRead' => 'boolean - Read status',
            'icon' => 'string - Emoji icon',
            'id' => 'string - Unique notification ID'
        ]
    ]);
}
?> 