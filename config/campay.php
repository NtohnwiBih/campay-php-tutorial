<?php

/**
 * Campay API Configuration
 * Use environment variables
 */

// Environment settings
define('CAMPAY_BASE_URL', 'https://demo.compay.net/api/');
define('CAMPAY_TOKEN', 'your-permament-token-here');
define('CAMPAY_DEMO_MAX_AMOUNT', 100); // for a demo account XAF 100 max transactional amount

// Error codes mapping for better user experience
define('CAMPAY_ERROR_CODES', [
    'ER101' => 'Invalid phone number format. Use country code format (237xxxxxxxxx)',
    'ER102' => 'Unsuppported carrier. Only MTN and Orange are accepted',
    'ER201' => 'Invalid amount. Decimal numbers not allowed',
    'ER301' => 'Insufficient balance for this transaction'
]);

// Supported carriers for validation
define('CAMPAY_SUPPORTED_CARRIERS', ['MTN', 'Orange']);

// Log file path for debugging
define('CAMPAY_LOG_FILE', __DIR__ . '/../logs/compay.log');

/**
 * Security settings
 */
// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Rate limiting (requests perminute)
define('RATE_LIMIT_REQUESTS', 10);

// CSRF token expiry (1 hour)
define('CSRF_TOKEN_EXPIRY', 3600);

?>