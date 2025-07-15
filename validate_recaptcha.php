<?php
/**
 * Server-side reCAPTCHA validation for PHP Platform
 * Matches the reCAPTCHA validation in the Kotlin application
 */

require_once 'security_logger.php';

class RecaptchaValidator {
    private static $instance = null;
    private $logger;
    
    // reCAPTCHA configuration (matching Kotlin SecurityConfig)
    const RECAPTCHA_SECRET_KEY = '6LfH-24rAAAAABb3B5K-rdloLCKFgwZgBWckyvhV';
    const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const CAPTCHA_TIMEOUT = 300; // 5 minutes
    
    private function __construct() {
        $this->logger = SecurityLogger::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function validateToken($token, $remoteIp = null) {
        if (empty($token)) {
            $this->logger->logEvent(SecurityLogger::EVENT_CAPTCHA_FAILED, [
                'reason' => 'Empty token provided',
                'ip' => $remoteIp ?? $this->getClientIP()
            ]);
            return [
                'success' => false,
                'error' => 'No reCAPTCHA token provided'
            ];
        }
        
        // Prepare POST data
        $postData = [
            'secret' => self::RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $remoteIp ?? $this->getClientIP()
        ];
        
        // Make request to Google reCAPTCHA API
        $response = $this->makeHttpRequest(self::RECAPTCHA_VERIFY_URL, $postData);
        
        if ($response === false) {
            $this->logger->logEvent(SecurityLogger::EVENT_CAPTCHA_FAILED, [
                'reason' => 'Failed to connect to reCAPTCHA API',
                'ip' => $postData['remoteip']
            ]);
            return [
                'success' => false,
                'error' => 'Failed to verify reCAPTCHA'
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($result === null) {
            $this->logger->logEvent(SecurityLogger::EVENT_CAPTCHA_FAILED, [
                'reason' => 'Invalid JSON response from reCAPTCHA API',
                'ip' => $postData['remoteip']
            ]);
            return [
                'success' => false,
                'error' => 'Invalid reCAPTCHA response'
            ];
        }
        
        // Check if verification was successful
        if ($result['success'] === true) {
            // Additional security checks
            $validationResult = $this->performAdditionalValidation($result, $postData['remoteip']);
            
            if ($validationResult['valid']) {
                $this->logger->logEvent(SecurityLogger::EVENT_CAPTCHA_SOLVED, [
                    'ip' => $postData['remoteip'],
                    'challenge_ts' => $result['challenge_ts'] ?? null,
                    'hostname' => $result['hostname'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'challenge_ts' => $result['challenge_ts'] ?? null,
                    'hostname' => $result['hostname'] ?? null
                ];
            } else {
                $this->logger->logEvent(SecurityLogger::EVENT_CAPTCHA_FAILED, [
                    'reason' => $validationResult['reason'],
                    'ip' => $postData['remoteip']
                ]);
                
                return [
                    'success' => false,
                    'error' => $validationResult['reason']
                ];
            }
        } else {
            // Log the specific error codes
            $errorCodes = $result['error-codes'] ?? ['unknown-error'];
            $this->logger->logEvent(SecurityLogger::EVENT_CAPTCHA_FAILED, [
                'reason' => 'reCAPTCHA verification failed',
                'error_codes' => $errorCodes,
                'ip' => $postData['remoteip']
            ]);
            
            return [
                'success' => false,
                'error' => 'reCAPTCHA verification failed',
                'error_codes' => $errorCodes
            ];
        }
    }
    
    private function performAdditionalValidation($result, $clientIP) {
        // Check challenge timestamp (prevent replay attacks)
        if (isset($result['challenge_ts'])) {
            $challengeTime = strtotime($result['challenge_ts']);
            $currentTime = time();
            
            if (($currentTime - $challengeTime) > self::CAPTCHA_TIMEOUT) {
                return [
                    'valid' => false,
                    'reason' => 'reCAPTCHA challenge expired'
                ];
            }
        }
        
        // Validate hostname (optional - depends on your domain setup)
        if (isset($result['hostname'])) {
            $allowedHostnames = [
                $_SERVER['HTTP_HOST'] ?? '',
                'meritxell-ally.org',
                'www.meritxell-ally.org'
            ];
            
            if (!in_array($result['hostname'], $allowedHostnames)) {
                return [
                    'valid' => false,
                    'reason' => 'Invalid hostname in reCAPTCHA response'
                ];
            }
        }
        
        return ['valid' => true];
    }
    
    private function makeHttpRequest($url, $postData) {
        // Use cURL if available
        if (function_exists('curl_init')) {
            return $this->makeCurlRequest($url, $postData);
        }
        
        // Fallback to file_get_contents
        return $this->makeFileGetContentsRequest($url, $postData);
    }
    
    private function makeCurlRequest($url, $postData) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'PHP reCAPTCHA Validator',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false || !empty($error) || $httpCode !== 200) {
            return false;
        }
        
        return $response;
    }
    
    private function makeFileGetContentsRequest($url, $postData) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($postData),
                'timeout' => 10
            ]
        ]);
        
        return file_get_contents($url, false, $context);
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
}

// API endpoint for AJAX validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_token'])) {
    header('Content-Type: application/json');
    
    $validator = RecaptchaValidator::getInstance();
    $result = $validator->validateToken($_POST['recaptcha_token']);
    
    echo json_encode($result);
    exit;
}
?> 