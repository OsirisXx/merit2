<?php
/**
 * Security Logger for PHP Platform
 * Matches the security logging capabilities of the Kotlin application
 */

class SecurityLogger {
    private static $instance = null;
    private $logFile;
    private $maxLogSize = 10485760; // 10MB
    private $maxLogFiles = 5;
    
    // Security event types (matching Kotlin SecurityConfig)
    const EVENT_LOGIN_ATTEMPT = 'LOGIN_ATTEMPT';
    const EVENT_LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    const EVENT_LOGIN_FAILURE = 'LOGIN_FAILURE';
    const EVENT_LOGOUT = 'LOGOUT';
    const EVENT_RATE_LIMIT_TRIGGERED = 'RATE_LIMIT_TRIGGERED';
    const EVENT_LOCKOUT_STARTED = 'LOCKOUT_STARTED';
    const EVENT_LOCKOUT_ENDED = 'LOCKOUT_ENDED';
    const EVENT_CAPTCHA_REQUIRED = 'CAPTCHA_REQUIRED';
    const EVENT_CAPTCHA_SOLVED = 'CAPTCHA_SOLVED';
    const EVENT_CAPTCHA_FAILED = 'CAPTCHA_FAILED';
    const EVENT_SESSION_CREATED = 'SESSION_CREATED';
    const EVENT_SESSION_EXPIRED = 'SESSION_EXPIRED';
    const EVENT_SUSPICIOUS_ACTIVITY = 'SUSPICIOUS_ACTIVITY';
    
    private function __construct() {
        $this->logFile = __DIR__ . '/logs/security.log';
        $this->ensureLogDirectory();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function ensureLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function logEvent($eventType, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $eventType,
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'session_id' => session_id(),
            'data' => $data
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        // Rotate logs if necessary
        $this->rotateLogsIfNeeded();
        
        // Write to log file
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Send real-time alert for critical events
        $this->checkForCriticalEvents($eventType, $data);
    }
    
    private function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 
                   'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    private function rotateLogsIfNeeded() {
        if (!file_exists($this->logFile)) {
            return;
        }
        
        if (filesize($this->logFile) > $this->maxLogSize) {
            // Rotate existing logs
            for ($i = $this->maxLogFiles - 1; $i > 0; $i--) {
                $oldFile = $this->logFile . '.' . $i;
                $newFile = $this->logFile . '.' . ($i + 1);
                
                if (file_exists($oldFile)) {
                    if ($i == $this->maxLogFiles - 1) {
                        unlink($oldFile); // Delete oldest log
                    } else {
                        rename($oldFile, $newFile);
                    }
                }
            }
            
            // Move current log to .1
            rename($this->logFile, $this->logFile . '.1');
        }
    }
    
    private function checkForCriticalEvents($eventType, $data) {
        $criticalEvents = [
            self::EVENT_RATE_LIMIT_TRIGGERED,
            self::EVENT_LOCKOUT_STARTED,
            self::EVENT_SUSPICIOUS_ACTIVITY
        ];
        
        if (in_array($eventType, $criticalEvents)) {
            // In a production environment, you would send alerts here
            // For now, we'll just log it as a critical event
            error_log("CRITICAL SECURITY EVENT: $eventType - " . json_encode($data));
        }
    }
    
    public function getRecentEvents($limit = 100, $eventType = null) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $events = [];
        
        // Get the last $limit lines
        $lines = array_slice($lines, -$limit);
        
        foreach ($lines as $line) {
            $event = json_decode($line, true);
            if ($event && ($eventType === null || $event['event_type'] === $eventType)) {
                $events[] = $event;
            }
        }
        
        return array_reverse($events); // Most recent first
    }
    
    public function getFailedLoginAttempts($timeWindow = 3600) { // 1 hour default
        $cutoffTime = date('Y-m-d H:i:s', time() - $timeWindow);
        $events = $this->getRecentEvents(1000, self::EVENT_LOGIN_FAILURE);
        
        $attempts = [];
        foreach ($events as $event) {
            if ($event['timestamp'] >= $cutoffTime) {
                $ip = $event['ip_address'];
                if (!isset($attempts[$ip])) {
                    $attempts[$ip] = 0;
                }
                $attempts[$ip]++;
            }
        }
        
        return $attempts;
    }
    
    public function isIPSuspicious($ip, $threshold = 10, $timeWindow = 3600) {
        $attempts = $this->getFailedLoginAttempts($timeWindow);
        return isset($attempts[$ip]) && $attempts[$ip] >= $threshold;
    }
}

// Usage example:
// $logger = SecurityLogger::getInstance();
// $logger->logEvent(SecurityLogger::EVENT_LOGIN_ATTEMPT, ['email' => 'user@example.com']);
?> 