<?php

/**
 * Security and utility functions
 */

session_start();

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken() {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token) && 
           isset($_SESSION['csrf_token_time']) &&
           (time() - $_SESSION['csrf_token_time']) <= CSRF_TOKEN_EXPIRY;
}

/**
 * Rate limiting check
 */
function checkRateLimit($identifier) {
    $key = 'rate_limit_' . md5($identifier);

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }

    // Clean old entries
    $currentTime = time();
    $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($currentTime) {
        return ($currentTime - $timestamp) < 60; // 1 minute window
    });

    // Check if limited exceeded
    if (count($_SESSION[$key]) >= RATE_LIMIT_REQUESTS) {
        return false;
    }

    // Add current request
    $_SESSION[$key][] = $currentTime;
    return true;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate amount input
 */
function validateAmount($amount) {
    $amount = filter_var($amount, FILTER_VALIDATE_INT);
    return $amount !== false && $amount > 0 && $amount <= CAMPAY_DEMO_MAX_AMOUNT;
}

/**
 * Generate unique reference ID
 */
function generateReference($prefix = 'TXN') {
    return $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = []) {
    $lofFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];

    file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
}

?>