<?php

require_once '../config/campay.php';
require_once '../classes/CamPayAPI.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {

    // Security checks
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

     // Rate limiting
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIP)) {
        logSecurityEvent('RATE_LIMIT_EXCEEDED', ['ip' => $clientIP]);
        throw new Exception('Rate limit exceeded. Please try again later.', 429);
    }
    
    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    // Verify CSRF token
    if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
        logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => $clientIP]);
        throw new Exception('Invalid security token', 403);
    }
    
    // Sanitize and validate inputs
    $amount = sanitizeInput($input['amount'] ?? '');
    $phoneNumber = sanitizeInput($input['phone_number'] ?? '');
    $description = sanitizeInput($input['description'] ?? 'Airtime Transfer');
    
    // Validate amount
    if (!validateAmount($amount)) {
        throw new Exception('Invalid amount. Must be between 1 and ' . CAMPAY_DEMO_MAX_AMOUNT . ' XAF');
    }
    
    // Validate phone number format
    if (!preg_match('/^237[0-9]{9}$/', preg_replace('/[^0-9]/', '', $phoneNumber))) {
        throw new Exception('Invalid phone number format. Use: 237xxxxxxxxx');
    }
    
    // Generate external reference
    $externalReference = generateReference('AIRTIME');
    
    // Create API instance and transfer airtime
    $campayAPI = new CamPayAPI();
    $response = $campayAPI->transferAirtime($amount, $phoneNumber, $externalReference);
    
    // Log successful transaction
    logSecurityEvent('AIRTIME_TRANSFER_SUCCESS', [
        'amount' => $amount,
        'phone_number' => substr($phoneNumber, -4), // Log only last 4 digits
        'reference' => $externalReference
    ]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Airtime transfer initiated successfully',
        'reference' => $externalReference,
        'amount' => $amount,
        'currency' => 'XAF'
    ]);
    
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;

    // Log failed attempts
    logSecurityEvent('AIRTIME_TRANSFER_FAILED', [
        'error' => $e->getMessage(),
        'input' => array_diff_key($input ?? [], array_flip(['csrf_token']))
    ]);

    jsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], $statusCode);
}

?>