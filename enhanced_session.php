<?php
/**
 * Enhanced Session Management for PHP Platform
 * Matches the session security capabilities of the Kotlin application
 */

require_once 'security_logger.php';

class EnhancedSessionManager {
    private static $instance = null;
    private $logger;
    
    // Session configuration (matching Kotlin SecurityConfig)
    const SESSION_DURATION = 24 * 60 * 60; // 24 hours
    const SESSION_REFRESH_THRESHOLD = 2 * 60 * 60; // 2 hours
    const SESSION_IDLE_TIMEOUT = 30 * 60; // 30 minutes
    const MAX_CONCURRENT_SESSIONS = 3;
    
    private function __construct() {
        $this->logger = SecurityLogger::getInstance();
        $this->configureSession();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function configureSession() {
        // Enhanced session security settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', self::SESSION_DURATION);
        
        // Regenerate session ID periodically
        ini_set('session.cookie_lifetime', 0); // Session cookie
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function createSession($userData) {
        $sessionId = session_id();
        $currentTime = time();
        
        // Generate device fingerprint
        $deviceFingerprint = $this->generateDeviceFingerprint();
        
        // Store session data
        $_SESSION['user_id'] = $userData['uid'] ?? null;
        $_SESSION['username'] = $userData['username'] ?? null;
        $_SESSION['email'] = $userData['email'] ?? null;
        $_SESSION['role'] = $userData['role'] ?? 'user';
        $_SESSION['created_at'] = $currentTime;
        $_SESSION['last_activity'] = $currentTime;
        $_SESSION['device_fingerprint'] = $deviceFingerprint;
        $_SESSION['ip_address'] = $this->getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Log session creation
        $this->logger->logEvent(SecurityLogger::EVENT_SESSION_CREATED, [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'device_fingerprint' => $deviceFingerprint,
            'ip_address' => $_SESSION['ip_address']
        ]);
        
        // Clean up old sessions for this user
        $this->cleanupOldSessions($_SESSION['user_id']);
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'expires_at' => $currentTime + self::SESSION_DURATION
        ];
    }
    
    public function validateSession() {
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user_id'])) {
            return ['valid' => false, 'reason' => 'No active session'];
        }
        
        $currentTime = time();
        
        // Check session expiration
        if (isset($_SESSION['created_at']) && 
            ($currentTime - $_SESSION['created_at']) > self::SESSION_DURATION) {
            $this->destroySession('Session expired');
            return ['valid' => false, 'reason' => 'Session expired'];
        }
        
        // Check idle timeout
        if (isset($_SESSION['last_activity']) && 
            ($currentTime - $_SESSION['last_activity']) > self::SESSION_IDLE_TIMEOUT) {
            $this->destroySession('Session idle timeout');
            return ['valid' => false, 'reason' => 'Session idle timeout'];
        }
        
        // Validate device fingerprint (only if it was set during session creation)
        if (isset($_SESSION['device_fingerprint'])) {
            $currentFingerprint = $this->generateDeviceFingerprint();
            if ($_SESSION['device_fingerprint'] !== $currentFingerprint) {
                $this->destroySession('Device fingerprint mismatch');
                $this->logger->logEvent(SecurityLogger::EVENT_SUSPICIOUS_ACTIVITY, [
                    'reason' => 'Device fingerprint mismatch',
                    'stored_fingerprint' => $_SESSION['device_fingerprint'],
                    'current_fingerprint' => $currentFingerprint
                ]);
                return ['valid' => false, 'reason' => 'Security violation'];
            }
        }
        
        // Update last activity
        $_SESSION['last_activity'] = $currentTime;
        
        // Check if session needs refresh
        if (($currentTime - $_SESSION['created_at']) > 
            (self::SESSION_DURATION - self::SESSION_REFRESH_THRESHOLD)) {
            $this->refreshSession();
        }
        
        return [
            'valid' => true,
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'expires_at' => $_SESSION['created_at'] + self::SESSION_DURATION
        ];
    }
    
    public function refreshSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        $oldSessionId = session_id();
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Update session timestamps
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
        
        $this->logger->logEvent(SecurityLogger::EVENT_SESSION_CREATED, [
            'action' => 'session_refresh',
            'old_session_id' => $oldSessionId,
            'new_session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? null
        ]);
        
        return true;
    }
    
    public function destroySession($reason = 'User logout') {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->logger->logEvent(SecurityLogger::EVENT_SESSION_EXPIRED, [
                'reason' => $reason,
                'user_id' => $_SESSION['user_id'] ?? null,
                'session_duration' => isset($_SESSION['created_at']) ? 
                    (time() - $_SESSION['created_at']) : 0
            ]);
            
            // Clear session data
            $_SESSION = [];
            
            // Delete session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Destroy session
            session_destroy();
        }
        
        return true;
    }
    
    private function generateDeviceFingerprint() {
        $components = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
            $_SERVER['HTTP_ACCEPT'] ?? ''
        ];
        
        return hash('sha256', implode('|', $components));
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
    
    private function cleanupOldSessions($userId) {
        // In a production environment, you would implement this to clean up
        // old sessions from a database or session storage
        // For now, we'll just log the cleanup attempt
        $this->logger->logEvent(SecurityLogger::EVENT_SESSION_CREATED, [
            'action' => 'cleanup_old_sessions',
            'user_id' => $userId
        ]);
    }
    
    public function getSessionInfo() {
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user_id'])) {
            return null;
        }
        
        return [
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'created_at' => $_SESSION['created_at'],
            'last_activity' => $_SESSION['last_activity'],
            'expires_at' => $_SESSION['created_at'] + self::SESSION_DURATION,
            'time_remaining' => ($_SESSION['created_at'] + self::SESSION_DURATION) - time()
        ];
    }
}

// Auto-validate session on every request
$sessionManager = EnhancedSessionManager::getInstance();
$sessionValidation = $sessionManager->validateSession();

// Make session validation result available globally
$GLOBALS['session_valid'] = $sessionValidation['valid'] ?? false;
$GLOBALS['session_data'] = $sessionValidation;
?> 