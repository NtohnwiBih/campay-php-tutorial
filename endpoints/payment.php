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
    $description = sanitizeInput($input['description'] ?? 'Payment');
    $phoneNumber = sanitizeInput($input['phone_number'] ?? '');
    
    // Validate required fields
    if (empty($amount) || empty($phoneNumber)) {
        throw new Exception('Amount and phone number are required');
    }
    
    // Validate amount
    if (!validateAmount($amount)) {
        throw new Exception('Invalid amount. Must be between 1 and ' . CAMPAY_DEMO_MAX_AMOUNT . ' XAF');
    }
    
    // Validate phone number
    if (!preg_match('/^237[0-9]{9}$/', preg_replace('/[^0-9]/', '', $phoneNumber))) {
        throw new Exception('Invalid phone number format. Use: 237xxxxxxxxx');
    }
    
    // For demo purposes, we'll simulate the payment process
    // In a real implementation, you would call the CamPay payment endpoint
    
    $transactionId = generateReference('PAY');
    
    // Simulate payment processing delay
    usleep(rand(500000, 2000000)); // 0.5 to 2 seconds
    
    // Simulate success/failure (90% success rate for demo)
    $isSuccessful = rand(1, 10) <= 9;
    
    if (!$isSuccessful) {
        throw new Exception('Payment failed. Please try again.');
    }
    
    // Log successful payment
    logSecurityEvent('PAYMENT_SUCCESS', [
        'transaction_id' => $transactionId,
        'amount' => $amount,
        'phone_number' => substr($phoneNumber, -4),
        'description' => $description
    ]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Payment processed successfully',
        'transaction_id' => $transactionId,
        'amount' => $amount,
        'currency' => 'XAF',
        'status' => 'COMPLETED',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    
    // Log failed payment attempts
    logSecurityEvent('PAYMENT_FAILED', [
        'error' => $e->getMessage(),
        'amount' => $input['amount'] ?? null,
        'phone_number' => isset($input['phone_number']) ? substr($input['phone_number'], -4) : null
    ]);
    
    jsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], $statusCode);
}
?>