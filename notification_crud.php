<?php

class NotificationCRUD {
    private $firebaseProjectId;
    private $baseUrl;
    
    public function __construct() {
        // Load Firebase config
        $config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
        $this->firebaseProjectId = $config['firebase']['projectId'] ?? 'ally-user';
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->firebaseProjectId}/databases/(default)/documents";
    }
    
    /**
     * Create a new notification in notification_logs
     */
    public function createNotification($data) {
        $url = $this->baseUrl . "/notification_logs";
        
        $firestoreData = [
            'fields' => $this->convertToFirestoreFields($data)
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($firestoreData),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            error_log("Failed to create notification in Firebase");
            return false;
        }
        
        $response = json_decode($result, true);
        return $response ? true : false;
    }
    
    /**
     * Read notifications for a specific user
     */
    public function getNotificationsForUser($userId, $limit = 50) {
        // Firebase REST API doesn't support complex queries easily, so we'll get all and filter
        $url = $this->baseUrl . "/notification_logs";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 30
            ]
        ]);
        
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            error_log("Failed to read notifications from Firebase");
            return [];
        }
        
        $response = json_decode($result, true);
        $notifications = [];
        
        if (isset($response['documents'])) {
            foreach ($response['documents'] as $doc) {
                $docData = $this->convertFromFirestoreFields($doc['fields'] ?? []);
                
                // Filter by userId
                if (isset($docData['userId']) && $docData['userId'] == $userId) {
                    $docData['id'] = basename($doc['name']);
                    $notifications[] = $docData;
                }
            }
        }
        
        // Sort by timestamp descending
        usort($notifications, function($a, $b) {
            $aTime = $a['timestamp'] ?? 0;
            $bTime = $b['timestamp'] ?? 0;
            return $bTime - $aTime;
        });
        
        // Limit results
        return array_slice($notifications, 0, $limit);
    }
    
    /**
     * Update a notification (mark as read, etc.)
     */
    public function updateNotification($notificationId, $updates) {
        $url = $this->baseUrl . "/notification_logs/" . $notificationId;
        
        // First get the existing document
        $existingDoc = @file_get_contents($url);
        if ($existingDoc === FALSE) {
            return false;
        }
        
        $existing = json_decode($existingDoc, true);
        if (!$existing || !isset($existing['fields'])) {
            return false;
        }
        
        // Merge updates with existing data
        $existingData = $this->convertFromFirestoreFields($existing['fields']);
        $updatedData = array_merge($existingData, $updates);
        
        $firestoreData = [
            'fields' => $this->convertToFirestoreFields($updatedData)
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'PATCH',
                'content' => json_encode($firestoreData),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        return $result !== FALSE;
    }
    
    /**
     * Delete a notification
     */
    public function deleteNotification($notificationId) {
        $url = $this->baseUrl . "/notification_logs/" . $notificationId;
        
        $options = [
            'http' => [
                'method' => 'DELETE',
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        return $result !== FALSE;
    }
    
    /**
     * Convert PHP array to Firestore fields format
     */
    private function convertToFirestoreFields($data) {
        $fields = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $fields[$key] = ['stringValue' => $value];
            } elseif (is_int($value)) {
                $fields[$key] = ['integerValue' => (string)$value];
            } elseif (is_float($value)) {
                $fields[$key] = ['doubleValue' => $value];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_array($value)) {
                $fields[$key] = ['mapValue' => ['fields' => $this->convertToFirestoreFields($value)]];
            } elseif (is_null($value)) {
                $fields[$key] = ['nullValue' => null];
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        
        return $fields;
    }
    
    /**
     * Convert Firestore fields format to PHP array
     */
    private function convertFromFirestoreFields($fields) {
        $data = [];
        
        foreach ($fields as $key => $value) {
            if (isset($value['stringValue'])) {
                $data[$key] = $value['stringValue'];
            } elseif (isset($value['integerValue'])) {
                $data[$key] = (int)$value['integerValue'];
            } elseif (isset($value['doubleValue'])) {
                $data[$key] = (float)$value['doubleValue'];
            } elseif (isset($value['booleanValue'])) {
                $data[$key] = $value['booleanValue'];
            } elseif (isset($value['mapValue']['fields'])) {
                $data[$key] = $this->convertFromFirestoreFields($value['mapValue']['fields']);
            } elseif (isset($value['nullValue'])) {
                $data[$key] = null;
            } else {
                $data[$key] = null;
            }
        }
        
        return $data;
    }
    
    /**
     * Get notification count for user
     */
    public function getNotificationCount($userId, $unreadOnly = false) {
        $notifications = $this->getNotificationsForUser($userId);
        
        if (!$unreadOnly) {
            return count($notifications);
        }
        
        $unreadCount = 0;
        foreach ($notifications as $notification) {
            if (!isset($notification['isRead']) || !$notification['isRead']) {
                $unreadCount++;
            }
        }
        
        return $unreadCount;
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        $notifications = $this->getNotificationsForUser($userId);
        $success = true;
        
        foreach ($notifications as $notification) {
            if (!isset($notification['isRead']) || !$notification['isRead']) {
                if (!$this->updateNotification($notification['id'], ['isRead' => true])) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
}

?> 